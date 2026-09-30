@extends('admin.layouts.app')
@section('title', 'Create Shipping Request')
@section('page_title', 'Create Shipping Request')
@section('page_subtitle', 'Masked brief for the shipper network — customer identity stays hidden')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Brief</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.shipping-requests.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $generated['order_id'] ?? old('order_id') }}" required>
                    <div class="form-group">
                        <label>Order #</label>
                        <input type="number" name="order_id" value="{{ $generated['order_id'] ?? old('order_id') }}" class="form-control" required>
                        <small class="text-muted">Leave the brief below auto-derived from this order.</small>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Service type</label>
                                <?php $st = old('service_type', $generated['service_type'] ?? 'ship_for_me'); ?>
                                <select name="service_type" class="form-control" required>
                                    <option value="ship_for_me" @if($st === 'ship_for_me') selected @endif>Ship for Me</option>
                                    <option value="buy_for_me" @if($st === 'buy_for_me') selected @endif>Buy for Me</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Country required</label>
                                <input type="text" name="country_required" maxlength="10"
                                       value="{{ old('country_required', $generated['country_required'] ?? '') }}"
                                       class="form-control text-uppercase" placeholder="e.g. US, UK, AE" required>
                                <small class="text-muted">ISO code — must match shippers' service countries.</small>
                                <?php $srCountry = trim((string) ($generated['country_required'] ?? old('country_required') ?? '')); ?>
                                @if ($srCountry !== '' && !\Illuminate\Support\Facades\DB::table('countries')->where('iso2', $srCountry)->where('is_active', 1)->exists())
                                    <small class="text-danger d-block"><i class="fas fa-exclamation-triangle"></i> <b>{{ $srCountry }}</b> is not enabled in the shipper network — enable it in Admin &rarr; Countries or this request will be hidden from all shippers.</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Required level</label>
                                <?php $rl = old('required_level', $generated['required_level'] ?? 2); ?>
                                <select name="required_level" class="form-control" required>
                                    <option value="1" @if($rl == 1) selected @endif>1 — Buy for Me</option>
                                    <option value="2" @if($rl == 2) selected @endif>2 — Ship for Me</option>
                                    <option value="3" @if($rl == 3) selected @endif>3 — Unlimited</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Brief text (shippers see exactly this)</label>
                        <textarea name="brief_text" rows="14" class="form-control" required>{{ $generated['brief_text'] ?? old('brief_text') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Internal admin notes (never shown to shipper or customer)</label>
                        <textarea name="admin_internal_notes" rows="2" class="form-control">{{ old('admin_internal_notes') }}</textarea>
                    </div>
                    <button type="submit" name="publish" value="0" class="btn btn-secondary">Save as Draft</button>
                    <button type="submit" name="publish" value="1" class="btn btn-success" onclick="return confirm('Publish to eligible shippers now?')">Publish Now</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Settings</h3></div>
            <div class="card-body">
                <form action="{{ route('admin.shipping-requests.generate') }}" method="POST" class="mb-3">
                    @csrf
                    <label>1-click generate from order</label>
                    <div class="input-group">
                        <input type="number" name="order_id" class="form-control" placeholder="Order #" required>
                        <div class="input-group-append"><button class="btn btn-primary">Generate</button></div>
                    </div>
                </form>
                @if($generated)
                    <table class="table table-sm table-striped">
                        <tr><th>Customer code</th><td>{{ $generated['customer_username'] }}</td></tr>
                        <tr><th>Service type</th><td>{{ $generated['service_type'] }}</td></tr>
                        <tr><th>Country</th><td>{{ $generated['country_required'] }}</td></tr>
                        <tr><th>Value range</th><td>${{ $generated['value_range_min'] }} – ${{ $generated['value_range_max'] }}</td></tr>
                        <tr><th>Products</th><td>{{ count($generated['product_details']) }}</td></tr>
                        <tr><th>Required level</th><td>{{ $generated['required_level'] }}</td></tr>
                    </table>
                    <small class="text-muted">These values are pre-filled into the brief above. Adjust the brief text as needed, then save/publish.</small>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
