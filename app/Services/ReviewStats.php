<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * RV-008: SQL-aggregated review analytics. Every number below comes from a
 * COUNT/AVG/GROUP BY query — never a full-table PHP loop.
 */
class ReviewStats
{
    /**
     * Admin KPI summary:
     * totals per status, overall + approved average rating, pending count,
     * verified share and 5-star share (approved scope), featured count.
     */
    public static function summary(): array
    {
        $byStatus = Review::query()
            ->select('status', DB::raw('COUNT(*) AS n'))
            ->groupBy('status')
            ->pluck('n', 'status')
            ->toArray();

        $approvedAgg = Review::approved()
            ->selectRaw('COUNT(*) AS n, COALESCE(AVG(rating),0) AS avg_rating, COALESCE(SUM(rating = ?),0) AS five_star, COALESCE(SUM(is_verified_purchase),0) AS verified', [config('admin_reviews.rating_max', 5)])
            ->first();

        $approvedCount = (int) ($approvedAgg->n ?? 0);

        return [
            'total'          => array_sum($byStatus),
            'by_status'      => $byStatus,
            'pending'        => (int) ($byStatus['pending'] ?? 0),
            'approved_count' => $approvedCount,
            'avg_rating'     => $approvedCount ? round((float) $approvedAgg->avg_rating, 2) : 0.0,
            'five_star'      => (int) ($approvedAgg->five_star ?? 0),
            'verified'       => (int) ($approvedAgg->verified ?? 0),
            'verified_share' => $approvedCount ? round(100 * (int) $approvedAgg->verified / $approvedCount) : 0,
            'five_star_share'=> $approvedCount ? round(100 * (int) $approvedAgg->five_star / $approvedCount) : 0,
            'featured'       => (int) Review::where('is_featured', true)->where('status', 'approved')->count(),
        ];
    }

    /**
     * Rating distribution for approved reviews: [rating => count, 1..5].
     * (One GROUP BY; missing buckets filled from a tiny 1..5 array.)
     */
    public static function distribution(): array
    {
        $rows = Review::approved()
            ->select('rating', DB::raw('COUNT(*) AS n'))
            ->groupBy('rating')
            ->pluck('n', 'rating')
            ->toArray();

        $min = (int) config('admin_reviews.rating_min', 1);
        $max = (int) config('admin_reviews.rating_max', 5);
        $out = [];
        for ($r = $min; $r <= $max; $r++) {
            $out[$r] = (int) ($rows[$r] ?? 0);
        }

        return $out;
    }

    /**
     * Per-month approved review volume + average rating (last N months).
     * Single GROUP BY over DATE_FORMAT(created_at, '%Y-%m').
     */
    public static function monthly(int $months = null): array
    {
        $months = $months ?: (int) config('admin_reviews.stats_months', 6);

        $rows = Review::approved()
            ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS n, COALESCE(AVG(rating),0) AS avg_rating")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = now()->subMonths($i)->format('Y-m');
            $out[$ym] = [
                'count' => (int) optional($rows->get($ym))->n ?: 0,
                'avg'   => optional($rows->get($ym))->avg_rating ? round((float) $rows->get($ym)->avg_rating, 2) : 0.0,
            ];
        }

        return $out;
    }

    /**
     * RV-006: aggregate summary for an arbitrary widget/public filter set.
     * Public scope (status=approved) is FORCED by the caller via
     * self::publicListing() — this method only computes numbers.
     */
    public static function summaryFor(Builder $query): array
    {
        $agg = $query->approved()
            ->selectRaw('COUNT(*) AS n, COALESCE(AVG(rating),0) AS avg_rating')
            ->first();

        return [
            'count' => (int) ($agg->n ?? 0),
            'avg'   => (int) ($agg->n ?? 0) ? round((float) $agg->avg_rating, 1) : 0.0,
        ];
    }

    /**
     * RV-006: widget listing query. `status = approved` is hard-forced so an
     * unmoderated review can NEVER be displayed publicly regardless of the
     * widget instance config.
     *
     * Supported cfg keys: rating_min, rating_max, verified, featured,
     * product_id, product_ids[], service_id, service_ids[], order_id,
     * user_id, type, sort, limit.
     */
    public static function publicListing(array $cfg): \Illuminate\Support\Collection
    {
        $min = (int) config('admin_reviews.rating_min', 1);
        $max = (int) config('admin_reviews.rating_max', 5);

        $q = Review::approved()->with('user:id,name');

        if (!empty($cfg['rating_min'])) {
            $q->where('rating', '>=', max($min, (int) $cfg['rating_min']));
        }
        if (!empty($cfg['rating_max'])) {
            $q->where('rating', '<=', min($max, (int) $cfg['rating_max']));
        }
        if (isset($cfg['verified']) && $cfg['verified'] !== '' && $cfg['verified'] !== null) {
            $q->where('is_verified_purchase', (bool) $cfg['verified']);
        }
        if (!empty($cfg['featured'])) {
            $q->where('is_featured', true);
        }
        if (!empty($cfg['product_id'])) {
            $q->where('product_id', (int) $cfg['product_id']);
        }
        if (!empty($cfg['product_ids'])) {
            $q->whereIn('product_id', array_map('intval', (array) $cfg['product_ids']));
        }
        if (!empty($cfg['service_id'])) {
            $q->where('service_id', (int) $cfg['service_id']);
        }
        if (!empty($cfg['service_ids'])) {
            $q->whereIn('service_id', array_map('intval', (array) $cfg['service_ids']));
        }
        if (!empty($cfg['order_id'])) {
            $q->where('order_id', (int) $cfg['order_id']);
        }
        if (!empty($cfg['user_id'])) {
            $q->where('user_id', (int) $cfg['user_id']);
        }
        if (!empty($cfg['type']) && in_array($cfg['type'], array_keys(config('admin_reviews.types', [])), true)) {
            $q->where('review_type', $cfg['type']);
        }

        switch ($cfg['sort'] ?? 'newest') {
            case 'oldest':
                $q->orderBy('created_at');
                break;
            case 'rating_high':
                $q->orderByDesc('rating')->orderByDesc('created_at');
                break;
            case 'rating_low':
                $q->orderBy('rating')->orderByDesc('created_at');
                break;
            case 'random':
                $q->inRandomOrder();
                break;
            case 'featured':
                $q->orderByDesc('is_featured')->orderByDesc('created_at');
                break;
            case 'newest':
            default:
                $q->orderByDesc('created_at');
                break;
        }

        return $q->limit(max(1, min(50, (int) ($cfg['limit'] ?? config('admin_reviews.widget.limit', 6)))))->get();
    }
}
