@extends('layouts.tailwind.app')

@section('title', 'Countries')
@section('page_title', 'Countries Matrix')
@section('page_subtitle', 'Enable or disable each country per workspace — changes apply everywhere instantly')

@section('content')
<div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <x-admin.card>
        <div class="flex items-center gap-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-white" style="background:#0b5fff"><i class="fas fa-globe"></i></span>
            <div>
                <div class="text-lg font-semibold text-slate-900" id="cntActive">&ndash;</div>
                <div class="text-xs text-slate-500">Network enabled</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-white" style="background:#16a34a"><i class="fas fa-export"></i></span>
            <div>
                <div class="text-lg font-semibold text-slate-900" id="cntFrom">&ndash;</div>
                <div class="text-xs text-slate-500">Request From (Shop)</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-white" style="background:#f59e0b"><i class="fas fa-import"></i></span>
            <div>
                <div class="text-lg font-semibold text-slate-900" id="cntTo">&ndash;</div>
                <div class="text-xs text-slate-500">Deliver To</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-white" style="background:#8b5cf6"><i class="fas fa-shipping-fast"></i></span>
            <div>
                <div class="text-lg font-semibold text-slate-900" id="cntShipper">&ndash;</div>
                <div class="text-xs text-slate-500">Shipper countries</div>
            </div>
        </div>
    </x-admin.card>
</div>

<div class="mb-4 flex flex-wrap items-center justify-between gap-2">
    <form method="GET" action="{{ route('admin.countries.index') }}" class="flex flex-wrap gap-2" data-dp-filters>
        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search name, ISO or dial code…" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <select name="active" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" data-dp-filter>
            <option value="">All network states</option>
            <option value="1" {{ $filters['active'] === '1' ? 'selected' : '' }}>Enabled only</option>
            <option value="0" {{ $filters['active'] === '0' ? 'selected' : '' }}>Disabled only</option>
        </select>
        <x-admin.button type="submit"><i class="fas fa-search"></i> Search</x-admin.button>
    </form>
    <x-admin.button tag="a" :href="route('admin.countries.create')"><i class="fas fa-plus"></i> New Country</x-admin.button>
</div>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Country</th>
                    <th class="py-2 pr-4">ISO2</th>
                    <th class="py-2 pr-4" title="Master switch — country enabled for the whole network">Network<br><span class="normal-case text-slate-400">register / operate</span></th>
                    <th class="py-2 pr-4" title="Shoppers see this country in the request form Ship From list">Request From<br><span class="normal-case text-slate-400">shop from</span></th>
                    <th class="py-2 pr-4" title="Country shows in the Ship To list — admin delivers there">Deliver To<br><span class="normal-case text-slate-400">ship to</span></th>
                    <th class="py-2 pr-4" title="Country appears in the shipper service-countries grid">Shopper &harr; Shipper<br><span class="normal-case text-slate-400">serve from</span></th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="dp-rows">
                <tr><td colspan="7" class="py-6 text-center text-slate-400">Loading&hellip;</td></tr>
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex justify-end" id="dp-pager"></div>
</x-admin.card>
@endsection

