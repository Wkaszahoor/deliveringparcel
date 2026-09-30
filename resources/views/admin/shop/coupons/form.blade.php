@extends('admin.layouts.app')
@section('title', isset($coupon->id) ? 'Edit Coupon' : 'New Coupon')
@section('page_title', isset($coupon->id) ? 'Edit Coupon' : 'New Coupon')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ isset($coupon->id) ? route('admin.shop.coupons.update', $coupon->id) : route('admin.shop.coupons.store') }}">
                    @csrf
                    @if (isset($coupon->id)) @method('PUT') @endif
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="co-code">Code <span class="text-danger">*</span></label>
                            <input id="co-code" type="text" name="code" value="{{ old('code', $coupon->code) }}" class="form-control text-uppercase" required maxlength="50">
                            @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="co-type">Type</label>
                            <select id="co-type" name="type" class="custom-select">
                                <option value="percent" {{ old('type', $coupon->type) === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed ($)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="co-value">Value <span class="text-danger">*</span></label>
                            <input id="co-value" type="number" step="0.01" min="0" name="value" value="{{ old('value', $coupon->value) }}" class="form-control" required>
                            @error('value')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="co-min">Min order ($)</label>
                            <input id="co-min" type="number" step="0.01" min="0" name="min_order" value="{{ old('min_order', $coupon->min_order) }}" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="co-start">Starts</label>
                            <input id="co-start" type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="co-end">Ends</label>
                            <input id="co-end" type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($coupon->ends_at)->format('Y-m-d\TH:i')) }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="co-limit">Usage limit</label>
                            <input id="co-limit" type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="form-control">
                        </div>
                        <div class="form-group col-md-6 d-flex align-items-end">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="co-active" name="is_active" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="co-active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.shop.coupons.index') }}" class="btn btn-outline-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary flex-fill">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
