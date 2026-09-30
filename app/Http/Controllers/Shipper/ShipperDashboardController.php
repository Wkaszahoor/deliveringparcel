<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperProfile;
use Illuminate\Http\Request;

class ShipperDashboardController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->get('shipperProfile');

        $available = 0;
        if ($profile->status === 'active' && $profile->kyc_status === 'approved') {
            $available = \App\Models\ShippingRequest::visibleToShipper($profile)->count();
        }

        $nextLevel = match (true) {
            $profile->level === 1 => ['need' => 5, 'label' => 'Verified Shipper (Level 2)'],
            $profile->level === 2 => ['need' => 25, 'label' => 'Elite Shipper (Level 3)'],
            default               => null,
        };

        // SHIPPER KPI ENGINE (2026-09-03) — personal command-center stats.
        $kpi = app(\App\Services\Shipper\ShipperKpiService::class)->shipperKpis($profile);

        return view('shipper.dashboard', [
            'profile'   => $profile,
            'available' => $available,
            'recent'    => $profile->assignments()->with('order')->latest()->limit(5)->get(),
            'unread'    => \App\Models\ShipperAdminChat::where(function ($q) use ($profile) {
                $q->whereHas('assignment', fn ($x) => $x->where('shipper_profile_id', $profile->id))
                    ->orWhereHas('request', fn ($x) => $x->where('assigned_shipper_profile_id', $profile->id));
            })->where('from_type', 'admin')->where('is_read', false)->count(),
            'nextLevel' => $nextLevel,
            'kpi'       => $kpi,
        ]);
    }
}
