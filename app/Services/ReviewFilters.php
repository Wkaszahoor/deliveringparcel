<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * RV-005: AND-combinable filter parser for the admin reviews data endpoint.
 *
 * Every filter below can be combined with every other filter in a single
 * request — there is no "single-filter-only" mode. Recognised input keys
 * (accepted from the request or from a saved review_filter_presets row):
 *
 *   q               full-text over title / body / user name / user email
 *   status          single status key (pending|approved|rejected|hidden|spam)
 *   rating          exact rating
 *   rating_min      rating >= n          ┐ both allowed together and with
 *   rating_max      rating <= n          ┘ rating / rating_between
 *   rating_between  "4,5" (min,max inclusive)
 *   verified        1|0  (is_verified_purchase)
 *   featured        1|0  (is_featured)
 *   product_ids[]   multi product filter (alias: product_id / product_ids)
 *   service_ids[]   multi service filter (alias: service_id / service_ids)
 *   user_id         single customer
 *   order_id        single order
 *   type            review_type key
 *   preset          today|7d|30d|90d|ytd|custom  (created_at window)
 *   date_from       Y-m-d (with preset=custom or standalone)
 *   date_to         Y-m-d (inclusive; end-of-day applied)
 *   sort            newest|oldest|rating_high|rating_low
 */
class ReviewFilters
{
    /** Apply ALL provided filters (AND semantics). Returns the query + parsed filters. */
    public static function apply(Builder $query, array $in): Builder
    {
        $min = (int) config('admin_reviews.rating_min', 1);
        $max = (int) config('admin_reviews.rating_max', 5);
        $statuses = array_keys(config('admin_reviews.statuses', []));
        $types = array_keys(config('admin_reviews.types', []));

        /* --- full text search --- */
        $q = trim((string) Arr::get($in, 'q', ''));
        if ($q !== '') {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                    ->orWhere('body', 'like', $like)
                    ->orWhereHas('user', function ($u) use ($like) {
                        $u->where('name', 'like', $like)->orWhere('email', 'like', $like);
                    });
            });
        }

        /* --- status / type / ownership --- */
        $status = Arr::get($in, 'status');
        if ($status !== null && $status !== '' && in_array($status, $statuses, true)) {
            $query->where('status', $status);
        }
        $type = Arr::get($in, 'type');
        if ($type !== null && $type !== '' && in_array($type, $types, true)) {
            $query->where('review_type', $type);
        }
        foreach (['user_id', 'order_id'] as $fk) {
            $v = Arr::get($in, $fk);
            if ($v !== null && $v !== '' && ctype_digit((string) $v)) {
                $query->where($fk, (int) $v);
            }
        }

        /* --- rating: exact + >= + <= + between, all AND-combinable --- */
        $rating = Arr::get($in, 'rating');
        if ($rating !== null && $rating !== '' && ctype_digit((string) $rating)) {
            $query->where('rating', max($min, min($max, (int) $rating)));
        }
        $rMin = Arr::get($in, 'rating_min');
        if ($rMin !== null && $rMin !== '' && ctype_digit((string) $rMin)) {
            $query->where('rating', '>=', max($min, (int) $rMin));
        }
        $rMax = Arr::get($in, 'rating_max');
        if ($rMax !== null && $rMax !== '' && ctype_digit((string) $rMax)) {
            $query->where('rating', '<=', min($max, (int) $rMax));
        }
        $between = Arr::get($in, 'rating_between');
        if (is_string($between) && strpos($between, ',') !== false) {
            [$bMin, $bMax] = array_pad(array_map('trim', explode(',', $between, 2)), 2, null);
            if (ctype_digit((string) $bMin) && ctype_digit((string) $bMax)) {
                $query->whereBetween('rating', [max($min, (int) $bMin), min($max, (int) $bMax)]);
            }
        }

        /* --- boolean flags --- */
        foreach (['verified' => 'is_verified_purchase', 'featured' => 'is_featured'] as $param => $col) {
            $v = Arr::get($in, $param);
            if ($v === '1' || $v === 1 || $v === true) {
                $query->where($col, true);
            } elseif ($v === '0' || $v === 0 || $v === false) {
                $query->where($col, false);
            }
        }

        /* --- multi product / service --- */
        $productIds = self::idList($in, ['product_ids', 'product_id']);
        if ($productIds) {
            $query->whereNotNull('product_id')->whereIn('product_id', $productIds);
        }
        $serviceIds = self::idList($in, ['service_ids', 'service_id']);
        if ($serviceIds) {
            $query->whereNotNull('service_id')->whereIn('service_id', $serviceIds);
        }

        /* --- created_at window: named preset and/or custom range --- */
        $preset = (string) Arr::get($in, 'preset', '');
        $dateFrom = self::dateOrNull(Arr::get($in, 'date_from'));
        $dateTo = self::dateOrNull(Arr::get($in, 'date_to'));

        if (!$dateFrom && !$dateTo) {
            switch ($preset) {
                case 'today':
                    $dateFrom = now()->toDateString();
                    $dateTo = now()->toDateString();
                    break;
                case '7d':
                    $dateFrom = now()->subDays(7)->toDateString();
                    break;
                case '30d':
                    $dateFrom = now()->subDays(30)->toDateString();
                    break;
                case '90d':
                    $dateFrom = now()->subDays(90)->toDateString();
                    break;
                case 'ytd':
                    $dateFrom = now()->startOfYear()->toDateString();
                    break;
            }
        }
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        /* --- ordering --- */
        switch ((string) Arr::get($in, 'sort', 'newest')) {
            case 'oldest':
                $query->orderBy('created_at')->orderBy('id');
                break;
            case 'rating_high':
                $query->orderByDesc('rating')->orderByDesc('created_at');
                break;
            case 'rating_low':
                $query->orderBy('rating')->orderByDesc('created_at');
                break;
            case 'newest':
            default:
                $query->orderByDesc('created_at')->orderByDesc('id');
                break;
        }

        return $query;
    }

    /** Collect an integer id list from multiple possible keys/aliases. */
    private static function idList(array $in, array $keys): array
    {
        $ids = [];
        foreach ($keys as $key) {
            $val = Arr::get($in, $key, []);
            $val = is_array($val) ? $val : (trim((string) $val) === '' ? [] : [trim((string) $val)]);
            foreach ($val as $v) {
                if (ctype_digit((string) $v)) {
                    $ids[] = (int) $v;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    private static function dateOrNull($value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }
        $t = strtotime(trim($value));

        return $t ? date('Y-m-d', $t) : null;
    }

    /** Whititelist a filter payload before it is persisted into a preset row. */
    public static function sanitize(array $in): array
    {
        $allowed = [
            'q', 'status', 'type', 'user_id', 'order_id', 'rating', 'rating_min',
            'rating_max', 'rating_between', 'verified', 'featured',
            'product_ids', 'service_ids', 'preset', 'date_from', 'date_to', 'sort',
        ];

        return Arr::only($in, $allowed);
    }

    /** Convenience: base query with the standard admin eager loads. */
    public static function baseQuery(): Builder
    {
        return Review::query()->with('user:id,name,email');
    }
}
