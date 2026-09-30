<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/mobile/v1/dashboard — operations command center payload for the
 * admin app (urgent alerts, KPIs, action-required, metrics, priority orders).
 *
 * Read-only DB::table() queries against the REAL legacy schema:
 * orders / offerorders / payments / order_chats / users / notifications.
 */
class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $labels = config('mobile.order_status_labels', []);

        /* ── KPIs ── */
        $totalOrders  = DB::table('orders')->whereNull('archived_at')->count();
        $totalClients = DB::table('users')->where('type', 'client')->count();
        $totalRevenue = (float) DB::table('payments')->where('status', 'paid')->sum('amount');
        $unreadNotifs = DB::table('notifications')
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        /* ── Orders by status (raw + labeled) ── */
        $countsRaw = DB::table('orders')
            ->whereNull('archived_at')
            ->selectRaw('order_status, COUNT(*) as cnt')
            ->groupBy('order_status')
            ->pluck('cnt', 'order_status')
            ->toArray();
        $counts = [];
        foreach ($countsRaw as $status => $cnt) {
            $key = $labels[$status] ?? ($status !== '' ? $status : 'Request Placed');
            $counts[$key] = ($counts[$key] ?? 0) + (int) $cnt;
        }

        /* ── Urgent alerts ── */
        $noOffer = DB::table('orders as o')
            ->where('o.created_at', '<=', now()->subDay())
            ->where(function ($q) { $q->where('o.order_status', '')->orWhereNull('o.order_status'); })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('offerorders as f')->whereColumn('f.order_id', 'o.id');
            })
            ->count();

        $paymentsPending = DB::table('payments')
            ->whereIn('status', ['awaiting_payment', 'awaiting_verification'])
            ->count();

        $trackingOverdue = DB::table('orders as o')
            ->whereNull('o.trackingid')
            ->where('o.updated_at', '<=', now()->subHours(48))
            ->whereExists(function ($q) {
                // latest offer on the order was accepted (offer_status = 1)
                $q->select(DB::raw(1))->fromRaw('offerorders latestf')
                    ->whereColumn('latestf.id', DB::raw('(SELECT MAX(f2.id) FROM offerorders f2 WHERE f2.order_id = o.id)'))
                    ->where('latestf.offer_status', 1);
            })
            ->count();

        $unreadMessages = DB::table('orders as o')
            ->whereExists(function ($q) {
                // unread messages sent by the CLIENT of that order
                $q->select(DB::raw(1))->from('order_chats as c')
                    ->whereColumn('c.order_id', 'o.id')
                    ->whereColumn('c.from', 'o.user_id')
                    ->where('c.read', 0);
            })
            ->count();

        $urgentAlerts = [
            'orders_no_offer'  => $noOffer,
            'payments_pending' => $paymentsPending,
            'tracking_overdue' => $trackingOverdue,
            'unread_messages'  => $unreadMessages,
        ];

        /* ── Action required ── */
        $offerRejected = DB::table('orders as o')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->fromRaw('offerorders latestf')
                    ->whereColumn('latestf.id', DB::raw('(SELECT MAX(f2.id) FROM offerorders f2 WHERE f2.order_id = o.id)'))
                    ->where('latestf.offer_status', 2);
            })
            ->count();

        $paymentVerification = DB::table('payments')->where('status', 'awaiting_verification')->count();

        $readyToShip = DB::table('orders')
            ->whereNull('archived_at')->where('confirmation', 2)
            ->whereNull('trackingid')
            ->count();

        $trackingNeeded = DB::table('orders as o')
            ->whereNull('o.trackingid')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->fromRaw('offerorders latestf')
                    ->whereColumn('latestf.id', DB::raw('(SELECT MAX(f2.id) FROM offerorders f2 WHERE f2.order_id = o.id)'))
                    ->where('latestf.offer_status', 1);
            })
            ->count();

        $actionRequired = [
            'awaiting_offer'   => $noOffer,
            'offer_rejected'   => $offerRejected,
            'payment_pending'  => $paymentVerification,
            'ready_to_ship'    => $readyToShip,
            'tracking_needed'  => $trackingNeeded,
        ];

        /* ── Metrics ── */
        $ordersToday     = DB::table('orders')->whereNull('archived_at')->whereDate('created_at', today())->count();
        $ordersYesterday = DB::table('orders')->whereNull('archived_at')->whereDate('created_at', today()->subDay())->count();

        $avgResponseHours = (float) DB::table('offerorders as f')
            ->join('orders as o', 'o.id', '=', 'f.order_id')
            ->whereRaw('f.id = (SELECT MIN(f2.id) FROM offerorders f2 WHERE f2.order_id = f.order_id)')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, o.created_at, f.created_at)) as agg')
            ->value('agg') ?: 0;

        $acceptedOffers = (int) DB::table('offerorders')->where('offer_status', 1)->count();
        $decidedOffers  = (int) DB::table('offerorders')->whereIn('offer_status', [1, 2])->count();
        $acceptRate = $decidedOffers > 0 ? round($acceptedOffers / $decidedOffers * 100, 1) : 0.0;

        $avgDeliveryDays = (float) DB::table('orders')
            ->whereNull('archived_at')->where('order_status', 'completed')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)) as agg')
            ->value('agg') ?: 0;

        $receivedCount  = (int) DB::table('orders')->whereNull('archived_at')->where('order_status', 'received')->count();
        $doneBaseline   = (int) DB::table('orders')->whereNull('archived_at')->whereIn('order_status', ['completed', 'received'])->count();
        $satisfaction   = $doneBaseline > 0 ? round($receivedCount / $doneBaseline * 100, 1) : 0.0;

        $metrics = [
            'orders_today'       => $ordersToday,
            'orders_yesterday'   => $ordersYesterday,
            'avg_response_hours' => round($avgResponseHours, 1),
            'offer_accept_rate'  => $acceptRate,
            'avg_delivery_days'  => round($avgDeliveryDays, 1),
            'client_satisfaction'=> $satisfaction,
        ];

        /* ── High priority orders (merged from 4 buckets, longest-waiting first) ── */
        $highPriority = collect()
            ->merge($this->priorityBucket('no_offer', '#E53935', function ($q) {
                $q->where('o.created_at', '<=', now()->subDay())
                    ->where(function ($qq) { $qq->where('o.order_status', '')->orWhereNull('o.order_status'); })
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))->from('offerorders as f')->whereColumn('f.order_id', 'o.id');
                    });
            }))
            ->merge($this->priorityBucket('rejected', '#B71C1C', function ($q) {
                $q->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))->fromRaw('offerorders latestf')
                        ->whereColumn('latestf.id', DB::raw('(SELECT MAX(f2.id) FROM offerorders f2 WHERE f2.order_id = o.id)'))
                        ->where('latestf.offer_status', 2);
                });
            }))
            ->merge($this->priorityBucket('payment_pending', '#F57C00', function ($q) {
                $q->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('payments as p')
                        ->whereColumn('p.order_id', 'o.id')
                        ->whereIn('p.status', ['awaiting_payment', 'awaiting_verification']);
                });
            }))
            ->merge($this->priorityBucket('tracking_needed', '#7B1FA2', function ($q) {
                $q->whereNull('o.trackingid')
                    ->where('o.updated_at', '<=', now()->subHours(48))
                    ->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))->fromRaw('offerorders latestf')
                            ->whereColumn('latestf.id', DB::raw('(SELECT MAX(f2.id) FROM offerorders f2 WHERE f2.order_id = o.id)'))
                            ->where('latestf.offer_status', 1);
                    });
            }))
            ->sortByDesc('days_waiting')
            ->values()
            ->take(10)
            ->map(function ($row) use ($labels) {
                $row['status_label'] = $labels[$row['status'] ?? ''] ?? ($row['status'] ?: 'Request Placed');
                return $row;
            })
            ->toArray();

        /* ── Recent orders ── */
        $recentOrders = DB::table('orders as o')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->whereNull('o.archived_at')
            ->orderByDesc('o.id')
            ->limit(10)
            ->get(['o.id', 'o.order_id', 'u.name as client', 'o.order_status as status', 'o.created_at as created'])
            ->map(function ($r) use ($labels) {
                return [
                    'id'           => $r->id,
                    'order_id'     => $r->order_id,
                    'client'       => $r->client,
                    'status'       => $r->status,
                    'status_label' => $labels[$r->status ?? ''] ?? ($r->status ?: 'Request Placed'),
                    'created'      => optional($r->created)->format('M d, H:i'),
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'total_orders'         => $totalOrders,
            'total_clients'        => $totalClients,
            'total_revenue'        => $totalRevenue,
            'unread_notifications' => $unreadNotifs,
            'counts'               => $counts,
            'urgent_alerts'        => $urgentAlerts,
            'action_required'      => $actionRequired,
            'metrics'              => $metrics,
            'high_priority_orders' => $highPriority,
            'recent_orders'        => $recentOrders,
        ]);
    }

    /**
     * One priority bucket: orders matching the scope, shaped for the
     * PriorityOrderCard (client, route, amount from latest offer, wait time).
     */
    private function priorityBucket(string $reason, string $color, callable $scope): array
    {
        $q = DB::table('orders as o')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->whereNull('o.archived_at')
            ->select([
                'o.id', 'o.order_id', 'u.name as client', 'o.shipfrom', 'o.shipto',
                'o.order_status as status', 'o.created_at',
                DB::raw('(SELECT f.total FROM offerorders f WHERE f.order_id = o.id ORDER BY f.id DESC LIMIT 1) as amount'),
                DB::raw('COALESCE(TIMESTAMPDIFF(HOUR, o.created_at, NOW()) / 24.0, 0) as days_waiting'),
            ]);
        $scope($q);

        return $q->orderByDesc('o.created_at')->limit(5)->get()
            ->map(function ($r) use ($reason, $color) {
                return [
                    'id'              => $r->id,
                    'order_id'        => $r->order_id,
                    'client'          => $r->client,
                    'shipfrom'        => $r->shipfrom,
                    'shipto'          => $r->shipto,
                    'amount'          => $r->amount !== null ? (float) $r->amount : null,
                    'days_waiting'    => round((float) $r->days_waiting, 1),
                    'priority_reason' => $reason,
                    'priority_color'  => $color,
                    'status'          => $r->status,
                ];
            })
            ->values()
            ->all();
    }
}
