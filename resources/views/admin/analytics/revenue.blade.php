@extends('layouts.tailwind.app')

@section('title', 'Revenue Analytics')
@section('page_title', 'Revenue Analytics')
@section('page_subtitle', 'Accepted-offer revenue by month')

{{-- window.DP (infinite-scroll/esc helpers) and Chart.js are only loaded by the old AdminLTE
     layout; this page relies on DP.infiniteScroll/DP.esc for its JS row templates and on
     Chart.js for the monthly bar chart, so both must be pulled in explicitly here — the new
     Tailwind layout never loads them globally. Pushed via @push('admin_scripts') so they load
     AFTER the layout's jQuery <script> tag (layout yields content, then jQuery, then
     @stack('admin_scripts')); an inline script directly in @section('content') would run before
     these globals exist. --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')
<x-admin.card class="mb-4">
    <form method="GET" action="{{ route('admin.analytics.revenue') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="rev-from">From</label>
            <input type="date" id="rev-from" name="from" value="{{ $from }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="rev-to">To</label>
            <input type="date" id="rev-to" name="to" value="{{ $to }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <x-admin.button type="submit"><i class="fas fa-filter"></i> Apply</x-admin.button>
        <x-admin.button variant="secondary" tag="a" :href="route('admin.analytics.revenue')">Reset</x-admin.button>
    </form>
</x-admin.card>

<div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <x-admin.card>
        <div class="flex items-center gap-2">
            <i class="fas fa-dollar-sign text-green-500"></i>
            <div>
                <div class="text-lg font-semibold text-slate-900">${{ number_format($summary['total_revenue'], 2) }}</div>
                <div class="text-xs text-slate-500">Total Revenue</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <i class="fas fa-box text-cyan-500"></i>
            <div>
                <div class="text-lg font-semibold text-slate-900">{{ number_format($summary['orders_count']) }}</div>
                <div class="text-xs text-slate-500">Orders</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <i class="fas fa-chart-bar text-blue-500"></i>
            <div>
                <div class="text-lg font-semibold text-slate-900">${{ number_format($summary['avg_month'], 2) }}</div>
                <div class="text-xs text-slate-500">Avg / Month</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <i class="fas fa-trophy text-yellow-500"></i>
            <div class="min-w-0">
                <div class="truncate text-base font-semibold text-slate-900">{{ $summary['best_month'] ?? '—' }}</div>
                <div class="text-xs text-slate-500">Best Month · ${{ number_format($summary['best_revenue'], 2) }}</div>
            </div>
        </div>
    </x-admin.card>
</div>

<x-admin.card class="mb-4">
    <div class="mb-2 text-sm font-semibold text-slate-800"><i class="fas fa-chart-bar mr-1 text-slate-400"></i> Monthly revenue</div>
    <div class="min-h-[260px]" id="dp-chart"><canvas></canvas></div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Month</th>
                    <th class="py-2 pr-4">Orders</th>
                    <th class="py-2 pr-4">Offers</th>
                    <th class="py-2 pr-4">Revenue</th>
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
    var months = @json($months);
    var revUrl = @json(route('admin.analytics.revenue.data')) + '?from={{ $from }}&to={{ $to }}';

    /* Chart (labels chronological for display) */
    var view = months.slice().reverse();
    new Chart(document.querySelector('#dp-chart canvas'), {
        type: 'bar',
        data: {
            labels: view.map(function (m) { return m.ym; }),
            datasets: [{ label: 'Revenue', data: view.map(function (m) { return m.revenue; }), backgroundColor: '#28a745' }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { xAxes: [{ ticks: { maxTicksLimit: 14 } }] } }
    });

    /* Lazy table */
    DP.infiniteScroll({
        url: revUrl,
        target: '#dp-tbody',
        render: function (m) {
            return '<tr>'
                + '<td data-label="Month" class="py-2 pr-4"><strong>' + DP.esc(m.ym) + '</strong></td>'
                + '<td data-label="Orders" class="py-2 pr-4">' + DP.esc(m.orders_count) + '</td>'
                + '<td data-label="Offers" class="py-2 pr-4">' + DP.esc(m.offers_count) + '</td>'
                + '<td data-label="Revenue" class="py-2 pr-4">$' + Number(m.revenue).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</td>'
                + '</tr>';
        }
    });
})();
</script>
@endpush
