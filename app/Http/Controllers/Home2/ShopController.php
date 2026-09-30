<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public const CART_KEY = 'h2_shop_cart';

    public function index(Request $request)
    {
        $q = ShopProduct::query()->where('is_active', true)
            ->when($request->filled('q'), function ($s) use ($request) {
                $v = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $request->input('q')) . '%';
                $s->where(function ($w) use ($v) {
                    $w->where('name', 'like', $v)->orWhere('description', 'like', $v);
                });
            })
            ->when($request->boolean('featured'), fn ($s) => $s->where('featured', true))
            ->orderByDesc('featured')->orderByDesc('id');

        return view('home2.shop.index', [
            'products'  => $q->paginate(dp_per_page($request))->withQueryString(),
            'cartCount' => $this->count(),
        ]);
    }

    public function show(string $slug)
    {
        $product = ShopProduct::where('is_active', true)->where('slug', $slug)->firstOrFail();
        return view('home2.shop.product', ['product' => $product, 'cartCount' => $this->count()]);
    }

    public function cart()
    {
        return view('home2.shop.cart', ['cart' => $this->detailedCart()]);
    }

    public function add(Request $request, ShopProduct $product)
    {
        $request->validate(['qty' => 'nullable|integer|min:1|max:99']);
        $qty = (int) $request->input('qty', 1);
        if (!$product->is_active) {
            return back()->with('error', 'This product is unavailable.');
        }
        if ($product->stock < 1) {
            return back()->with('error', 'Out of stock.');
        }

        $cart = session(self::CART_KEY, []);
        $have = (int) ($cart[$product->id] ?? 0);
        $new  = min(99, $have + $qty);
        if ($new > (int) $product->stock) {
            $new = (int) $product->stock;
        }
        if ($new === $have) {
            return back()->with('error', 'Cannot add more than available stock.');
        }
        $cart[$product->id] = $new;
        session([self::CART_KEY => $cart]);
        return redirect()->route('home2.shop.cart')->with('success', 'Added to cart.');
    }

    public function update(Request $request)
    {
        $qty = (array) $request->input('qty', []);
        $cart = session(self::CART_KEY, []);
        foreach ($qty as $id => $n) {
            $id = (int) $id; $n = (int) $n;
            if ($n <= 0) { unset($cart[$id]); continue; }
            $stock = (int) ShopProduct::whereKey($id)->value('stock');
            $cart[$id] = max(0, min(99, min($n, $stock)));
        }
        session([self::CART_KEY => $cart]);
        return redirect()->route('home2.shop.cart')->with('success', 'Cart updated.');
    }

    public function clear()
    {
        session()->forget(self::CART_KEY);
        return redirect()->route('home2.shop.cart')->with('success', 'Cart cleared.');
    }

    /** Detailed cart rows with live prices (server-authoritative totals). */
    public function detailedCart(): array
    {
        $cart = session(self::CART_KEY, []);
        if (!$cart) {
            return ['rows' => collect(), 'total' => 0.0];
        }
        $rows = ShopProduct::where('is_active', true)->whereIn('id', array_keys($cart))->get()
            ->map(function ($p) use ($cart) {
                $qty = (int) $cart[$p->id];
                return (object) [
                    'id' => $p->id, 'name' => $p->name, 'slug' => $p->slug,
                    'image' => ($p->images[0] ?? null), 'price' => (float) $p->price,
                    'qty' => $qty, 'line' => round($qty * (float) $p->price, 2),
                ];
            })->filter(fn ($r) => $r->qty > 0);
        return ['rows' => $rows, 'total' => round($rows->sum('line'), 2)];
    }

    public function count(): int
    {
        return array_sum(array_map('intval', session(self::CART_KEY, [])));
    }
}
