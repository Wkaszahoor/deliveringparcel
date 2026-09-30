<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

/**
 * Agent PM — Stripe adapter (PM-007/PM-013).
 *
 * - Official tokenized pattern: a PaymentIntent is created server-side and
 *   the browser only ever sees the CLIENT SECRET + publishable key. No card
 *   data (PAN/CVV/CVC/expiry) touches our servers, DB or logs.
 * - The secret key is read exclusively from config('services.stripe.secret')
 *   (env STRIPE_SECRET). Its VALUE is never echoed, logged or returned.
 * - When the key is absent the adapter runs in MOCK mode: no network calls,
 *   payments surface as simulated so flows remain demoable and safe.
 * - Truth about paid/failed arrives ONLY via the signature-verified webhook
 *   (routes/api.php) — browser redirects never mark success (PM-013).
 *
 * stripe/stripe-php v7.128.0 is present in vendor/ (composer.lock) — we use
 * the official \Stripe\StripeClient; no HTTP re-implementation needed.
 */
class StripeGateway implements PaymentGatewayContract
{
    public function code(): string
    {
        return 'stripe';
    }

    public function name(): string
    {
        return 'Stripe';
    }

    public function isConfigured(): bool
    {
        return trim((string) config('services.stripe.secret')) !== '';
    }

    public function publishableKey(): ?string
    {
        $key = trim((string) config('services.stripe.key'));

        return $key !== '' ? $key : null;
    }

    /* ------------------------------------------------ initiate */

    public function initiate(Payment $payment, ?User $customer = null, array $context = []): array
    {
        if (!$this->isConfigured()) {
            // Mock mode — no secrets, no network, clearly labelled as simulated.
            $this->logger()->info('stripe gateway in mock mode (no secret configured)', [
                'payment_id' => $payment->id, 'reference' => $payment->reference,
            ]);

            return [
                'kind'        => 'mock',
                'simulated'   => true,
                'message'     => 'Stripe is not configured on this environment, so this payment is being simulated for testing. It will only settle through the verified webhook/admin path.',
                'client_secret' => null,
                'publishable_key' => $this->publishableKey(),
            ];
        }

        try {
            // Use Stripe Checkout Sessions for better UX (redirect to hosted page).
            // Order-less payments are wallet top-ups; order payments remember
            // which layout started them (legacy / home2) so Stripe returns
            // the customer to the page they came from.
            $isTopup = $payment->order_id === null;
            $origin  = $context['origin'] ?? ($payment->metadata['origin'] ?? 'home2');

            if ($isTopup) {
                $successUrl = route('home2.wallet.topup.return', $payment->id);
                $cancelUrl  = route('home2.wallet');
                $name       = 'Wallet top-up ' . $payment->reference;
                $descr      = 'Add funds to your Delivering Parcel wallet';
            } else {
                $successUrl = route('home2.pay.success', $payment->id);
                $cancelUrl  = $origin === 'legacy'
                    ? route('orders.show', $payment->order_id)
                    : route('home2.pay.show', $payment->order_id);
                $name       = 'Order Payment #' . $payment->reference;
                $descr      = 'Order #' . $payment->order_id;
            }

            $session = $this->client()->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower((string) $payment->currency),
                            'product_data' => [
                                'name'        => $name,
                                'description' => $descr,
                            ],
                            'unit_amount' => $payment->minorUnits(),
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'customer_email' => $customer?->email,
                'metadata' => [
                    'payment_reference' => $payment->reference,
                    'payment_id'        => (string) $payment->id,
                    'order_id'          => (string) $payment->order_id,
                    'purpose'           => (string) ($payment->metadata['purpose'] ?? 'order_payment'),
                ],
                'expires_at' => time() + 1800, // 30 minutes
            ], ['idempotency_key' => 'checkout-' . $payment->reference]);

