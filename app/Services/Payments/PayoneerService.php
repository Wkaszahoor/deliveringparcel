<?php

namespace App\Services\Payments;

use App\Models\Orders;
use App\Models\PayoneerRequest;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\TaskNotification;
use App\Services\AuditLogger;
use App\Support\LinkFormat;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Payoneer "payment link" flow (manual mode, 2026-09-12).
 *
 * Payoneer business accounts expose no self-serve link-generation API, so the
 * link itself is produced by an admin in the Payoneer dashboard ("Request a
 * Payment") and pasted into the admin inbox. Everything else reuses the
 * payments engine so the ledger, proof handling, verification and the
 * order/offer paid-sync behave EXACTLY like the bank-transfer flow:
 *
 *   client requests  → PayoneerRequest(requested)           [no Payment row yet]
 *   admin sends link → Payment(awaiting_payment) created     [request(link_sent)]
 *   client uploads   → PaymentService::attachProof           [request(proof_submitted)]
 *   admin verifies   → PaymentService::verifyBankPayment / markBankPaymentReceived
 *
 * If Payoneer later grants API access, only sendLink()'s body changes — every
 * other step already runs through the engine.
 */
class PayoneerService
{
    public function __construct(protected PaymentService $payments)
    {
    }

    /* ------------------------------------------------------------------
     * Settings
     * ------------------------------------------------------------------ */

    public function enabled(): bool
    {
        return Setting::getBool('payoneer_enabled', false);
    }

    public function proofRequired(): bool
    {
        return Setting::getBool('payoneer_proof_required', true);
    }

    public function instructions(): string
    {
        return (string) Setting::get('payoneer_instructions',
            'Pay securely with your Payoneer account. Request a payment link, complete the payment on Payoneer, then upload the payment screenshot as proof.');
    }

    /* ------------------------------------------------------------------
     * Client actions
     * ------------------------------------------------------------------ */

    /** Client asks admin to generate a Payoneer link for this order's amount. */
    public function requestLink(Orders $order, User $client): PayoneerRequest
    {
        if (!$this->enabled()) {
            throw new PaymentException('Payoneer payments are currently disabled.');
        }

        if ((int) $order->user_id !== (int) $client->id) {
            throw new PaymentException('This order does not belong to your account.');
        }

        // Amount is server-authoritative (same source the pay page uses).
        $resolved = $this->payments->resolveAmount($order->id);

        $active = PayoneerRequest::where('order_id', $order->id)
            ->whereIn('status', PayoneerRequest::ACTIVE_STATUSES)
            ->first();
        if ($active) {
            throw new PaymentException('A Payoneer payment request is already in progress for this order.');
        }

        $request = PayoneerRequest::create([
            'order_id'     => $order->id,
            'user_id'      => $client->id,
            'amount'       => $resolved['amount'],
            'currency'     => $resolved['currency'],
            'status'       => PayoneerRequest::STATUS_REQUESTED,
            'requested_at' => now(),
        ]);

        $this->notifyAdmins(
            'Payoneer link requested for order #' . $order->order_id,
            $order,
            $client->name . ' requested a Payoneer payment link for ' . $resolved['currency'] . ' ' . number_format($resolved['amount'], 2) . '. Generate the link in your Payoneer account and send it from Admin → Payoneer Payments.'
        );

        return $request;
    }

    /**
     * Admin pastes the Payoneer link → creates the ledger Payment row and
     * notifies the client. This is the ONLY step that touches the payments
     * table; the double-payment guard mirrors PaymentService::initiatePayment.
     */
    public function sendLink(PayoneerRequest $request, User $admin, string $url, ?string $note = null): Payment
    {
        if ($request->status !== PayoneerRequest::STATUS_REQUESTED) {
            throw new PaymentException('This request is not waiting for a link.');
        }

        $url = LinkFormat::normalize(trim($url));
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
            throw new PaymentException('That does not look like a valid Payoneer payment link (it must start with http/https).');
        }

        $order = Orders::findOrFail($request->order_id);
        $client = User::findOrFail($request->user_id);

        // Same guard as initiatePayment: no second payment method may open
        // while one is already in a blocking state.
        $blocked = Payment::query()
            ->where('order_id', $order->id)
            ->whereIn('status', [
                Payment::STATUS_PAID,
                Payment::STATUS_AWAITING_VERIFICATION,
                Payment::STATUS_PROCESSING,
                Payment::STATUS_REFUNDED,
                Payment::STATUS_PARTIALLY_REFUNDED,
            ])
            ->exists();
        if ($blocked) {
            throw new PaymentException('This order already has a paid or in-verification payment — sending another link is blocked.');
        }

        $payment = DB::transaction(function () use ($order, $client, $request, $url, $note, $admin) {
            // Reuse an existing OPEN payoneer payment if the request was
            // re-opened after a reject (never stack attempts).
            $payment = $request->payment_id
                ? Payment::find($request->payment_id)
                : null;

            if (!$payment || !in_array($payment->status, Payment::OPEN_STATUSES, true)) {
                $attempt = ((int) Payment::where('order_id', $order->id)->max('attempt')) + 1;

                $payment = Payment::create([
                    'reference'           => $this->buildReference($order->id, $attempt),
                    'order_id'            => $order->id,
                    'user_id'             => $client->id,
                    'attempt'             => $attempt,
                    'amount'              => $request->amount,
                    'currency'            => $request->currency,
                    'payment_method_code' => 'payoneer',
                    'gateway'             => 'payoneer',
                    'status'              => Payment::STATUS_AWAITING_PAYMENT,
                    'status_set_by'       => 'admin',
                    'initiated_at'        => now(),
                    'metadata'            => [
                        'purpose'         => 'payoneer_link',
                        'payoneer_link'   => $url,
                        'link_note'       => $note,
                        'amount_source'   => 'payoneer_requests:' . $request->id,
                        'origin'          => 'payoneer',
                    ],
                ]);

                AuditLogger::log($payment, 'created');
            } else {
                $payment->mergeMetadata(['payoneer_link' => $url, 'link_note' => $note]);
                $payment->save();
            }

            $request->update([
                'payment_id'    => $payment->id,
                'status'        => PayoneerRequest::STATUS_LINK_SENT,
                'link_url'      => $url,
                'link_note'     => $note,
                'link_sent_at'  => now(),
                'handled_by'    => $admin->id,
            ]);

            return $payment;
        });

        $this->notifyClient($client, $order,
            'Your Payoneer payment link is ready for order #' . $order->order_id,
            'Open your order page and pay via the Payoneer link, then upload the payment screenshot as proof.'
        );

        return $payment;
    }

    /**
     * Client uploads the Payoneer screenshot — stored on the PRIVATE proof
     * disk exactly like bank receipts, then handed to PaymentService so the
     * awaiting_verification transition + admin notification are identical.
     */
    public function submitProof(PayoneerRequest $request, User $client, UploadedFile $file): Payment
    {
        if ($request->status !== PayoneerRequest::STATUS_LINK_SENT || (int) $request->user_id !== (int) $client->id) {
            throw new PaymentException('No Payoneer link is waiting for your proof right now.');
        }

        $payment = Payment::findOrFail($request->payment_id);
        if (!$payment || !in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_AWAITING_VERIFICATION], true)) {
            throw new PaymentException('This payment can no longer receive a receipt.');
        }

        $proof = config('admin_payments_engine.proof', []);
        $maxKb = (int) ($proof['max_kb'] ?? 5120);
        $mimes = $proof['mimes'] ?? 'jpg,jpeg,png,pdf';

        $rules = ['proof' => 'required|file|mimes:' . $mimes . '|max:' . $maxKb];
        $data = validator(['proof' => $file], $rules)->validate();

        // Random name — original filename never reaches the filesystem (C3).
        $name = Str::random(40) . '.' . strtolower($file->getClientOriginalExtension() ?: 'bin');
        Storage::disk(Payment::PROOF_DISK)->putFileAs(Payment::PROOF_DIR, $file->getPathname(), $name);

        $payment = $this->payments->attachProof($payment, Payment::PROOF_DIR . '/' . $name, $client);

        $request->update([
            'status'             => PayoneerRequest::STATUS_PROOF_SUBMITTED,
            'proof_submitted_at' => now(),
        ]);

        return $payment;
    }

    /** Proof-optional mode: client just declares "I paid" — admin reconciles. */
    public function markPaidByClient(PayoneerRequest $request, User $client): PayoneerRequest
    {
        if ($request->status !== PayoneerRequest::STATUS_LINK_SENT || (int) $request->user_id !== (int) $client->id) {
            throw new PaymentException('No Payoneer link is waiting for your confirmation right now.');
        }

        $request->update([
            'status'        => PayoneerRequest::STATUS_MARKED_PAID,
            'marked_paid_at'=> now(),
        ]);

        $order = Orders::find($request->order_id);
        if ($order) {
            $this->notifyAdmins(
                'Payoneer payment marked done for order #' . $order->order_id,
                $order,
                $client->name . ' marked the Payoneer payment as done. Verify the money arrived in your Payoneer account, then mark the payment received.'
            );
        }

        return $request;
    }

    /** Client may cancel only BEFORE the admin sends a link. */
    public function cancelByClient(PayoneerRequest $request, User $client): PayoneerRequest
    {
        if ((int) $request->user_id !== (int) $client->id) {
            throw new PaymentException('This request does not belong to your account.');
        }
        if ($request->status !== PayoneerRequest::STATUS_REQUESTED) {
            throw new PaymentException('This request can no longer be cancelled — contact support to revoke a sent link.');
        }

        $request->update(['status' => PayoneerRequest::STATUS_CANCELLED]);

        return $request;
    }

    /* ------------------------------------------------------------------
     * Admin actions
     * ------------------------------------------------------------------ */

    /**
     * Admin verification — deliberately routes through the SAME engine calls
     * the bank panel uses, so transition(PAID) triggers syncOrderOnPaid
     * (orders.order_status + offerorders.offer_status lockstep, wallet, mail).
     */
    public function verify(PayoneerRequest $request, User $admin, bool $approve, ?string $note = null): PayoneerRequest
    {
        if (!in_array($request->status, [PayoneerRequest::STATUS_PROOF_SUBMITTED, PayoneerRequest::STATUS_MARKED_PAID], true)) {
            throw new PaymentException('This request is not waiting for verification.');
        }

        $payment = Payment::findOrFail($request->payment_id);

        if ($approve) {
            if ($payment->status === Payment::STATUS_AWAITING_VERIFICATION) {
                $this->payments->verifyBankPayment($payment, $admin, true, $note);
            } elseif ($payment->status === Payment::STATUS_AWAITING_PAYMENT) {
                $this->payments->markBankPaymentReceived($payment, $admin, $note);
            } else {
                throw new PaymentException('The payment is not in a verifiable state (' . $payment->status . ').');
            }

            $request->update([
                'status'      => PayoneerRequest::STATUS_VERIFIED,
                'verified_at' => now(),
                'handled_by'  => $admin->id,
            ]);
        } else {
            if ($payment->status === Payment::STATUS_AWAITING_VERIFICATION) {
                $this->payments->verifyBankPayment($payment, $admin, false, $note);
            } else {
                // awaiting_payment (marked_paid path) — engine has no public
                // cancel; mirror its terminal bookkeeping directly.
                $payment->failure_reason = $note ?: 'Rejected by admin';
                $payment->status = Payment::STATUS_FAILED;
                $payment->status_set_by = 'admin';
                $payment->failed_at = now();
                $payment->save();
                AuditLogger::log($payment, 'status_changed');
            }

            $request->update([
                'status'        => PayoneerRequest::STATUS_REJECTED,
                'reject_reason' => $note ?: 'Proof rejected by admin.',
                'handled_by'    => $admin->id,
            ]);
        }

        $order = Orders::find($request->order_id);
        $client = User::find($request->user_id);
        if ($order && $client) {
            $this->notifyClient($client, $order,
                $approve
                    ? 'Payoneer payment verified for order #' . $order->order_id
                    : 'Payoneer proof rejected for order #' . $order->order_id,
                $approve
                    ? 'Your payment has been verified and the order is now paid. Thank you!'
                    : 'Your Payoneer payment proof was rejected. ' . ($note ?: 'Please contact support or request a new link.')
            );
        }

        return $request;
    }

    /** Admin revokes a sent link (frees the client to use another method). */
    public function cancelByAdmin(PayoneerRequest $request, User $admin): PayoneerRequest
    {
        if (!in_array($request->status, [PayoneerRequest::STATUS_REQUESTED, PayoneerRequest::STATUS_LINK_SENT], true)) {
            throw new PaymentException('Only open requests can be cancelled.');
        }

        if ($request->payment_id) {
            $payment = Payment::find($request->payment_id);
            if ($payment && in_array($payment->status, Payment::OPEN_STATUSES, true)) {
                $payment->status = Payment::STATUS_CANCELLED;
                $payment->status_set_by = 'admin';
                $payment->cancelled_at = now();
                $payment->save();
                AuditLogger::log($payment, 'status_changed');
            }
        }

        $request->update([
            'status'     => PayoneerRequest::STATUS_CANCELLED,
            'handled_by' => $admin->id,
        ]);

        $order = Orders::find($request->order_id);
        $client = User::find($request->user_id);
        if ($order && $client) {
            $this->notifyClient($client, $order,
                'Payoneer link cancelled for order #' . $order->order_id,
                'The Payoneer payment link for this order was cancelled. You can pay with another method or request a new link.'
            );
        }

        return $request;
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------ */

    /** Same shape as PaymentService::buildReference (protected there). */
    protected function buildReference(int $orderId, int $attempt): string
    {
        $prefix = strtoupper(trim((string) Setting::get(
            config('admin_payments_engine.reference_prefix_setting'),
            config('admin_payments_engine.reference_fallback_prefix')
        )));
        $prefix = $prefix !== '' ? $prefix : 'DP';
        $rand = strtoupper(Str::random((int) config('admin_payments_engine.reference_rand_length', 6)));

        return sprintf('%s-%d-%d-%s', $prefix, $orderId, $attempt, $rand);
    }

    protected function notifyAdmins(string $title, Orders $order, string $description): void
    {
        try {
            foreach (User::where('type', 'admin')->get() as $admin) {
                $admin->notify(new TaskNotification([
                    'title'        => $title,
                    'order_number' => $order->order_id,
                    'greeting'     => 'Payoneer payment',
                    'order_id'     => $order->id,
                    'description'  => $description,
                ]));
            }
        } catch (Throwable $e) {
            report($e); // mail failures must never break the flow
        }
    }

    protected function notifyClient(User $client, Orders $order, string $title, string $description): void
    {
        try {
            $client->notify(new TaskNotification([
                'title'        => $title,
                'order_number' => $order->order_id,
                'greeting'     => 'Payoneer payment',
                'order_id'     => $order->id,
                'description'  => $description,
            ]));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
