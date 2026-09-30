@extends('admin.layouts.app')
@section('title', 'Shippers Overview')
@section('page_title', 'Shipper Network — Mission Control')
@section('page_subtitle', '3-party shipping workflow control center')

@section('content')
@php($c = $kpi['critical'] ?? [])
@php($v = $kpi['velocity'] ?? [])
@php($s = $kpi['stuck'] ?? [])
@php($m = $kpi['marketplace'] ?? [])
@php($n = $kpi['network'] ?? [])
@php($mo = $kpi['money'] ?? [])
@php($q = $kpi['quality'] ?? [])
@php($feed = $kpi['feed'] ?? [])

{{-- ── Top KPI boxes ── --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info"><div class="inner"><h3>{{ $total }}</h3><p>Total Shippers</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('admin.shippers.index') }}" class="small-box-footer">All shippers <i class="fas fa-arrow-circle-right"></i></a></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning"><div class="inner"><h3>{{ $pendingKyc }}</h3><p>Pending KYC</p></div>
            <div class="icon"><i class="fas fa-id-card"></i></div>
            <a href="{{ route('admin.shippers.pending-kyc') }}" class="small-box-footer">Review <i class="fas fa-arrow-circle-right"></i></a></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary"><div class="inner"><h3>{{ $activeAssign }}</h3><p>Active Assignments</p></div>
            <div class="icon"><i class="fas fa-tasks"></i></div>
            <a href="{{ route('admin.shipper-assignments.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success"><div class="inner"><h3>${{ number_format($mo['platform_revenue_30d'] ?? 0, 0) }}</h3><p>Platform Revenue (30d)</p></div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
            <a href="{{ route('admin.shippers.payout-requests') }}" class="small-box-footer">Payouts <i class="fas fa-arrow-circle-right"></i></a></div>
    </div>
</div>

{{-- ── Critical action queue + flow velocity ── --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Needs Immediate Action</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped table-sm mb-0">
                    <tr class="{{ ($c['overdue_purchases'] ?? collect())->count() ? 'table-danger' : '' }}">
                        <td>🔴 Overdue Purchases</td><td class="text-right"><b>{{ ($c['overdue_purchases'] ?? collect())->count() }}</b></td>
                        <td><a class="btn btn-xs btn-outline-danger" href="{{ route('admin.shipper-assignments.index') }}">Open</a></td></tr>
                    <tr><td>🔴 Pending KYC &gt;48h</td><td class="text-right"><b>{{ $c['kyc_stale'] ?? 0 }}</b></td>
                        <td><a class="btn btn-xs btn-outline-danger" href="{{ route('admin.shippers.pending-kyc') }}">Review</a></td></tr>
                    <tr><td>🟠 Proofs awaiting review</td><td class="text-right"><b>{{ $c['proofs_pending'] ?? 0 }}</b></td>
                        <td><a class="btn btn-xs btn-outline-primary" href="{{ route('admin.shipper-assignments.index', ['status' => 'proof_uploaded']) }}">Open</a></td></tr>
                    <tr><td>🟠 Address pending forward</td><td class="text-right"><b>{{ $c['address_pending'] ?? 0 }}</b></td>
                        <td><a class="btn btn-xs btn-outline-primary" href="{{ route('admin.shipper-assignments.index', ['status' => 'address_received']) }}">Open</a></td></tr>
                    <tr><td>🟠 Tracking pending share</td><td class="text-right"><b>{{ $c['tracking_pending'] ?? 0 }}</b></td>
                        <td><a class="btn btn-xs btn-outline-primary" href="{{ route('admin.shipper-assignments.index', ['status' => 'tracking_added']) }}">Open</a></td></tr>
                    <tr><td>🟡 Payout requests</td><td class="text-right"><b>{{ $c['payouts_pending'] ?? 0 }}</b></td>
                        <td><a class="btn btn-xs btn-outline-primary" href="{{ route('admin.shippers.payout-requests') }}">Open</a></td></tr>
                    <tr><td>🟡 Quotes awaiting decision &gt;24h</td><td class="text-right"><b>{{ $c['quotes_awaiting'] ?? 0 }}</b></td>
                        <td><a class="btn btn-xs btn-outline-primary" href="{{ route('admin.shipping-requests.index', ['status' => 'open']) }}">Open</a></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-stopwatch"></i> Flow Velocity</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td>Avg quote response</td><td class="text-right">{{ ($v['quote_response_hrs'] ?? null) !== null ? $v['quote_response_hrs'] : '—' }} hrs</td></tr>
                    <tr><td>Avg quote selection</td><td class="text-right">{{ ($v['selection_hrs'] ?? null) !== null ? $v['selection_hrs'] : '—' }} hrs</td></tr>
                    <tr><td>Avg purchase time (Buy)</td><td class="text-right">{{ ($v['purchase_hrs'] ?? null) !== null ? $v['purchase_hrs'] : '—' }} hrs</td></tr>
                    <tr><td>Avg proof approval</td><td class="text-right">{{ ($v['proof_approval_hrs'] ?? null) !== null ? $v['proof_approval_hrs'] : '—' }} hrs</td></tr>
                    <tr><td>Avg address forward</td><td class="text-right">{{ ($v['address_forward_hrs'] ?? null) !== null ? $v['address_forward_hrs'] : '—' }} hrs</td></tr>
                    <tr><td>Avg dispatch time</td><td class="text-right">{{ ($v['dispatch_days'] ?? null) !== null ? $v['dispatch_days'] : '—' }} days</td></tr>
                    <tr><td>Avg tracking share</td><td class="text-right">{{ ($v['tracking_share_hrs'] ?? null) !== null ? $v['tracking_share_hrs'] : '—' }} hrs</td></tr>
                    <tr class="table-info"><td><b>End-to-end cycle</b></td><td class="text-right"><b>{{ ($v['e2e_days'] ?? null) !== null ? $v['e2e_days'] : '—' }} days</b></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Bottlenecks + money ── --}}
<div class="row">
    <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-hourglass-half"></i> Stuck (&gt;48h in status)</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td>Assigned (not accepted)</td><td class="text-right">{{ $s['assigned'] ?? 0 }}</td></tr>
                    <tr><td>Purchasing (not purchased)</td><td class="text-right">{{ $s['purchasing'] ?? 0 }}</td></tr>
                    <tr><td>Proof uploaded (no review)</td><td class="text-right">{{ $s['proof_uploaded'] ?? 0 }}</td></tr>
                    <tr><td>Address received (not forwarded)</td><td class="text-right">{{ $s['address_received'] ?? 0 }}</td></tr>
                    <tr><td>Tracking added (not shared)</td><td class="text-right">{{ $s['tracking_added'] ?? 0 }}</td></tr>
                </table>
            </div>
        </div>
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-dollar-sign"></i> Money Flow</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td>Total pending release (holds)</td><td class="text-right">${{ number_format($mo['pending_release'] ?? 0, 2) }}</td></tr>
                    <tr><td>Holds auto-releasing (7d)</td><td class="text-right">${{ number_format($mo['releasing_7d'] ?? 0, 2) }}</td></tr>
                    <tr><td>Pending payouts</td><td class="text-right">${{ number_format($mo['pending_payouts'] ?? 0, 2) }}</td></tr>
                    <tr><td>Platform revenue (30d)</td><td class="text-right">${{ number_format($mo['platform_revenue_30d'] ?? 0, 2) }}</td></tr>
                    <tr><td>Avg shipper / platform fee</td><td class="text-right">${{ number_format($mo['avg_shipper_fee'] ?? 0, 0) }} / ${{ number_format($mo['avg_platform_fee'] ?? 0, 0) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-globe"></i> Marketplace Health</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td>Open requests (no quotes)</td><td class="text-right">{{ $m['open_no_quotes'] ?? 0 }}</td></tr>
                    <tr><td>Avg quotes per request</td><td class="text-right">{{ ($m['avg_quotes'] ?? null) !== null ? $m['avg_quotes'] : '—' }}</td></tr>
                    <tr><td>Quote acceptance rate</td><td class="text-right">{{ ($m['acceptance_rate'] ?? null) !== null ? $m['acceptance_rate'] : '—' }}%</td></tr>
                    <tr><td>Request expiry rate</td><td class="text-right">{{ ($m['expiry_rate'] ?? null) !== null ? $m['expiry_rate'] : '—' }}%</td></tr>
                </table>
                @if(($m['by_country'] ?? collect())->count())
                    <table class="table table-sm mb-0">
                        @foreach($m['by_country'] as $bc)
                            <tr><td>{{ $bc['country'] }}</td><td class="text-right">{{ $bc['open'] }} open · {{ $bc['completed'] }} done</td></tr>
                        @endforeach
                    </table>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-network-wired"></i> Network &amp; Quality</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td>Active shippers</td><td class="text-right">{{ $n['total_active'] ?? 0 }}</td></tr>
                    <tr><td>Avg utilization</td><td class="text-right">{{ $n['avg_utilization'] ?? 0 }}%</td></tr>
                    <tr><td>At capacity / idle 7d</td><td class="text-right">{{ $n['at_capacity'] ?? 0 }} / {{ $n['idle_7d'] ?? 0 }}</td></tr>
                    @foreach(($n['by_level'] ?? collect()) as $lv)
                        <tr><td>L{{ $lv['level'] }} shippers</td><td class="text-right">{{ $lv['count'] }} · ⭐{{ $lv['avg_rating'] }}</td></tr>
                    @endforeach
                    <tr class="table-info"><td><b>Overall rating</b></td><td class="text-right"><b>⭐ {{ ($q['overall_rating'] ?? null) !== null ? $q['overall_rating'] : '—' }}</b> ({{ $q['ratings_30d'] ?? 0 }} in 30d)</td></tr>
                    <tr><td>Completion / dispute rate</td><td class="text-right">{{ ($q['completion_rate'] ?? null) !== null ? $q['completion_rate'] : '—' }}% / {{ ($q['dispute_rate'] ?? null) !== null ? $q['dispute_rate'] : '—' }}%</td></tr>
                    <tr><td>Testimonials published</td><td class="text-right">{{ $q['testimonials'] ?? 0 }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Activity feed + top/low shippers ── --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-stream"></i> Recent Events (24h)</h3></div>
            <div class="card-body" style="max-height:280px;overflow:auto">
                @forelse($feed as $e)
                    <div class="mb-1">
                        <small class="text-muted">{{ $e['at']->format('H:i') }}</small>
                        <span class="badge badge-{{ $e['color'] === 'green' ? 'success' : ($e['color'] === 'red' ? 'danger' : ($e['color'] === 'yellow' ? 'warning' : 'info')) }}">●</span>
                        {{ $e['text'] }}
                    </div>
                @empty<p class="text-muted mb-0">No events in the last 24 hours.</p>@endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-medal"></i> Top Rated &amp; Overdue Purchases</h3></div>
            <div class="card-body">
                <b>Top rated</b>
                <ul class="list-unstyled mt-1 mb-2">
                    @forelse($topShippers as $s2)
                        <li><a href="{{ route('admin.shippers.show', $s2->id) }}">{{ $s2->user->shipper_username ?? 'SHP' }}</a> — ⭐ {{ $s2->rating }} ({{ $s2->total_ratings }})</li>
                    @empty<li class="text-muted">No rated shippers yet.</li>@endforelse
                </ul>
                @if(($c['overdue_purchases'] ?? collect())->count())
                    <b class="text-danger">Overdue purchases</b>
                    <ul class="list-unstyled mb-2">
                        @foreach($c['overdue_purchases'] as $o)
                            <li class="text-danger">{{ $o->shipper->user->shipper_username ?? 'SHP' }} — order #{{ $o->order_id }}
                                <a class="btn btn-xs btn-outline-danger" href="{{ route('admin.shipper-assignments.show', $o->id) }}">Open</a></li>
                        @endforeach
                    </ul>
                @endif
                @if($lowRating->count())
                    <b class="text-danger">Low rating alerts</b>
                    <ul class="list-unstyled mb-0">
                        @foreach($lowRating as $s3)
                            <li class="text-danger">{{ $s3->user->shipper_username ?? 'SHP' }} — ⭐ {{ $s3->rating }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card card-lightblue">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-plus"></i> Create a Shipping Request</h3></div>
    <div class="card-body">
        <p class="mb-2">Generate a masked brief from an existing order and publish it to verified shippers.</p>
        <a href="{{ route('admin.shipping-requests.create') }}" class="btn btn-primary">Open Create Form</a>
        <a href="{{ route('admin.shipping-requests.index') }}" class="btn btn-outline-secondary">View All Requests</a>
    </div>
</div>
@endsection
