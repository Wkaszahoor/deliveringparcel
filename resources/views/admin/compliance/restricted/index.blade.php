@extends('layouts.tailwind.app')

@section('title', 'Restricted Items')
@section('page_title', 'Restricted & Prohibited Items')

{{-- window.DP (infinite scroll helpers), toastr and SweetAlert2 are only loaded by the old
     AdminLTE layouts; this page relies on DP.infiniteScroll/DP.esc for its table and Swal for
     the delete-row confirm, so all three must be pulled in explicitly here — the new Tailwind
     layout never loads them globally. Delete confirmation uses a delegated click handler (rather
     than DP.bindConfirms, which only binds elements present in the DOM at call time and would
     miss rows inserted later by infinite scroll) so it reliably catches rows added on every
     scroll page, not just the first. Pushed via @push('admin_scripts') so it loads AFTER the
     layout's jQuery <script> tag. --}}
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
        <select id="f-severity" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">Severity: all</option>
            <option value="prohibited">Prohibited</option>
            <option value="restricted">Restricted</option>
        </select>
        <div class="ml-auto flex flex-wrap gap-2">
            <x-admin.button tag="a" variant="secondary" :href="route('admin.compliance.hs.index')">HS codes</x-admin.button>
            <x-admin.button tag="a" variant="secondary" :href="route('admin.compliance.vat.index')">VAT</x-admin.button>
            <x-admin.button tag="a" variant="secondary" :href="route('admin.compliance.consent.index')">Consent</x-admin.button>
            <x-admin.button tag="a" :href="route('admin.compliance.restricted.create')"><i class="fas fa-plus"></i> New</x-admin.button>
        </div>
    </div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Item</th>
                    <th class="py-2 pr-4">Category</th>
                    <th class="py-2 pr-4">Severity</th>
                    <th class="py-2 pr-4">Declaration</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody-ri" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <div class="dp-scroll-sentinel py-3 text-center text-xs text-slate-400"></div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var url = @json(route('admin.compliance.restricted.data'));
    var CSRF = document.querySelector('meta[name=csrf-token]').content;

    function badgeClasses(color) {
        return color === 'bg-danger' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800';
    }

    function buildUrl() {
        var u = new URL(url);
        var q = document.getElementById('f-q').value.trim();
        var severity = document.getElementById('f-severity').value;
        if (q) u.searchParams.set('q', q);
        if (severity) u.searchParams.set('severity', severity);
        return u.toString();
    }

    function render(r) {
        return '<tr>' +
            '<td data-label="Item" class="py-2 pr-4">' + DP.esc(r.name) + '</td>' +
            '<td data-label="Category" class="py-2 pr-4">' + DP.esc(r.category) + '</td>' +
            '<td data-label="Severity" class="py-2 pr-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + badgeClasses(r.color) + '">' + DP.esc(r.severity) + '</span></td>' +
            '<td data-label="Declaration" class="py-2 pr-4">' + DP.esc(r.decl) + '</td>' +
            '<td data-label="Actions" class="py-2 pr-4 text-right whitespace-nowrap">' +
                '<a href="' + r.urls.edit + '" class="mr-1 inline-flex items-center rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" title="Edit"><i class="fas fa-pen"></i></a>' +
                '<a href="#" data-dp-confirm data-title="Delete item" data-url="' + r.urls.delete + '" data-method="DELETE" class="inline-flex items-center rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50" title="Delete"><i class="fas fa-trash"></i></a>' +
            '</td>' +
        '</tr>';
    }

    var sc = DP.infiniteScroll({ url: buildUrl(), target: '#dp-tbody-ri', render: render });

    var t;
    ['f-q', 'f-severity'].forEach(function (id) {
        var el = document.getElementById(id);
        var ev = el.tagName === 'SELECT' ? 'change' : 'input';
        el.addEventListener(ev, function () {
            clearTimeout(t);
            t = setTimeout(function () { sc.reload(buildUrl()); }, 350);
        });
    });

    // Delegated confirm handler — rows are inserted asynchronously by infinite scroll, so binding
    // listeners up front (as DP.bindConfirms does) would miss anything loaded after the first call.
    document.getElementById('dp-tbody-ri').addEventListener('click', function (e) {
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
