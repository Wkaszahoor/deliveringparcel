<?php

namespace App\Services\Shipper;

use App\Models\ShipperOrderAssignment;
use App\Models\ShipperProfile;
use App\Models\ShipperQuote;
use App\Models\ShippingRequest;
use Illuminate\Support\Facades\DB;

/**
 * SHIPPER KPI ENGINE (2026-09-03) — mission-control metrics for the admin
 * overview + personal command-center stats for each shipper + the
 * customer-facing order progress data. Every block is independently
 * guarded: a failing metric degrades to null, never blanks the page.
 */
class ShipperKpiService
{
    /* ════════════════════════ ADMIN ════════════════════════ */

    public function adminKpis(): array
    {
        return [
            'critical'   => $this->guard(fn () => $this->critical()),
            'velocity'   => $this->guard(fn () => $this->velocity()),
            'stuck'      => $this->guard(fn () => $this->stuck()),
            'marketplace' => $this->guard(fn () => $this->marketplace()),
            'network'    => $this->guard(fn () => $this->network()),
            'money'      => $this->guard(fn () => $this->money()),
            'quality'    => $this->guard(fn () => $this->quality()),
            'feed'       => $this->guard(fn () => $this->activityFeed()),
        ];
    }

    /** Critical action queue (counts + age detail). */
    private function critical(): array
    {
        $overdue = ShipperOrderAssignment::with('shipper.user')
            ->whereIn('status', ['assigned', 'accepted', 'purchasing'])
            ->whereNotNull('purchase_deadline')
            ->where('purchase_deadline', '<', now())->get()
            ->filter(fn ($a) => $a->isPurchaseOverdue())->values();

        return [
            'overdue_purchases' => $overdue,
            'kyc_stale'         => ShipperProfile::where('kyc_status', 'pending')
                ->where('created_at', '<', now()->subHours(48))->count(),
            'proofs_pending'    => ShipperOrderAssignment::where('status', 'proof_uploaded')->count(),
            'address_pending'   => ShipperOrderAssignment::where('status', 'address_received')->count(),
            'tracking_pending'  => ShipperOrderAssignment::where('status', 'tracking_added')->count(),
            'payouts_pending'   => DB::table('shipper_payout_requests')->where('status', 'pending')->count(),
            'quotes_awaiting'   => ShipperQuote::where('status', 'pending')
                ->where('created_at', '<', now()->subHours(24))
                ->whereIn('request_id', ShippingRequest::where('status', 'open')->select('id'))
                ->count(),
        ];
    }

