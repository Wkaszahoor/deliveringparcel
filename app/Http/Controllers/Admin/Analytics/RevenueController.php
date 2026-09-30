<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Admin\Analytics\Concerns\ComputesRevenue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Revenue analytics: /admin/analytics/revenue (Agent A).
 * Monthly accepted-offer revenue with daterangepicker (from/to query params).
 */
class RevenueController extends Controller
{
    use ComputesRevenue;

    /**
     * Page: monthly revenue table + bar chart + summary KPIs.
     */
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);
        $minutes     = (int) config('admin_analytics.cache_minutes', 10);

        $summary = Cache::remember(
            'analytics.revenue.summary.v1.' . $from->format('Ymd') . '.' . $to->format('Ymd'),
            now()->addMinutes($minutes),
            function () use ($from, $to) {
                return $this->summary($from, $to);
            }
        );

        return view('admin.analytics.revenue', [
            'from'    => $from->format('Y-m-d'),
            'to'      => $to->format('Y-m-d'),
            'summary' => $summary,
            'months'  => $summary['months'], // chart dataset (all months in range)
        ]);
    }

    /**
     * JSON: monthly rows, server-side paginated ({data, last_page}).
     * GET /admin/analytics/revenue/data?from=YYYY-MM-DD&to=YYYY-MM-DD&page=N
     */
    public function data(Request $request)
    {
        [$from, $to] = $this->range($request);
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 12;

        $rows = $this->monthly($from, $to);

        $slice = array_slice($rows, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'data'      => $slice,
            'last_page' => (int) max(1, ceil(count($rows) / $perPage)),
        ]);
    }

    /* ------------------------------------------------------------------ */

    protected function range(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to'   => 'nullable|date_format:Y-m-d|after_or_equal:from',
        ]);

        $months = (int) config('admin_analytics.revenue_default_months', 12);

        $from = $request->filled('from')
            ? Carbon::createFromFormat('Y-m-d', $request->input('from'))->startOfDay()
            : now()->subMonths($months - 1)->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::createFromFormat('Y-m-d', $request->input('to'))->endOfDay()
            : now()->endOfDay();

        // Hard cap: 5 years to keep aggregation bounded.
        if ($from->lt(now()->copy()->subYears(5)->startOfYear())) {
            $from = now()->copy()->subYears(5)->startOfYear();
        }

        return [$from, $to];
    }

    /**
     * Monthly aggregates (cached).
     */
    protected function monthly(Carbon $from, Carbon $to): array
    {
        $minutes = (int) config('admin_analytics.cache_minutes', 10);

        return Cache::remember(
            'analytics.revenue.monthly.v1.' . $from->format('Ymd') . '.' . $to->format('Ymd'),
            now()->addMinutes($minutes),
            function () use ($from, $to) {
                $rows = $this->acceptedOffersJoin()
                    ->whereBetween('ao.offer_at', [$from, $to])
                    ->selectRaw("
                        DATE_FORMAT(ao.offer_at, '%Y-%m') AS ym,
                        COUNT(DISTINCT ao.order_id) AS orders_count,
                        COUNT(*) AS offers_count,
                        SUM(ao.amount) AS revenue
                    ")
                    ->groupBy('ym')
                    ->orderByDesc('ym')
                    ->get();

                return $rows->map(function ($r) {
                    return [
                        'ym'           => $r->ym,
                        'orders_count' => (int) $r->orders_count,
                        'offers_count' => (int) $r->offers_count,
                        'revenue'      => (float) $r->revenue,
                    ];
                })->all();
            }
        );
    }

    protected function summary(Carbon $from, Carbon $to): array
    {
        $months = $this->monthly($from, $to);

        $total    = array_sum(array_column($months, 'revenue'));
        $orders   = array_sum(array_column($months, 'orders_count'));
        $monthCnt = max(1, count($months));

        $best = null;
        foreach ($months as $m) {
            if ($best === null || $m['revenue'] > $best['revenue']) {
                $best = $m;
            }
        }

        return [
            'months'        => $months,
            'total_revenue' => $total,
            'orders_count'  => $orders,
            'avg_month'     => $total / $monthCnt,
            'best_month'    => $best['ym'] ?? null,
            'best_revenue'  => $best['revenue'] ?? 0,
        ];
    }
}
