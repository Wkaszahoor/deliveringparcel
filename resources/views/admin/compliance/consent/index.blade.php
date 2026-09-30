@extends('layouts.tailwind.app')

@section('title', 'Consent Templates')
@section('page_title', 'Consent Templates')
@section('page_subtitle', 'Versioned consent forms with conditional triggers')

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
        <input id="f-q" type="text" placeholder="Search…" class="w-full max-w-[240px] rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <x-admin.button tag="a" :href="route('admin.compliance.consent.create')" class="ml-auto"><i class="fas fa-plus"></i> New Template</x-admin.button>
    </div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Template</th>
                    <th class="py-2 pr-4">Trigger</th>
                    <th class="py-2 pr-4">Signature</th>
                    <th class="py-2 pr-4">Active</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody-ct" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <div class="dp-scroll-sentinel py-3 text-center text-xs text-slate-400"></div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var url = @json(route('admin.compliance.consent.data'));
    var CSRF = document.querySelector('meta[name=csrf-token]').content;

    function buildUrl() {
        var u = new URL(url);
        var q = document.getElementById('f-q').value.trim();
        if (q) u.searchParams.set('q', q);
        return u.toString();
    }

    function render(r) {
        var activeBadge = r.active
            ? '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700">Yes</span>'
            : '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-700">No</span>';
        return '<tr>' +
            '<td data-label="Template" class="py-2 pr-4">' + DP.esc(r.name) + '</td>' +
            '<td data-label="Trigger" class="py-2 pr-4">' + DP.esc(r.trigger) + '</td>' +
            '<td data-label="Signature" class="py-2 pr-4">' + DP.esc(r.sig) + '</td>' +
            '<td data-label="Active" class="py-2 pr-4">' + activeBadge + '</td>' +
            '<td data-label="Actions" class="py-2 pr-4 text-right whitespace-nowrap">' +
                '<a href="' + r.urls.edit + '" class="mr-1 inline-flex items-center rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" title="Edit"><i class="fas fa-pen"></i></a>' +
                '<a href="#" data-dp-confirm data-title="Delete template" data-url="' + r.urls.delete + '" data-method="DELETE" class="inline-flex items-center rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50" title="Delete"><i class="fas fa-trash"></i></a>' +
            '</td>' +
        '</tr>';
    }

    var sc = DP.infiniteScroll({ url: buildUrl(), target: '#dp-tbody-ct', render: render });

    var t;
    document.getElementById('f-q').addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () { sc.reload(buildUrl()); }, 350);
    });

    // Delegated confirm handler — see admin/compliance/restricted/index.blade.php for rationale.
    document.getElementById('dp-tbody-ct').addEventListener('click', function (e) {
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