    /** Flow velocity — avg hours/days between the lifecycle timestamps. */
    private function velocity(): array
    {
        $quoteResponse = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(HOUR, r.created_at, q.created_at)) v
             FROM shipper_quotes q JOIN shipping_requests r ON q.request_id = r.id'
        )->v ?? null;
        $selection = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, assigned_at)) v
             FROM shipping_requests WHERE assigned_at IS NOT NULL'
        )->v ?? null;
        $purchase = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, purchased_at)) v
             FROM shipper_order_assignments WHERE purchased_at IS NOT NULL'
        )->v ?? null;
        $proof = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, admin_approved_at)) v
             FROM shipper_proofs WHERE admin_approved = 1 AND admin_approved_at IS NOT NULL'
        )->v ?? null;
        $address = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, forwarded_at)) v
             FROM shipper_delivery_addresses WHERE forwarded_to_shipper = 1 AND forwarded_at IS NOT NULL'
        )->v ?? null;
        $dispatch = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(DAY, a.forwarded_at, s.dispatched_at)) v
             FROM shipper_order_assignments s
             JOIN shipper_delivery_addresses a ON a.assignment_id = s.id
             WHERE s.dispatched_at IS NOT NULL AND a.forwarded_at IS NOT NULL'
        )->v ?? null;
        $share = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(HOUR, t.created_at, t.shared_at)) v
             FROM shipper_tracking_details t WHERE t.shared_with_customer = 1 AND t.shared_at IS NOT NULL'
        )->v ?? null;
        $e2e = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(DAY, r.created_at, s.completed_at)) v
             FROM shipper_order_assignments s JOIN shipping_requests r ON r.id = s.request_id
             WHERE s.completed_at IS NOT NULL'
        )->v ?? null;

        return [
            'quote_response_hrs' => $quoteResponse !== null ? round((float) $quoteResponse, 1) : null,
            'selection_hrs'      => $selection !== null ? round((float) $selection, 1) : null,
            'purchase_hrs'       => $purchase !== null ? round((float) $purchase, 1) : null,
            'proof_approval_hrs' => $proof !== null ? round((float) $proof, 1) : null,
            'address_forward_hrs' => $address !== null ? round((float) $address, 1) : null,
            'dispatch_days'      => $dispatch !== null ? round((float) $dispatch, 1) : null,
            'tracking_share_hrs' => $share !== null ? round((float) $share, 1) : null,
            'e2e_days'           => $e2e !== null ? round((float) $e2e, 1) : null,
        ];
    }

    /** Stuck assignments — same status for >48h. */
    private function stuck(): array
    {
        $rows = DB::table('shipper_order_assignments')
            ->selectRaw('status, COUNT(*) n')
            ->whereIn('status', ['assigned', 'accepted', 'purchasing', 'proof_uploaded', 'address_received', 'tracking_added'])
            ->where('updated_at', '<', now()->subHours(48))
            ->whereNotIn('status', ['completed', 'cancelled', 'disputed'])
            ->groupBy('status')->pluck('n', 'status');

        return [
            'assigned'         => (int) ($rows['assigned'] ?? 0) + (int) ($rows['accepted'] ?? 0),
            'purchasing'       => (int) ($rows['purchasing'] ?? 0),
            'proof_uploaded'   => (int) ($rows['proof_uploaded'] ?? 0),
            'address_received' => (int) ($rows['address_received'] ?? 0),
            'tracking_added'   => (int) ($rows['tracking_added'] ?? 0),
        ];
    }

    /** Marketplace health. */
    private function marketplace(): array
    {
        $openNoQuotes = ShippingRequest::where('status', 'open')
            ->whereDoesntHave('quotes')->count();
        $published = ShippingRequest::whereNotIn('status', ['draft', 'cancelled'])->count();
        $assignedCount = ShippingRequest::whereIn('status', ['assigned', 'active', 'proof_pending', 'proof_approved', 'address_pending', 'dispatched', 'completed'])->count();
        $expired = ShippingRequest::where('status', 'open')->whereNotNull('expires_at')->where('expires_at', '<', now())->count();

        $avgQuotes = (float) (DB::selectOne(
            'SELECT AVG(c) v FROM (SELECT COUNT(*) c FROM shipper_quotes WHERE status != "withdrawn" GROUP BY request_id) t'
        )->v ?? 0);

        $byCountry = DB::table('shipping_requests')
            ->selectRaw("country_required,
                SUM(status = 'open') open_n,
                SUM(status = 'completed') done_n")
            ->groupBy('country_required')->orderByDesc('done_n')->limit(5)->get();

        return [
            'open_no_quotes'   => $openNoQuotes,
            'avg_quotes'       => round($avgQuotes, 1),
            'acceptance_rate'  => $published > 0 ? round($assignedCount / $published * 100) : null,
            'expiry_rate'      => $published > 0 ? round($expired / $published * 100) : null,
            'by_country'       => $byCountry->map(fn ($r) => [
                'country' => $r->country_required, 'open' => (int) $r->open_n, 'completed' => (int) $r->done_n,
            ]),
        ];
    }

    /** Shipper network health. */
    private function network(): array
    {
        $byLevel = ShipperProfile::selectRaw('level, COUNT(*) n, AVG(rating) avg_rating')
            ->groupBy('level')->orderBy('level')->get();

        $idle = ShipperProfile::where('status', 'active')
            ->where('current_active_orders', 0)
            ->whereDoesntHave('assignments', fn ($q) => $q->where('created_at', '>', now()->subDays(7)))
            ->count();

        return [
            'total_active'   => (int) ShipperProfile::where('status', 'active')->count(),
            'avg_utilization' => round((float) (ShipperProfile::where('status', 'active')
                ->selectRaw('AVG(current_active_orders / GREATEST(max_concurrent_orders,1) * 100) v')->value('v') ?? 0)),
            'at_capacity'    => (int) ShipperProfile::where('status', 'active')
                ->whereRaw('current_active_orders >= max_concurrent_orders')->count(),
            'idle_7d'        => $idle,
            'by_level'       => $byLevel->map(fn ($r) => [
                'level' => (int) $r->level, 'count' => (int) $r->n, 'avg_rating' => round((float) $r->avg_rating, 2),
            ]),
            'problem_shippers' => ShipperProfile::with('user')
                ->where('rating', '<', 3.5)->where('total_ratings', '>=', 5)->count(),
        ];
    }

    /** Money flow. */
    private function money(): array
    {
        return [
            'pending_release'   => (float) ShipperProfile::sum('wallet_pending'),
            'releasing_7d'      => (float) ShipperOrderAssignment::where('status', 'completed')
                ->whereNotNull('hold_release_at')
                ->whereBetween('hold_release_at', [now(), now()->addDays(7)])->sum('wallet_hold_amount'),
            'pending_payouts'   => (float) DB::table('shipper_payout_requests')->where('status', 'pending')->sum('amount'),
            'platform_revenue_30d' => (float) ShipperOrderAssignment::where('status', 'completed')
                ->where('completed_at', '>=', now()->subDays(30))->sum('platform_fee'),
            'avg_shipper_fee'   => round((float) (ShipperOrderAssignment::where('status', 'completed')->avg('shipper_fee') ?? 0), 2),
            'avg_platform_fee'  => round((float) (ShipperOrderAssignment::where('status', 'completed')->avg('platform_fee') ?? 0), 2),
        ];
    }

    /** Quality metrics. */
    private function quality(): array
    {
        $total = (int) ShipperOrderAssignment::whereNotIn('status', ['assigned'])->count();
        $completed = (int) ShipperOrderAssignment::where('status', 'completed')->count();
        $cancelled = (int) ShipperOrderAssignment::whereIn('status', ['cancelled', 'disputed'])->count();

        return [
            'overall_rating' => round((float) (DB::table('shipper_ratings')->avg('overall_rating') ?? 0), 2),
            'ratings_30d'    => (int) DB::table('shipper_ratings')->where('created_at', '>=', now()->subDays(30))->count(),
            'completion_rate' => ($completed + $cancelled) > 0 ? round($completed / ($completed + $cancelled) * 100) : null,
            'dispute_rate'   => $total > 0 ? round($cancelled / $total * 100, 1) : null,
            'testimonials'   => (int) DB::table('shipper_ratings')->where('published_as_testimonial', 1)->count(),
        ];
    }

    /** Recent events (last 24h) merged into one feed. */
    private function activityFeed(): array
    {
        $events = [];

        foreach (ShipperQuote::with('shipper.user', 'request')->where('created_at', '>=', now()->subDay())->latest()->limit(6)->get() as $q) {
            $events[] = ['at' => $q->created_at, 'color' => 'blue',
                'text' => ($q->shipper->user->shipper_username ?? 'SHP') . ' quoted $' . number_format($q->quoted_amount, 0) . ' on ' . ($q->request->reference ?? '#')];
        }
        foreach (ShipperOrderAssignment::with('shipper.user')->where('updated_at', '>=', now()->subDay())->latest('updated_at')->limit(8)->get() as $a) {
            $good = in_array($a->status, ['completed', 'dispatched', 'delivered', 'tracking_shared', 'package_received']);
            $events[] = ['at' => $a->updated_at, 'color' => $good ? 'green' : 'yellow',
                'text' => ($a->shipper->user->shipper_username ?? 'SHP') . ' → ' . str_replace('_', ' ', $a->status) . ' #' . $a->order_id];
        }
        foreach (\App\Models\ShipperProfile::where('verified_at', '>=', now()->subDay())->limit(4)->get() as $p) {
            $events[] = ['at' => $p->verified_at, 'color' => 'green',
                'text' => 'KYC approved: ' . ($p->user->shipper_username ?? 'SHP')];
        }
        foreach (\App\Models\ShipperProof::where('created_at', '>=', now()->subDay())->limit(4)->get() as $p) {
            $events[] = ['at' => $p->created_at, 'color' => 'blue',
                'text' => 'Proof uploaded (' . str_replace('_', ' ', $p->proof_type) . ') on assignment #' . $p->assignment_id];
        }

        usort($events, fn ($a, $b) => $b['at'] <=> $a['at']);

        return array_slice($events, 0, 12);
    }

    /* ════════════════════════ SHIPPER (personal) ════════════════════════ */

    public function shipperKpis(ShipperProfile $profile): array
    {
        return [
            'earnings'  => $this->guard(fn () => $this->shipperEarnings($profile)),
            'velocity'  => $this->guard(fn () => $this->shipperVelocity($profile)),
            'breakdown' => $this->guard(fn () => $this->shipperRatingBreakdown($profile)),
            'todos'     => $this->guard(fn () => $this->shipperTodos($profile)),
            'top_requests' => $this->guard(fn () => $this->shipperTopRequests($profile)),
        ];
    }

    private function shipperEarnings(ShipperProfile $p): array
    {
        $month = (float) DB::table('shipper_wallet_transactions')
            ->where('shipper_profile_id', $p->id)->where('type', 'credit')
            ->where('created_at', '>=', now()->startOfMonth())->sum('amount');

        $next = ShipperOrderAssignment::where('shipper_profile_id', $p->id)
            ->where('status', 'completed')->whereNotNull('hold_release_at')
            ->where('hold_release_at', '>', now())->orderBy('hold_release_at')->first();

        $done = (int) $p->assignments()->where('status', 'completed')->count();
        $bad = (int) $p->assignments()->whereIn('status', ['cancelled', 'disputed'])->count();

        return [
            'this_month'   => $month,
            'next_release_amount' => $next ? (float) $next->wallet_hold_amount : null,
            'next_release_days'   => $next ? (int) ceil(now()->diffInDays($next->hold_release_at, false)) : null,
            'success_rate'  => ($done + $bad) > 0 ? round($done / ($done + $bad) * 100) : null,
            'avg_completion_days' => round((float) ($p->assignments()->where('status', 'completed')
                ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, completed_at)) v')->value('v') ?? 0), 1),
        ];
    }

    private function shipperVelocity(ShipperProfile $p): array
    {
        $purchase = $p->assignments()->whereNotNull('purchased_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, purchased_at)) v')->value('v');
        $dispatch = $p->assignments()->whereNotNull('dispatched_at')
            ->join('shipper_delivery_addresses as da', 'da.assignment_id', '=', 'shipper_order_assignments.id')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, da.forwarded_at, shipper_order_assignments.dispatched_at)) v')->value('v');

        return [
            'avg_purchase_hrs' => $purchase !== null ? round((float) $purchase, 1) : null,
            'avg_dispatch_days' => $dispatch !== null ? round((float) $dispatch, 1) : null,
        ];
    }

    private function shipperRatingBreakdown(ShipperProfile $p): array
    {
        $row = DB::table('shipper_ratings')->where('shipper_profile_id', $p->id)
            ->selectRaw('AVG(communication_rating) c, AVG(speed_rating) s, AVG(value_rating) v, AVG(condition_rating) q')
            ->first();

        return [
            'communication' => $row->c !== null ? round((float) $row->c, 1) : null,
            'speed'         => $row->s !== null ? round((float) $row->s, 1) : null,
            'value'         => $row->v !== null ? round((float) $row->v, 1) : null,
            'condition'     => $row->q !== null ? round((float) $row->q, 1) : null,
        ];
    }

    /** Shipper to-do list: urgent → important → FYI. */
    private function shipperTodos(ShipperProfile $p): array
    {
        $todos = [];

        foreach ($p->assignments()->whereIn('status', ['assigned', 'accepted', 'purchasing'])
            ->whereNotNull('purchase_deadline')->orderBy('purchase_deadline')->limit(3)->get() as $a) {
            $hrs = now()->diffInHours($a->purchase_deadline, false);
            if ($hrs < 0) {
                $todos[] = ['level' => 'red', 'label' => 'PURCHASE OVERDUE', 'assignment_id' => $a->id, 'order_id' => $a->order_id];
            } elseif ($hrs < 24) {
                $todos[] = ['level' => 'red', 'label' => 'Purchase within ' . max(1, $hrs) . 'h', 'assignment_id' => $a->id, 'order_id' => $a->order_id];
            }
        }
        foreach ($p->assignments()->whereIn('status', ['purchased', 'package_received'])->limit(3)->get() as $a) {
            $todos[] = ['level' => 'orange', 'label' => 'Upload proof photos', 'assignment_id' => $a->id, 'order_id' => $a->order_id];
        }
        foreach ($p->assignments()->where('status', 'address_forwarded')->limit(3)->get() as $a) {
            $todos[] = ['level' => 'yellow', 'label' => 'Address ready — ship now', 'assignment_id' => $a->id, 'order_id' => $a->order_id];
        }
        foreach ($p->assignments()->where('status', 'tracking_added')->limit(3)->get() as $a) {
            $todos[] = ['level' => 'yellow', 'label' => 'Awaiting admin to share tracking', 'assignment_id' => $a->id, 'order_id' => $a->order_id];
        }

        return $todos;
    }

    /** Top 3 open requests for the marketplace snapshot. */
    private function shipperTopRequests(ShipperProfile $p): array
    {
        if ($p->status !== 'active' || $p->kyc_status !== 'approved') {
            return [];
        }

        return ShippingRequest::visibleToShipper($p)
            ->withCount('quotes')->orderBy('expires_at')->limit(3)->get()
            ->map(fn ($r) => [
                'id' => $r->id, 'reference' => $r->reference,
                'service_type' => $r->service_type, 'country' => $r->country_required,
                'value_range_min' => (float) $r->value_range_min, 'value_range_max' => (float) $r->value_range_max,
                'quotes' => $r->quotes_count,
                'expires_hrs' => $r->expires_at ? (int) max(0, now()->diffInHours($r->expires_at, false)) : null,
            ])->all();
    }

    private function guard(callable $fn, $fallback = [])
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            report($e);
            return $fallback;
        }
    }
}
