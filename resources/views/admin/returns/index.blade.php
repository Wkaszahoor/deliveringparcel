@extends('layouts.tailwind.app')

@section('title', 'Returns')
@section('page_title', 'Return Requests')
@section('page_subtitle', 'Status flow: requested → approved → received → refunded')

{{-- window.DP (infinite scroll + esc helpers) is only loaded by the old AdminLTE layouts; this
     page relies on DP.infiniteScroll for its table, so it must be pulled in explicitly here.
     Pushed via @push('admin_scripts') so it loads AFTER the layout's jQuery <script> tag (layout
     yields content, then jQuery, then @stack('admin_scripts')) — an inline script directly in
     @section('content') would run before dp-lazy.js exists (it isn't jQuery-dependent, but the
     layout still only ever loads it via this push). --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

@php
    // Bootstrap badge class (from config/admin_quotes.php) → Tailwind pill classes.
    // Same mapping convention as admin/ordersm/index.blade.php's $badgeClasses helper — the new
    // layout loads no Bootstrap CSS, so raw `bg-secondary`/`bg-info`/etc classes render unstyled.
    // Mirrored in the inline <script> below for JS-rendered rows.
    $badgeClasses = fn (string $bootstrap) => [
        'bg-secondary' => 'bg-slate-100 text-slate-700',
        'bg-primary'   => 'bg-blue-100 text-blue-700',
        'bg-info'      => 'bg-cyan-100 text-cyan-700',
        'bg-success'   => 'bg-green-100 text-green-700',
        'bg-danger'    => 'bg-red-100 text-red-700',
        'bg-warning'   => 'bg-yellow-100 text-yellow-800',
    ][$bootstrap] ?? 'bg-slate-100 text-slate-700';
@endphp

<x-admin.card class="mb-4">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="f-q">Search</label>
            <input id="f-q" type="search" class="w-64 rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Search user / reason…">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="f-status">Status</label>
            <select id="f-status" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">Status: all</option>
                @foreach ($statusMap as $key => $m)
                    <option value="{{ $key }}">{{ $m['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="ml-auto">
            <a href="{{ route('admin.returns.claims') }}" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-3 py-1.5 text-sm font-medium text-blue-600 hover:bg-blue-50">
                <i class="fas fa-exclamation-triangle"></i> Claims
            </a>
        </div>
    </div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">#</th>
                    <th class="py-2 pr-4">User</th>
                    <th class="py-2 pr-4">Order</th>
                    <th class="py-2 pr-4">Reason</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Created</th>
                    <th class="py-2 pr-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody id="dp-tbody" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var base = @json(route('admin.returns.data'));

    // Bootstrap badge class → Tailwind pill classes — mirrors the $badgeClasses map above.
    function badgeClasses(bootstrap) {
        var map = {
            'bg-secondary': 'bg-slate-100 text-slate-700',
            'bg-primary':   'bg-blue-100 text-blue-700',
            'bg-info':      'bg-cyan-100 text-cyan-700',
            'bg-success':   'bg-green-100 text-green-700',
            'bg-danger':    'bg-red-100 text-red-700',
            'bg-warning':   'bg-yellow-100 text-yellow-800'
        };
        return map[bootstrap] || map['bg-secondary'];
    }

    function url() {
        var u = new URL(base);
        if (document.getElementById('f-q').value.trim()) u.searchParams.set('q', document.getElementById('f-q').value.trim());
        if (document.getElementById('f-status').value) u.searchParams.set('status', document.getElementById('f-status').value);
        return u.toString();
    }

    var sc = DP.infiniteScroll({
        url: url(), target: '#dp-tbody',
        render: function (r) {
            return '<tr>'
                + '<td data-label="#" class="py-2 pr-4"><strong>#' + DP.esc(r.id) + '</strong></td>'
                + '<td data-label="User" class="py-2 pr-4">' + DP.esc(r.user) + '<br><small class="text-slate-500">' + DP.esc(r.email || '') + '</small></td>'
                + '<td data-label="Order" class="py-2 pr-4">' + (r.order_id ? '#' + DP.esc(r.order_id) : '&mdash;') + '</td>'
                + '<td data-label="Reason" class="py-2 pr-4">' + DP.esc(r.reason) + '</td>'
                + '<td data-label="Status" class="py-2 pr-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + badgeClasses(r.color) + '">' + DP.esc(r.status_lbl) + '</span></td>'
                + '<td data-label="Created" class="py-2 pr-4 text-slate-500">' + DP.esc(r.created_at) + '</td>'
                + '<td data-label="Action" class="py-2 pr-4 text-right"><a href="' + r.urls.show + '" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"><i class="fas fa-eye"></i></a></td>'
                + '</tr>';
        }
    });
    var q = document.getElementById('f-q'), t;
    q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { sc.reload(url()); }, 350); });
    document.getElementById('f-status').addEventListener('change', function () { sc.reload(url()); });
})();
</script>
@endpush
