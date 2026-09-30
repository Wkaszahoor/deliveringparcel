<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethodRule;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use App\Services\Payments\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Agent PM — customer payment flow on /home2 (PM-003/PM-004/PM-008/PM-011/PM-012).
 *
 * NOTE: Home2Controller itself is owned by another agent and is NOT touched.
 * All endpoints below live in the home2 group (routes/web.php, additive).
 *
 * Security posture:
 *  - amounts are ALWAYS resolved server-side from the accepted offerorder;
 *    any amount/total sent by the browser is discarded (PM-009)
 *  - double "Pay" clicks yield exactly one ledger row (PM-012)
 *  - customers only ever see friendly copy; technical detail goes to the
 *    payments log (PM-011)
 */
class PaymentController extends Controller
{
    protected PaymentService $payments;
    protected WalletService $wallets;

    public function __construct(PaymentService $payments, WalletService $wallets)
    {
        $this->payments = $payments;
        $this->wallets = $wallets;
    }

    /* ---------------- GET /home2/pay/{orderId} — checkout page ---------------- */

    public function show(Request $request, int $orderId)
    {
        $order = $this->ownOrderOrFail($orderId);

        try {
            $resolved = $this->payments->resolveAmount($order->id);
        } catch (PaymentException $e) {
            return view('home2.pay', [
                'order'       => $order,
                'amount'      => null,
                'currency'    => null,
                'methods'     => collect(),
                'payment'     => null,
                'instructions'=> null,
                'gatewayPayload' => null,
                'blocked'     => $e->getMessage(),
            ]);
        }

        $context = $this->payments->serviceContextForOrder($order);
        $methods = collect($this->payments->allowedMethods($context, $resolved['amount']));

        // Admin negotiation override: offer only the method forced for this order.
        $forced = $this->payments->forcedMethodForOrder($order);
        if ($forced) {
            $methods = $methods->filter(fn ($entry) => $entry['method']->code === $forced->code)->values();
        }

        // Wallet (PM-014): attach the live balance and disable the entry
        // when it cannot cover the order amount.
        $methods = collect($this->wallets->decorateMethods($methods->all(), (float) $resolved['amount'], $request->user()));

        $payment = $this->payments->findOpenPayment($order->id);

        $instructions = null;
        $gatewayPayload = null;
        $allBankAccounts = null;
        if ($payment && $payment->status === Payment::STATUS_AWAITING_PAYMENT && $payment->gateway === 'bank_transfer') {
            $gateway = $this->payments->gatewayFor($payment);
            if ($gateway) {
                $instructions = $gateway->instructions($payment);

                // Get all enabled bank accounts for client selection
                $bankAccounts = \App\Models\BankAccount::enabled()->ordered()->get();
                if ($bankAccounts->isNotEmpty()) {
                    $allBankAccounts = [];
                    foreach ($bankAccounts as $account) {
                        $allBankAccounts[] = [
                            'id' => $account->id,
                            'bank_name' => $account->bank_name,
                            'account_title' => $account->account_title,
                            'instructions' => $gateway->instructionsFromAccount($account, $payment),
                        ];
                    }
                }
            }
        }
        if ($payment && $payment->status === Payment::STATUS_PROCESSING) {
            $gatewayPayload = [
                'kind'         => 'processing',
                'simulated'    => ($payment->metadata['gateway_kind'] ?? null) === 'mock',
                'publishable_key' => trim((string) config('services.stripe.key')) ?: null,
            ];
        }

        return view('home2.pay', [
            'order'       => $order,
            'amount'      => $resolved['amount'],
            'currency'    => $resolved['currency'],
            'methods'     => $methods,
            'payment'     => $payment,
            'instructions'=> $instructions,
            'gatewayPayload' => $gatewayPayload,
            'allBankAccounts' => $allBankAccounts,
            'blocked'     => null,
            'statusLabels' => config('admin_payments_engine.states'),
        ]);
    }

    /* ---------------- GET /home2/pay/{orderId}/methods — JSON (PM-003) ---------------- */

