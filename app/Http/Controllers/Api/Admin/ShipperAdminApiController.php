<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShipperOrderAssignment;
use App\Models\ShipperPayoutRequest;
use App\Models\ShipperProfile;
use App\Models\ShippingRequest;
use App\Services\Shipper\ShippingRequestService;
use Illuminate\Http\Request;

class ShipperAdminApiController extends Controller
{
    public function overview()
    {
        try {
            $kpi = app(\App\Services\Shipper\ShipperKpiService::class)->adminKpis();

            return response()->json(['ok' => true, 'data' => [
                'total_shippers'        => (int) ShipperProfile::count(),
                'pending_kyc'           => (int) ShipperProfile::where('kyc_status', 'pending')->count(),
                'active_assignments'    => (int) ShipperOrderAssignment::whereNotIn('status', ['completed', 'cancelled', 'disputed'])->count(),
                'pending_proofs'        => (int) ShipperOrderAssignment::where('status', 'proof_uploaded')->count(),
                'pending_addresses'     => (int) ShipperOrderAssignment::where('status', 'address_received')->count(),
                'pending_tracking'      => (int) ShipperOrderAssignment::where('status', 'tracking_added')->count(),
                'pending_payouts'       => (int) ShipperPayoutRequest::where('status', 'pending')->count(),
                'pending_shipper_actions' => (int) (ShipperOrderAssignment::whereIn('status', ['proof_uploaded', 'address_received', 'tracking_added'])->count()
                    + ShipperProfile::where('kyc_status', 'pending')->count()
                    + ShipperPayoutRequest::where('status', 'pending')->count()),

                /* ── KPI ENGINE extras (2026-09-03) ── */
                'money'    => $kpi['money'] ?? [],
                'velocity' => $kpi['velocity'] ?? [],
                'quality'  => $kpi['quality'] ?? [],
            ]]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'message' => 'Overview failed.'], 500);
        }
    }

    public function index(Request $request)
    {
        $q = ShipperProfile::with('user')
            ->when($request->filled('status'), fn ($x) => $x->where('status', $request->status))
            ->when($request->filled('search'), function ($x) use ($request) {
                $x->whereHas('user', fn ($u) => $u->where('email', 'like', "%{$request->search}%")
                    ->orWhere('shipper_username', 'like', "%{$request->search}%"));
            })->latest()->paginate(20);

        return response()->json(['ok' => true, 'data' => [
            'shippers' => collect($q->items())->map(fn ($s) => [
                'id' => $s->id, 'username' => $s->user->shipper_username, 'email' => $s->user->email,
                'level' => $s->level, 'status' => $s->status, 'kyc_status' => $s->kyc_status,
                'rating' => (float) $s->rating, 'completed' => (int) $s->total_completed,
                'wallet_balance' => (float) $s->wallet_balance,
            ]),
            'pagination' => ['current_page' => $q->currentPage(), 'last_page' => $q->lastPage()],
        ]]);
    }

    public function requests(Request $request)
    {
        $q = ShippingRequest::withCount('quotes')
            ->when($request->filled('status'), fn ($x) => $x->where('status', $request->status))
            ->when($request->filled('country'), fn ($x) => $x->where('country_required', strtoupper($request->country)))
            ->when($request->filled('search'), fn ($x) => $x->where(function ($w) use ($request) {
                $w->where('reference', 'like', "%{$request->search}%")
                    ->orWhere('customer_username', 'like', "%{$request->search}%");
            }))->latest()->paginate(20);

        return response()->json(['ok' => true, 'data' => [
            'requests' => collect($q->items())->map(fn ($r) => [
                'id' => $r->id, 'reference' => $r->reference, 'customer' => $r->customer_username,
                'country' => $r->country_required, 'service_type' => $r->service_type,
                'quotes_count' => $r->quotes_count, 'status' => $r->status, 'is_frozen' => (bool) $r->is_frozen,
            ]),
            'pagination' => ['current_page' => $q->currentPage(), 'last_page' => $q->lastPage()],
        ]]);
    }

    public function assignments(Request $request)
    {
        $q = ShipperOrderAssignment::with('shipper.user')
            ->when($request->filled('status'), fn ($x) => $x->where('status', $request->status))
            ->when($request->filled('order_id'), fn ($x) => $x->where('order_id', (int) $request->order_id))
            ->latest()->paginate(20);

        return response()->json(['ok' => true, 'data' => [
            'assignments' => collect($q->items())->map(fn ($a) => [
                'id' => $a->id, 'order_id' => $a->order_id,
                'shipper_username' => $a->shipper->user->shipper_username,
                'status' => $a->status, 'days_active' => $a->created_at->diffInDays(now()),
                'shipper_fee' => (float) $a->shipper_fee,
            ]),
            'pagination' => ['current_page' => $q->currentPage(), 'last_page' => $q->lastPage()],
        ]]);
    }

    public function freeze($id)
    {
        ShippingRequest::findOrFail($id)->freeze((int) auth()->id());
        return response()->json(['ok' => true, 'message' => 'Request frozen.']);
    }

    public function selectShipper(Request $request, $id)
    {
        $data = $request->validate([
            'shipper_profile_id' => 'required|integer|exists:shipper_profiles,id',
            'shipper_fee'        => 'required|numeric|min:0',
            'platform_fee'       => 'required|numeric|min:0',
        ]);
        $sr = ShippingRequest::findOrFail($id);
        $assignment = app(ShippingRequestService::class)->assignShipper(
            $sr, ShipperProfile::findOrFail($data['shipper_profile_id']),
            (float) $data['shipper_fee'], (float) $data['platform_fee'],
            (float) $data['shipper_fee'] + (float) $data['platform_fee'],
            (int) auth()->id()
        );

        return response()->json(['ok' => true, 'message' => 'Shipper selected.', 'data' => ['assignment_id' => $assignment->id]]);
    }

    public function approveProof(Request $request, $id)
    {
        $data = $request->validate([
            'proof_ids'        => 'required|array',
            'customer_visible' => 'nullable|boolean',
        ]);
        $a = ShipperOrderAssignment::findOrFail($id);
        \App\Models\ShipperProof::where('assignment_id', $a->id)
            ->whereIn('id', $data['proof_ids'])
            ->update([
                'admin_approved'    => true,
                'customer_visible'  => (bool) ($data['customer_visible'] ?? true),
                'admin_approved_at' => now(),
                'admin_approved_by' => auth()->id(),
            ]);

        if (!in_array($a->status, ['address_received', 'address_forwarded', 'dispatched', 'tracking_added', 'tracking_shared', 'delivered', 'completed'])) {
            $a->update(['status' => 'proof_approved']);
        }
        \DB::table('orders')->where('id', $a->order_id)->update(['shipper_proof_approved' => true]);

        return response()->json(['ok' => true, 'message' => 'Proofs approved.']);
    }

    public function forwardAddress(Request $request, $id)
    {
        $data = $request->validate(['forward_level' => 'required|in:full,address_only,city_country']);
        $a = ShipperOrderAssignment::with('deliveryAddress')->findOrFail($id);

        if (!$a->deliveryAddress) {
            return response()->json(['ok' => false, 'message' => 'No address submitted yet.'], 422);
        }
        $a->deliveryAddress->update([
            'forwarded_to_shipper' => true, 'forwarded_at' => now(),
            'forwarded_by' => auth()->id(), 'forward_level' => $data['forward_level'],
        ]);
        $a->update(['status' => 'address_forwarded']);
        \DB::table('orders')->where('id', $a->order_id)->update(['delivery_address_forwarded' => true]);

        return response()->json(['ok' => true, 'message' => 'Address forwarded.']);
    }

    public function shareTracking($id)
    {
        $a = ShipperOrderAssignment::with('trackingDetails')->findOrFail($id);
        if (!$a->trackingDetails) {
            return response()->json(['ok' => false, 'message' => 'No tracking submitted.'], 422);
        }
        $a->trackingDetails->update([
            'shared_with_customer' => true, 'shared_at' => now(), 'shared_by' => auth()->id(),
        ]);
        $a->update(['status' => 'tracking_shared']);
        \DB::table('orders')->where('id', $a->order_id)->update(['shipper_tracking_shared' => true]);

        return response()->json(['ok' => true, 'message' => 'Tracking shared.']);
    }

    public function releasePayment($id)
    {
        $a = ShipperOrderAssignment::whereIn('status', ['delivered', 'tracking_shared'])->findOrFail($id);
        app(ShippingRequestService::class)->releasePayment($a);

        return response()->json(['ok' => true, 'message' => 'Payment released.']);
    }

    public function payoutRequests()
    {
        $q = ShipperPayoutRequest::with('shipper.user')->where('status', 'pending')->latest()->get();

        return response()->json(['ok' => true, 'data' => ['payouts' => $q->map(fn ($p) => [
            'id' => $p->id, 'shipper_username' => $p->shipper->user->shipper_username,
            'amount' => (float) $p->amount, 'method' => $p->method,
            'created_at' => $p->created_at?->toIso8601String(),
        ])]]);
    }

    public function approvePayout(Request $request, $id)
    {
        $data = $request->validate(['reference' => 'nullable|string|max:100']);
        $payout = ShipperPayoutRequest::where('status', 'pending')->findOrFail($id);
        $shipper = $payout->shipper;

        \DB::transaction(function () use ($payout, $shipper, $data) {
            $shipper->decrement('wallet_balance', (float) $payout->amount);
            \App\Models\ShipperWalletTransaction::create([
                'shipper_profile_id' => $shipper->id, 'type' => 'payout_paid',
                'amount' => $payout->amount, 'balance_after' => $shipper->fresh()->wallet_balance,
                'reference' => $data['reference'] ?? null, 'note' => 'Payout paid (mobile)',
                'processed_by' => auth()->id(), 'status' => 'completed',
            ]);
            $payout->update(['status' => 'paid', 'processed_by' => auth()->id(), 'processed_at' => now()]);
        });

        return response()->json(['ok' => true, 'message' => 'Payout paid.']);
    }
}
