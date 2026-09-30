<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Webhook;
use Throwable;

/**
 * Agent PM — Stripe webhook endpoint (PM-013).
 *
 * - Signature-verified with STRIPE_WEBHOOK_SECRET (config services.stripe.webhook_secret).
 *   Invalid/missing signature → 400, event is never processed.
 * - Idempotent: event ids are recorded in payment metadata; replays are no-ops.
 * - Server-authoritative amounts (PM-009): a succeeded intent whose amount
 *   does not match the ledger row is quarantined (logged, not marked paid).
 * - Browser redirects NEVER set paid — this endpoint is the only automated
 *   path to `paid` for card payments.
 */
class StripeWebhookController extends Controller
{
    public const SUPPORTED_EVENTS = [
        'payment_intent.succeeded',
        'payment_intent.payment_failed',
        'payment_intent.canceled',
        'charge.refunded',
        'checkout.session.completed',
        'checkout.session.async_payment_succeeded',
        'checkout.session.async_payment_failed',
        'checkout.session.expired',
    ];

    protected PaymentService $payments;

    public function __construct(PaymentService $payments)
    {
        $this->payments = $payments;
    }

    public function __invoke(Request $request)
    {
        $secret = trim((string) config('services.stripe.webhook_secret'));
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');

        if ($secret === '') {
            $this->payments->logTech('warning', 'stripe webhook received but no webhook secret configured — rejected');
            $this->logEvent('unsig-' . uniqid(), null, 'error', null, 'rejected: no webhook secret configured', $payload);

            return response()->json(['ok' => false, 'error' => 'webhook not configured'], 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (Throwable $e) {
            $this->payments->logTech('error', 'stripe webhook signature verification failed: ' . $e->getMessage());
            $this->logEvent('sigfail-' . uniqid(), null, 'signature_failed', null, 'invalid or missing signature', $payload);

            return response()->json(['ok' => false, 'error' => 'invalid signature'], 400);
        }

        $eventId = (string) $event->id;
        $type = (string) $event->type;

        if (!in_array($type, self::SUPPORTED_EVENTS, true)) {
            $this->logEvent($eventId, $type, 'ignored', null, 'event type not in allow-list', $payload, $event->api_version ?? null);

            return response()->json(['ok' => true, 'ignored' => $type]);
        }

        try {
            $outcome = DB::transaction(function () use ($event, $eventId, $type) {
                $payment = $this->resolvePayment($event);

                if (!$payment) {
                    return ['ok' => true, 'result' => 'no matching payment'];
                }

                // Lock the row: concurrent webhook deliveries serialize here.
                $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->first();

                // Idempotency: replayed events are no-ops (PM-013).
                $seen = $payment->metadata['processed_event_ids'] ?? [];
                if (in_array($eventId, $seen, true)) {
                    return ['ok' => true, 'result' => 'duplicate event ignored'];
                }
                $seen[] = $eventId;
                $payment->mergeMetadata(['processed_event_ids' => array_slice($seen, -100)]);

                $intent = $event->data->object ?? null;

                switch ($type) {
                    case 'payment_intent.succeeded':
                        if (in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_PROCESSING], true)) {
                            // Amount truth check (PM-009): ledger row is authoritative.
                            $intentAmount = (int) ($intent->amount ?? 0);
                            if ($intentAmount > 0 && $intentAmount !== $payment->minorUnits()) {
                                $this->payments->logTech('error', 'webhook amount mismatch — payment quarantined', [
                                    'payment'     => $payment->reference,
                                    'expected'    => $payment->minorUnits(),
                                    'stripe_sent' => $intentAmount,
                                ]);
                                $payment->mergeMetadata(['amount_mismatch' => [
                                    'expected' => $payment->minorUnits(), 'received' => $intentAmount, 'at' => now()->toDateTimeString(),
                                ]]);
                                $payment->save();

                                return ['ok' => true, 'result' => 'amount mismatch — not marked paid'];
                            }

                            $payment->gateway_transaction_id = $payment->gateway_transaction_id ?: (string) ($intent->id ?? '');
                            $payment->save();

                            $this->payments->transition($payment, Payment::STATUS_PAID, 'webhook', [
                                'webhook_event' => ['id' => $eventId, 'type' => $type, 'at' => now()->toDateTimeString()],
                            ]);

                            return ['ok' => true, 'result' => 'paid'];
                        }

                        if ($payment->status === Payment::STATUS_PAID) {
                            $payment->save(); // persist the dedupe marker only

                            return ['ok' => true, 'result' => 'already paid'];
                        }

                        $payment->save();

                        return ['ok' => true, 'result' => 'state ' . $payment->status . ' not payable'];

                    case 'payment_intent.payment_failed':
                        if (in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_PROCESSING], true)) {
                            $payment->failure_reason = 'gateway reported payment_intent.payment_failed';
                            $this->payments->transition($payment, Payment::STATUS_FAILED, 'webhook');

                            return ['ok' => true, 'result' => 'failed'];
                        }
                        $payment->save();

                        return ['ok' => true, 'result' => 'ignored in state ' . $payment->status];

                    case 'payment_intent.canceled':
                        if (in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_PROCESSING], true)) {
                            $this->payments->transition($payment, Payment::STATUS_CANCELLED, 'webhook');

                            return ['ok' => true, 'result' => 'cancelled'];
                        }
                        $payment->save();

                        return ['ok' => true, 'result' => 'ignored in state ' . $payment->status];

                    case 'checkout.session.completed':
                    case 'checkout.session.async_payment_succeeded':
                        // Checkout Session completed - extract payment intent and handle as succeeded
                        $session = $event->data->object ?? null;
                        $paymentIntentId = (string) ($session->payment_intent ?? '');

                        if ($paymentIntentId && in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_PROCESSING], true)) {
                            // Update the gateway transaction ID with the payment intent ID
                            $payment->gateway_transaction_id = $paymentIntentId;
                            $payment->save();

                            // Fetch the payment intent to get the amount for verification
                            try {
                                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                                $intent = $stripe->paymentIntents->retrieve($paymentIntentId);

                                // Amount truth check (PM-009)
                                $intentAmount = (int) ($intent->amount ?? 0);
                                if ($intentAmount > 0 && $intentAmount !== $payment->minorUnits()) {
                                    $this->payments->logTech('error', 'checkout webhook amount mismatch — payment quarantined', [
                                        'payment'     => $payment->reference,
                                        'expected'    => $payment->minorUnits(),
                                        'stripe_sent' => $intentAmount,
                                    ]);
                                    $payment->mergeMetadata(['amount_mismatch' => [
                                        'expected' => $payment->minorUnits(), 'received' => $intentAmount, 'at' => now()->toDateTimeString(),
                                    ]]);
                                    $payment->save();

                                    return ['ok' => true, 'result' => 'amount mismatch — not marked paid'];
                                }

                                $this->payments->transition($payment, Payment::STATUS_PAID, 'webhook', [
                                    'webhook_event' => ['id' => $eventId, 'type' => $type, 'at' => now()->toDateTimeString()],
                                ]);

                                return ['ok' => true, 'result' => 'paid via checkout'];
                            } catch (\Throwable $e) {
                                $this->payments->logTech('error', 'checkout webhook failed to fetch intent: ' . $e->getMessage());
                                $payment->save();
                                return ['ok' => true, 'result' => 'failed to fetch intent'];
                            }
                        }

                        if ($payment->status === Payment::STATUS_PAID) {
                            $payment->save();
                            return ['ok' => true, 'result' => 'already paid'];
                        }

                        $payment->save();
                        return ['ok' => true, 'result' => 'checkout completed but ignored in state ' . $payment->status];

                    case 'checkout.session.async_payment_failed':
                    case 'checkout.session.expired':
                        // Checkout Session failed/expired without payment
                        if (in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_PROCESSING], true)) {
                            $payment->failure_reason = 'gateway reported ' . $type;
                            $this->payments->transition($payment, Payment::STATUS_FAILED, 'webhook');

                            return ['ok' => true, 'result' => 'failed via checkout'];
                        }
                        $payment->save();
                        return ['ok' => true, 'result' => 'checkout failed but ignored in state ' . $payment->status];

                    case 'charge.refunded':
                        $refundedTotal = isset($intent->amount_refunded) ? ((int) $intent->amount_refunded) / 100 : null;
                        if ($refundedTotal !== null && in_array($payment->status, [Payment::STATUS_PAID, Payment::STATUS_PARTIALLY_REFUNDED], true)) {
                            $payment->amount_refunded = round(min($refundedTotal, (float) $payment->amount), 2);
                            $target = ((float) $payment->amount_refunded >= (float) $payment->amount - 0.001)
                                ? Payment::STATUS_REFUNDED
                                : Payment::STATUS_PARTIALLY_REFUNDED;
                            if ($payment->status !== $target) {
                                $this->payments->transition($payment, $target, 'webhook');
                            } else {
                                $payment->save();
                            }

                            return ['ok' => true, 'result' => 'refund synced'];
                        }
                        $payment->save();

                        return ['ok' => true, 'result' => 'ignored in state ' . $payment->status];
                }

                $payment->save();

                return ['ok' => true, 'result' => 'unhandled'];
            });
        } catch (\App\Services\Payments\PaymentException $e) {
            // Illegal state machine move from a webhook — log, never 500 (Stripe retries would spam).
            $this->payments->logTech('error', 'webhook transition rejected: ' . ($e->technical ?? $e->getMessage()));
            $this->logEvent($eventId, $type, 'error', null, 'transition rejected: ' . $e->getMessage(), $payload, $event->api_version ?? null);

            return response()->json(['ok' => true, 'result' => 'transition rejected']);
        } catch (Throwable $e) {
            $this->payments->logTech('error', 'webhook processing failed: ' . $e->getMessage(), ['event' => $eventId]);
            $this->logEvent($eventId, $type, 'error', null, 'processing error: ' . $e->getMessage(), $payload, $event->api_version ?? null);

            return response()->json(['ok' => false, 'error' => 'processing error'], 500);
        }

        $this->payments->logTech('info', 'stripe webhook processed', ['event' => $eventId, 'type' => $type, 'outcome' => $outcome['result'] ?? null]);

        $payment = $this->resolvePayment($event);
        $this->logEvent($eventId, $type, 'processed', $payment?->id, (string) ($outcome['result'] ?? 'processed'), $payload, $event->api_version ?? null);

        return response()->json($outcome);
    }

    /**
     * PM-017: persist every webhook delivery (outcome + full payload) so the
     * admin can view the complete history and match it against the Stripe
     * account. Logging must NEVER break payment processing.
     */
    protected function logEvent(string $eventId, ?string $type, string $status, ?int $paymentId, string $result, ?string $payload = null, ?string $apiVersion = null): void
    {
        try {
            \App\Models\WebhookEvent::updateOrCreate(
                ['event_id' => $eventId],
                [
                    'gateway'      => 'stripe',
                    'type'         => $type,
                    'api_version'  => $apiVersion,
                    'status'       => $status,
                    'payment_id'   => $paymentId,
                    'result'       => \Illuminate\Support\Str::limit($result, 250),
                    'payload'      => $payload !== null && $payload !== '' ? json_decode($payload, true) : null,
                    'received_at'  => now(),
                ]
            );
        } catch (Throwable $e) {
            $this->payments->logTech('error', 'webhook history write failed: ' . $e->getMessage(), ['event' => $eventId]);
        }
    }

    /** Locate the ledger row for an event — by intent/session id first, then metadata reference. */
    protected function resolvePayment($event): ?Payment
    {
        $obj = $event->data->object ?? null;
        $eventId = (string) ($obj->id ?? '');
        $type = (string) $event->type;
        $reference = (string) ($obj->metadata->payment_reference ?? '');

        $query = Payment::query();

        if ($eventId !== '') {
            // For checkout sessions, first try to find by metadata session_id
            if (str_contains($type, 'checkout.session')) {
                $match = (clone $query)->where('metadata->session_id', $eventId)->first();
                if ($match) {
                    return $match;
                }

                // For checkout sessions, also try payment_intent field
                $paymentIntentId = (string) ($obj->payment_intent ?? '');
                if ($paymentIntentId !== '') {
                    $match = (clone $query)->where('gateway_transaction_id', $paymentIntentId)->first();
                    if ($match) {
                        return $match;
                    }
                }
            }

            // For payment intents, try gateway_transaction_id
            $match = (clone $query)->where('gateway_transaction_id', $eventId)->first();
            if ($match) {
                return $match;
            }
        }

        if ($reference !== '') {
            return (clone $query)->where('reference', $reference)->first();
        }

        return null;
    }
}
