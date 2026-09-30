<?php

namespace App\Http\Controllers\Api\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperProfile;
use Illuminate\Http\Request;

class ShipperApiDashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $profile = ShipperProfile::with('user')
                ->where('user_id', $request->user()->id)->firstOrFail();

            $levelLabels = [1 => 'Starter Shipper', 2 => 'Verified Shipper', 3 => 'Elite Shipper'];
            $nextLevel = null;
            if ($profile->level === 1) {
                $nextLevel = ['need_completed' => 5, 'current' => $profile->total_completed, 'need_rating' => 4.0];
            } elseif ($profile->level === 2) {
                $nextLevel = ['need_completed' => 25, 'current' => $profile->total_completed, 'need_rating' => 4.5];
            }

            return response()->json([
                'ok'   => true,
                'data' => [
                    'shipper_username'        => $profile->user->shipper_username,
                    'level'                   => $profile->level,
                    'level_label'             => $levelLabels[$profile->level] ?? '',
                    'next_level_requirements' => $nextLevel,
                    'wallet_balance'          => (float) $profile->wallet_balance,
                    'wallet_pending'          => (float) $profile->wallet_pending,
                    'total_earned'            => (float) $profile->total_earned,
                    'rating'                  => (float) $profile->rating,
                    'total_ratings'           => (int) $profile->total_ratings,
                    'total_completed'         => (int) $profile->total_completed,
                    'status'                  => $profile->status,
                    'kyc_status'              => $profile->kyc_status,
                    'active_assignments_count' => (int) $profile->assignments()
                        ->whereNotIn('status', ['completed', 'cancelled', 'disputed'])->count(),
                    'available_requests_count' => (int) \App\Models\ShippingRequest::visibleToShipper($profile)->count(),
                    'unread_messages_count'   => (int) \App\Models\ShipperAdminChat::where(function ($q) use ($profile) {
                        $q->whereHas('assignment', fn ($x) => $x->where('shipper_profile_id', $profile->id))
                            ->orWhereHas('request', fn ($x) => $x->where('assigned_shipper_profile_id', $profile->id));
                    })->where('from_type', 'admin')->where('is_read', false)->count(),
                    'recent_assignments'      => $profile->assignments()->latest()->limit(5)
                        ->get(['id', 'order_id', 'status', 'shipper_fee'])->map(fn ($a) => [
                            'id' => $a->id, 'order_id' => $a->order_id,
                            'status' => $a->status, 'fee' => (float) $a->shipper_fee,
                        ]),
                    'alerts' => $profile->assignments()
                        ->where('purchase_deadline', '<', now())
                        ->whereIn('status', ['assigned', 'accepted', 'purchasing'])->get()
                        ->filter(fn ($a) => $a->isPurchaseOverdue())
                        ->map(fn ($a) => ['type' => 'purchase_overdue', 'assignment_id' => $a->id, 'order_id' => $a->order_id])
                        ->values(),

                    /* ── SHIPPER KPI ENGINE (2026-09-03) — personal command center ── */
                    'kpi' => app(\App\Services\Shipper\ShipperKpiService::class)->shipperKpis($profile),
                ],
                'errors' => (object) [],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'message' => 'Could not load shipper dashboard.'], 500);
        }
    }
}
