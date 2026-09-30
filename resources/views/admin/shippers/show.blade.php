@extends('admin.layouts.app')
@section('title', 'Shipper Detail')
@section('page_title', 'Shipper: ' . ($shipper->user->shipper_username ?? 'SHP'))
@section('page_subtitle', 'Profile, KYC, assignments, ratings, wallet')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Profile</h3></div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tr><th>Username</th><td>{{ $shipper->user->shipper_username ?? '—' }}</td></tr>
                    <tr><th>Email</th><td>{{ $shipper->user->email ?? '—' }}</td></tr>
                    <tr><th>Level</th><td>{{ $shipper->level }} (max {{ $shipper->max_concurrent_orders }} concurrent)</td></tr>
                    <tr><th>Status</th><td><span class="badge badge-{{ $shipper->status === 'active' ? 'success' : 'warning' }}">{{ $shipper->status }}</span> · KYC <span class="badge badge-secondary">{{ $shipper->kyc_status }}</span></td></tr>
                    <tr><th>Countries</th><td>{{ implode(', ', $shipper->service_countries ?? []) }}</td></tr>
                    <tr><th>Services</th><td>{{ implode(', ', array_keys(array_filter($shipper->services_offered ?? []))) ?: '—' }}</td></tr>
                    <tr><th>Residence</th><td>{{ $shipper->residence_type }}{{ $shipper->has_storage ? ' (storage)' : '' }}</td></tr>
                    <tr><th>Rating</th><td>⭐ {{ $shipper->rating }} ({{ $shipper->total_ratings }}) · {{ $shipper->total_completed }} completed</td></tr>
                    <tr><th>Wallet</th><td>${{ number_format($shipper->wallet_balance, 2) }} (+${{ number_format($shipper->wallet_pending, 2) }} held)</td></tr>
                    @if($shipper->suspended_at)<tr><th>Suspended</th><td class="text-danger">{{ $shipper->suspended_at }} — {{ $shipper->suspension_reason }}</td></tr>@endif
                </table>
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                <hr>
                <form action="{{ route('admin.shippers.approve', $shipper->id) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-success btn-sm" onclick="return confirm('Approve this shipper?')"><i class="fas fa-check"></i> Approve KYC</button></form>
                @if($shipper->level < 3)
                    <form action="{{ route('admin.shippers.promote', $shipper->id) }}" method="POST" class="d-inline">@csrf
                        <button class="btn btn-info btn-sm">Promote</button></form>
                @endif
                @if($shipper->status === 'active')
                    <form action="{{ route('admin.shippers.suspend', $shipper->id) }}" method="POST" class="d-inline mt-2">@csrf
                        <input type="text" name="reason" class="form-control form-control-sm mb-1" placeholder="Suspension reason" required>
                        <button class="btn btn-warning btn-sm" onclick="return confirm('Suspend?')">Suspend</button></form>
                @else
                    <form action="{{ route('admin.shippers.reinstate', $shipper->id) }}" method="POST" class="d-inline mt-2">@csrf
                        <button class="btn btn-success btn-sm">Reinstate</button></form>
                @endif
                <form action="{{ route('admin.shippers.reject', $shipper->id) }}" method="POST" class="mt-2">@csrf
                    <input type="text" name="reason" class="form-control form-control-sm mb-1" placeholder="Rejection reason" required>
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Reject permanently?')">Reject</button></form>
                <form action="{{ route('admin.shippers.wallet-adjust', $shipper->id) }}" method="POST" class="mt-3">@csrf
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
                        <input type="text" name="note" class="form-control" placeholder="Note" required>
                        <div class="input-group-append"><button class="btn btn-secondary">Debit</button></div>
                    </div></form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">KYC Documents</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead><tr><th>Type</th><th>Status</th><th>Notes</th><th></th></tr></thead>
                    <tbody>
                    @forelse($shipper->kycDocuments as $d)
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $d->document_type)) }}</td>
                            <td><span class="badge badge-{{ $d->status === 'approved' ? 'success' : ($d->status === 'rejected' ? 'danger' : 'warning') }}">{{ $d->status }}</span></td>
                            <td>{{ $d->review_notes }}</td>
                            <td>
                                @if($d->status === 'pending')
                                    <form action="{{ route('admin.shippers.kyc.approve-doc', $d->id) }}" method="POST" class="d-inline">@csrf <button class="btn btn-xs btn-success">Approve</button></form>
                                    <form action="{{ route('admin.shippers.kyc.reject-doc', $d->id) }}" method="POST" class="d-inline">@csrf
                                        <input type="hidden" name="reason" value="Not acceptable">
                                        <button class="btn btn-xs btn-danger">Reject</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty<tr><td colspan="4" class="text-muted text-center">No KYC documents uploaded.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Assignments</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead><tr><th>Order</th><th>Status</th><th>Shipper fee</th><th>Created</th><th></th></tr></thead>
                    <tbody>
                    @forelse($shipper->assignments as $a)
                        <tr><td>#{{ $a->order_id }}</td><td><span class="badge badge-secondary">{{ $a->status }}</span></td>
                            <td>${{ number_format($a->shipper_fee, 2) }}</td><td>{{ $a->created_at->diffForHumans() }}</td>
                            <td><a class="btn btn-xs btn-primary" href="{{ route('admin.shipper-assignments.show', $a->id) }}">Open</a></td></tr>
                    @empty<tr><td colspan="5" class="text-muted text-center">No assignments.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Wallet History (last 50)</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead><tr><th>When</th><th>Type</th><th>Amount</th><th>Balance</th><th>Note</th></tr></thead>
                    <tbody>
                    @forelse($shipper->walletTransactions as $t)
                        <tr><td>{{ $t->created_at->diffForHumans() }}</td><td>{{ $t->type }}</td>
                            <td>${{ number_format($t->amount, 2) }}</td><td>${{ number_format($t->balance_after, 2) }}</td><td>{{ $t->note }}</td></tr>
                    @empty<tr><td colspan="5" class="text-muted text-center">No transactions.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Ratings</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead><tr><th>Order</th><th>Rating</th><th>Review</th><th>Approved</th></tr></thead>
                    <tbody>
                    @forelse($shipper->ratings as $r)
                        <tr><td>#{{ $r->order_id }}</td><td>⭐ {{ $r->overall_rating }}</td><td>{{ Str::limit($r->review_text, 80) }}</td>
                            <td>{{ $r->admin_approved ? 'Yes' : 'No' }}{{ $r->published_as_testimonial ? ' · published' : '' }}
                                @if(!$r->admin_approved)
                                    <form action="{{ route('admin.shippers.ratings.approve', $r->id) }}" method="POST" class="mt-1">@csrf
                                        <button class="btn btn-xs btn-success">Approve</button></form>
                                @elseif($r->consent_testimonial !== 'no')
                                    <form action="{{ route('admin.shippers.ratings.publish', $r->id) }}" method="POST" class="mt-1">@csrf
                                        <button class="btn btn-xs btn-{{ $r->published_as_testimonial ? 'secondary' : 'info' }}">{{ $r->published_as_testimonial ? 'Unpublish' : 'Publish' }}</button></form>
                                @endif
                            </td></tr>
                    @empty<tr><td colspan="4" class="text-muted text-center">No ratings.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
