@extends('admin.layouts.app')
@section('title', 'Shipper Assignments')
@section('page_title', 'Shipper Assignments')
@section('page_subtitle', 'Live 3-party workflow tracking')

@section('content')
<div class="card card-default">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-tasks"></i> Assignments</h3></div>
    <div class="card-body">
        <form method="GET" class="form-inline mb-3">
            <select name="status" class="form-control form-control-sm mr-2">
                <option value="">Status…</option>
                @foreach(['assigned','purchased','package_received','proof_uploaded','proof_approved','address_received','address_forwarded','tracking_added','tracking_shared','delivered','completed','cancelled'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ str_replace('_',' ',ucwords($st,'_')) }}</option>
                @endforeach
            </select>
            <input type="number" name="order_id" value="{{ request('order_id') }}" class="form-control form-control-sm mr-2" placeholder="Order #" style="max-width:120px">
            <input type="text" name="shipper" value="{{ request('shipper') }}" class="form-control form-control-sm mr-2" placeholder="SHP-username">
            <button class="btn btn-sm btn-primary">Filter</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>Order</th><th>Shipper</th><th>Type</th><th>Status</th><th>Days active</th><th>Shipper fee</th><th></th></tr></thead>
                <tbody>
                @forelse($assignments as $a)
                    <tr>
                        <td><b>#{{ $a->order_id }}</b></td>
                        <td>{{ $a->shipper->user->shipper_username ?? '—' }}</td>
                        <td>{{ $a->request?->service_type === 'buy_for_me' ? 'Buy' : 'Ship' }}</td>
                        <td><span class="badge badge-{{ in_array($a->status, ['completed']) ? 'success' : (in_array($a->status, ['proof_uploaded','address_received','tracking_added']) ? 'warning' : 'info') }}">{{ str_replace('_', ' ', $a->status) }}</span></td>
                        <td>{{ $a->created_at->diffInDays(now()) }}</td>
                        <td>${{ number_format($a->shipper_fee, 2) }}</td>
                        <td><a href="{{ route('admin.shipper-assignments.show', $a->id) }}" class="btn btn-sm btn-primary">Open</a></td>
                    </tr>
                @empty<tr><td colspan="7" class="text-muted text-center">No assignments match.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $assignments->links() }}
    </div>
</div>
@endsection
