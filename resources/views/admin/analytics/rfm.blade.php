@extends('layouts.tailwind.app')

@section('title', 'RFM Segmentation')
@section('page_title', 'RFM Customer Segmentation')
@section('page_subtitle', 'Recency / Frequency / Monetary scoring with adjustable thresholds')

{{-- window.DP (infinite-scroll/esc helpers) is only loaded by the old AdminLTE layout; this
     page relies on DP.infiniteScroll/DP.esc for its JS row templates, so it must be pulled in
     explicitly here — the new Tailwind layout never loads it globally. Pushed via
     @push('admin_scripts') so it loads AFTER the layout's jQuery <script> tag (layout yields
     content, then jQuery, then @stack('admin_scripts')); an inline script directly in
     @section('content') would run before this global exists. This page has no Chart.js usage
     in the original — table only, no chart — so Chart.js is intentionally not pulled in. --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')
<x-admin.card class="mb-4">
    <form method="GET" action="{{ route('admin.analytics.rfm') }}" class="flex flex-wrap items-end justify-between gap-3">
        <div class="flex flex-wrap items-end gap-3">
            @foreach ($thresholds as $key => $value)
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600" for="th-{{ $key }}">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                    <input type="number" id="th-{{ $key }}" name="{{ $key }}" value="{{ $value }}" min="1" max="5" class="w-16 rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                </div>
            @endforeach
        </div>
        <div class="flex flex-wrap items-end gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600" for="rfm-segment">Segment</label>
                <select id="rfm-segment" name="segment" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All segments</option>
                    @foreach (\App\Http\Controllers\Admin\Analytics\RfmController::SEGMENTS as $seg)
                        <option value="{{ $seg }}" {{ ($filters['segment'] ?? '') === $seg ? 'selected' : '' }}>{{ $seg }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600" for="rfm-q">Search</label>
                <input id="rfm-q" name="q" value="{{ $filters['q'] ?? '' }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Search name/email…">
            </div>
            <x-admin.button type="submit"><i class="fas fa-filter"></i></x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('admin.analytics.rfm')">Reset</x-admin.button>
        </div>
    </form>
</x-admin.card>

<div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-10">
    <x-admin.card class="xl:col-span-2">
        <div class="flex items-center gap-2">
            <i class="fas fa-users text-blue-500"></i>
            <div>
                <div class="text-lg font-semibold text-slate-900">{{ number_format($summary['total_customers']) }}</div>
                <div class="text-xs text-slate-500">Customers</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card class="xl:col-span-2">
        <div class="flex items-center gap-2">
            <i class="fas fa-dollar-sign text-green-500"></i>
            <div>
                <div class="text-lg font-semibold text-slate-900">${{ number_format($summary['total_revenue'], 2) }}</div>
                <div class="text-xs text-slate-500">Total Revenue</div>
            </div>
        </div>
    </x-admin.card>
    @foreach ($summary['segments'] as $seg => $s)
        <x-admin.card>
            <div class="truncate text-xs text-slate-500" title="{{ $seg }}">{{ $seg }}</div>
            <div class="text-base font-semibold text-slate-900">{{ number_format($s['customers']) }}</div>
            <div class="text-xs text-slate-500">${{ number_format($s['revenue']) }}</div>
        </x-admin.card>
    @endforeach
</div>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Customer</th>
                    <th class="py-2 pr-4">Segment</th>
                    <th class="py-2 pr-4">R</th>
                    <th class="py-2 pr-4">F</th>
                    <th class="py-2 pr-4">M</th>
                    <th class="py-2 pr-4">Recency (d)</th>
                    <th class="py-2 pr-4">Freq</th>
                    <th class="py-2 pr-4">Monetary</th>
                    <th class="py-2 pr-4">Last won</th>
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
    var base = @json(route('admin.analytics.rfm.data'));
    var current = location.search; /* keep threshold + filter params on paging */
    var url = base + current;

    // Bootstrap badge class (as used by the original AdminLTE segColors map) → Tailwind pill
    // classes — same mapping convention as admin/ordersm/index.blade.php's badgeClasses().
    var segColors = {
        'Champions': 'bg-green-100 text-green-700',
        'Loyal': 'bg-blue-100 text-blue-700',
        'Potential Loyalist': 'bg-cyan-100 text-cyan-700',
        'New': 'bg-slate-100 text-slate-700',
        'Promising': 'bg-teal-100 text-teal-700',
        'Need Attention': 'bg-yellow-100 text-yellow-800',
        'At Risk': 'bg-orange-100 text-orange-700',
        'Hibernating': 'bg-red-100 text-red-700'
    };

    DP.infiniteScroll({
        url: url,
        target: '#dp-tbody',
        render: function (r) {
            var color = segColors[r.segment] || segColors['New'];
            return '<tr>'
                + '<td data-label="Customer" class="py-2 pr-4"><strong>' + DP.esc(r.name) + '</strong><br><small class="text-slate-500">' + DP.esc(r.email) + '</small></td>'
                + '<td data-label="Segment" class="py-2 pr-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + color + '">' + DP.esc(r.segment) + '</span></td>'
                + '<td data-label="R" class="py-2 pr-4">' + DP.esc(r.r) + '</td>'
                + '<td data-label="F" class="py-2 pr-4">' + DP.esc(r.f) + '</td>'
                + '<td data-label="M" class="py-2 pr-4">' + DP.esc(r.m) + '</td>'
                + '<td data-label="Recency (d)" class="py-2 pr-4">' + DP.esc(r.recency_days) + '</td>'
                + '<td data-label="Freq" class="py-2 pr-4">' + DP.esc(r.frequency) + '</td>'
                + '<td data-label="Monetary" class="py-2 pr-4">$' + Number(r.monetary).toLocaleString() + '</td>'
                + '<td data-label="Last won" class="py-2 pr-4 text-xs text-slate-500">' + DP.esc(r.last_won_at) + '</td>'
                + '</tr>';
        }
    });
})();
</script>
@endpush
