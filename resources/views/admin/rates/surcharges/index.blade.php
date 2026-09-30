@extends('layouts.tailwind.app')

@section('title', 'Surcharges')
@section('page_title', 'Surcharges')
@section('page_subtitle', 'Fuel, remote-area and other add-on charges')

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
<div class="mb-4 flex items-center">
    <x-admin.button tag="a" :href="route('admin.rates.surcharges.create')" class="ml-auto"><i class="fas fa-plus"></i> New Surcharge</x-admin.button>
</div>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Name</th>
                    <th class="py-2 pr-4">Type</th>
                    <th class="py-2 pr-4">Value</th>
                    <th class="py-2 pr-4">Applies to</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
</x-admin.card>
@endsection

{{-- Same DP.infiniteScroll + data-dp-confirm(SweetAlert2) pattern as rates/zones/index — window.DP,
     toastr and sweetalert2 aren't loaded globally by the Tailwind layout, so they're pushed here
     after the layout's jQuery <script> tag. --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
(function () {
    function badgeClasses(color) {
        var map = { success: 'bg-green-100 text-green-700', secondary: 'bg-slate-100 text-slate-700' };
        return 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + (map[color] || map.secondary);
    }
    DP.infiniteScroll({
        url: @json(route('admin.rates.surcharges.data')),
        target: '#dp-tbody',
        render: function (s) {
            return '<tr>'
                + '<td class="py-2 pr-4" data-label="Name"><strong>' + DP.esc(s.name) + '</strong></td>'
                + '<td class="py-2 pr-4" data-label="Type">' + DP.esc(s.type) + '</td>'
                + '<td class="py-2 pr-4" data-label="Value">' + DP.esc(s.value) + (s.type === 'percentage' ? '%' : ' $') + '</td>'
                + '<td class="py-2 pr-4" data-label="Applies to">' + DP.esc(s.applies) + '</td>'
                + '<td class="py-2 pr-4" data-label="Status"><span class="' + badgeClasses(s.active ? 'success' : 'secondary') + '">' + (s.active ? 'Active' : 'Off') + '</span></td>'
                + '<td class="py-2 pr-4 text-right" data-label="Actions">'
                + '<a href="' + s.urls.edit + '" class="mr-2 text-slate-400 hover:text-brand" title="Edit"><i class="fas fa-edit"></i></a>'
                + '<a href="#" data-dp-confirm data-url="' + s.urls.delete + '" data-method="DELETE" data-title="Delete surcharge?" class="text-slate-400 hover:text-red-600" title="Delete"><i class="fas fa-trash"></i></a>'
                + '</td></tr>';
        }
    });
})();
</script>
@endpush
