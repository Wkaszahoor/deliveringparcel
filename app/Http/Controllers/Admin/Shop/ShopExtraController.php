<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\ShopReview;
use App\Models\ShopCoupon;
use App\Models\ShopOrder;
use App\Models\ShopOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shop: categories CRUD + reviews moderation + coupons CRUD + orders read + analytics.
 */
class ShopExtraController extends Controller
{
    /* ---------------- Categories ---------------- */

    public function categoriesIndex()
    {
        return view('admin.shop.categories.index');
    }

    public function categoriesData(Request $request)
    {
        $query = ShopCategory::query();
        if ($q = trim((string) $request->input('q', ''))) {
            $query->where('name', 'like', '%' . $q . '%');
        }
        $query->orderBy('name');

        return response()->json($query->paginate(dp_per_page($request))->through(function (ShopCategory $c) {
            return [
                'id'       => $c->id,
                'name'     => $c->name,
                'slug'     => $c->slug,
                'parent'   => optional($c->parent)->name,
                'products' => $c->products()->count(),
                'active'   => (bool) $c->is_active,
                'urls'     => [
                    'edit'   => route('admin.shop.categories.edit', $c->id),
                    'delete' => route('admin.shop.categories.destroy', $c->id),
                ],
            ];
        })->toArray());
    }

    public function categoriesCreate()
    {
        return view('admin.shop.categories.form', ['category' => new ShopCategory(['is_active' => true]), 'categories' => ShopCategory::orderBy('name')->get()]);
    }

    public function categoriesStore(Request $request)
    {
        ShopCategory::create($this->categoryRules($request));

        return redirect()->route('admin.shop.categories.index')->with('success', 'Category created.');
    }

    public function categoriesEdit(ShopCategory $category)
    {
        return view('admin.shop.categories.form', ['category' => $category, 'categories' => ShopCategory::where('id', '!=', $category->id)->orderBy('name')->get()]);
    }

    public function categoriesUpdate(Request $request, ShopCategory $category)
    {
        $category->update($this->categoryRules($request, $category->id));

        return redirect()->route('admin.shop.categories.index')->with('success', 'Category updated.');
    }

    public function categoriesDestroy(ShopCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.shop.categories.index')->with('success', 'Category deleted.');
    }

