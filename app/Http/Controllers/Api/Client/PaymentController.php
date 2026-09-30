<?php
/* mobile-api-v4 — upload-verification marker (2026-08-27) */

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function methods(): JsonResponse
    {
        // payment_methods has is_enabled + priority (NOT is_active/sort_order)
        $methods = PaymentMethod::enabled()
            ->orderByDesc('priority')
            ->get()
            ->map(fn($m) => [
                'id'   => $m->id,
                'code' => $m->code,
                'name' => $m->name,
            ]);

        // Enabled bank accounts — the mobile app renders the Bank A / Bank B
        // selector for the bank-transfer method (web pay-page parity).
        $bankAccounts = \App\Models\BankAccount::enabled()->ordered()->get()
            ->map(fn($a) => [
                'id'             => $a->id,
                'bank_name'      => $a->bank_name,
                'account_title'  => $a->account_title,
                'account_number' => $a->account_number,
                'iban'           => $a->iban,
                'swift_code'     => $a->swift_code,
                'currency'       => $a->currency,
            ]);

        return response()->json([
            'methods'       => $methods,
            'bank_accounts' => $bankAccounts,
        ]);
    }

    public function initiate(Request $request, Orders $order): JsonResponse
    {
        // (int) cast: strict === breaks when PDO returns user_id as string.
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $data = $request->validate([
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            // Bank A / Bank B selection for bank transfers (web pay-page parity).
            'bank_account_id'   => 'nullable|integer|exists:bank_accounts,id',
        ]);

        try {
            $method = PaymentMethod::findOrFail($data['payment_method_id']);

            // Real engine API: initiatePayment(order, methodCode, user, context).
            // Server recomputes the amount from the accepted offer (never trusts client).
            $context = ['origin' => 'mobile'];
            if (!empty($data['bank_account_id'])) {
                $context['bank_account_id'] = (int) $data['bank_account_id'];
            }
            $result = app(PaymentService::class)->initiatePayment(
                $order,
                $method->code,
                $request->user(),
                $context
            );

            $payload = $result['payload'] ?? [];

            return response()->json([
                'message'      => 'Payment initiated.',
                'payment_id'   => $result['payment']->id ?? null,
                'next'         => $payload['kind'] ?? null,
                'checkout_url' => $payload['checkout_url'] ?? null,
                // Bank transfer: full account details + transfer reference so
                // the app can render the instructions screen (gateway payload).
                'instructions' => $payload['instructions'] ?? null,
                'status'       => $result['payment']->status ?? 'pending',
            ]);
        } catch (\App\Services\Payments\PaymentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Payment initiation failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/client/orders/{order}/payment-proof
     *
     * Mobile equivalent of the web pay-page receipt upload (PM-008): the
     * client attaches a photo/screenshot of their bank transfer. Stored on
     * the PRIVATE proofs disk with a random name; PaymentService::attachProof
     * moves the payment to awaiting_verification and notifies admins — the
     * admin app's Payment Queue then shows the receipt for verification.
     */
    public function uploadProof(Request $request, Orders $order): JsonResponse
    {
        // (int) cast: strict === breaks when PDO returns user_id as string.
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404,
            'Order not found or not accessible from this account.');

        $proof = config('admin_payments_engine.proof', []);
        $maxKb = (int) ($proof['max_kb'] ?? 5120);
        $mimes = $proof['mimes'] ?? 'jpg,jpeg,png,pdf';

        $data = $request->validate([
            'proof' => 'required|file|mimes:' . $mimes . '|max:' . $maxKb,
        ]);

        $payment = Payment::where('order_id', $order->id)
            ->whereIn('status', Payment::OPEN_STATUSES)
            ->orderByDesc('id')
            ->first();

        abort_unless($payment, 404,
            'No open payment for this order — choose a payment method first.');

        try {
            $file = $data['proof'];

            // Random name — the original filename never reaches the disk.
            // C3: private disk only; receipts stream through authorized
            // routes (admin queue / owning customer), never public URLs.
            $name = Str::random(40) . '.' . strtolower($file->getClientOriginalExtension() ?: 'bin');
            Storage::disk(Payment::PROOF_DISK)->putFileAs(Payment::PROOF_DIR, $file->getPathname(), $name);

            $payment = app(PaymentService::class)->attachProof(
                $payment,
                Payment::PROOF_DIR . '/' . $name,
                $request->user()
            );

            return response()->json([
                'ok'      => true,
                'status'  => $payment->status,
                'message' => 'Thank you — your receipt was uploaded and is awaiting verification.',
            ]);
        } catch (\App\Services\Payments\PaymentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Your receipt could not be uploaded. Please try again.',
            ], 422);
        }
    }

    public function status(Request $request, Orders $order): JsonResponse
    {
        // (int) cast: strict === breaks when PDO returns user_id as string.
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        $payment = Payment::where('order_id', $order->id)
            ->orderByDesc('id')
            ->first();

        if (!$payment) {
            return response()->json(['status' => 'no_payment', 'payment' => null]);
        }

        // Auto-verify if still in open status
        if ($payment->isOpen() && $payment->gateway === 'stripe') {
            try {
                $verified = app(PaymentService::class)->verifyPayment($payment->id);
                if ($verified) {
                    $payment->refresh();
                }
            } catch (\Throwable $e) {
                // Best-effort verification
            }
        }

        return response()->json([
            'status'  => $payment->status,
            'payment' => [
                'id'             => $payment->id,
                'amount'         => $payment->amount,
                'currency'       => $payment->currency,
                'status'         => $payment->status,
                'state_label'    => $payment->stateLabel(),
                'gateway'        => $payment->gateway,
                'paid_at'        => optional($payment->paid_at)->toDateTimeString(),
                'failure_reason' => $payment->friendlyFailure(),
            ],
        ]);
    }
}
