@extends('layouts.tailwind.app')

@section('title', 'Claims')
@section('page_title', 'Damage / Loss / Late Claims')
@section('page_subtitle', 'Claims processing with status flow and payouts')

{{-- window.DP (infinite scroll + esc helpers) is only loaded by the old AdminLTE layouts; this
     page relies on DP.infiniteScroll for its table, so it must be pulled in explicitly here.
     Pushed via @push('admin_scripts') so it loads AFTER the layout's jQuery <script> tag (layout
     yields content, then jQuery, then @stack('admin_scripts')). --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

@php
    // Bootstrap badge class (from config/admin_quotes.php) → Tailwind pill classes.
    // Same mapping convention as admin/returns/index.blade.php. Mirrored in the inline
    // <script> below for JS-rendered rows.
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
            <input id="f-q" type="search" class="w-64 rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Search user / description…">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="f-type">Type</label>
            <select id="f-type" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">Type: all</option>
                @foreach ($types as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
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
            <a href="{{ route('admin.returns.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-3 py-1.5 text-sm font-medium text-blue-600 hover:bg-blue-50">
                <i class="fas fa-undo"></i> Returns
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
                    <th class="py-2 pr-4">Type</th>
                    <th class="py-2 pr-4">Claimed</th>
                    <th class="py-2 pr-4">Payout</th>
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
    var base = @json(route('admin.returns.claims.data'));

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
        if (document.getElementById('f-type').value) u.searchParams.set('type', document.getElementById('f-type').value);
        if (document.getElementById('f-status').value) u.searchParams.set('status', document.getElementById('f-status').value);
        return u.toString();
    }

    var sc = DP.infiniteScroll({
        url: url(), target: '#dp-tbody',
        render: function (c) {
            return '<tr>'
                + '<td data-label="#" class="py-2 pr-4"><strong>#' + DP.esc(c.id) + '</strong></td>'
                + '<td data-label="User" class="py-2 pr-4">' + DP.esc(c.user) + '<br><small class="text-slate-500">' + DP.esc(c.email || '') + '</small></td>'
                + '<td data-label="Type" class="py-2 pr-4">' + DP.esc(c.type) + '</td>'
                + '<td data-label="Claimed" class="py-2 pr-4">$' + DP.esc(c.amount) + '</td>'
                + '<td data-label="Payout" class="py-2 pr-4">' + (c.payout !== null ? '$' + DP.esc(c.payout) : '<span class="text-slate-400">&mdash;</span>') + '</td>'
                + '<td data-label="Status" class="py-2 pr-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + badgeClasses(c.color) + '">' + DP.esc(c.status_lbl) + '</span></td>'
                + '<td data-label="Created" class="py-2 pr-4 text-slate-500">' + DP.esc(c.created_at) + '</td>'
                + '<td data-label="Action" class="py-2 pr-4 text-right"><a href="' + c.urls.show + '" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"><i class="fas fa-eye"></i></a></td>'
                + '</tr>';
        }
    });
    var q = document.getElementById('f-q'), t;
    q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { sc.reload(url()); }, 350); });
    ['type', 'status'].forEach(function (k) { document.getElementById('f-' + k).addEventListener('change', function () { sc.reload(url()); }); });
})();
</script>
@endpush
