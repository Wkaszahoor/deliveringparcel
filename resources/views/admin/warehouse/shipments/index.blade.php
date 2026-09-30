@extends('layouts.tailwind.app')

@section('title', 'Shipments')
@section('page_title', 'Warehouse Shipments')
@section('page_subtitle', 'Consolidated outbound shipments with status flow')

{{-- window.DP (infinite scroll helper) and toastr are only loaded by the old AdminLTE layouts;
     this page relies on DP.infiniteScroll for its table, so both must be pulled in explicitly
     here — the new Tailwind layout never loads them globally. Pushed via @push('admin_scripts')
     so they load AFTER the layout's jQuery <script> tag (layout yields content, then jQuery,
     then @stack('admin_scripts')); an inline script directly in @section('content') would run
     before jQuery/these plugins exist. --}}
@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

<x-admin.card class="mb-4">
    <div class="flex flex-wrap items-center gap-2">
        <input id="f-q" type="text" placeholder="Search code/tracking/user…"
               class="w-full max-w-[260px] rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <select id="f-status" class="w-full max-w-[180px] rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">Status: all</option>
            @foreach ($statusMap as $key => $m)
                <option value="{{ $key }}">{{ $m['label'] }}</option>
            @endforeach
        </select>
        <x-admin.button variant="secondary" tag="a" :href="route('admin.warehouse.packages.index')" class="ml-auto">
            <i class="fas fa-box-open"></i> Packages
        </x-admin.button>
    </div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Code</th>
                    <th class="py-2 pr-4">Customer</th>
                    <th class="py-2 pr-4">Carrier</th>
                    <th class="py-2 pr-4">Tracking</th>
                    <th class="py-2 pr-4">Packages</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Created</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody-ws" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <div class="dp-scroll-sentinel py-3 text-center text-xs text-slate-400"></div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var url = @json(route('admin.warehouse.shipments.data'));

    // Bootstrap badge class (from config/admin_warehouse.php's shipment_statuses) → Tailwind
    // pill classes — same mapping convention as admin/ordersm/index.blade.php's badgeClasses().
    function badgeClasses(bootstrap) {
        var map = {
            'bg-secondary': 'bg-slate-100 text-slate-700',
            'bg-info':      'bg-cyan-100 text-cyan-700',
            'bg-primary':   'bg-blue-100 text-blue-700',
            'bg-success':   'bg-green-100 text-green-700',
            'bg-danger':    'bg-red-100 text-red-700'
        };
        return map[bootstrap] || map['bg-secondary'];
    }

    function buildUrl() {
        var u = new URL(url);
        var q = document.getElementById('f-q').value.trim();
        var status = document.getElementById('f-status').value;
        if (q) u.searchParams.set('q', q);
        if (status) u.searchParams.set('status', status);
        return u.toString();
    }

    function render(r) {
        return '<tr>' +
            '<td data-label="Code" class="py-2 pr-4">' + DP.esc(r.code) + '</td>' +
            '<td data-label="Customer" class="py-2 pr-4">' + DP.esc(r.user) + (r.email ? '<br><small class="text-slate-500">' + DP.esc(r.email) + '</small>' : '') + '</td>' +
            '<td data-label="Carrier" class="py-2 pr-4">' + DP.esc(r.carrier) + '</td>' +
            '<td data-label="Tracking" class="py-2 pr-4">' + DP.esc(r.tracking) + '</td>' +
            '<td data-label="Packages" class="py-2 pr-4">' + DP.esc(r.packages) + '</td>' +
            '<td data-label="Status" class="py-2 pr-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + badgeClasses(r.color) + '">' + DP.esc(r.status) + '</span></td>' +
            '<td data-label="Created" class="py-2 pr-4">' + DP.esc(r.at) + '</td>' +
            '<td data-label="Actions" class="py-2 pr-4 text-right whitespace-nowrap">' +
                '<a href="' + r.urls.show + '" class="inline-flex items-center rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" title="View"><i class="fas fa-eye"></i></a>' +
            '</td>' +
        '</tr>';
    }

    var sc = DP.infiniteScroll({ url: buildUrl(), target: '#dp-tbody-ws', render: render });

    ['f-q', 'f-status'].forEach(function (id) {
        var el = document.getElementById(id);
        var ev = el.tagName === 'SELECT' ? 'change' : 'input';
        var t;
        el.addEventListener(ev, function () {
            clearTimeout(t);
            t = setTimeout(function () { sc.reload(buildUrl()); }, 350);
        });
    });
})();
</script>
@endpush
