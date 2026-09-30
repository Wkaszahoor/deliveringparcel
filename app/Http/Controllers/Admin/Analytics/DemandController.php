<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Demand analytics: /admin/analytics/demand (Agent A).
 * Top requested products by grouping orderproducts + offerorderproducts.
 */
class DemandController extends Controller
{
    /**
     * Page: horizontal bar chart (top 10) + responsive table.
     */
    public function index(Request $request)
    {
        $q      = $this->searchTerm($request);
        $top    = $this->topProducts($q, 10);
        $totals = $this->totals($q);

        return view('admin.analytics.demand', [
            'q'      => $q,
            'top'    => $top,
            'totals' => $totals,
        ]);
    }

    /**
     * JSON: product rows, server-side paginated ({data, last_page}).
     * GET /admin/analytics/demand/data?q=...&page=N
     */
    public function data(Request $request)
    {
        $q = $this->searchTerm($request);

        $rows = $this->query($q)
            ->paginate((int) config('admin_analytics.products_per_page', 15))
            ->toArray();

        // Normalise types for the JSON consumer.
        $rows['data'] = array_map(function ($r) {
            return [
                'name'         => $r->name,
                'items'        => (int) $r->items,
                'orders_count' => (int) $r->orders_count,
                'qty'          => (int) $r->qty,
                'image'        => $r->image,
            ];
        }, $rows['data']);

        return response()->json($rows);
    }

    /* ------------------------------------------------------------------ */

    protected function searchTerm(Request $request): string
    {
        $q = trim((string) $request->input('q', ''));
        return mb_substr($q, 0, 100);
    }

    /**
     * Union of both product tables grouped by normalised product name.
     */
    protected function query(string $q)
    {
        $len = (int) config('admin_analytics.demand_name_length', 80);

        $union = DB::table('orderproducts')
            ->select('order_id', 'productname', 'productquantity', 'image')
            ->unionAll(
                DB::table('offerorderproducts')
                    ->select('offer_id as order_id', 'productname', 'productquantity', 'image')
            );

        return DB::query()
            ->fromSub($union, 'p')
            ->selectRaw("
                SUBSTRING(TRIM(p.productname), 1, {$len}) AS name,
                COUNT(*) AS items,
                COUNT(DISTINCT p.order_id) AS orders_count,
                SUM(p.productquantity) AS qty,
                MIN(NULLIF(TRIM(p.image), '')) AS image
            ")
            ->when($q !== '', function ($b) use ($q) {
                $b->where('p.productname', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%');
            })
            ->groupBy('name')
            ->orderByDesc('qty')
            ->orderByDesc('items');
    }

    protected function topProducts(string $q, int $limit): array
    {
        return $this->query($q)->limit($limit)->get()->map(function ($r) {
            return [
                'name' => $r->name,
                'qty'  => (int) $r->qty,
            ];
        })->all();
    }

    protected function totals(string $q): array
    {
        $minutes = (int) config('admin_analytics.cache_minutes', 10);

        return Cache::remember('analytics.demand.totals.v1', now()->addMinutes($minutes), function () {
            $row = DB::table('orderproducts')->selectRaw('COUNT(*) c')->first();
            $c1  = (int) $row->c;
            $row = DB::table('offerorderproducts')->selectRaw('COUNT(*) c')->first();
            $c2  = (int) $row->c;

            return ['line_items' => $c1 + $c2];
        });
    }
}
