@extends('admin.layouts.app')
@section('title', 'Assignment #' . $assignment->id)
@section('page_title', 'Assignment — Order #' . $assignment->order_id)
@section('page_subtitle', 'Shipper ' . ($assignment->shipper->user->shipper_username ?? '') . ' · ' . str_replace('_', ' ', $assignment->status))

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card card-info">
    <div class="card-header"><h3 class="card-title">Status &amp; Actions</h3></div>
    <div class="card-body">
        <div class="mb-2">
            Status: <span class="badge badge-lg badge-{{ $assignment->status === 'completed' ? 'success' : 'info' }}">{{ str_replace('_', ' ', $assignment->status) }}</span>
            @if($assignment->purchase_deadline && $assignment->isPurchaseOverdue())<span class="badge badge-danger">PURCHASE OVERDUE</span>@endif
            <span class="ml-2 text-muted">Shipper earns ${{ number_format($assignment->shipper_fee, 2) }} · platform ${{ number_format($assignment->platform_fee, 2) }} · hold ${{ number_format($assignment->wallet_hold_amount, 2) }}</span>
        </div>
        <div class="btn-group flex-wrap">
            @if($assignment->status === 'tracking_shared')
                <form action="{{ route('admin.shipper-assignments.release-payment', $assignment->id) }}" method="POST" onsubmit="return confirm('Release payment to shipper wallet?')">@csrf
                    <button class="btn btn-success btn-sm"><i class="fas fa-dollar-sign"></i> Release Payment</button></form>
            @endif
            @if($assignment->deliveryAddress && !$assignment->deliveryAddress->forwarded_to_shipper)
                <form action="{{ route('admin.shipper-assignments.forward-address', $assignment->id) }}" method="POST" class="form-inline">@csrf
                    <select name="forward_level" class="form-control form-control-sm">
                        <option value="full">Full address + phone</option>
                        <option value="address_only">Address only (no phone)</option>
                        <option value="city_country">City + country only</option>
                    </select>
                    <button class="btn btn-primary btn-sm">Forward Address</button></form>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">1. Proof Photos</h3></div>
            <div class="card-body">
                @forelse($assignment->proofs as $p)
                    <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <b>{{ ucwords(str_replace('_', ' ', $p->proof_type)) }}</b>
                            <small class="text-muted d-block">{{ $p->created_at->diffForHumans() }} · {{ $p->shipper_notes }}</small>
                            <span class="badge badge-{{ $p->admin_approved ? 'success' : 'warning' }}">{{ $p->admin_approved ? 'approved' : 'pending' }}</span>
                            @if($p->customer_visible)<span class="badge badge-info">customer-visible</span>@endif
                        </div>
                        <div class="text-right">
                            <a href="{{ route('admin.shipper-assignments.proofs.stream', $p->id) }}" target="_blank" class="btn btn-sm btn-secondary">View</a>
                            @if(!$p->admin_approved)
                                <form action="{{ route('admin.shipper-assignments.approve-proof', [$assignment->id, $p->id]) }}" method="POST" class="mt-1">@csrf
                                    <input type="hidden" name="customer_visible" value="1">
                                    <button class="btn btn-sm btn-success btn-block">Approve + Show Customer</button></form>
                                <form action="{{ route('admin.shipper-assignments.reject-proof', [$assignment->id, $p->id]) }}" method="POST" class="mt-1">@csrf
                                    <input type="hidden" name="note" value="Please re-upload clearer photos">
                                    <button class="btn btn-sm btn-danger btn-block">Reject</button></form>
                            @endif
                        </div>
                    </div>
                @empty<p class="text-muted">No proofs uploaded yet.</p>@endforelse
            </div>
        </div>

        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">2. Delivery Address (customer-submitted)</h3></div>
            <div class="card-body">
                @if($assignment->deliveryAddress)
                    @php($addr = $assignment->deliveryAddress)
                    <table class="table table-sm table-striped">
                        <tr><th>Recipient</th><td>{{ $addr->recipient_name }}</td></tr>
                        <tr><th>Address</th><td>{{ $addr->address_line_1 }} {{ $addr->address_line_2 }}, {{ $addr->city }} {{ $addr->postal_code }}, {{ $addr->country }}</td></tr>
                        <tr><th>Phone</th><td>{{ $addr->phone ?? '—' }}</td></tr>
                        <tr><th>Instructions</th><td>{{ $addr->delivery_instructions ?? '—' }}</td></tr>
                        <tr><th>Forwarded</th><td>{{ $addr->forwarded_to_shipper ? 'Yes (' . $addr->forward_level . ', ' . $addr->forwarded_at->diffForHumans() . ')' : 'No' }}</td></tr>
                    </table>
                @else<p class="text-muted">Customer has not submitted the delivery address yet.</p>@endif
            </div>
        </div>

        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">3. Tracking (shipper-submitted)</h3></div>
            <div class="card-body">
                @if($assignment->trackingDetails)
                    @php($t = $assignment->trackingDetails)
                    <table class="table table-sm table-striped">
                        <tr><th>Carrier</th><td>{{ $t->carrier }}</td></tr>
                        <tr><th>Number</th><td><code>{{ $t->tracking_number }}</code></td></tr>
                        <tr><th>URL</th><td>{{ $t->tracking_url ?: '—' }}</td></tr>
                        <tr><th>Shipped</th><td>{{ $t->ship_date->format('d M Y') }}</td></tr>
                        <tr><th>Shared with customer</th><td>{{ $t->shared_with_customer ? 'Yes' : 'No' }}</td></tr>
                    </table>
                    @if(!$t->shared_with_customer)
                        <form action="{{ route('admin.shipper-assignments.share-tracking', [$assignment->id, $t->id]) }}" method="POST">@csrf
                            <button class="btn btn-primary btn-sm">Share Tracking with Customer</button></form>
                    @endif
                @else<p class="text-muted">Shipper has not submitted tracking yet.</p>@endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Order Summary</h3></div>
            <div class="card-body">
                <a href="{{ url('admin/orders/' . $assignment->order_id) }}" class="btn btn-outline-primary btn-sm mb-2">Open Order #{{ $assignment->order_id }} →</a>
                <table class="table table-sm table-striped">
                    <tr><th>Shipper</th><td>{{ $assignment->shipper->user->shipper_username ?? '' }} (L{{ $assignment->shipper->level }}, ⭐{{ $assignment->shipper->rating }})</td></tr>
                    <tr><th>Purchase deadline</th><td>{{ $assignment->purchase_deadline?->diffForHumans() ?? '—' }}</td></tr>
                    <tr><th>Wallet credit on release</th><td>${{ number_format($assignment->wallet_credit_amount, 2) }} + ${{ number_format($assignment->wallet_hold_amount, 2) }} held</td></tr>
                    <tr><th>Completed at</th><td>{{ $assignment->completed_at?->diffForHumans() ?? '—' }}</td></tr>
                </table>
                <form action="{{ route('admin.shipper-assignments.add-note', $assignment->id) }}" method="POST">@csrf
                    <label>Internal notes</label>
                    <textarea name="admin_notes" rows="2" class="form-control">{{ $assignment->admin_notes }}</textarea>
                    <button class="btn btn-sm btn-secondary mt-1">Save Note</button></form>
            </div>
        </div>
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Admin ⇄ Shipper Chat ({{ $unread_chat ?? 0 }} unread)</h3></div>
            <div class="card-body">
                <div style="max-height:300px;overflow:auto;border:1px solid #eee;padding:10px" class="mb-2">
                    @forelse($assignment->adminChat->sortBy('created_at') as $m)
                        <div class="mb-2"><b class="text-{{ $m->from_type === 'admin' ? 'primary' : 'success' }}">{{ ucfirst($m->from_type) }}</b>
                            <small class="text-muted">{{ $m->created_at->diffForHumans() }}</small>
                            <div style="white-space:pre-wrap">{{ $m->message }}</div></div>
                    @empty<p class="text-muted mb-0">No messages yet.</p>@endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
