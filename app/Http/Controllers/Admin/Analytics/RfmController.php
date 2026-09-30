<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Admin\Analytics\Concerns\ComputesRevenue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * RFM customer segmentation: /admin/analytics/rfm (Agent A).
 *
 * Base: one row per customer who has at least one accepted offer.
 *  R = days since last accepted offer, F = won orders, M = accepted revenue.
 * Scores 1..5 via quintiles (NTILE), segments per config/admin_analytics.php
 * (overridable via query params champion_r / champion_f / loyal_f / recent_r /
 * repeat_f / risk_f / attention_r).
 */
class RfmController extends Controller
{
    use ComputesRevenue;

    public const SEGMENTS = [
        'Champions',
        'Loyal',
        'Potential Loyalist',
        'New',
        'Promising',
        'Need Attention',
        'At Risk',
        'Hibernating',
    ];

    /**
     * Page: segment summary KPI row + responsive table.
     */
    public function index(Request $request)
    {
        $thresholds = $this->thresholds($request);
        $rows       = $this->scoredRows();
        $segments   = $this->segmentAll($rows, $thresholds);

        return view('admin.analytics.rfm', [
            'thresholds' => $thresholds,
            'summary'    => $this->summary($segments),
            'filters'    => $this->filters($request),
        ]);
    }

    /**
     * JSON: customer rows, server-side paginated ({data, last_page}).
     */
    public function data(Request $request)
    {
        $thresholds = $this->thresholds($request);
        $filters    = $this->filters($request);

        $rows     = $this->scoredRows();
        $segments = $this->segmentAll($rows, $thresholds);

        $filtered = $this->filterRows($segments, $filters);

        $page    = max(1, (int) $request->input('page', 1));
        $perPage = (int) config('admin_analytics.rfm_per_page', 15);

        $paginator = new LengthAwarePaginator(
            array_slice($filtered, ($page - 1) * $perPage, $perPage),
            count($filtered),
            $perPage,
            $page
        );

        return response()->json($paginator->toArray());
    }

    /* ------------------------------------------------------------------
     * Input handling
     * ------------------------------------------------------------------ */

    protected function thresholds(Request $request): array
    {
        $request->validate([
            'champion_r'  => 'nullable|integer|between:1,5',
            'champion_f'  => 'nullable|integer|between:1,5',
            'loyal_f'     => 'nullable|integer|between:1,5',
            'recent_r'    => 'nullable|integer|between:1,5',
            'repeat_f'    => 'nullable|integer|between:1,5',
            'risk_f'      => 'nullable|integer|between:1,5',
            'attention_r' => 'nullable|integer|between:1,5',
        ]);

        $defaults = config('admin_analytics.rfm', []);

        $out = [];
        foreach ($defaults as $key => $value) {
            $out[$key] = $request->filled($key) ? (int) $request->input($key) : (int) $value;
        }

        return $out;
    }

    protected function filters(Request $request): array
    {
        $request->validate([
            'segment' => 'nullable|in:' . implode(',', self::SEGMENTS),
            'q'       => 'nullable|string|max:100',
        ]);

        return [
            'segment' => (string) $request->input('segment', ''),
            'q'       => mb_substr(trim((string) $request->input('q', '')), 0, 100),
        ];
    }

    /* ------------------------------------------------------------------
     * Computation
     * ------------------------------------------------------------------ */