            return [
                'kind'            => 'checkout',
                'checkout_url'    => $session->url,
                'session_id'      => $session->id,
                'publishable_key' => $this->publishableKey(),
                'simulated'       => false,
            ];
        } catch (\Throwable $e) {
            // Technical detail to logs only — customers see generic copy (PM-011).
            $this->logError('Stripe Checkout Session create failed', $e, $payment);

            throw new PaymentException(
                config('admin_payments_engine.friendly_errors.generic'),
                'stripe initiate failed: ' . $e->getMessage()
            );
        }
    }

    /* ------------------------------------------------ verify */

    public function verify(Payment $payment): array
    {
        if (!$this->isConfigured()) {
            return ['status' => $payment->status, 'raw' => []];
        }

        // Checkout sessions: gateway_transaction_id is null until the webhook
        // fires and populates it with the PaymentIntent ID.  Fall back to
        // checkoutSessionState() so callers can still observe live status.
        if (empty($payment->gateway_transaction_id)) {
            $sessionState = $this->checkoutSessionState($payment);
            if ($sessionState === null) {
                return ['status' => $payment->status, 'raw' => []];
            }

            $map = [
                'complete' => 'paid',
                'expired'  => 'failed',
                'open'     => 'pending',
            ];

            return [
                'status' => $map[$sessionState['status']] ?? 'pending',
                'raw'    => ['stripe_session_status' => $sessionState['status']],
            ];
        }

        try {
            $intent = $this->client()->paymentIntents->retrieve($payment->gateway_transaction_id);

            $map = [
                'succeeded'  => 'paid',
                'processing' => 'processing',
                'requires_payment_method' => 'failed',
                'canceled'   => 'cancelled',
            ];

            return [
                'status' => $map[$intent->status] ?? 'pending',
                'raw'    => ['stripe_status' => $intent->status],
            ];
        } catch (\Throwable $e) {
            $this->logError('PaymentIntent retrieve failed', $e, $payment);

            return ['status' => 'pending', 'raw' => []];
        }
    }

    /**
     * Live state of the Checkout Session backing $payment (open|complete|
     * expired + hosted-page URL while open). Returns null when unknowable
     * (mock mode, no session on file, Stripe unreachable) — callers must
     * treat null as "leave the payment alone", never as success/failure.
     */
    public function checkoutSessionState(Payment $payment): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $sessionId = trim((string) ($payment->metadata['session_id'] ?? ''));
        if ($sessionId === '') {
            return null;
        }

        try {
            $session = $this->client()->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            $this->logError('Checkout Session retrieve failed', $e, $payment);

            return null;
        }

        return [
            'status' => (string) $session->status,
            'paid'   => $session->payment_status === 'paid',
            'url'    => $session->status === 'open' ? (string) $session->url : null,
        ];
    }

    /* ------------------------------------------------ refund */

    public function refund(Payment $payment, ?float $amount = null): array
    {
        if (!$this->isConfigured()) {
            // Mock mode: bookkeeping only; PaymentService records the ledger state.
            return ['ok' => true, 'refunded' => $amount ?? (float) $payment->amount, 'gateway_ref' => null, 'error' => null, 'simulated' => true];
        }

        // Resolve the PaymentIntent ID: direct for old flows, extracted from
        // the checkout session for the new flow.
        $piId = $this->resolvePaymentIntentId($payment);
        if ($piId === null) {
            return ['ok' => false, 'refunded' => 0.0, 'gateway_ref' => null, 'error' => 'No gateway transaction id on this payment.'];
        }

        try {
            $params = ['payment_intent' => $piId];
            if ($amount !== null && $amount > 0) {
                $params['amount'] = (int) round($amount * 100);
            }

            $refund = $this->client()->refunds->create(
                $params,
                ['idempotency_key' => 'rf-' . $payment->reference . '-' . (int) round(($amount ?? (float) $payment->amount) * 100)]
            );

            if ($refund->status === 'failed') {
                return ['ok' => false, 'refunded' => 0.0, 'gateway_ref' => $refund->id, 'error' => 'Stripe reported a failed refund.'];
            }

            return ['ok' => true, 'refunded' => $amount ?? (float) $payment->amount, 'gateway_ref' => $refund->id, 'error' => null];
        } catch (\Throwable $e) {
            $this->logError('Refund failed', $e, $payment);

            return ['ok' => false, 'refunded' => 0.0, 'gateway_ref' => null, 'error' => 'Stripe refund request failed.'];
        }
    }

    /**
     * Resolve the PaymentIntent ID for a payment.  For checkout sessions the
     * PI lives inside the session object; for old-style flows it is stored
     * directly in gateway_transaction_id.
     */
    protected function resolvePaymentIntentId(Payment $payment): ?string
    {
        if (!empty($payment->gateway_transaction_id)) {
            return (string) $payment->gateway_transaction_id;
        }

        // Checkout session: fetch the session and extract its payment_intent.
        $sessionId = trim((string) ($payment->metadata['session_id'] ?? ''));
        if ($sessionId === '') {
            return null;
        }

        try {
            $session = $this->client()->checkout->sessions->retrieve($sessionId);
            $piId = (string) ($session->payment_intent ?? '');

            if ($piId !== '') {
                // Cache it so future verify/refund calls don't need the round-trip.
                $payment->gateway_transaction_id = $piId;
                $payment->save();
            }

            return $piId !== '' ? $piId : null;
        } catch (\Throwable $e) {
            $this->logError('resolvePaymentIntentId failed', $e, $payment);

            return null;
        }
    }

    /* ------------------------------------------------ helpers */

    protected function client(): StripeClient
    {
        return new StripeClient((string) config('services.stripe.secret'));
    }

    /** PM-017: read-only events API for the admin webhook reconciliation page. */
    public function events(): \Stripe\Service\EventService
    {
        return $this->client()->events;
    }

    /** On-demand 'payments' log channel (no config/logging.php edit needed). */
    protected function logger(): \Psr\Log\LoggerInterface
    {
        try {
            return Log::build(['driver' => 'single', 'path' => storage_path('logs/payments.log')]);
        } catch (\Throwable $e) {
            return Log::getLogger();
        }
    }

    protected function logError(string $what, \Throwable $e, ?Payment $payment = null): void
    {
        $this->logger()->error($what . ': ' . $e->getMessage(), [
            'payment_id' => $payment->id ?? null,
            'reference'  => $payment->reference ?? null,
            // NOTE: stripe secret intentionally NOT included
        ]);
    }
}
