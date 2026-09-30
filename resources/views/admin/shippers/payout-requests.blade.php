@extends('admin.layouts.app')
@section('title', 'Shipper Payouts')
@section('page_title', 'Shipper Payout Requests')
@section('page_subtitle', 'Withdrawal requests from shipper wallets')

@section('content')
<div class="card card-default">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Payout Requests</h3></div>
    <div class="card-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Shipper</th><th>Amount</th><th>Method</th><th>Requested</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($requests as $r)
                    <tr>
                        <td>{{ $r->shipper->user->shipper_username ?? 'SHP' }}</td>
                        <td><b>${{ number_format($r->amount, 2) }}</b></td>
                        <td>{{ ucfirst($r->method) }}</td>
                        <td>{{ $r->created_at->diffForHumans() }}</td>
                        <td><span class="badge badge-{{ $r->status === 'paid' ? 'success' : ($r->status === 'rejected' ? 'danger' : 'warning') }}">{{ $r->status }}</span></td>
                        <td>
                            @if($r->status === 'pending')
                                <form action="{{ route('admin.shippers.payouts.approve', $r->id) }}" method="POST" class="form-inline">@csrf
                                    <input type="text" name="reference" class="form-control form-control-sm mr-1" placeholder="Txn ref (optional)">
                                    <button class="btn btn-sm btn-success mr-1" onclick="return confirm('Mark paid?')">Mark Paid</button></form>
                                <form action="{{ route('admin.shippers.payouts.reject', $r->id) }}" method="POST" class="form-inline">@csrf
                                    <input type="text" name="reason" class="form-control form-control-sm mr-1" placeholder="Reason" required>
                                    <button class="btn btn-sm btn-danger">Reject</button></form>
                            @else
                                <span class="text-muted">{{ $r->admin_notes }}</span>
                            @endif
                        </td>
                    </tr>
                @empty<tr><td colspan="6" class="text-muted text-center">No payout requests.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $requests->links() }}
    </div>
</div>
@endsection