    private function categoryRules(Request $request, $ignoreId = null)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'nullable|string|max:255|unique:shop_categories,slug' . ($ignoreId ? ',' . $ignoreId : ''),
            'parent_id' => 'nullable|integer|exists:shop_categories,id',
            'is_active' => 'nullable|boolean',
        ]);
        $data['slug'] = $data['slug'] ?: \Str::slug($data['name']);
        $data['parent_id'] = $data['parent_id'] ?: null;
        $data['is_active'] = $request->has('is_active');

        return $data;
    }

    /* ---------------- Reviews ---------------- */

    public function reviewsIndex()
    {
        $stats = [
            'avg'     => (float) ShopReview::avg('rating') ?: 0,
            'pending' => ShopReview::where('is_approved', false)->count(),
        ];
        foreach ([5, 4, 3, 2, 1] as $s) {
            $stats['dist'][$s] = ShopReview::where('rating', $s)->count();
        }

        return view('admin.shop.reviews.index', ['stats' => $stats]);
    }

    public function reviewsData(Request $request)
    {
        $query = ShopReview::query()->with('product:id,name,slug', 'user:id,name');
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)->orWhere('body', 'like', $like)
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $like));
            });
        }
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }
        if ($request->filled('approved')) {
            $query->where('is_approved', (int) $request->input('approved'));
        }
        $query->orderByDesc('id');

        return response()->json($query->paginate(dp_per_page($request))->through(function (ShopReview $r) {
            return [
                'id'       => $r->id,
                'product'  => optional($r->product)->name ?: '(deleted product)',
                'user'     => optional($r->user)->name ?: 'guest',
                'rating'   => $r->rating,
                'title'    => $r->title,
                'body'     => \Str::limit(strip_tags((string) $r->body), 90),
                'approved' => (bool) $r->is_approved,
                'at'       => optional($r->created_at)->format('M d, Y'),
                'urls'     => [
                    'approve' => route('admin.shop.reviews.approve', $r->id),
                    'reject'  => route('admin.shop.reviews.reject', $r->id),
                    'delete'  => route('admin.shop.reviews.destroy', $r->id),
                ],
            ];
        })->toArray());
    }

    public function reviewApprove(ShopReview $review)
    {
        $review->update(['is_approved' => true]);

        return redirect()->route('admin.shop.reviews.index')->with('success', 'Review approved.');
    }

    public function reviewReject(ShopReview $review)
    {
        $review->update(['is_approved' => false]);

        return redirect()->route('admin.shop.reviews.index')->with('success', 'Review unapproved.');
    }

    public function reviewDestroy(ShopReview $review)
    {
        $review->delete();

        return redirect()->route('admin.shop.reviews.index')->with('success', 'Review deleted.');
    }

    /* ---------------- Coupons ---------------- */

    public function couponsIndex()
    {
        return view('admin.shop.coupons.index');
    }

    public function couponsData(Request $request)
    {
        $query = ShopCoupon::query();
        if ($q = trim((string) $request->input('q', ''))) {
            $query->where('code', 'like', '%' . $q . '%');
        }
        $query->orderByDesc('id');

        return response()->json($query->paginate(dp_per_page($request))->through(function (ShopCoupon $c) {
            return [
                'id'      => $c->id,
                'code'    => $c->code,
                'type'    => $c->type,
                'value'   => $c->type === 'percent' ? rtrim(rtrim(number_format((float) $c->value, 1, '.', ''), '0'), '.') . '%' : '$' . number_format((float) $c->value, 2),
                'usage'   => $c->used_count . ($c->usage_limit ? ' / ' . $c->usage_limit : ''),
                'active'  => (bool) $c->is_active,
                'ends'    => optional($c->ends_at)->format('M d, Y'),
                'urls'    => [
                    'edit'   => route('admin.shop.coupons.edit', $c->id),
                    'delete' => route('admin.shop.coupons.destroy', $c->id),
                ],
            ];
        })->toArray());
    }

    public function couponsCreate()
    {
        return view('admin.shop.coupons.form', ['coupon' => new ShopCoupon(['is_active' => true, 'type' => 'percent'])]);
    }

    public function couponsStore(Request $request)
    {
        ShopCoupon::create($this->couponRules($request));

        return redirect()->route('admin.shop.coupons.index')->with('success', 'Coupon created.');
    }

    public function couponsEdit(ShopCoupon $coupon)
    {
        return view('admin.shop.coupons.form', ['coupon' => $coupon]);
    }

    public function couponsUpdate(Request $request, ShopCoupon $coupon)
    {
        $coupon->update($this->couponRules($request, $coupon->id));

        return redirect()->route('admin.shop.coupons.index')->with('success', 'Coupon updated.');
    }

    public function couponsDestroy(ShopCoupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.shop.coupons.index')->with('success', 'Coupon deleted.');
    }

    private function couponRules(Request $request, $ignoreId = null)
    {
        $data = $request->validate([
            'code'        => 'required|string|max:50|unique:shop_coupons,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'type'        => 'required|in:percent,fixed',
            'value'       => 'required|numeric|min:0|max:99999999',
            'min_order'   => 'nullable|numeric|min:0',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['code'] = strtoupper(trim($data['code']));
        $data['min_order'] = $data['min_order'] ?? null;
        $data['usage_limit'] = $data['usage_limit'] ?? null;
        $data['is_active'] = $request->has('is_active');

        return $data;
    }

    /* ---------------- Orders (read + status) ---------------- */

    public function ordersIndex()
    {
        return view('admin.shop.orders.index', ['statusMap' => config('admin_shop.order_statuses')]);
    }

    public function ordersData(Request $request)
    {
        $query = ShopOrder::query()->with('user:id,name,email');
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('code', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $query->orderByDesc('id');
        $map = config('admin_shop.order_statuses');

        return response()->json($query->paginate(dp_per_page($request))->through(function (ShopOrder $o) use ($map) {
            $m = $map[$o->status] ?? ['label' => $o->status, 'color' => 'bg-secondary'];
            return [
                'id'     => $o->id,
                'code'   => $o->code,
                'user'   => optional($o->user)->name ?: '—',
                'email'  => optional($o->user)->email,
                'total'  => number_format((float) $o->total, 2),
                'items'  => $o->items()->count(),
                'status' => $m['label'],
                'color'  => $m['color'],
                'at'     => optional($o->created_at)->format('M d, Y'),
                'urls'   => ['show' => route('admin.shop.orders.show', $o->id)],
            ];
        })->toArray());
    }

    public function ordersShow(ShopOrder $order)
    {
        $order->load('items', 'user:id,name,email');

        return view('admin.shop.orders.show', [
            'order'     => $order,
            'statusMap' => config('admin_shop.order_statuses'),
            // PM-015: force-method options + the effective one for this order.
            'payMethods' => \App\Models\PaymentMethod::enabled()->orderBy('priority')->get(),
            'forcedNow'  => app(\App\Services\Payments\PaymentService::class)->forcedMethodForShopOrder($order)?->code,
        ]);
    }

    /**
     * PM-015: set/clear the per-shop-order forced payment method (mirrors the
     * per-order negotiation tool). Order-level wins over product-level forcing.
     */
    public function updatePaymentMethod(Request $request, ShopOrder $order)
    {
        $validated = $request->validate([
            'method' => 'nullable|string|max:30',
            'note'   => 'nullable|string|max:255',
        ]);

        $code = trim((string) ($validated['method'] ?? ''));

        if ($code === '') {
            $order->update(['forced_payment_method_code' => null]);

            return response()->json([
                'ok'      => true,
                'cleared' => true,
                'message' => 'Payment method override cleared for ' . $order->code . ' — product forcing (if any) applies again.',
            ]);
        }

        $method = \App\Models\PaymentMethod::where('code', $code)->where('is_enabled', true)->first();
        if (!$method) {
            return response()->json(['ok' => false, 'message' => 'Unknown or disabled payment method: ' . $code], 422);
        }

        $order->update(['forced_payment_method_code' => $method->code]);

        return response()->json([
            'ok'      => true,
            'cleared' => false,
            'message' => $method->name . ' is now the required payment method for ' . $order->code . '.',
        ]);
    }

    /* ---------------- Analytics ---------------- */

    public function analytics()
    {
        $monthStart = now()->startOfMonth();
        $kpis = [
            'revenue'   => (float) ShopOrder::whereIn('status', ['paid', 'fulfilled'])->sum('total'),
            'orders'    => ShopOrder::count(),
            'avg_order' => (float) ShopOrder::whereIn('status', ['paid', 'fulfilled'])->avg('total') ?: 0,
            'month_rev' => (float) ShopOrder::where('created_at', '>=', $monthStart)->whereIn('status', ['paid', 'fulfilled'])->sum('total'),
        ];
        $topProducts = ShopOrderItem::select('name', DB::raw('SUM(qty) as qty'), DB::raw('SUM(qty * price) as revenue'))
            ->groupBy('name')->orderByDesc('qty')->limit(10)->get();

        return view('admin.shop.analytics.index', ['kpis' => $kpis, 'topProducts' => $topProducts]);
    }
}
