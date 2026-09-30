@extends('layouts.tailwind.app')

@section('title', 'Rate Zones')
@section('page_title', 'Rate Zones')
@section('page_subtitle', 'Origin/destination zone definitions and country mappings')

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
<div class="mb-4 flex items-center gap-2">
    <input id="f-q" type="text" placeholder="Search name/code&hellip;" class="max-w-xs rounded-md border border-slate-300 px-3 py-1.5 text-sm">
    <x-admin.button tag="a" :href="route('admin.rates.zones.create')" class="ml-auto"><i class="fas fa-plus"></i> New Zone</x-admin.button>
</div>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Zone</th>
                    <th class="py-2 pr-4">Code</th>
                    <th class="py-2 pr-4">Countries</th>
                    <th class="py-2 pr-4">Rules</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
</x-admin.card>
@endsection

{{-- Rows are fetched via DP.infiniteScroll (window.DP, only loaded by the old AdminLTE layouts) and
     row delete buttons use data-dp-confirm, which DP.bindConfirms() wires to a SweetAlert2 dialog —
     so dp-lazy.js, toastr and sweetalert2 must all be pushed here. Pushed via @push('admin_scripts')
     so they load AFTER the layout's jQuery <script> tag. --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
(function () {
    var base = @json(route('admin.rates.zones.data'));

    function badgeClasses(color) {
        var map = { info: 'bg-blue-100 text-blue-700' };
        return 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + (map[color] || map.info);
    }

    var sc = DP.infiniteScroll({
        url: base, target: '#dp-tbody',
        render: function (z) {
            return '<tr>'
                + '<td class="py-2 pr-4" data-label="Zone"><strong>' + DP.esc(z.name) + '</strong></td>'
                + '<td class="py-2 pr-4" data-label="Code"><span class="' + badgeClasses('info') + '">' + DP.esc(z.code) + '</span></td>'
                + '<td class="py-2 pr-4" data-label="Countries">' + DP.esc(z.countries) + '</td>'
                + '<td class="py-2 pr-4" data-label="Rules">' + DP.esc(z.rules) + '</td>'
                + '<td class="py-2 pr-4 text-right" data-label="Actions">'
                + '<a href="' + z.urls.edit + '" class="mr-2 text-slate-400 hover:text-brand" title="Edit"><i class="fas fa-edit"></i></a>'
                + '<a href="#" data-dp-confirm data-url="' + z.urls.delete + '" data-method="DELETE" data-title="Delete zone?" class="text-slate-400 hover:text-red-600" title="Delete"><i class="fas fa-trash"></i></a>'
                + '</td></tr>';
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
