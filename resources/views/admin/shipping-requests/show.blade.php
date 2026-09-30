@extends('admin.layouts.app')
@section('title', 'Request ' . $request->reference)
@section('page_title', 'Shipping Request ' . $request->reference)
@section('page_subtitle', 'Status: ' . $request->status . ($request->is_frozen ? ' (frozen)' : ''))

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="row">
    <div class="col-md-7">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Brief (what shippers see)</h3></div>
            <div class="card-body"><pre class="mb-0" style="white-space:pre-wrap">{{ $request->brief_text }}</pre></div>
        </div>
        @if($request->admin_internal_notes)
            <div class="card card-warning">
                <div class="card-header"><h3 class="card-title">Internal notes</h3></div>
                <div class="card-body">{{ $request->admin_internal_notes }}</div>
            </div>
        @endif
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Admin ⇄ Shipper Chat</h3></div>
            <div class="card-body">
                <div style="max-height:260px;overflow:auto;border:1px solid #eee;padding:10px" class="mb-2">
                    @forelse($request->adminChat as $m)
                        <div class="mb-2"><b class="text-{{ $m->from_type === 'admin' ? 'primary' : 'success' }}">{{ ucfirst($m->from_type) }}</b>
                            <small class="text-muted">{{ $m->created_at->diffForHumans() }}</small>
                            <div style="white-space:pre-wrap">{{ $m->message }}</div></div>
                    @empty<p class="text-muted mb-0">No messages yet.</p>@endforelse
                </div>
                <form action="{{ route('admin.shipping-requests.send-message', $request->id) }}" method="POST">@csrf
                    <div class="input-group">
                        <input type="text" name="message" class="form-control" placeholder="Message to shipper…" required>
                        <div class="input-group-append"><button class="btn btn-primary">Send</button></div>
                    </div></form>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card card-info">
            <div class="card-header"><h3 class="card-title">Request Details</h3></div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tr><th>Order</th><td><a href="{{ url('admin/orders/' . $request->order_id) }}">#{{ $request->order_id }}</a></td></tr>
                    <tr><th>Customer code</th><td>{{ $request->customer_username }}</td></tr>
                    <tr><th>Service type</th><td>{{ $request->service_type }}</td></tr>
                    <tr><th>Country</th><td>{{ $request->country_required }}</td></tr>
                    <tr><th>Required level</th><td>{{ $request->required_level }}</td></tr>
                    <tr><th>Expires</th><td>{{ $request->expires_at?->diffForHumans() ?? '—' }}</td></tr>
                    <tr><th>Contact</th><td>{{ $request->contact_email ?: '—' }} / {{ $request->contact_phone ?: '—' }}</td></tr>
                </table>
                <div class="btn-group flex-wrap">
                    @if(in_array($request->status, ['draft','frozen']))
                        <form action="{{ route('admin.shipping-requests.publish', $request->id) }}" method="POST">@csrf
                            <button class="btn btn-success btn-sm">Publish</button></form>
                    @endif
                    @if($request->is_frozen && $request->status !== 'assigned')
                        <form action="{{ route('admin.shipping-requests.unfreeze', $request->id) }}" method="POST">@csrf
                            <button class="btn btn-warning btn-sm">Unfreeze</button></form>
                    @elseif($request->status === 'open')
                        <form action="{{ route('admin.shipping-requests.freeze', $request->id) }}" method="POST">@csrf
                            <button class="btn btn-warning btn-sm">Freeze</button></form>
                    @endif
                    @if(!in_array($request->status, ['assigned','completed','cancelled']))
                        <form action="{{ route('admin.shipping-requests.cancel', $request->id) }}" method="POST" onsubmit="return confirm('Cancel request?')">@csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Cancel</button></form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card card-success">
            <div class="card-header"><h3 class="card-title">Quotes Received ({{ $request->quotes_count }})</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead><tr><th>Shipper</th><th>Amount</th><th>Days</th><th>⭐</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($request->quotes as $q)
                        <tr>
                            <td>{{ $q->shipper->user->shipper_username ?? 'SHP' }}</td>
                            <td><b>${{ number_format($q->quoted_amount, 2) }}</b></td>
                            <td>{{ $q->estimated_days }}</td>
                            <td>{{ $q->shipper->rating }}</td>
                            <td><span class="badge badge-{{ $q->status === 'accepted' ? 'success' : ($q->status === 'pending' ? 'warning' : 'secondary') }}">{{ $q->status }}</span></td>
                            <td>
                                @if($request->status !== 'assigned')
                                    <form action="{{ route('admin.shipping-requests.select-shipper', $request->id) }}" method="POST"
                                          onsubmit="return confirm('Select this shipper?')">
                                        @csrf
                                        <input type="hidden" name="shipper_profile_id" value="{{ $q->shipper_profile_id }}">
                                        <input type="hidden" name="shipper_fee" value="{{ $q->quoted_amount }}">
                                        <div class="input-group input-group-sm" style="width:190px">
                                            <input type="number" step="0.01" name="platform_fee" class="form-control" placeholder="Platform fee" required>
                                            <div class="input-group-append"><button class="btn btn-success">Select</button></div>
                                        </div>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty<tr><td colspan="6" class="text-muted text-center">No quotes yet.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($request->assignment)
            <div class="card card-info">
                <div class="card-header"><h3 class="card-title">Assignment</h3></div>
                <div class="card-body">
                    Status: <b>{{ $request->assignment->status }}</b> — shipper {{ $request->assignment->shipper->user->shipper_username ?? '' }}
                    <a href="{{ route('admin.shipper-assignments.show', $request->assignment->id) }}" class="btn btn-primary btn-sm d-block mt-2">Open Assignment →</a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
