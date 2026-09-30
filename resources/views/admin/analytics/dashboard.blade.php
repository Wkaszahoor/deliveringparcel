@extends('layouts.tailwind.app')

@section('title', 'Analytics Dashboard')
@section('page_title', 'Analytics Dashboard')
@section('page_subtitle', 'Live KPIs, charts & recent orders — all computed from the database')

{{-- window.DP (infinite-scroll/esc helpers) and Chart.js are only loaded by the old AdminLTE
     layout; this page relies on DP.esc for its JS row templates and on Chart.js for the three
     charts, so both must be pulled in explicitly here — the new Tailwind layout never loads
     them globally. Pushed via @push('admin_scripts') so they load AFTER the layout's jQuery
     <script> tag (layout yields content, then jQuery, then @stack('admin_scripts')); an inline
     script directly in @section('content') would run before these globals exist. --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')
<div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-7" id="dp-kpis">
    @php
        $cards = [
            ['revenue_today',    'Revenue Today',     'fa-dollar-sign',    'text-green-500'],
            ['revenue_month',    'Revenue This Month','fa-calendar-alt',   'text-blue-500'],
            ['total_orders',     'Total Orders',       'fa-box',            'text-cyan-500'],
            ['awaiting_orders',  'Awaiting Action',    'fa-hourglass-half', 'text-yellow-500'],
            ['new_users_30d',    'New Users (30d)',    'fa-user-plus',      'text-teal-500'],
            ['contact_messages', 'Messages (30d)',     'fa-envelope',       'text-red-500'],
            ['open_quotes',      'Open Quotes (30d)',  'fa-file-alt',       'text-slate-500'],
        ];
    @endphp
    @foreach ($cards as $c)
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fas {{ $c[2] }} {{ $c[3] }}"></i>
                <div class="min-w-0">
                    <div class="text-lg font-semibold text-slate-900" data-kpi="{{ $c[0] }}">&nbsp;</div>
                    <div class="text-xs text-slate-500">{{ $c[1] }}</div>
                </div>
            </div>
        </x-admin.card>
    @endforeach
</div>

<div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
    <x-admin.card class="xl:col-span-8">
        <div class="mb-2 text-sm font-semibold text-slate-800"><i class="fas fa-chart-area mr-1 text-slate-400"></i> Revenue — last 30 days</div>
        <div class="min-h-[260px]" id="dp-chart-rev"><canvas></canvas></div>
    </x-admin.card>
    <x-admin.card class="xl:col-span-4">
        <div class="mb-2 text-sm font-semibold text-slate-800"><i class="fas fa-chart-pie mr-1 text-slate-400"></i> Orders by status</div>
        <div class="min-h-[260px]" id="dp-chart-status"><canvas></canvas></div>
    </x-admin.card>
    <x-admin.card class="xl:col-span-12">
        <div class="mb-2 text-sm font-semibold text-slate-800"><i class="fas fa-users mr-1 text-slate-400"></i> New users per week (12 weeks)</div>
        <div class="min-h-[220px]" id="dp-chart-users"><canvas></canvas></div>
    </x-admin.card>
</div>

<h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-clock mr-2 text-slate-400"></i>Recent orders</h3>
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Order</th>
                    <th class="py-2 pr-4">Customer</th>
                    <th class="py-2 pr-4">Route</th>
                    <th class="py-2 pr-4">Total</th>
                    <th class="py-2 pr-4">Accepted</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Date</th>
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
    var esc = DP.esc;
    var money = function (v) { return '$' + Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

    /* KPI placeholders + skeleton rows */
    var kpiRow = '<tr>' + '<td colspan="7" class="py-2 pr-4"><div class="h-5 animate-pulse rounded bg-slate-100"></div></td>' + '</tr>';
    var tb = document.getElementById('dp-tbody');
    for (var i = 0; i < 5; i++) tb.insertAdjacentHTML('beforeend', kpiRow);

    fetch(@json(route('admin.analytics.data')), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            /* KPIs */
            Object.keys(res.kpis).forEach(function (k) {
                var el = document.querySelector('[data-kpi="' + k + '"]');
                if (!el) return;
                el.textContent = (k.indexOf('revenue') === 0) ? money(res.kpis[k]) : Number(res.kpis[k]).toLocaleString();
            });

            /* Charts — init lazily per canvas container */
            lazyChart('dp-chart-rev', function () {
                var d = res.charts.revenue_daily;
                return lineChart(d.labels, d.values);
            });
            lazyChart('dp-chart-status', function () {
                var d = res.charts.orders_by_status;
                return doughnut(d.labels, d.values);
            });
            lazyChart('dp-chart-users', function () {
                var d = res.charts.users_weekly;
                return barChart(d.labels, d.values);
            });

            /* Recent orders — infinite scroll over res.orders (same {data,last_page} shape) */
            tb.innerHTML = '';
            var state = { page: 1, loading: false, done: false };
            var sentinel = document.createElement('div');
            sentinel.className = 'flex items-center justify-center py-3';
            sentinel.innerHTML = '<i class="fas fa-spinner fa-spin text-slate-400"></i>';
            tb.parentElement.appendChild(sentinel);

            function fetchPage() {
                if (state.loading || state.done) return;
                state.loading = true;
                var u = @json(route('admin.analytics.data')) + '?page=' + state.page;
                fetch(u, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (r2) {
                        (r2.orders.data || []).forEach(function (o) { tb.insertAdjacentHTML('beforeend', row(o)); });
                        if (state.page >= (r2.orders.last_page || 1)) {
                            state.done = true;
                            sentinel.innerHTML = '<span class="text-xs text-slate-400">No more records</span>';
                        }
                        state.page++;
                        state.loading = false;
                    });
            }
            function row(o) {
                var st = esc(o.order_status || '—');
                return '<tr>'
                    + '<td data-label="Order" class="py-2 pr-4"><strong>#' + esc(o.order_id) + '</strong></td>'
                    + '<td data-label="Customer" class="py-2 pr-4">' + esc(o.user_name || '—') + '<br><small class="text-slate-500">' + esc(o.user_email || '') + '</small></td>'
                    + '<td data-label="Route" class="py-2 pr-4">' + esc(o.shipfrom || '?') + ' <i class="fas fa-arrow-right fa-xs mx-1 text-slate-400"></i> ' + esc(o.shipto || '?') + '</td>'
                    + '<td data-label="Total" class="py-2 pr-4">$' + esc(o.total) + '</td>'
                    + '<td data-label="Accepted" class="py-2 pr-4">' + (o.accepted_amount ? money(o.accepted_amount) : '<span class="text-slate-400">—</span>') + '</td>'
                    + '<td data-label="Status" class="py-2 pr-4"><span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">' + st + '</span></td>'
                    + '<td data-label="Date" class="py-2 pr-4 text-xs text-slate-500">' + esc(o.created_at) + '</td>'
                    + '</tr>';
            }
            new IntersectionObserver(function (e) { if (e[0].isIntersecting) fetchPage(); }, { rootMargin: '250px 0px' }).observe(sentinel);
        });

    function lazyChart(id, make) {
        var el = document.getElementById(id);
        if (!el) return;
        if (!('IntersectionObserver' in window)) { make(); return; }
        new IntersectionObserver(function (e, io) {
            if (e[0].isIntersecting) { io.disconnect(); make(); }
        }, { rootMargin: '100px 0px' }).observe(el);
    }
    function lineChart(labels, values) {
        return new Chart(document.querySelector('#dp-chart-rev canvas'), {
            type: 'line',
            data: { labels: labels, datasets: [{ label: 'Revenue', data: values, borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,.12)', fill: true, tension: .3, pointRadius: 2 }] },
            options: responsiveOpts(labels.length)
        });
    }
    function doughnut(labels, values) {
        return new Chart(document.querySelector('#dp-chart-status canvas'), {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#fd7e14'] }] },
            options: { responsive: true, maintainAspectRatio: false, legend: { position: 'right' } }
        });
    }
    function barChart(labels, values) {
        return new Chart(document.querySelector('#dp-chart-users canvas'), {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'New users', data: values, backgroundColor: '#007bff' }] },
            options: responsiveOpts(labels.length)
        });
    }
    function responsiveOpts(n) {
        return {
            responsive: true, maintainAspectRatio: false,
            scales: { xAxes: [{ ticks: { maxTicksLimit: Math.min(n, 12), autoSkip: true } }] }
        };
    }
})();
</script>
@endpush
