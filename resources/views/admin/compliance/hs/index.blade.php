@extends('layouts.tailwind.app')

@section('title', 'HS Codes')
@section('page_title', 'HS Codes')
@section('page_subtitle', 'Harmonized System customs codes')

{{-- window.DP (infinite scroll helpers), toastr and SweetAlert2 are only loaded by the old
     AdminLTE layouts; see admin/compliance/restricted/index.blade.php for why the delete confirm
     uses a delegated click handler instead of DP.bindConfirms. --}}
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
        <input id="f-q" type="text" placeholder="Search code/description…" class="w-full max-w-[280px] rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <x-admin.button tag="a" variant="secondary" :href="route('admin.compliance.restricted.index')" class="ml-auto">Restricted items</x-admin.button>
        <x-admin.button tag="a" :href="route('admin.compliance.hs.create')"><i class="fas fa-plus"></i> New</x-admin.button>
    </div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Code</th>
                    <th class="py-2 pr-4">Description</th>
                    <th class="py-2 pr-4">Category</th>
                    <th class="py-2 pr-4">Duty hint</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody-hs" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <div class="dp-scroll-sentinel py-3 text-center text-xs text-slate-400"></div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var url = @json(route('admin.compliance.hs.data'));
    var CSRF = document.querySelector('meta[name=csrf-token]').content;

    function buildUrl() {
        var u = new URL(url);
        var q = document.getElementById('f-q').value.trim();
        if (q) u.searchParams.set('q', q);
        return u.toString();
    }

    function render(r) {
        return '<tr>' +
            '<td data-label="Code" class="py-2 pr-4">' + DP.esc(r.code) + '</td>' +
            '<td data-label="Description" class="py-2 pr-4">' + DP.esc(r.desc) + '</td>' +
            '<td data-label="Category" class="py-2 pr-4">' + DP.esc(r.cat) + '</td>' +
            '<td data-label="Duty hint" class="py-2 pr-4">' + DP.esc(r.duty) + '</td>' +
            '<td data-label="Actions" class="py-2 pr-4 text-right whitespace-nowrap">' +
                '<a href="' + r.urls.edit + '" class="mr-1 inline-flex items-center rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" title="Edit"><i class="fas fa-pen"></i></a>' +
                '<a href="#" data-dp-confirm data-title="Delete HS code" data-url="' + r.urls.delete + '" data-method="DELETE" class="inline-flex items-center rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50" title="Delete"><i class="fas fa-trash"></i></a>' +
            '</td>' +
        '</tr>';
    }

    var sc = DP.infiniteScroll({ url: buildUrl(), target: '#dp-tbody-hs', render: render });

    var t;
    document.getElementById('f-q').addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () { sc.reload(buildUrl()); }, 350);
    });

    // Delegated confirm handler — see admin/compliance/restricted/index.blade.php for rationale.
    document.getElementById('dp-tbody-hs').addEventListener('click', function (e) {
        var el = e.target.closest('[data-dp-confirm]');
        if (!el) return;
        e.preventDefault();
        Swal.fire({
            title: el.dataset.title || 'Are you sure?',
            text: el.dataset.text || 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed'
        }).then(function (res) {
            if (!res.isConfirmed) return;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = el.dataset.url;
            form.innerHTML = '<input type="hidden" name="_token" value="' + CSRF + '">' +
                '<input type="hidden" name="_method" value="' + (el.dataset.method || 'DELETE') + '">';
            document.body.appendChild(form);
            form.submit();
        });
    });
})();
</script>
@endpush
