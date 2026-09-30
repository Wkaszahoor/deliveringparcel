@extends('admin.layouts.app')
@section('title', 'All Shippers')
@section('page_title', 'All Shippers')
@section('page_subtitle', 'Manage shipper accounts, levels and wallets')

@section('content')
<div class="card card-default">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-users"></i> Shippers</h3></div>
    <div class="card-body">
        <form method="GET" class="form-inline mb-3">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm mr-2" placeholder="SHP-username / email / name">
            <select name="status" class="form-control form-control-sm mr-2">
                <option value="">Status…</option>
                @foreach(['pending','active','suspended','banned'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <select name="level" class="form-control form-control-sm mr-2">
                <option value="">Level…</option>
                @foreach([1,2,3] as $lv)
                    <option value="{{ $lv }}" {{ request('level') == $lv ? 'selected' : '' }}>Level {{ $lv }}</option>
                @endforeach
            </select>
            <select name="kyc_status" class="form-control form-control-sm mr-2">
                <option value="">KYC…</option>
                @foreach(['pending','approved','rejected'] as $ks)
                    <option value="{{ $ks }}" {{ request('kyc_status') === $ks ? 'selected' : '' }}>{{ ucfirst($ks) }}</option>
                @endforeach
            </select>
            <input type="text" name="country" value="{{ request('country') }}" class="form-control form-control-sm mr-2" placeholder="Country (UK)" style="max-width:110px">
            <button class="btn btn-sm btn-primary">Filter</button>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>Shipper ID</th><th>Email</th><th>Level</th><th>Status</th><th>Rating</th><th>Done</th><th>Wallet</th><th></th></tr></thead>
                <tbody>
                @forelse($shippers as $s)
                    <tr>
                        <td><b>{{ $s->user->shipper_username ?? '—' }}</b></td>
                        <td>{{ $s->user->email ?? '—' }}</td>
                        <td><span class="badge badge-info">L{{ $s->level }}</span></td>
                        <td><span class="badge badge-{{ $s->status === 'active' ? 'success' : ($s->status === 'pending' ? 'warning' : 'danger') }}">{{ $s->status }}</span>
                            @if($s->kyc_status !== 'approved')<span class="badge badge-secondary">kyc:{{ $s->kyc_status }}</span>@endif</td>
                        <td>⭐ {{ $s->rating }} ({{ $s->total_ratings }})</td>
                        <td>{{ $s->total_completed }}</td>
                        <td>${{ number_format($s->wallet_balance, 2) }} <small class="text-muted">+{{ number_format($s->wallet_pending, 2) }} hold</small></td>
                        <td><a href="{{ route('admin.shippers.show', $s->id) }}" class="btn btn-sm btn-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No shippers match. New shippers register at <code>/become-a-shipper</code>.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $shippers->links() }}
    </div>
</div>
@endsection