    /**
     * Per-customer RFM aggregates with quintile tiles (cached 10 min).
     */
    protected function scoredRows(): array
    {
        $minutes = (int) config('admin_analytics.cache_minutes', 10);

        return Cache::remember('analytics.rfm.scored.v1', now()->addMinutes($minutes), function () {
            $perOrder = $this->acceptedOffersPerOrder();

            $base = DB::query()
                ->fromSub($perOrder, 'ao')
                ->join('orders as o', 'o.id', '=', 'ao.order_id')
                ->join('users as u', 'u.id', '=', 'o.user_id')
                ->selectRaw("
                    o.user_id,
                    MIN(u.name) AS name,
                    MIN(u.email) AS email,
                    MAX(ao.offer_at) AS last_won_at,
                    COUNT(DISTINCT o.id) AS frequency,
                    SUM(ao.amount) AS monetary,
                    DATEDIFF(NOW(), MAX(ao.offer_at)) AS recency_days
                ")
                ->groupBy('o.user_id');

            $scored = DB::query()
                ->fromSub($base, 't')
                ->selectRaw("
                    t.*,
                    NTILE(5) OVER (ORDER BY t.recency_days ASC, t.user_id) AS r_tile,
                    NTILE(5) OVER (ORDER BY t.frequency ASC, t.monetary DESC, t.user_id) AS f_tile,
                    NTILE(5) OVER (ORDER BY t.monetary ASC, t.user_id) AS m_tile
                ")
                ->get();

            return $scored->map(function ($r) {
                return [
                    'user_id'      => (int) $r->user_id,
                    'name'         => $r->name,
                    'email'        => $r->email,
                    'last_won_at'  => (string) $r->last_won_at,
                    'recency_days' => (int) $r->recency_days,
                    'frequency'    => (int) $r->frequency,
                    'monetary'     => (float) $r->monetary,
                    'r'            => 6 - (int) $r->r_tile,
                    'f'            => (int) $r->f_tile,
                    'm'            => (int) $r->m_tile,
                ];
            })->all();
        });
    }

    /**
     * Attach a segment to every row (first matching rule wins).
     */
    protected function segmentAll(array $rows, array $t): array
    {
        foreach ($rows as &$row) {
            $row['segment'] = $this->segmentFor($row['r'], $row['f'], $t);
        }

        return $rows;
    }

    protected function segmentFor(int $r, int $f, array $t): string
    {
        if ($r >= $t['champion_r'] && $f >= $t['champion_f']) {
            return 'Champions';
        }
        if ($r >= $t['recent_r'] && $f >= $t['loyal_f']) {
            return 'Loyal';
        }
        if ($r >= $t['recent_r'] && $f >= $t['repeat_f']) {
            return 'Potential Loyalist';
        }
        if ($r >= $t['champion_r'] && $f < $t['repeat_f']) {
            return 'New';
        }
        if ($r >= $t['recent_r'] && $f < $t['repeat_f']) {
            return 'Promising';
        }
        if ($r >= $t['attention_r'] && $f >= $t['repeat_f']) {
            return 'Need Attention';
        }
        if ($f >= $t['risk_f']) {
            return 'At Risk';
        }

        return 'Hibernating';
    }

    protected function filterRows(array $rows, array $filters): array
    {
        return array_values(array_filter($rows, function ($row) use ($filters) {
            if ($filters['segment'] !== '' && $row['segment'] !== $filters['segment']) {
                return false;
            }
            if ($filters['q'] !== '') {
                $hay = mb_strtolower($row['name'] . ' ' . $row['email']);
                if (mb_strpos($hay, mb_strtolower($filters['q'])) === false) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Segment summary for the KPI row: customers, revenue, revenue share.
     */
    protected function summary(array $rows): array
    {
        $totalRevenue = array_sum(array_column($rows, 'monetary'));

        $out = [];
        foreach (self::SEGMENTS as $seg) {
            $out[$seg] = ['customers' => 0, 'revenue' => 0.0];
        }

        foreach ($rows as $row) {
            $out[$row['segment']]['customers']++;
            $out[$row['segment']]['revenue'] += $row['monetary'];
        }

        foreach ($out as $seg => $data) {
            $out[$seg]['share'] = $totalRevenue > 0 ? round($data['revenue'] / $totalRevenue * 100, 1) : 0.0;
        }

        return ['segments' => $out, 'total_customers' => count($rows), 'total_revenue' => $totalRevenue];
    }
}
