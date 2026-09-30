@extends('admin.layouts.app')

@section('title', 'Shipper Country Requests')
@section('page_title', 'Shipper Country Requests')
@section('page_subtitle', 'Approve or reject shipper service-country changes — blocked countries are hidden from the marketplace')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Pending requests ({{ $pending->count() }})</h3></div>
            <div class="card-body p-0">
                @if ($pending->isEmpty())
                    <p class="text-muted p-3 mb-0">No pending countries change requests.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Shipper</th>
                                    <th>Current</th>
                                    <th>Requested</th>
                                    <th>Note</th>
                                    <th style="min-width:210px">Decision</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pending as $r)
                                    <tr>
                                        <td>
                                            <b>{{ $r->profile->shipper_username ?? ('SHP-' . $r->profile->id) }}</b><br>
                                            <small class="text-muted">{{ optional($r->profile->user)->name }}
                                                · lvl {{ $r->profile->level }}
                                                · {{ $r->created_at->format('d M, H:i') }}</small>
                                        </td>
                                        <td>
                                            @foreach ($r->profile->service_countries ?? [] as $iso)
                                                <span class="badge badge-light border">{{ $iso }}</span>
                                            @endforeach
                                            @if (empty($r->profile->service_countries))<span class="text-muted">—</span>@endif
                                        </td>
                                        <td>
                                            @foreach ($r->countries as $iso)
                                                <span class="badge badge-primary">{{ $iso }}</span>
                                            @endforeach
                                        </td>
                                        <td><small>{{ $r->note ?: '—' }}</small></td>
                                        <td>
                                            <form action="{{ route('admin.shippers.country-requests.approve', $r->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" data-confirm="Approve and apply these countries to the shipper profile?">Approve</button>
                                            </form>
                                            <form action="{{ route('admin.shippers.country-requests.reject', $r->id) }}" method="POST" class="d-inline mt-1">
                                                @csrf
                                                <input type="text" name="admin_note" class="form-control form-control-sm d-block mb-1" placeholder="Reason (optional)">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Reject this request?">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Recent decisions</h3></div>
            <div class="card-body p-0">
                @if ($reviewed->isEmpty())
                    <p class="text-muted p-3 mb-0">Nothing reviewed yet.</p>
                @else
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Shipper</th><th>Countries</th><th>Status</th><th>Reviewed</th></tr></thead>
                        <tbody>
                            @foreach ($reviewed as $r)
                                <tr>
                                    <td>{{ $r->profile->shipper_username ?? ('SHP-' . $r->profile->id) }}</td>
                                    <td>{{ implode(', ', $r->countries) }}</td>
                                    <td>
                                        <span class="badge {{ $r->status === 'approved' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($r->status) }}</span>
                                    </td>
                                    <td><small>{{ optional($r->reviewed_at)->format('d M Y, H:i') }} @if($r->admin_note) — {{ $r->admin_note }}@endif</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-warning">
            <div class="card-header"><h3 class="card-title">Direct country edit (admin override)</h3></div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.shippers.country-requests') }}" class="mb-3">
                    <label>Pick a shipper</label>
                    <div class="input-group">
                        <select name="edit" class="form-control">
                            <option value="">— select shipper —</option>
                            @foreach ($shippers as $s)
                                <option value="{{ $s->id }}" @if($editShipper && $editShipper->id === $s->id) selected @endif>
                                    {{ $s->shipper_username ?? ('SHP-' . $s->id) }} ({{ optional($s->user)->name }}) — {{ implode('/', $s->service_countries ?? []) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append"><button class="btn btn-primary">Load</button></div>
                    </div>
                </form>

                @if ($editShipper)
                    <form action="{{ route('admin.shippers.country-requests.update', $editShipper->id) }}" method="POST">
                        @csrf
                        <p class="mb-2"><b>{{ $editShipper->shipper_username ?? ('SHP-' . $editShipper->id) }}</b>
                            <small class="text-muted">— currently {{ implode(', ', $editIsos) ?: 'none' }}</small></p>
                        <div class="dp-check-grid mb-3">
                            @foreach ($activeCountries as $c)
                                <label class="dp-check-item">
                                    <input type="checkbox" name="service_countries[]" value="{{ $c->iso2 }}"
                                        @if (in_array($c->iso2, $editIsos)) checked @endif>
                                    <span>{{ $c->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mb-2">Leave all unticked to clear. Ticking a disabled country is allowed for admins — requests for it stay hidden until you enable it in Admin → Countries.</small>
                        <button type="submit" class="btn btn-warning" data-confirm="Save these countries for this shipper?">Save countries</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Network countries</h3></div>
            <div class="card-body text-sm">
                Enabled: {{ $activeCountries->pluck('iso2')->implode(', ') }}<br>
                <span class="text-muted">Enable/block countries in Admin → Content → Countries. Blocked = hidden from every shipper's marketplace.</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_styles')
<style>
.dp-check-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .4rem;
    max-height: 300px; overflow-y: auto; border: 1px solid #e3e6ea; border-radius: .5rem; padding: .6rem; }
.dp-check-item { display: flex; align-items: center; gap: .4rem; font-size: .88rem; margin: 0; }
.dp-check-item input { margin: 0; }
</style>
@endpush
