<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.shop.products.index', [
            'categories' => ShopCategory::orderBy('name')->get(),
            'filters'    => ['q' => request('q', ''), 'category' => (string) request('category', ''), 'active' => (string) request('active', '')],
        ]);
    }

    public function data(Request $request)
    {
        $query = ShopProduct::query()->with('category:id,name');
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)->orWhere('sku', 'like', $like)->orWhere('slug', 'like', $like);
            });
        }
        if ($cat = (int) $request->input('category')) {
            $query->where('shop_category_id', $cat);
        }
        if ($request->input('active') !== '' && $request->input('active') !== null) {
            $query->where('is_active', (int) $request->input('active'));
        }
        $query->orderByDesc('id');

        return response()->json($query->paginate(dp_per_page($request))->through(function (ShopProduct $p) {
            $stock = $p->stock > 5 ? ['In stock', 'badge-success']
                : ($p->stock > 0 ? ['Low (' . $p->stock . ')', 'badge-warning'] : ['Out', 'badge-danger']);
            return [
                'id'       => $p->id,
                'name'     => $p->name,
                'sku'      => $p->sku,
                'category' => optional($p->category)->name,
                'price'    => number_format((float) $p->price, 2),
                'stock'    => $stock[0],
                'stock_c'  => $stock[1],
                'active'   => (bool) $p->is_active,
                'image'    => $p->firstImage() ? asset($p->firstImage()) : null,
                'urls'     => [
                    'edit'   => route('admin.shop.products.edit', $p->id),
                    'delete' => route('admin.shop.products.destroy', $p->id),
                ],
            ];
        })->toArray());
    }

    public function create()
    {
        return view('admin.shop.products.form', [
            'product'    => new ShopProduct(['is_active' => true, 'stock' => 0, 'price' => 0]),
            'categories' => ShopCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        ShopProduct::create($this->validated($request) + ['images' => $this->storeImages($request)]);

        return redirect()->route('admin.shop.products.index')->with('success', 'Product created.');
    }

    public function edit(ShopProduct $product)
    {
        return view('admin.shop.products.form', [
            'product'    => $product,
            'categories' => ShopCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ShopProduct $product)
    {
        $data = $this->validated($request, $product->id);
        $images = $this->storeImages($request);
        if ($images) {
            $data['images'] = array_merge($product->images ?? [], $images);
        }
        $product->update($data);

        return redirect()->route('admin.shop.products.index')->with('success', 'Product updated.');
    }

    public function destroy(ShopProduct $product)
    {
        $product->delete(); // soft

        return redirect()->route('admin.shop.products.index')->with('success', 'Product deleted.');
    }

    private function validated(Request $request, $ignoreId = null)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:shop_products,slug' . ($ignoreId ? ',' . $ignoreId : ''),
            'sku'               => 'nullable|string|max:100',
            'shop_category_id'  => 'nullable|integer|exists:shop_categories,id',
            'description'       => 'nullable|string|max:4294900000',
            'price'             => 'required|numeric|min:0|max:99999999.99',
            'compare_price'     => 'nullable|numeric|min:0|max:99999999.99',
            'stock'             => 'required|integer|min:0|max:2147483647',
            'is_active'         => 'nullable|boolean',
            'featured'          => 'nullable|boolean',
            'images.*'          => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:2048',
            // PM-015: per-product forced payment gateway for shop checkout.
            'forced_payment_method_code' => ['nullable', \Illuminate\Validation\Rule::in(\App\Models\PaymentMethod::enabled()->pluck('code')->all())],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']) . '-' . strtolower(Str::random(4));
        $data['shop_category_id'] = $data['shop_category_id'] ?: null;
        $data['compare_price'] = $data['compare_price'] ?? null;
        $data['is_active'] = $request->has('is_active');
        $data['featured'] = $request->has('featured');
        $data['forced_payment_method_code'] = $data['forced_payment_method_code'] ?: null;

        return $data;
    }

    private function storeImages(Request $request): array
    {
        $paths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // UploadGuard: allow-list mimes/ext, size cap, random name (SE-006).
                if ($path = \App\Support\UploadGuard::store($file, 'shop/products')) {
                    $paths[] = $path;
                }
            }
        }

        return $paths;
    }
}
