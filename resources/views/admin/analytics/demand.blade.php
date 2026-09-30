@extends('layouts.tailwind.app')

@section('title', 'Service Demand')
@section('page_title', 'Service & Product Demand')
@section('page_subtitle', 'Most requested items across orders and offers')

{{-- window.DP (infinite-scroll/esc helpers) and Chart.js are only loaded by the old AdminLTE
     layout; this page relies on DP.esc/DP.infiniteScroll for its JS row templates and on
     Chart.js for the top-10 bar chart, so both must be pulled in explicitly here — the new
     Tailwind layout never loads them globally. Pushed via @push('admin_scripts') so they load
     AFTER the layout's jQuery <script> tag (layout yields content, then jQuery, then
     @stack('admin_scripts')); an inline script directly in @section('content') would run before
     these globals exist. --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')
<div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
    <x-admin.card>
        <div class="flex items-center gap-2">
            <i class="fas fa-list-ol text-cyan-500"></i>
            <div>
                <div class="text-lg font-semibold text-slate-900">{{ number_format($totals['line_items']) }}</div>
                <div class="text-xs text-slate-500">Total line items</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <i class="fas fa-crown text-green-500"></i>
            <div class="min-w-0">
                <div class="truncate text-base font-semibold text-slate-900">{{ $top[0]['name'] ?? '—' }}</div>
                <div class="text-xs text-slate-500">Top item{{ isset($top[0]) ? ' · ' . number_format($top[0]['qty']) . ' units' : '' }}</div>
            </div>
        </div>
    </x-admin.card>
</div>

<x-admin.card class="mb-4">
    <div class="mb-2 text-sm font-semibold text-slate-800"><i class="fas fa-chart-bar mr-1 text-slate-400"></i> Top 10 requested items</div>
    <div class="min-h-[280px]"><canvas id="dp-top"></canvas></div>
</x-admin.card>

<x-admin.card class="mb-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="f-q">Search</label>
            <input id="f-q" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:280px" placeholder="Search product/service name…" value="{{ $q }}">
        </div>
        <span class="text-xs text-slate-400">Loaded on scroll</span>
    </div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Item</th>
                    <th class="py-2 pr-4">Line items</th>
                    <th class="py-2 pr-4">Orders</th>
                    <th class="py-2 pr-4">Total qty</th>
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
    var top = @json($top);
    var base = @json(route('admin.analytics.demand.data'));

    new Chart(document.getElementById('dp-top'), {
        type: 'horizontalBar',
        data: {
            labels: top.map(function (t) { return DP.esc(t.name); }),
            datasets: [{ label: 'Units', data: top.map(function (t) { return t.qty; }), backgroundColor: '#17a2b8' }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    var sc = DP.infiniteScroll({
        url: base,
        target: '#dp-tbody',
        render: function (r) {
            return '<tr>'
                + '<td data-label="Item" class="py-2 pr-4">' + DP.esc(r.name) + '</td>'
                + '<td data-label="Line items" class="py-2 pr-4">' + DP.esc(r.items) + '</td>'
                + '<td data-label="Orders" class="py-2 pr-4">' + DP.esc(r.orders_count) + '</td>'
                + '<td data-label="Total qty" class="py-2 pr-4">' + DP.esc(r.qty) + '</td>'
                + '</tr>';
        }
    });

    var q = document.getElementById('f-q'), t;
    q.addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () {
            var u = new URL(base);
            if (q.value.trim()) u.searchParams.set('q', q.value.trim());
            sc.reload(u.toString());
        }, 350);
    });
})();
</script>
@endpush