@push('admin_styles')
<style>
.dp-mx-switch { position: relative; display: inline-block; width: 40px; height: 22px; }
.dp-mx-switch input { opacity: 0; width: 0; height: 0; }
.dp-mx-slider { position: absolute; cursor: pointer; inset: 0; background-color: #c8ccd0; border-radius: 22px; transition: .2s; }
.dp-mx-slider:before { content: ""; position: absolute; height: 16px; width: 16px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .2s; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
.dp-mx-switch input:checked + .dp-mx-slider { background-color: #16a34a; }
.dp-mx-switch input:disabled + .dp-mx-slider { opacity: .55; cursor: not-allowed; }
.dp-mx-switch input:checked + .dp-mx-slider.dp-mx-blue { background-color: #0b5fff; }
.dp-mx-switch input:checked + .dp-mx-slider.dp-mx-amber { background-color: #f59e0b; }
.dp-mx-switch input:checked + .dp-mx-slider.dp-mx-violet { background-color: #8b5cf6; }
</style>
@endpush

{{-- This script only uses plain fetch/DOM APIs (no jQuery, no DP/toastr), but is kept
     in @push('admin_scripts') anyway to run after the table markup it queries exists,
     consistent with the rest of this migrated module. --}}
@push('admin_scripts')
<script>
(function () {
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };

    var COLS = [
        { key: 'is_active',          cls: '',            title: 'Network enable' },
        { key: 'allow_request_from', cls: 'dp-mx-blue',  title: 'Request From' },
        { key: 'allow_delivery_to',  cls: 'dp-mx-amber', title: 'Deliver To' },
        { key: 'allow_shipper',      cls: 'dp-mx-violet', title: 'Shipper serve' }
    ];

    function switchCell(row, col) {
        var on = row[col.key] ? 'checked' : '';
        return '<td class="py-2 pr-4"><label class="dp-mx-switch" title="' + col.title + '">' +
            '<input type="checkbox" ' + on + ' data-id="' + row.id + '" data-col="' + col.key + '">' +
            '<span class="dp-mx-slider ' + col.cls + '"></span></label></td>';
    }

    function render(rows, resp) {
        var tbody = document.getElementById('dp-rows');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-slate-400">No countries found.</td></tr>';
        } else {
            tbody.innerHTML = rows.map(function (r) {
                return '<tr>' +
                    '<td class="py-2 pr-4"><span class="font-medium text-slate-900">' + esc(r.name) + '</span></td>' +
                    '<td class="py-2 pr-4"><span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700">' + esc(r.iso2) + '</span></td>' +
                    COLS.map(function (c) { return switchCell(r, c); }).join('') +
                    '<td class="py-2 pr-4 text-right text-nowrap">' +
                    '<a href="' + esc(r.urls.edit) + '" class="text-brand hover:underline">Edit</a> ' +
                    '<button type="button" class="text-red-600 hover:underline" data-del="' + r.id + '">Delete</button>' +
                    '</td></tr>';
            }).join('');
        }

        [['is_active', 'cntActive'], ['allow_request_from', 'cntFrom'],
         ['allow_delivery_to', 'cntTo'], ['allow_shipper', 'cntShipper']].forEach(function (p) {
            var el = document.getElementById(p[1]);
            if (el && resp.matrix_counts) el.textContent = resp.matrix_counts[p[0]] || 0;
        });
        var pager = document.getElementById('dp-pager');
        if (pager && resp.last_page > 1) {
            var html = '';
            for (var i = 1; i <= resp.last_page; i++) {
                html += '<button type="button" class="ml-1 rounded-md px-2.5 py-1 text-sm ' + (i === resp.current_page ? 'bg-brand text-white' : 'border border-slate-300 text-slate-600 hover:bg-slate-50') + '" data-page="' + i + '">' + i + '</button>';
            }
            pager.innerHTML = html;
            pager.querySelectorAll('[data-page]').forEach(function (b) {
                b.addEventListener('click', function () { load(parseInt(b.getAttribute('data-page'), 10)); });
            });
        } else if (pager) { pager.innerHTML = ''; }
        bind();
    }

    function load(page) {
        var q = new URLSearchParams(window.location.search);
        q.set('page', page || 1);
        fetch('{{ url('admin/countries/data') }}?' + q.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (resp) { render(resp.data || [], resp); })
            .catch(function () {
                document.getElementById('dp-rows').innerHTML =
                    '<tr><td colspan="7" class="py-6 text-center text-red-600">Failed to load countries.</td></tr>';
            });
    }

    function bind() {
        document.querySelectorAll('#dp-rows input[type="checkbox"]').forEach(function (box) {
            box.addEventListener('change', function () {
                var id = box.getAttribute('data-id'), col = box.getAttribute('data-col');
                var url = '{{ url('admin/countries') }}' + '/toggle/' + id;
                var fd = new FormData();
                fd.append('column', col);
                fd.append('_token', '{{ csrf_token() }}');
                fd.append(col, box.checked ? 1 : 0);
                box.disabled = true;
                fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); }).then(function (resp) {
                    box.disabled = false;
                    if (!resp.ok) { box.checked = !box.checked; return; }
                    box.checked = !!resp.value;
                    loadCountsOnly();
                }).catch(function () { box.disabled = false; box.checked = !box.checked; });
            });
        });

        document.querySelectorAll('#dp-rows [data-del]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('Delete this country?')) return;
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('admin/countries') }}' + '/' + btn.getAttribute('data-del');
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                    '<input type="hidden" name="_method" value="DELETE">';
                document.body.appendChild(form); form.submit();
            });
        });
    }

    function loadCountsOnly() { load(currentPage()); }

    function currentPage() {
        var p = parseInt(new URLSearchParams(window.location.search).get('page'), 10);
        return isNaN(p) ? 1 : p;
    }

    load(currentPage());
})();
</script>
@endpush