    public function methods(Request $request, int $orderId)
    {
        $order = $this->ownOrderOrFail($orderId);

        try {
            $resolved = $this->payments->resolveAmount($order->id);
        } catch (PaymentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        $context = $this->payments->serviceContextForOrder($order);
        $payment = $this->payments->findOpenPayment($order->id);

        $methodEntries = $this->payments->allowedMethods($context, $resolved['amount']);

        // Admin negotiation override: expose only the forced method.
        $forced = $this->payments->forcedMethodForOrder($order);
        if ($forced) {
            $methodEntries = array_values(array_filter(
                $methodEntries,
                fn ($entry) => $entry['method']->code === $forced->code
            ));
        }

        // Wallet (PM-014): live balance + availability for this amount.
        $methodEntries = $this->wallets->decorateMethods($methodEntries, (float) $resolved['amount'], $request->user());

        $methods = collect($methodEntries)->map(function ($entry) use ($payment) {
            return [
                'code'        => $entry['method']->code,
                'name'        => $entry['method']->name,
                'description' => $entry['method']->description,
                'priority'    => $entry['priority'],
                'selected'    => $payment && $payment->payment_method_code === $entry['method']->code,
                'disabled'    => !$entry['available'],
                'reason'      => $entry['reason'],
                'wallet_balance' => $entry['wallet_balance'] ?? null,
            ];
        })->values();

        return response()->json([
            'ok'         => true,
            'order_id'   => $order->id,
            'order_ref'  => $order->order_id,
            'amount'     => $resolved['amount'],
            'currency'   => $resolved['currency'],
            'context'    => $context,
            'selected'   => $payment?->payment_method_code,
            'payment'    => $payment ? [
                'reference' => $payment->reference,
                'status'    => $payment->status,
                'method_locked' => !$payment->methodChangeable(),
            ] : null,
            'methods'    => $methods,
        ]);
    }

    /* ---------------- POST /home2/pay/{orderId}/select-method (PM-004) ---------------- */

    public function selectMethod(Request $request, int $orderId)
    {
        $order = $this->ownOrderOrFail($orderId);
        $data = $request->validate(['method' => 'required|string|max:30']);

        try {
            $resolved = $this->payments->resolveAmount($order->id);
            $context = $this->payments->serviceContextForOrder($order);
            $this->payments->assertMethodSelectable($context, $data['method'], $resolved['amount']);
            $this->payments->assertNoOrderOverride($order, $data['method']);

            $payment = $this->payments->findOpenPayment($order->id);
            if ($payment) {
                // Switch (allowed only pre-processing — service enforces + audits).
                $this->payments->changeMethod($payment, $data['method'], $request->user(), 'customer selection');
            }
        } catch (PaymentException $e) {
            return $this->friendlyFail($request, $e, 'select');
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'selected' => $data['method']])
            : redirect()->route('home2.pay.show', $order->id)->with('success', 'Payment method selected. Continue below to pay.');
    }

    /* ---------------- POST /home2/pay/{orderId}/pay (PM-012 idempotent) ---------------- */

    public function pay(Request $request, int $orderId)
    {
        $order = $this->ownOrderOrFail($orderId);
        $data = $request->validate([
            'method' => 'required|string|max:30',
            // No exists: rule on purpose — if the account was disabled since
            // the page loaded, the gateway silently falls back to the first
            // enabled account instead of failing the payment.
            'bank_account_id' => 'nullable|integer',
            // NOTE: no amount field is accepted — server is authoritative (PM-009).
        ]);

        try {
            // Remember which layout started the payment (legacy order page vs
            // home2) so Stripe returns the customer to the page they came from.
            $context = ['origin' => $request->input('origin') === 'legacy' ? 'legacy' : 'home2'];
            if (!empty($data['bank_account_id'])) {
                $context['bank_account_id'] = (int) $data['bank_account_id'];
            }
            $result = $this->payments->initiatePayment($order, $data['method'], $request->user(), $context);
            $payment = $result['payment'];
            $payload = $result['payload'];
        } catch (PaymentException $e) {
            return $this->friendlyFail($request, $e, 'pay');
        } catch (Throwable $e) {
            $this->payments->logTech('error', 'pay failed: ' . $e->getMessage(), ['order' => $order->id]);
            report($e);

            return $this->friendlyFail(
                $request,
                new PaymentException(config('admin_payments_engine.friendly_errors.generic'), $e->getMessage()),
                'pay'
            );
        }

        $response = [
            'ok'        => true,
            'created'   => $result['created'],
            'payment_id'=> $payment->id,
            'reference' => $payment->reference,
            'status'    => $payment->status,
            'status_url'=> route('home2.pay.status', $payment->id),
            'next'      => $payload['kind'] ?? null,
            'payload'   => $this->publicPayload($payload),
        ];

        return $request->expectsJson()
            ? response()->json($response)
            : redirect()->route('home2.pay.show', $order->id)->with('success', 'Payment started — reference ' . $payment->reference . '. Follow the instructions below.');
    }

