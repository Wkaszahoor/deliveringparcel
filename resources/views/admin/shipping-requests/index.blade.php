@extends('admin.layouts.app')
@section('title', 'Shipping Requests')
@section('page_title', 'Shipping Requests')
@section('page_subtitle', 'Masked briefs published to the shipper network')

@section('content')
<div class="card card-default">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Requests</h3>
        <div class="card-tools">
            <a href="{{ route('admin.shipping-requests.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Request</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline mb-3">
            <input type="text" name="username" value="{{ request('username') }}" class="form-control form-control-sm mr-2" placeholder="CUS- / DP-SR- reference">
            <input type="text" name="country" value="{{ request('country') }}" class="form-control form-control-sm mr-2" placeholder="Country" style="max-width:100px">
            <select name="status" class="form-control form-control-sm mr-2">
                <option value="">Status…</option>
                @foreach(['draft','open','frozen','assigned','completed','cancelled'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <select name="service_type" class="form-control form-control-sm mr-2">
                <option value="">Type…</option>
                <option value="buy_for_me" {{ request('service_type') === 'buy_for_me' ? 'selected' : '' }}>Buy for Me</option>
                <option value="ship_for_me" {{ request('service_type') === 'ship_for_me' ? 'selected' : '' }}>Ship for Me</option>
            </select>
            <button class="btn btn-sm btn-primary">Filter</button>
        </form>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>Reference</th><th>Customer</th><th>Country</th><th>Type</th><th>Responses</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($requests as $r)
                    <tr>
                        <td><b>{{ $r->reference }}</b></td>
                        <td>{{ $r->customer_username }}</td>
                        <td>{{ $r->country_required }}</td>
                        <td>{{ $r->service_type === 'buy_for_me' ? 'Buy' : 'Ship' }}</td>
                        <td>{{ $r->quotes_count }}</td>
                        <td>
                            <span class="badge badge-{{ $r->status === 'open' ? 'success' : ($r->status === 'assigned' ? 'info' : 'secondary') }}">{{ $r->status }}</span>
                            @if($r->is_frozen)<span class="badge badge-warning">frozen</span>@endif
                        </td>
                        <td><a href="{{ route('admin.shipping-requests.show', $r->id) }}" class="btn btn-sm btn-primary">Open</a></td>
                    </tr>
                @empty<tr><td colspan="7" class="text-muted text-center">No shipping requests yet — create one from an order.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $requests->links() }}
    </div>
</div>
@endsection
