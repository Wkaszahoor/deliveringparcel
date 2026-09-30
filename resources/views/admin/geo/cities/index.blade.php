@extends('layouts.tailwind.app')

@section('title', 'Cities')
@section('page_title', 'Cities')
@section('page_subtitle', 'City management linked to states')

{{-- window.DP (infinite scroll + confirm helpers), toastr and SweetAlert2 are only loaded by the
     old AdminLTE layouts; this page relies on DP.infiniteScroll for its table and DP.bindConfirms
     (backs the delete-city action via Swal), so all three must be pulled in explicitly here — the
     new Tailwind layout never loads them globally. Pushed via @push('admin_scripts') so they load
     AFTER the layout's jQuery <script> tag (layout yields content, then jQuery, then
     @stack('admin_scripts')); an inline script directly in @section('content') would run before
     jQuery/these plugins exist. --}}
@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

<x-admin.card class="mb-4">
    <div class="flex flex-wrap items-center gap-2">
        <input id="f-q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Search city…"
               class="w-full max-w-[240px] rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <select id="f-state" class="w-full max-w-[220px] rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">State: all</option>
            @foreach ($states as $s)
                <option value="{{ $s->id }}" {{ (string) ($filters['state'] ?? '') === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <x-admin.button tag="a" :href="route('admin.geo.cities.create')" class="ml-auto">
            <i class="fas fa-plus"></i> New City
        </x-admin.button>
    </div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">City</th>
                    <th class="py-2 pr-4">State</th>
                    <th class="py-2 pr-4">Country</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody-gc" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <div class="dp-scroll-sentinel py-3 text-center text-xs text-slate-400"></div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var base = @json(route('admin.geo.cities.data'));

    function buildUrl() {
        var u = new URL(base);
        var q = document.getElementById('f-q').value.trim();
        var state = document.getElementById('f-state').value;
        if (q) u.searchParams.set('q', q);
        if (state) u.searchParams.set('state', state);
        return u.toString();
    }

    function render(c) {
        var statusBadge = c.active
            ? '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700">Active</span>'
            : '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-700">Off</span>';
        return '<tr>' +
            '<td data-label="City" class="py-2 pr-4"><strong>' + DP.esc(c.name) + '</strong></td>' +
            '<td data-label="State" class="py-2 pr-4">' + DP.esc(c.state || '—') + '</td>' +
            '<td data-label="Country" class="py-2 pr-4">' + DP.esc(c.country || '—') + '</td>' +
            '<td data-label="Status" class="py-2 pr-4">' + statusBadge + '</td>' +
            '<td data-label="Actions" class="py-2 pr-4 text-right whitespace-nowrap">' +
                '<a href="' + c.urls.edit + '" class="mr-1 inline-flex items-center rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" title="Edit"><i class="fas fa-edit"></i></a>' +
                '<a href="#" data-dp-confirm data-title="Delete city?" data-url="' + c.urls.delete + '" data-method="DELETE" class="inline-flex items-center rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50" title="Delete"><i class="fas fa-trash"></i></a>' +
            '</td>' +
        '</tr>';
    }

    var sc = DP.infiniteScroll({ url: buildUrl(), target: '#dp-tbody-gc', render: render });

    var t;
    document.getElementById('f-q').addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () { sc.reload(buildUrl()); }, 350);
    });
    document.getElementById('f-state').addEventListener('change', function () { sc.reload(buildUrl()); });

    DP.bindConfirms();
})();
</script>
@endpush
