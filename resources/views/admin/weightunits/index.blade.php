@extends('layouts.tailwind.app')

@section('title', 'Weight Units')
@section('page_title', 'Weight Units')
@section('page_subtitle', 'Units used for parcel weights across the site')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-2">
    <form method="GET" action="{{ route('admin.weight-units.index') }}" class="flex flex-wrap gap-2" data-dp-filters>
        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search name or symbol…" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <select name="active" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" data-dp-filter>
            <option value="">All statuses</option>
            <option value="1" {{ $filters['active'] === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ $filters['active'] === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <select name="default" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" data-dp-filter>
            <option value="">All units</option>
            <option value="1" {{ $filters['default'] === '1' ? 'selected' : '' }}>Default only</option>
        </select>
        <x-admin.button type="submit"><i class="fas fa-search"></i> Search</x-admin.button>
    </form>
    <x-admin.button tag="a" :href="route('admin.weight-units.create')"><i class="fas fa-plus"></i> New Unit</x-admin.button>
</div>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Unit</th>
                    <th class="py-2 pr-4">Symbol</th>
                    <th class="py-2 pr-4">Grams</th>
                    <th class="py-2 pr-4">Default</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="dp-rows"></tbody>
        </table>
    </div>
</x-admin.card>
@endsection

@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
<link rel="stylesheet" href="{{ url('dashbord/plugins/sweetalert2/sweetalert2.min.css') }}">
@endpush

{{-- This page's table is built by DP.infiniteScroll (window.DP, from dp-lazy.js), row
     actions call DP.toast (toastr) and Swal.fire (SweetAlert2) — none of these are
     loaded globally by layouts.tailwind.app, so all three are pushed explicitly here.
     jQuery loads at the bottom of the shared layout, after @yield('content'), so this
     script — which also touches $ indirectly via DP/toastr's own jQuery-free usage —
     stays in @push('admin_scripts') regardless. --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
(function () {
    'use strict';

    var esc = function (s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    function buildUrl() {
        var params = new URLSearchParams();
        ['q', 'active', 'default'].forEach(function (name) {
            var el = document.querySelector('[data-dp-filters] [name="' + name + '"]');
            if (el && el.value !== '') params.set(name, el.value);
        });
        var qs = params.toString();
        return '{{ route('admin.weight-units.data') }}' + (qs ? '?' + qs : '');
    }

    function post(url) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            }
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (res.ok) { DP.toast.success(res.message || 'Done.'); setTimeout(function () { location.reload(); }, 500); }
            else { DP.toast.error(res.message || 'Request failed.'); }
        }).catch(function () { DP.toast.error('Request failed.'); });
    }

    document.addEventListener('click', function (ev) {
        var el = ev.target.closest('[data-dp-action]');
        if (el) { ev.preventDefault(); post(el.dataset.url); return; }

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

    document.querySelectorAll('[data-dp-filter]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });

    DP.infiniteScroll({
        url: buildUrl(),
        target: '#dp-rows',
        render: function (item) {
            var defBadge = item.is_default
                ? '<span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">Default</span>'
                : '<span class="text-slate-400">&mdash;</span>';
            var badge = item.is_active
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">Inactive</span>';
            return '<tr>' +
                '<td class="py-2 pr-4"><span class="font-medium text-slate-900">' + esc(item.name) + '</span></td>' +
                '<td class="py-2 pr-4"><span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700">' + esc(item.symbol) + '</span></td>' +
                '<td class="py-2 pr-4">' + item.grams + ' g</td>' +
                '<td class="py-2 pr-4">' + defBadge + '</td>' +
                '<td class="py-2 pr-4">' + badge + '</td>' +
                '<td class="py-2 pr-4 text-right text-nowrap">' +
                    '<a href="' + item.urls.edit + '" class="text-brand hover:underline" title="Edit">Edit</a> ' +
                    (item.is_default
                        ? '<span class="cursor-not-allowed text-slate-300" title="The default unit cannot be deleted">Delete</span>'
                        : '<a href="#" data-dp-delete data-url="' + item.urls.delete + '" data-title="Delete unit?" class="text-red-600 hover:underline" title="Delete">Delete</a>') +
                    (item.is_default
                        ? ''
                        : ' <a href="#" data-dp-action="default" data-url="' + item.urls.default + '" class="text-green-600 hover:underline" title="Make default">Make default</a>') +
                '</td>' +
            '</tr>';
        }
    });

    @if (session('success'))
        document.addEventListener('DOMContentLoaded', function () { DP.toast.success(@json(session('success'))); });
    @endif
    @if (session('error'))
        document.addEventListener('DOMContentLoaded', function () { DP.toast.error(@json(session('error'))); });
    @endif
})();
</script>
@endpush