    /** Payload for the browser — client_secret/publishable_key are public by
     *  design (Stripe tokenized pattern); no server secrets ever ride along. */
    protected function publicPayload(array $payload): array
    {
        return $payload;
    }

    /* ---------------- POST /home2/pay/{payment}/proof (PM-008) ---------------- */

    public function uploadProof(Request $request, int $paymentId)
    {
        $payment = $this->ownPaymentOrFail($paymentId);

        $proof = config('admin_payments_engine.proof', []);
        $maxKb = (int) ($proof['max_kb'] ?? 5120);
        $mimes = $proof['mimes'] ?? 'jpg,jpeg,png,pdf';

        $data = $request->validate([
            'proof' => 'required|file|mimes:' . $mimes . '|max:' . $maxKb,
        ]);

        try {
            $file = $data['proof'];

            // Random name — original filename never reaches the filesystem.
            // C3: stored on the PRIVATE `local` disk (storage/app/payments-
            // proofs) — receipts are never web-servable; they stream only
            // through the authorized download routes (admin + owning
            // customer). The pathname (not the object) is handed over so
            // FilesystemAdapter streams from it directly — getRealPath()
            // is unreliable for fcgid temp uploads on Windows.
            $name = Str::random(40) . '.' . strtolower($file->getClientOriginalExtension() ?: 'bin');
            Storage::disk(Payment::PROOF_DISK)->putFileAs(Payment::PROOF_DIR, $file->getPathname(), $name);

            $payment = $this->payments->attachProof($payment, Payment::PROOF_DIR . '/' . $name, $request->user());
        } catch (PaymentException $e) {
            return $this->friendlyFail($request, $e, 'proof', $payment->order_id);
        } catch (Throwable $e) {
            $this->payments->logTech('error', 'proof upload failed: ' . $e->getMessage(), ['payment' => $paymentId]);

            return $this->friendlyFail(
                $request,
                new PaymentException('Your receipt could not be uploaded. Please try again.', $e->getMessage()),
                'proof',
                $payment->order_id
            );
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'status' => $payment->status, 'message' => 'Thank you — your receipt was uploaded and is awaiting verification.'])
            : redirect()->route('home2.pay.show', $payment->order_id)->with('success', 'Thank you — your receipt was uploaded and is awaiting verification.');
    }

    /* ---------------- GET /home2/pay/proof/{payment} — receipt download (C3) ---------------- */

    public function downloadProof(Request $request, int $paymentId)
    {
        $payment = $this->ownPaymentOrFail($paymentId);

        $resolved = $payment->proofDiskPath();
        if (!$resolved || !Storage::disk($resolved['disk'])->exists($resolved['path'])) {
            abort(404, 'No receipt on file for this payment.');
        }

        return Storage::disk($resolved['disk'])->download(
            $resolved['path'],
            'receipt-' . $payment->reference . '.' . pathinfo($resolved['path'], PATHINFO_EXTENSION)
        );
    }

    /* ---------------- GET /home2/pay/{payment}/status ---------------- */

    public function status(Request $request, int $paymentId)
    {
        $payment = $this->ownPaymentOrFail($paymentId);

        // Self-heal stuck Stripe checkouts (cooldown-guarded Stripe API call).
        $payment = $this->payments->recoverStuckCheckout($payment);

        $bankInstructions = null;
        if ($payment->status === Payment::STATUS_AWAITING_PAYMENT && $payment->gateway === 'bank_transfer') {
            $gateway = $this->payments->gatewayFor($payment);
            $bankInstructions = $gateway ? $gateway->instructions($payment) : null;
        }

        return response()->json([
            'ok'           => true,
            'reference'    => $payment->reference,
            'status'       => $payment->status,
            'status_label' => $payment->stateLabel(),
            'is_paid'      => $payment->isPaid(),
            'method'       => $payment->payment_method_code,
            'amount'       => (float) $payment->amount,
            'currency'     => $payment->currency,
            'proof_needed' => $payment->status === Payment::STATUS_AWAITING_PAYMENT && $payment->gateway === 'bank_transfer',
            'instructions' => $bankInstructions,
        ]);
    }

    /* ---------------- GET /home2/pay/{payment}/success — Stripe Checkout return ---------------- */

    public function success(Request $request, int $paymentId)
    {
        $payment = $this->ownPaymentOrFail($paymentId);
        $sessionId = $request->query('session_id');
        $order = $this->ownOrderOrFail($payment->order_id);
        $back = $this->payBackUrl($payment, $order->id);

        if (!$sessionId) {
            return redirect()->to($back)
                ->with('error', 'No session ID provided. Please contact support.');
        }

        try {
            // Verify the Checkout Session with Stripe
            $gateway = $this->payments->gatewayFor($payment);
            if (!$gateway || $gateway->code() !== 'stripe') {
                throw new \Exception('Invalid payment gateway');
            }

            // Hard timeouts so a slow Stripe API never hangs the customer's return
            // trip — the pay page re-checks automatically afterwards.
            // (stripe-php 7.x takes timeouts on the curl client, NOT in the
            // StripeClient config array — 'curl_options' there throws.)
            \Stripe\HttpClient\CurlClient::instance()->setConnectTimeout(4);
            \Stripe\HttpClient\CurlClient::instance()->setTimeout(8);
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            // Check if the payment was successful
            if ($session->payment_status === 'paid' && $session->status === 'complete') {
                // The webhook stays the source of truth, but if it hasn't landed
                // yet the session we just fetched from Stripe IS proof of payment —
                // finalize now so the customer isn't stuck "verifying".
                if (!$payment->isPaid()) {
                    try {
                        $fresh = Payment::find($payment->id);
                        if ($fresh && !$fresh->isPaid()) {
                            $this->payments->transition($fresh, Payment::STATUS_PAID, 'system', [
                                'verified_via'         => 'checkout_return',
                                'stripe_session_id'    => $sessionId,
                                'stripe_payment_intent' => $session->payment_intent ?? null,
                            ]);
                        }
                        $payment->refresh();
                    } catch (\Throwable $t) {
                        // Illegal-transition race (webhook arrived first) is fine;
                        // anything else just leaves the webhook to finish the job.
                        $this->payments->logTech('warning', 'checkout-return finalize skipped: ' . $t->getMessage(), [
                            'payment_id' => $paymentId,
                        ]);
                    }
                }

                if ($payment->isPaid()) {
                    return redirect()->to($back)
                        ->with('success', 'Payment completed successfully! Thank you.');
                }

                return redirect()->to($back)
                    ->with('success', 'Payment is being processed. This page will keep checking automatically.');
            }

            // Payment not successful
            return redirect()->to($back)
                ->with('error', 'Payment was not completed. Please try again or contact support.');

        } catch (\Throwable $e) {
            $this->payments->logTech('error', 'stripe checkout success handling failed: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'session_id' => $sessionId,
            ]);

            return redirect()->to($back)
                ->with('error', 'We could not reach Stripe to verify instantly — your payment may still have completed. This page keeps checking automatically; if it still shows unpaid after a few minutes, contact support with reference: ' . $payment->reference);
        }
    }

    /* ---------------- helpers ---------------- */

    /**
     * Where the customer should land after a Stripe return: the layout that
     * started the payment (metadata.origin) — legacy order page or home2 pay.
     */
    protected function payBackUrl(Payment $payment, int $orderId): string
    {
        return ($payment->metadata['origin'] ?? 'home2') === 'legacy'
            ? route('orders.show', $orderId)
            : route('home2.pay.show', $orderId);
    }

    /** Orders row scoped to the authenticated customer (or 404). */
    protected function ownOrderOrFail(int $orderId)
    {
        $order = DB::table('orders')->where('id', $orderId)->where('user_id', auth()->id())->first();

        if (!$order) {
            abort(404, 'Order not found.');
        }

        return $order;
    }

    protected function ownPaymentOrFail(int $paymentId): Payment
    {
        $payment = Payment::where('id', $paymentId)->where('user_id', auth()->id())->first();

        if (!$payment) {
            abort(404, 'Payment not found.');
        }

        return $payment;
    }

    /** Friendly copy to the user; technical detail to the payments log only. */
    protected function friendlyFail(Request $request, PaymentException $e, string $stage, ?int $orderId = null)
    {
        if ($e->technical) {
            $this->payments->logTech('error', $stage . ' rejected: ' . $e->technical);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        $orderId = $orderId ?? (int) $request->route()->parameter('orderId');

        return redirect()->route('home2.pay.show', $orderId)->with('error', $e->getMessage());
    }
}
