@extends('layouts.tailwind.app')

@section('title', 'Rate Rules')
@section('page_title', 'Rate Rules')
@section('page_subtitle', 'Zone x zone x service rate matrix with weight brackets')

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
<div class="mb-4 flex flex-wrap items-center gap-2">
    <select id="f-origin" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:180px">
        <option value="">Origin: all</option>
        @foreach ($zones as $z)<option value="{{ $z->id }}">{{ $z->name }}</option>@endforeach
    </select>
    <select id="f-dest" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:180px">
        <option value="">Destination: all</option>
        @foreach ($zones as $z)<option value="{{ $z->id }}">{{ $z->name }}</option>@endforeach
    </select>
    <select id="f-service" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:150px">
        <option value="">Service: all</option>
        @foreach (config('admin_rates.service_types') as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
    </select>
    <select id="f-active" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:140px">
        <option value="">Status: all</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>
    <x-admin.button tag="a" :href="route('admin.rates.rules.create')" class="ml-auto"><i class="fas fa-plus"></i> New Rule</x-admin.button>
</div>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Route</th>
                    <th class="py-2 pr-4">Service</th>
                    <th class="py-2 pr-4">Weight</th>
                    <th class="py-2 pr-4">Base</th>
                    <th class="py-2 pr-4">Per kg</th>
                    <th class="py-2 pr-4">Transit</th>
                    <th class="py-2 pr-4">Prio</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
</x-admin.card>
@endsection

{{-- Same DP.infiniteScroll + data-dp-confirm(SweetAlert2) pattern as rates/zones/index — see the
     comment there. dp-lazy.js/toastr/sweetalert2 aren't loaded globally by the Tailwind layout, so
     they're pushed here after the layout's jQuery <script> tag. --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
(function () {
    var base = @json(route('admin.rates.rules.data'));
    function url() {
        var u = new URL(base);
        ['origin', 'dest', 'service', 'active'].forEach(function (k) {
            var v = document.getElementById('f-' + k).value;
            if (v) u.searchParams.set(k, v);
        });
        return u.toString();
    }

    function badgeClasses(color) {
        var map = {
            info: 'bg-blue-100 text-blue-700',
            success: 'bg-green-100 text-green-700',
            secondary: 'bg-slate-100 text-slate-700',
        };
        return 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + (map[color] || map.secondary);
    }

    var sc = DP.infiniteScroll({
        url: url(), target: '#dp-tbody',
        render: function (r) {
            return '<tr>'
                + '<td class="py-2 pr-4" data-label="Route">' + DP.esc(r.origin) + ' <i class="fas fa-arrow-right fa-xs text-slate-400"></i> ' + DP.esc(r.dest) + '</td>'
                + '<td class="py-2 pr-4" data-label="Service"><span class="' + badgeClasses('info') + '">' + DP.esc(r.service) + '</span></td>'
                + '<td class="py-2 pr-4" data-label="Weight">' + DP.esc(r.weight) + '</td>'
                + '<td class="py-2 pr-4" data-label="Base">$' + DP.esc(r.base) + '</td>'
                + '<td class="py-2 pr-4" data-label="Per kg">$' + DP.esc(r.per_kg) + '</td>'
                + '<td class="py-2 pr-4" data-label="Transit">' + DP.esc(r.transit) + '</td>'
                + '<td class="py-2 pr-4" data-label="Prio">' + DP.esc(r.priority) + '</td>'
                + '<td class="py-2 pr-4" data-label="Status"><span class="' + badgeClasses(r.active ? 'success' : 'secondary') + '">' + (r.active ? 'Active' : 'Off') + '</span></td>'
                + '<td class="py-2 pr-4 text-right" data-label="Actions">'
                + '<a href="' + r.urls.edit + '" class="mr-2 text-slate-400 hover:text-brand" title="Edit"><i class="fas fa-edit"></i></a>'
                + '<a href="#" data-dp-confirm data-url="' + r.urls.delete + '" data-method="DELETE" data-title="Delete rule?" class="text-slate-400 hover:text-red-600" title="Delete"><i class="fas fa-trash"></i></a>'
                + '</td></tr>';
        }
    });
    ['f-origin', 'f-dest', 'f-service', 'f-active'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () { sc.reload(url()); });
    });
})();
</script>
@endpush
