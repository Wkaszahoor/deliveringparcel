<?php

namespace App\Http\Controllers\Admin\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperOrderAssignment;
use App\Models\ShipperPayoutRequest;
use App\Models\ShipperProfile;

class ShipperDashboardController extends Controller
{
    public function index()
    {
        $byStatus = ShipperProfile::selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status');

        $overdue = ShipperOrderAssignment::with('shipper.user')
            ->where('purchase_deadline', '<', now())
            ->whereIn('status', ['assigned', 'accepted', 'purchasing'])->get()
            ->filter(fn ($a) => $a->isPurchaseOverdue());

        // SHIPPER KPI ENGINE (2026-09-03) — mission-control metrics.
        $kpi = app(\App\Services\Shipper\ShipperKpiService::class)->adminKpis();

        return view('admin.shippers.overview', [
            'total'          => ShipperProfile::count(),
            'byStatus'       => $byStatus,
            'pendingKyc'     => (int) ShipperProfile::where('kyc_status', 'pending')->count(),
            'activeAssign'   => (int) ShipperOrderAssignment::whereNotIn('status', ['completed', 'cancelled', 'disputed'])->count(),
            'pendingProofs'  => (int) ShipperOrderAssignment::where('status', 'proof_uploaded')->count(),
            'pendingAddr'    => (int) ShipperOrderAssignment::where('status', 'address_received')->count(),
            'pendingTrack'   => (int) ShipperOrderAssignment::where('status', 'tracking_added')->count(),
            'pendingPayouts' => (int) ShipperPayoutRequest::where('status', 'pending')->count(),
            'topShippers'    => ShipperProfile::with('user')->where('total_ratings', '>', 0)
                ->orderByDesc('rating')->limit(5)->get(),
            'lowRating'      => ShipperProfile::with('user')->where('rating', '<', 3.5)
                ->where('total_ratings', '>', 0)->limit(5)->get(),
            'overdue'        => $overdue,
            'kpi'            => $kpi,
        ]);
    }
}
