<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use App\Services\Payments\WalletService;
use Illuminate\Http\Request;
use Throwable;

/**
 * Agent WL — customer wallet on /home2 (PM-014).
 *
 * Security posture mirrors Home2\PaymentController:
 *  - top-up amounts are clamped server-side by PaymentService against the
 *    wallet min/max settings (client values are only a request);
 *  - the browser NEVER marks a top-up successful — only the signature-
 *    verified Stripe webhook (or an admin verification) transitions the
 *    top-up payment to paid, and only then is the wallet credited;
 *  - the card page shows just the publishable key + client secret
 *    (tokenized pattern — no card data touches our servers).
 */
class WalletController extends Controller
{
    protected PaymentService $payments;
    protected WalletService $wallets;

    public function __construct(PaymentService $payments, WalletService $wallets)
    {
        $this->payments = $payments;
        $this->wallets = $wallets;
    }

    /* ---------------- GET /home2/wallet — balance + history + top-up ---------------- */

    public function index(Request $request)
    {
        $user = $request->user();
        $wallet = $this->wallets->walletFor($user);
        $wallet->refresh(); // balance may have just been credited by the webhook

        $transactions = $wallet->transactions()->paginate(15);

        // Any in-flight top-up (processing) is surfaced so the page can poll.
        $pendingTopup = Payment::query()
            ->whereNull('order_id')
            ->where('user_id', $user->id)
            ->where('metadata->purpose', 'wallet_topup')
            ->whereIn('status', [Payment::STATUS_PROCESSING, Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_AWAITING_VERIFICATION])
            ->orderByDesc('id')
            ->first();

        return view('home2.wallet', [
            'wallet'        => $wallet,
            'transactions'  => $transactions,
            'pendingTopup'  => $pendingTopup,
            'enabled'       => $this->wallets->enabled(),
            'min'           => (float) Setting::get('wallet_topup_min', 10),
            'max'           => (float) Setting::get('wallet_topup_max', 5000),
            'currency'      => (string) Setting::get('business_currency', 'USD'),
            'publishableKey'=> trim((string) config('services.stripe.key')) ?: null,
            'statusLabels'  => config('admin_payments_engine.states'),
        ]);
    }

    /* ---------------- POST /home2/wallet/topup — create + drive the top-up payment ---------------- */

    public function topup(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:1000000',
        ]);

        try {
            $result = $this->payments->initiateTopup($request->user(), (float) $data['amount'], 'stripe');
        } catch (PaymentException $e) {
            if ($e->technical) {
                $this->payments->logTech('error', 'topup rejected: ' . $e->technical);
            }

            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            $this->payments->logTech('error', 'topup failed: ' . $e->getMessage());
            report($e);

            return response()->json(['ok' => false, 'message' => config('admin_payments_engine.friendly_errors.generic')], 422);
        }

        $payment = $result['payment'];
        $payload = $result['payload'];

        return response()->json([
            'ok'         => true,
            'created'    => $result['created'],
            'payment_id' => $payment->id,
            'reference'  => $payment->reference,
            'status'     => $payment->status,
            'next'       => $payload['kind'] ?? null,
            'payload'    => $payload, // client_secret + publishable_key only (public by design)
        ]);
    }

    /* ---------------- GET /home2/wallet/topup/return/{payment} — Stripe Checkout return ---------------- */

    /**
     * Customer lands here after paying on Stripe's hosted page. Verifies the
     * session directly with Stripe (hard timeouts) and finalizes instantly
     * when possible — transition() credits the wallet (idempotent), so the
     * webhook replaying moments later is harmless.
     */
    public function topupReturn(Request $request, int $paymentId)
    {
        $payment = Payment::where('id', $paymentId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $sessionId = $request->query('session_id');
        if ($sessionId && !$payment->isPaid()) {
            try {
                // Timeouts on the curl client (stripe-php 7.x rejects them in
                // the StripeClient config array).
                \Stripe\HttpClient\CurlClient::instance()->setConnectTimeout(4);
                \Stripe\HttpClient\CurlClient::instance()->setTimeout(8);
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $session = $stripe->checkout->sessions->retrieve($sessionId);

                if ($session->payment_status === 'paid' && $session->status === 'complete') {
                    $fresh = Payment::find($payment->id);
                    if ($fresh && !$fresh->isPaid()) {
                        $this->payments->transition($fresh, Payment::STATUS_PAID, 'system', [
                            'verified_via'          => 'checkout_return',
                            'stripe_session_id'     => $sessionId,
                            'stripe_payment_intent' => $session->payment_intent ?? null,
                        ]);
                    }
                    $payment->refresh();
                }
            } catch (Throwable $e) {
                // Leave the webhook to settle it — never fail the customer here.
                $this->payments->logTech('warning', 'topup return verify failed: ' . $e->getMessage(), [
                    'payment_id' => $paymentId,
                ]);
            }
        }

        return redirect()->route('home2.wallet')->with(
            $payment->isPaid() ? 'success' : 'error',
            $payment->isPaid()
                ? 'Top-up completed — your balance has been updated.'
                : 'We could not confirm the top-up instantly. Your balance updates automatically the moment Stripe confirms it; refresh in a minute if it still looks pending.'
        );
    }

    /* ---------------- GET /home2/wallet/refresh — light polling endpoint ---------------- */

    public function refresh(Request $request)
    {
        $user = $request->user();
        $wallet = $this->wallets->walletFor($user);

        $lastTopup = Payment::query()
            ->whereNull('order_id')
            ->where('user_id', $user->id)
            ->where('metadata->purpose', 'wallet_topup')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'ok'       => true,
            'balance'  => (float) $wallet->balance,
            'currency' => $wallet->currency,
            'locked'   => (bool) $wallet->is_locked,
            'last_topup' => $lastTopup ? [
                'reference' => $lastTopup->reference,
                'status'    => $lastTopup->status,
                'is_paid'   => $lastTopup->isPaid(),
            ] : null,
        ]);
    }
}
