@extends('admin.layouts.app')
@section('title', isset($product->id) ? 'Edit Product' : 'New Product')
@section('page_title', isset($product->id) ? 'Edit Product' : 'New Product')
@section('page_subtitle', $product->name ?? '')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" action="{{ isset($product->id) ? route('admin.shop.products.update', $product->id) : route('admin.shop.products.store') }}">
                    @csrf
                    @if (isset($product->id)) @method('PUT') @endif

                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="sp-name">Name <span class="text-danger">*</span></label>
                            <input id="sp-name" type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required maxlength="255">
                            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="sp-sku">SKU</label>
                            <input id="sp-sku" type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="sp-cat">Category</label>
                            <select id="sp-cat" name="shop_category_id" class="custom-select">
                                <option value="">—</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('shop_category_id', $product->shop_category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="sp-price">Price ($) <span class="text-danger">*</span></label>
                            <input id="sp-price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="form-control" required>
                            @error('price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="sp-cmp">Compare price ($)</label>
                            <input id="sp-cmp" type="number" step="0.01" min="0" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}" class="form-control">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="sp-stock">Stock <span class="text-danger">*</span></label>
                            <input id="sp-stock" type="number" min="0" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control" required>
                            @error('stock')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sp-desc">Description</label>
                        <textarea id="sp-desc" name="description" rows="5" class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="sp-imgs">Images (jpg/png/webp, max 2MB each)</label>
                        <input id="sp-imgs" type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif" class="form-control-file">
                        @error('images.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        @if (!empty($product->images))
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach ($product->images as $img)
                                    <img src="{{ asset($img) }}" class="dp-thumb" alt="">
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="sp-forced">Force payment method (PM-015)</label>
                        <select id="sp-forced" name="forced_payment_method_code" class="custom-select">
                            <option value="">— No forcing (normal checkout) —</option>
                            @foreach (\App\Models\PaymentMethod::enabled()->orderBy('priority')->get() as $pm)
                                <option value="{{ $pm->code }}" {{ old('forced_payment_method_code', $product->forced_payment_method_code) === $pm->code ? 'selected' : '' }}>{{ $pm->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Shop orders containing this product can only be paid with the selected gateway.</small>
                    </div>
                    <div class="form-group d-flex gap-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="sp-active" name="is_active" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="sp-active">Active</label>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="sp-featured" name="featured" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="sp-featured">Featured</label>
                        </div>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.shop.products.index') }}" class="btn btn-outline-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary flex-fill">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
