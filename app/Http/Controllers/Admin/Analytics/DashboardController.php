<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Admin\Analytics\Concerns\ComputesRevenue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Dynamic dashboard (Agent A): /admin/dashbord2 + JSON data endpoint.
 */
class DashboardController extends Controller
{
    use ComputesRevenue;

    /**
     * Dynamic dashboard page.
     */
    public function index()
    {
        return view('admin.analytics.dashboard');
    }

    /**
     * One JSON endpoint for everything: KPIs + chart datasets + recent orders (paginated).
     * GET /admin/analytics/data?page=N
     */
    public function data(Request $request)
    {
        $minutes = (int) config('admin_analytics.cache_minutes', 10);

        $kpiAndCharts = Cache::remember('analytics.dashboard.v1', now()->addMinutes($minutes), function () {
            return [
                'kpis'   => $this->kpis(),
                'charts' => $this->chartDatasets(),
            ];
        });

        $orders = $this->recentOrders($request);

        return response()->json($kpiAndCharts + ['orders' => $orders]);
    }

    /* ------------------------------------------------------------------
     * KPI values (all real, from DB)
     * ------------------------------------------------------------------ */

    protected function kpis(): array
    {
        $now        = now();
        $startMonth = $now->copy()->startOfMonth();

        $awaiting = (array) config('admin_analytics.awaiting_action_statuses', []);

        $totalOrders = DB::table('orders')->count();

        $awaitingOrders = DB::table('orders')
            ->where(function ($q) use ($awaiting) {
                $q->whereNull('order_status')->orWhereIn('order_status', $awaiting);
            })
            ->count();

        $newUsers = DB::table('users')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->count();

        // contactuses / request_quotes have no read/status flag in the schema,
        // so "unread"/"open" are proxied by messages received in the last 30 days.
        $contactMessages = DB::table('contactuses')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->count();

        $openQuotes = DB::table('request_quotes')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->count();

        return [
            'revenue_today'     => $this->revenueBetween($now->copy()->startOfDay(), $now),
            'revenue_month'     => $this->revenueBetween($startMonth, $now),
            'total_orders'      => $totalOrders,
            'awaiting_orders'   => $awaitingOrders,
            'new_users_30d'     => $newUsers,
            'contact_messages'  => $contactMessages,
            'open_quotes'       => $openQuotes,
        ];
    }

    /* ------------------------------------------------------------------
     * Chart datasets
     * ------------------------------------------------------------------ */

    protected function chartDatasets(): array
    {
        return [
            'revenue_daily'    => $this->revenueDaily(30),
            'orders_by_status' => $this->ordersByStatus(),
            'users_weekly'     => $this->usersWeekly(12),
        ];
    }

    /**
     * Accepted-offer revenue per day for the last N days (gap-filled).
     */
    protected function revenueDaily(int $days): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = $this->acceptedOffersJoin()
            ->where('ao.offer_at', '>=', $from)
            ->selectRaw("DATE_FORMAT(ao.offer_at, '%Y-%m-%d') AS d, SUM(ao.amount) AS total")
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $map = $rows->pluck('total', 'd');

        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day      = $from->copy()->addDays($i)->format('Y-m-d');
            $labels[] = $day;
            $values[] = (float) ($map[$day] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Order counts grouped by order_status (NULL shown as "New (no offer)"),
     * biggest 6 first, the rest bucketed into "Other".
     */
    protected function ordersByStatus(): array
    {
        $rows = DB::table('orders')
            ->selectRaw('COALESCE(NULLIF(order_status, ""), "New (no offer)") AS st, COUNT(*) AS c')
            ->groupBy('st')
            ->orderByDesc('c')
            ->get();

        $labels = $rows->pluck('st')->all();
        $values = $rows->pluck('c')->map(fn ($v) => (int) $v)->all();

        if (count($labels) > 7) {
            $labels = array_slice($labels, 0, 6);
            $values = array_slice($values, 0, 6);
            $labels[] = 'Other';
            $values[] = array_sum(array_slice($rows->pluck('c')->all(), 6));
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * New users per ISO week (week starting Monday) for the last N weeks, gap-filled.
     */
    protected function usersWeekly(int $weeks): array
    {
        $from = now()->subWeeks($weeks - 1)->startOfWeek();

        $rows = DB::table('users')
            ->where('created_at', '>=', $from)
            ->selectRaw("DATE_FORMAT(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY), '%Y-%m-%d') AS wk, COUNT(*) AS c")
            ->groupBy('wk')
            ->orderBy('wk')
            ->get();

        $map = $rows->pluck('c', 'wk');

        $labels = [];
        $values = [];
        for ($i = 0; $i < $weeks; $i++) {
            $week     = $from->copy()->addWeeks($i)->format('Y-m-d');
            $labels[] = $from->copy()->addWeeks($i)->format('M j');
            $values[] = (int) ($map[$week] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /* ------------------------------------------------------------------
     * Recent orders (server-side paginated, {data, last_page} shape)
     * ------------------------------------------------------------------ */

    protected function recentOrders(Request $request): array
    {
        $perPage = (int) config('admin_analytics.per_page', 10);
        $page    = max(1, (int) $request->input('page', 1));

        $query = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoinSub($this->acceptedOffersPerOrder(), 'ao', function ($join) {
                $join->on('ao.order_id', '=', 'o.id');
            })
            ->select(
                'o.id',
                'o.order_id',
                'o.order_status',
                'o.total',
                'o.shipfrom',
                'o.shipto',
                'o.created_at',
                'u.name as user_name',
                'u.email as user_email',
                'ao.amount as accepted_amount'
            )
            ->orderByDesc('o.created_at')
            ->orderByDesc('o.id');

        return $query->paginate($perPage, ['*'], 'page', $page)->toArray();
    }
}
