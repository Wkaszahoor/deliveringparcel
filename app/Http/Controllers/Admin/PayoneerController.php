<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoneerRequest;
use App\Models\Setting;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PayoneerService;
use Illuminate\Http\Request;
use Throwable;

/**
 * Admin → Payoneer Payments: the link-generation inbox + verification +
 * settings. Per-order verification ALSO stays available on the existing
 * admin order panel (payment_method_code='payoneer' rows render there
 * method-agnostically) — both paths call the same PaymentService methods.
 */
class PayoneerController extends Controller
{
    public function __construct(protected PayoneerService $payoneer)
    {
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $query = PayoneerRequest::with(['order', 'user', 'payment'])
            ->orderByDesc('id');

        $knownStatuses = array_merge(PayoneerRequest::ACTIVE_STATUSES, [
            PayoneerRequest::STATUS_VERIFIED,
            PayoneerRequest::STATUS_REJECTED,
            PayoneerRequest::STATUS_CANCELLED,
        ]);
        if ($status && in_array($status, $knownStatuses, true)) {
            $query->where('status', $status);
        }

        return view('admin.payoneer.index', [
            'requests'      => $query->paginate(20)->withQueryString(),
            'filter'        => $status,
            'enabled'       => $this->payoneer->enabled(),
            'proofRequired' => $this->payoneer->proofRequired(),
            'instructions'  => $this->payoneer->instructions(),
        ]);
    }

    /** POST /admin/payoneer/{request}/link — paste the Payoneer link. */
    public function sendLink(Request $request, PayoneerRequest $payoneerRequest)
    {
        $data = $request->validate([
            'link' => 'required|string|max:2048',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $this->payoneer->sendLink($payoneerRequest, $request->user(), $data['link'], $data['note'] ?? null);
        } catch (PaymentException $e) {
            return redirect()->route('admin.payoneer.index')->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('admin.payoneer.index')->with('error', 'Could not send the link. Please try again.');
        }

        return redirect()->route('admin.payoneer.index')->with('success', 'Link sent — the customer has been notified.');
    }

    /** POST /admin/payoneer/{request}/verify — approve or reject. */
    public function verify(Request $request, PayoneerRequest $payoneerRequest)
    {
        $data = $request->validate([
            'approve' => 'required|boolean',
            'note'    => 'nullable|string|max:1000',
        ]);

        try {
            $this->payoneer->verify($payoneerRequest, $request->user(), (bool) $data['approve'], $data['note'] ?? null);
        } catch (PaymentException $e) {
            return redirect()->route('admin.payoneer.index')->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('admin.payoneer.index')->with('error', 'Verification failed. Please try again.');
        }

        return redirect()->route('admin.payoneer.index')
            ->with('success', $data['approve'] ? 'Payment verified — order marked paid.' : 'Proof rejected — customer notified.');
    }

    /** POST /admin/payoneer/{request}/cancel — revoke a link / decline a request. */
    public function cancel(Request $request, PayoneerRequest $payoneerRequest)
    {
        try {
            $this->payoneer->cancelByAdmin($payoneerRequest, $request->user());
        } catch (PaymentException $e) {
            return redirect()->route('admin.payoneer.index')->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('admin.payoneer.index')->with('error', 'Could not cancel. Please try again.');
        }

        return redirect()->route('admin.payoneer.index')->with('success', 'Request cancelled.');
    }

    /** POST /admin/payoneer/settings */
    public function settings(Request $request)
    {
        $data = $request->validate([
            'payoneer_enabled'        => 'nullable|boolean',
            'payoneer_proof_required' => 'nullable|boolean',
            'payoneer_instructions'   => 'nullable|string|max:2000',
        ]);

        Setting::set('payoneer_enabled', !empty($data['payoneer_enabled']) ? '1' : '0', 'payments', 'boolean');
        Setting::set('payoneer_proof_required', !empty($data['payoneer_proof_required']) ? '1' : '0', 'payments', 'boolean');
        if (array_key_exists('payoneer_instructions', $data)) {
            Setting::set('payoneer_instructions', (string) ($data['payoneer_instructions'] ?? ''), 'payments', 'text');
        }

        return redirect()->route('admin.payoneer.index')->with('success', 'Payoneer settings saved.');
    }
}
