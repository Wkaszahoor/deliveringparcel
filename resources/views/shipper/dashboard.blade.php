@extends('shipper.layouts.app')
@section('title', 'Shipper Dashboard')

@section('content')
@php($kp = $kpi ?? [])
@php($e = $kp['earnings'] ?? [])
@php($vel = $kp['velocity'] ?? [])
@php($br = $kp['breakdown'] ?? [])
@php($todos = $kp['todos'] ?? [])
@php($top = $kp['top_requests'] ?? [])

<h2 style="margin:8px 0 2px">Welcome, {{ $profile->user->shipper_username ?? $profile->user->name }}</h2>
<p style="color:var(--h2-muted);margin-top:0">
    Level {{ $profile->level }} ·
    <span class="h2-badge" style="background:{{ $profile->status === 'active' ? '#e6f6ec' : '#fff4e0' }};color:{{ $profile->status === 'active' ? '#137333' : '#9a6700' }}">{{ ucfirst($profile->status) }}</span>
    @if($profile->kyc_status !== 'approved')<span class="h2-badge">KYC: {{ $profile->kyc_status }}</span>@endif
    · <span class="star">⭐ {{ $profile->rating }}</span> ({{ $profile->total_ratings }})
</p>
@if($profile->status === 'pending')
    <div class="h2-alert" style="background:#fff4e0;color:#9a6700;border:1px solid #f0d8a8">
        Your account is pending KYC approval. Upload your documents from the
        <a href="{{ route('shipper.profile') }}">Profile</a> page — you'll get access to requests once approved.
    </div>
@endif

<div class="shp-grid">
    <div class="shp-tile" style="background:linear-gradient(135deg,#0d6efd,#3d8bfd)">
        <div class="v">{{ $available }}</div><div class="l">Available Requests</div></div>
    <div class="shp-tile" style="background:linear-gradient(135deg,#198754,#3fb27f)">
        <div class="v">{{ $profile->assignments()->whereNotIn('status', ['completed','cancelled','disputed'])->count() }}</div><div class="l">Active ({{ $profile->current_active_orders }}/{{ $profile->max_concurrent_orders }} capacity)</div></div>
    <div class="shp-tile" style="background:linear-gradient(135deg,#6f42c1,#9d6bff)">
        <div class="v">${{ number_format($profile->wallet_balance, 2) }}</div><div class="l">Wallet Balance (+${{ number_format($profile->wallet_pending, 2) }} held)</div></div>
    <div class="shp-tile" style="background:linear-gradient(135deg,#dc3545,#e8697a)">
        <div class="v">${{ number_format($e['this_month'] ?? 0, 0) }}</div><div class="l">Earned This Month (total ${{ number_format($profile->total_earned, 0) }})</div></div>
</div>

@if(($e['next_release_amount'] ?? null) !== null)
    <div class="h2-alert" style="background:#e6f6ec;color:#137333">
        ⏳ Next hold release: <b>${{ number_format($e['next_release_amount'], 2) }}</b> in {{ $e['next_release_days'] }} day(s)
        @if(($e['success_rate'] ?? null) !== null) · 🏆 Success rate {{ $e['success_rate'] }}% · ⚡ Avg completion {{ $e['avg_completion_days'] }} days @endif
    </div>
@endif

@if(count($todos))
    <div class="h2-section" style="margin-top:14px">
        <h4 style="margin:0 0 8px">My To-Do List</h4>
        @foreach($todos as $t)
            <a href="{{ route('shipper.assignments.show', $t['assignment_id']) }}"
               style="display:block;text-decoration:none;margin-bottom:6px">
                <div style="border-left:4px solid {{ ['red'=>'#dc3545','orange'=>'#f0a800','yellow'=>'#f5b301'][$t['level']] ?? '#ccc' }};background:#fff;border-radius:8px;padding:10px 12px">
                    <b style="color:{{ $t['level'] === 'red' ? '#b3261e' : '#16324f' }}">{{ $t['label'] }}</b>
                    <span style="color:var(--h2-muted)"> — order #{{ $t['order_id'] }} →</span>
                </div>
            </a>
        @endforeach
    </div>
@endif

@if($nextLevel)
    <div class="h2-section" style="margin-top:14px">
        <h4 style="margin:0 0 6px">Progress to {{ $nextLevel['label'] }}</h4>
        <div style="background:var(--h2-bg);border-radius:8px;padding:8px">
            {{ $profile->total_completed }} / {{ $nextLevel['need'] }} completed orders
            @if(($br['speed'] ?? null) !== null) · speed ⭐{{ $br['speed'] }} @endif
        </div>
    </div>
@endif

@if(count($top))
    <div class="h2-section" style="margin-top:14px">
        <h4 style="margin:0 0 8px">Fresh Requests in Your Countries</h4>
        @foreach($top as $r)
            <a href="{{ route('shipper.requests.show', $r['id']) }}" style="text-decoration:none">
                <div style="background:#fff;border:1px solid var(--h2-border);border-radius:10px;padding:10px 12px;margin-bottom:6px">
                    <b>{{ $r['reference'] }}</b>
                    <span class="h2-badge">{{ $r['service_type'] === 'buy_for_me' ? 'Buy' : 'Ship' }}</span>
                    <span class="h2-badge">{{ $r['country'] }}</span>
                    <div style="color:var(--h2-muted);font-size:13px">
                        ${{ number_format($r['value_range_min'], 0) }}–${{ number_format($r['value_range_max'], 0) }} · {{ $r['quotes'] }} quote(s)
                        @if($r['expires_hrs'] !== null) · closes in {{ $r['expires_hrs'] }}h @endif
                    </div>
                </div>
            </a>
        @endforeach
        <a class="h2-btn h2-btn-primary" style="margin-top:8px" href="{{ route('shipper.requests.index') }}">Browse All →</a>
    </div>
@endif

<div class="h2-section" style="margin-top:14px">
    <h4 style="margin:0 0 8px">Recent Assignments @if($unread) <span class="h2-badge">{{ $unread }} unread admin messages</span>@endif</h4>
    <div class="h2-table-wrap">
        <table class="h2-table">
            <thead><tr><th>Order</th><th>Status</th><th>Your fee</th><th></th></tr></thead>
            <tbody>
            @forelse($recent as $a)
                <tr><td data-label="Order">#{{ $a->order_id }}</td>
                    <td data-label="Status">{{ str_replace('_', ' ', $a->status) }}</td>
                    <td data-label="Fee">${{ number_format($a->shipper_fee, 2) }}</td>
                    <td data-label=""><a class="h2-btn h2-btn-primary" style="padding:.35rem 1rem;font-size:.85rem" href="{{ route('shipper.assignments.show', $a->id) }}">Open</a></td></tr>
            @empty<tr><td colspan="4">No assignments yet — submit quotes on open requests.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <p style="margin-top:12px">
        <a class="h2-btn h2-btn-primary" href="{{ route('shipper.requests.index') }}">Browse Open Requests →</a>
        <a class="h2-btn h2-btn-outline" href="{{ route('shipper.wallet') }}">Wallet →</a>
        <a class="h2-btn h2-btn-outline" href="{{ route('shipper.assignments.index') }}">Assignments →</a>
    </p>
</div>
@endsection
