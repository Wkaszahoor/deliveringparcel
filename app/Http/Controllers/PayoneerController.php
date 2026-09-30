<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\PayoneerRequest;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PayoneerService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Throwable;

/**
 * Client-side Payoneer link-payment page (separate from the legacy order
 * payment tab by design — the legacy renderPaymentMethods JS is untouched).
 */
class PayoneerController extends Controller
{
    public function __construct(
        protected PayoneerService $payoneer,
        protected PaymentService $payments,
    ) {
    }

    /** GET /payoneer/{order} — the whole client flow on one page. */
    public function show(Request $request, int $order)
    {
        $order = $this->ownOrderOrFail($order);
        $user = $request->user();

        $amount = null;
        $currency = null;
        $amountError = null;
        try {
            $resolved = $this->payments->resolveAmount($order->id);
            $amount = $resolved['amount'];
            $currency = $resolved['currency'];
        } catch (PaymentException $e) {
            $amountError = $e->getMessage();
        }

        $payoneerRequest = PayoneerRequest::where('order_id', $order->id)
            ->orderByDesc('id')
            ->first();

        return view('payoneer.pay', [
            'order'           => $order,
            'amount'          => $amount,
            'currency'        => $currency,
            'amountError'     => $amountError,
            'payoneerRequest' => $payoneerRequest,
            'proofRequired'   => $this->payoneer->proofRequired(),
            'instructions'    => $this->payoneer->instructions(),
            'enabled'         => $this->payoneer->enabled(),
        ]);
    }

    /** POST /payoneer/{order}/request */
    public function requestLink(Request $request, int $order)
    {
        $order = $this->ownOrderOrFail($order);

        try {
            $this->payoneer->requestLink($order, $request->user());
            $message = 'Request sent! We will generate your Payoneer payment link shortly — you will be notified here and by email.';
        } catch (PaymentException $e) {
            return redirect()->route('payoneer.show', $order->id)->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('payoneer.show', $order->id)->with('error', 'Something went wrong while sending your request. Please try again.');
        }

        return redirect()->route('payoneer.show', $order->id)->with('success', $message);
    }

    /** POST /payoneer/{order}/proof */
    public function uploadProof(Request $request, int $order)
    {
        $order = $this->ownOrderOrFail($order);
        $payoneerRequest = PayoneerRequest::where('order_id', $order->id)
            ->whereIn('status', PayoneerRequest::ACTIVE_STATUSES)
            ->orderByDesc('id')
            ->first();

        if (!$payoneerRequest) {
            return redirect()->route('payoneer.show', $order->id)->with('error', 'No active Payoneer request for this order.');
        }

        try {
            $this->payoneer->submitProof($payoneerRequest, $request->user(), $request->file('proof'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('payoneer.show', $order->id)->with('error', implode(' ', $e->errors()['proof'] ?? ['Invalid file.']));
        } catch (PaymentException $e) {
            return redirect()->route('payoneer.show', $order->id)->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('payoneer.show', $order->id)->with('error', 'Your receipt could not be uploaded. Please try again.');
        }

        return redirect()->route('payoneer.show', $order->id)
            ->with('success', 'Thank you — your payment proof was uploaded and is awaiting verification.');
    }

    /** POST /payoneer/{order}/mark-paid (proof-optional mode) */
    public function markPaid(Request $request, int $order)
    {
        $order = $this->ownOrderOrFail($order);
        $payoneerRequest = PayoneerRequest::where('order_id', $order->id)
            ->whereIn('status', PayoneerRequest::ACTIVE_STATUSES)
            ->orderByDesc('id')
            ->first();

        if (!$payoneerRequest) {
            return redirect()->route('payoneer.show', $order->id)->with('error', 'No active Payoneer request for this order.');
        }

        try {
            $this->payoneer->markPaidByClient($payoneerRequest, $request->user());
        } catch (PaymentException $e) {
            return redirect()->route('payoneer.show', $order->id)->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('payoneer.show', $order->id)->with('error', 'Something went wrong. Please try again.');
        }

        return redirect()->route('payoneer.show', $order->id)
            ->with('success', 'Thanks! We marked your payment as done — our team will verify it shortly.');
    }

    /** POST /payoneer/{order}/cancel */
    public function cancel(Request $request, int $order)
    {
        $order = $this->ownOrderOrFail($order);
        $payoneerRequest = PayoneerRequest::where('order_id', $order->id)
            ->whereIn('status', PayoneerRequest::ACTIVE_STATUSES)
            ->orderByDesc('id')
            ->first();

        if (!$payoneerRequest) {
            return redirect()->route('payoneer.show', $order->id)->with('error', 'No active Payoneer request for this order.');
        }

        try {
            $this->payoneer->cancelByClient($payoneerRequest, $request->user());
        } catch (PaymentException $e) {
            return redirect()->route('payoneer.show', $order->id)->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('payoneer.show', $order->id)->with('error', 'Something went wrong. Please try again.');
        }

        return redirect()->route('payoneer.show', $order->id)->with('success', 'Your Payoneer request was cancelled.');
    }

    protected function ownOrderOrFail(int $orderId): Orders
    {
        $order = Orders::findOrFail($orderId);
        $user = request()->user();

        if ((int) $order->user_id !== (int) $user->id && $user->type !== 'admin') {
            abort(404, 'Order not found or not accessible from this account.');
        }

        return $order;
    }
}
