@extends('layouts.tailwind.app')

@section('title', 'Hero Slider')
@section('page_title', 'Hero Slider')
@section('page_subtitle', 'Manage homepage banner slides')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-2">
    <form method="GET" action="{{ route('admin.hero-slides.index') }}" class="flex flex-wrap gap-2" data-dp-filters>
        <select name="active" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:160px;" data-dp-filter>
            <option value="">All slides</option>
            <option value="1" {{ $filters['active'] === '1' ? 'selected' : '' }}>Active only</option>
            <option value="0" {{ $filters['active'] === '0' ? 'selected' : '' }}>Inactive only</option>
        </select>
    </form>
    <x-admin.button tag="a" :href="route('admin.hero-slides.create')"><i class="fas fa-plus"></i> New Slide</x-admin.button>
</div>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Pos.</th>
                    <th class="py-2 pr-4">Image</th>
                    <th class="py-2 pr-4">Slide</th>
                    <th class="py-2 pr-4">Button</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-rows" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
</x-admin.card>
@endsection

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ url('dashbord/plugins/sweetalert2/sweetalert2.min.css') }}">
@endpush

@push('admin_scripts')
{{-- window.DP (infinite scroll + toast helpers), toastr and SweetAlert2 are only loaded by the
     old AdminLTE layouts; this page relies on DP.infiniteScroll/DP.toast and Swal.fire for the
     delete confirm, so all three must be pulled in explicitly here — the shared Tailwind layout
     never loads them globally. These tags sit in @push('admin_scripts'), which renders AFTER the
     layout's jQuery <script> tag (jQuery loads near the bottom, after @yield('content')), so
     placing this inline script directly in @section('content') would run before jQuery/DP exist. --}}
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
(function () {
    'use strict';

    var esc = function (s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };
    var thumb = '<img class="dp-lazy h-10 w-16 rounded object-cover" alt="slide" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="';

    function buildUrl() {
        var params = new URLSearchParams();
        var el = document.querySelector('[data-dp-filters] [name="active"]');
        if (el && el.value !== '') params.set('active', el.value);
        var qs = params.toString();
        return '{{ route('admin.hero-slides.data') }}' + (qs ? '?' + qs : '');
    }

    function post(url, done) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            }
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (res.ok) { DP.toast.success(res.message || (res.label ? 'Status: ' + res.label : 'Done')); setTimeout(done, 500); }
            else { DP.toast.error(res.message || 'Request failed.'); }
        }).catch(function () { DP.toast.error('Request failed.'); });
    }

    // delegated actions inside appended rows
    document.addEventListener('click', function (ev) {
        var el = ev.target.closest('[data-dp-action]');
        if (el) {
            ev.preventDefault();
            var after = el.dataset.dpAction === 'toggle' ? function () { updateBadge(el); } : function () { location.reload(); };
            post(el.dataset.url, after);
            return;
        }

        var btn = ev.target.closest('[data-dp-delete]');
        if (!btn) return;
        ev.preventDefault();
        Swal.fire({
            title: btn.dataset.title || 'Delete?',
            text: 'This action cannot be undone.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = btn.dataset.url;
            form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name=csrf-token]').content + '">' +
                             '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        });
    });

    function updateBadge(el) {
        var row = el.closest('tr');
        if (!row) return location.reload();
        fetch(buildUrl(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (res) { location.reload(); })
            .catch(function () { location.reload(); });
    }

    document.querySelectorAll('[data-dp-filter]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });

    DP.infiniteScroll({
        url: buildUrl(),
        target: '#dp-rows',
        render: function (item) {
            var imageCell = item.image ? thumb + esc(item.image) + '">' : '<i class="fas fa-image text-lg text-slate-300"></i>';
            var badge = item.is_active
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">Inactive</span>';
            return '<tr>' +
                '<td class="py-2 pr-4" data-label="Position"><span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">#' + item.position + '</span></td>' +
                '<td class="py-2 pr-4" data-label="Image">' + imageCell + '</td>' +
                '<td class="py-2 pr-4" data-label="Slide"><strong class="text-slate-800">' + esc(item.title) + '</strong>' + (item.subtitle ? '<br><small class="text-slate-500">' + esc(item.subtitle) + '</small>' : '') + '</td>' +
                '<td class="py-2 pr-4" data-label="Button">' + (item.btn_text ? esc(item.btn_text) + (item.btn_link ? '<br><small class="text-slate-500">' + esc(item.btn_link) + '</small>' : '') : '—') + '</td>' +
                '<td class="py-2 pr-4" data-label="Status">' + badge + '</td>' +
                '<td class="py-2 pr-4 text-right text-nowrap" data-label="Actions">' +
                    '<div class="flex items-center justify-end gap-2">' +
                    '<a href="' + item.urls.up + '" data-dp-action="move" data-url="' + item.urls.up + '" class="text-slate-400 hover:text-brand" title="Move up"><i class="fas fa-arrow-up"></i></a>' +
                    '<a href="' + item.urls.down + '" data-dp-action="move" data-url="' + item.urls.down + '" class="text-slate-400 hover:text-brand" title="Move down"><i class="fas fa-arrow-down"></i></a>' +
                    '<a href="' + item.urls.edit + '" class="text-slate-400 hover:text-brand" title="Edit"><i class="fas fa-pen"></i></a>' +
                    '<a href="#" data-dp-action="toggle" data-url="' + item.urls.toggle + '" class="text-slate-400 hover:text-amber-600" title="Toggle status"><i class="fas fa-power-off"></i></a>' +
                    '<a href="#" data-dp-delete data-url="' + item.urls.delete + '" data-title="Delete slide?" class="text-slate-400 hover:text-red-600" title="Delete"><i class="fas fa-trash"></i></a>' +
                    '</div>' +
                '</td>' +
            '</tr>';
        }
    });

    @if (session('success'))
        document.addEventListener('DOMContentLoaded', function () { DP.toast.success(@json(session('success'))); });
    @endif
})();
</script>
@endpush
