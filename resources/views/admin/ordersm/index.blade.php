@extends('layouts.tailwind.app')

@section('title', 'Orders Management')
@section('page_title', 'Orders')
@section('page_subtitle', 'Track offers, shipments and completions — live orders table')

@push('admin_styles')
<link rel="stylesheet" href="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.css') }}">
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

{{-- window.DP (infinite scroll + toast helpers), toastr and SweetAlert2 are only loaded by the
     old AdminLTE layouts; this page relies on DP.infiniteScroll for its table, DP.toast for
     feedback and Swal for the bulk-archive confirm, so all three must be pulled in explicitly
     here — the new Tailwind layout never loads them globally. These run via @push('admin_scripts')
     so they load AFTER the layout's jQuery <script> tag (layout yields content, then jQuery,
     then @stack('admin_scripts')); an inline script directly in @section('content') would run
     before jQuery/these plugins exist. --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

@php
    /* Filter value for each status key. The '' key (Request Placed / NULL in DB)
       is filtered through the dedicated 'none' sentinel of OrdersController::data(). */
    $filterValue = fn ($key) => $key === '' ? 'none' : $key;

    /* Bootstrap badge class (from config/admin_orders.php) → Tailwind pill classes.
       Same mapping convention as admin/contacts/index.blade.php's badgeClasses() JS
       helper — the new layout loads no Bootstrap CSS, so raw `bg-primary`/`bg-teal`/etc.
       classes render unstyled. Mirrored in the inline <script> below for JS-rendered rows. */
    $badgeClasses = fn (string $bootstrap) => [
        'bg-secondary' => 'bg-slate-100 text-slate-700',
        'bg-primary'   => 'bg-blue-100 text-blue-700',
        'bg-info'      => 'bg-cyan-100 text-cyan-700',
        'bg-teal'      => 'bg-teal-100 text-teal-700',
        'bg-success'   => 'bg-green-100 text-green-700',
        'bg-danger'    => 'bg-red-100 text-red-700',
        'bg-indigo'    => 'bg-indigo-100 text-indigo-700',
        'bg-orange'    => 'bg-orange-100 text-orange-700',
        'bg-purple'    => 'bg-purple-100 text-purple-700',
        'bg-warning'   => 'bg-yellow-100 text-yellow-800',
    ][$bootstrap] ?? 'bg-slate-100 text-slate-700';
@endphp

{{-- ============ KPI row ============ --}}
<div class="mb-4 flex flex-wrap items-stretch gap-2">
    <div class="flex min-w-[140px] flex-col justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm">
        <span class="text-xs text-slate-500">Total matching</span>
        <span class="text-lg font-semibold text-slate-900" id="kpiTotal">&mdash;</span>
    </div>
    @foreach ($statusMap as $key => $meta)
        <button type="button"
                class="dp-status-chip inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium transition {{ $badgeClasses($meta['badge']) }}"
                data-status="{{ $filterValue($key) }}"
                title="{{ $meta['description'] }}">
            {{ $meta['label'] }}
        </button>
    @endforeach
</div>

{{-- ============ Toolbar ============ --}}
<x-admin.card class="mb-4">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fQ">Search</label>
            <input type="search" id="fQ" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm"
                   placeholder="Order id, tracking no, customer email&hellip;">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fStatus">Status</label>
            <select id="fStatus" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All statuses</option>
                <option value="none">Request Placed (unset)</option>
                @foreach ($statusOptions as $key => $label)
                    @if ($key !== '')
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fArchived">Archive</label>
            <select id="fArchived" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">Active only</option>
                <option value="1">Archived only</option>
                <option value="all">Active + archived</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fRange">Date placed</label>
            <input type="text" id="fRange" readonly placeholder="Any date"
                   class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fSort">Sort</label>
            <select id="fSort" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="newest">Newest first</option>
                <option value="oldest">Oldest first</option>
                <option value="amount_desc">Amount high &rarr; low</option>
                <option value="amount_asc">Amount low &rarr; high</option>
                <option value="status">Status A&ndash;Z</option>
            </select>
        </div>
    </div>
    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
        <span class="text-sm text-slate-500" id="dpListMeta">Loading&hellip;</span>
        <button type="button" id="btnReset"
                class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100">
            <i class="fas fa-undo"></i> Reset filters
        </button>
    </div>
</x-admin.card>

{{-- ============ Bulk bar ============ --}}
<div id="bulkBar" class="mb-4 hidden rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
    <div class="flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-1.5 text-sm text-slate-700">
            <input type="checkbox" id="checkAll"> Select all listed
        </label>
        <span class="text-sm text-slate-600"><span id="bulkCount">0</span> selected</span>
        <select id="bulkStatus" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">Move to status&hellip;</option>
            @foreach ($statusOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <button type="button" id="btnBulkStatus"
                class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-dark">
            <i class="fas fa-exchange-alt"></i> Apply status
        </button>
        <button type="button" id="btnBulkArchive"
                class="inline-flex items-center gap-1.5 rounded-md border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100">
            <i class="fas fa-archive"></i> Archive selected
        </button>
    </div>
</div>

{{-- ============ Orders table ============ --}}
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="w-9 py-2">
                        <input type="checkbox" id="checkAllTop" aria-label="Select all">
                    </th>
                    <th class="py-2 pr-4">Order</th>
                    <th class="py-2 pr-4">Customer</th>
                    <th class="py-2 pr-4">Items</th>
                    <th class="py-2 pr-4">Amount</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Tracking</th>
                    <th class="py-2 pr-4">Placed</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="tbody" class="divide-y divide-slate-100">
                <tr><td colspan="9" class="py-6 text-center text-slate-400">Loading orders&hellip;</td></tr>
            </tbody>
        </table>
    </div>
    <div class="py-3 text-center text-xs text-slate-400" id="scrollHint">Scroll for more&hellip;</div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var SM          = @json($statusMap);
    var DATA_URL    = '{{ route('admin.orders.data') }}';
    var SHOW_URL    = '{{ route('admin.orders.show', ['id' => ':ID']) }}';
    var BULK_STATUS = '{{ route('admin.orders.bulk-status') }}';
    var BULK_ARCH   = '{{ route('admin.orders.bulk-archive') }}';
    var CSRF        = document.querySelector('meta[name=csrf-token]').content;

    var state = { q: '', status: '', archived: '', from: '', to: '', sort: 'newest' };

    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    // Bootstrap badge class (from config/admin_orders.php, via statusMap) → Tailwind pill
    // classes — mirrors the $badgeClasses map in this file's PHP block above.
    function badgeClasses(bootstrap) {
        var map = {
            'bg-secondary': 'bg-slate-100 text-slate-700',
            'bg-primary':   'bg-blue-100 text-blue-700',
            'bg-info':      'bg-cyan-100 text-cyan-700',
            'bg-teal':      'bg-teal-100 text-teal-700',
            'bg-success':   'bg-green-100 text-green-700',
            'bg-danger':    'bg-red-100 text-red-700',
            'bg-indigo':    'bg-indigo-100 text-indigo-700',
            'bg-orange':    'bg-orange-100 text-orange-700',
            'bg-purple':    'bg-purple-100 text-purple-700',
            'bg-warning':   'bg-yellow-100 text-yellow-800'
        };
        return map[bootstrap] || map['bg-secondary'];
    }
    function meta(status) {
        var k = String(status == null ? '' : status);
        return SM[k] || { label: k || 'Request Placed', badge: 'bg-secondary' };
    }
    function badge(status) {
        var m = meta(status);
        return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + badgeClasses(m.badge) + '">' + esc(m.label) + '</span>';
    }
    function fmtDate(v) {
        if (!v) return '&mdash;';
        return window.moment ? moment(v).format('DD MMM YYYY, HH:mm') : String(v).replace('T', ' ').slice(0, 16);
    }
    function money(v) {
        var n = parseFloat(v);
        return isNaN(n) ? ('$ ' + esc(v)) : ('$ ' + n.toFixed(2));
    }

    function render(v) {
        var show = SHOW_URL.replace(':ID', v.id);
        var archBtn = v.archived_at
            ? '<button type="button" class="rounded-md border border-green-200 px-2 py-1 text-xs font-medium text-green-600 hover:bg-green-50" onclick="dpToggleArchive(' + v.id + ')" title="Restore from archive"><i class="fas fa-box-open"></i></button>'
            : '<button type="button" class="rounded-md border border-amber-200 px-2 py-1 text-xs font-medium text-amber-600 hover:bg-amber-50" onclick="dpToggleArchive(' + v.id + ')" title="Archive order"><i class="fas fa-archive"></i></button>';
        return '<tr>' +
            '<td data-label="Select" class="py-2 pr-4"><input type="checkbox" class="dp-row-check" value="' + v.id + '"></td>' +
            '<td data-label="Order" class="py-2 pr-4"><a href="' + show + '" class="text-brand hover:underline"><strong>#' + esc(v.order_id) + '</strong></a><br><small class="text-slate-500">id ' + v.id + (v.archived_at ? ' &middot; <span class="text-amber-600">archived</span>' : '') + '</small></td>' +
            '<td data-label="Customer" class="py-2 pr-4">' + esc(v.user_name) + '<br><small class="text-slate-500">' + esc(v.user_email) + '</small></td>' +
            '<td data-label="Items" class="py-2 pr-4">' + parseInt(v.items_count || 0, 10) + '</td>' +
            '<td data-label="Amount" class="py-2 pr-4">' + money(v.total) + '</td>' +
            '<td data-label="Status" class="py-2 pr-4">' + badge(v.order_status) + '</td>' +
            '<td data-label="Tracking" class="py-2 pr-4">' + (v.trackingid ? '<small>' + esc(v.trackingid) + '</small>' : '&mdash;') +
                (v.companyname ? '<br><small class="text-slate-500">' + esc(v.companyname) + '</small>' : '') + '</td>' +
            '<td data-label="Placed" class="py-2 pr-4">' + fmtDate(v.created_at) + '</td>' +
            '<td data-label="Actions" class="py-2 pr-4 text-right whitespace-nowrap">' +
                '<a href="' + show + '" class="mr-1 rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" title="View"><i class="fas fa-eye"></i></a>' +
                archBtn +
            '</td>' +
        '</tr>';
    }

    function buildUrl(page) {
        var p = new URLSearchParams();
        p.set('page', page || 1);
        if (state.q)        p.set('q', state.q);
        if (state.status)   p.set('status', state.status);
        if (state.archived) p.set('archived', state.archived);
        if (state.sort)     p.set('sort', state.sort);
        if (state.from)     p.set('from', state.from);
        if (state.to)       p.set('to', state.to);
        return DATA_URL + '?' + p.toString();
    }

    function refresh() {
        updateChips();
        document.getElementById('bulkBar').classList.add('hidden');
        window.__dpOrdersList = DP.infiniteScroll({ url: buildUrl(1), target: '#tbody', render: render });
        fetch(buildUrl(1), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                document.getElementById('kpiTotal').textContent = (json.total || 0).toLocaleString();
                document.getElementById('dpListMeta').textContent =
                    (json.total || 0).toLocaleString() + ' order(s) match — page 1 of ' + (json.last_page || 1);
            })
            .catch(function () {});
    }

    function updateChips() {
        document.querySelectorAll('.dp-status-chip').forEach(function (chip) {
            chip.classList.toggle('ring-2', chip.dataset.status === state.status);
            chip.classList.toggle('ring-brand', chip.dataset.status === state.status);
        });
    }

    // --- toolbar bindings
    var qTimer = null;
    document.getElementById('fQ').addEventListener('input', function () {
        clearTimeout(qTimer);
        qTimer = setTimeout(function () { state.q = document.getElementById('fQ').value.trim(); refresh(); }, 350);
    });
    ['fStatus', 'fArchived', 'fSort'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () {
            state.status   = document.getElementById('fStatus').value;
            state.archived = document.getElementById('fArchived').value;
            state.sort     = document.getElementById('fSort').value;
            refresh();
        });
    });
    document.querySelectorAll('.dp-status-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var sel = document.getElementById('fStatus');
            state.status = (state.status === chip.dataset.status) ? '' : chip.dataset.status;
            sel.value = state.status;
            refresh();
        });
    });
    document.getElementById('btnReset').addEventListener('click', function () {
        state = { q: '', status: '', archived: '', from: '', to: '', sort: 'newest' };
        document.getElementById('fQ').value = '';
        document.getElementById('fStatus').value = '';
        document.getElementById('fArchived').value = '';
        document.getElementById('fSort').value = 'newest';
        if (window.jQuery) { jQuery('#fRange').val(''); }
        refresh();
    });

    // --- date range
    if (window.jQuery && jQuery().daterangepicker) {
        jQuery('#fRange').daterangepicker({
            autoUpdateInput: false,
            opens: 'left',
            locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' },
            ranges: {
                'Today': [moment(), moment()],
                'Last 7 days': [moment().subtract(6, 'days'), moment()],
                'Last 30 days': [moment().subtract(29, 'days'), moment()],
                'This month': [moment().startOf('month'), moment().endOf('month')],
                'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
        jQuery('#fRange').on('apply.daterangepicker', function (ev, picker) {
            jQuery(this).val(picker.startDate.format('YYYY-MM-DD') + ' — ' + picker.endDate.format('YYYY-MM-DD'));
            state.from = picker.startDate.format('YYYY-MM-DD');
            state.to = picker.endDate.format('YYYY-MM-DD');
            refresh();
        });
        jQuery('#fRange').on('cancel.daterangepicker', function (ev, picker) {
            jQuery(this).val('');
            state.from = ''; state.to = '';
            refresh();
        });
    }

    // --- selection helpers
    function selectedIds() {
        return Array.prototype.slice.call(document.querySelectorAll('.dp-row-check:checked')).map(function (c) { return parseInt(c.value, 10); });
    }
    function syncBulkBar() {
        var n = selectedIds().length;
        document.getElementById('bulkCount').textContent = n;
        document.getElementById('bulkBar').classList.toggle('hidden', n === 0);
    }
    document.getElementById('tbody').addEventListener('change', syncBulkBar);
    function bindCheckAll(el) {
        if (!el) return;
        el.addEventListener('change', function () {
            var checked = el.checked;
            document.querySelectorAll('.dp-row-check').forEach(function (c) { c.checked = checked; });
            syncBulkBar();
        });
    }
    bindCheckAll(document.getElementById('checkAll'));
    bindCheckAll(document.getElementById('checkAllTop'));

    function post(url, body, okMsg) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok && j.ok, j: j }; }); })
          .then(function (res) {
              if (DP.toast) { res.ok ? DP.toast.success(res.j.message || okMsg) : DP.toast.error(res.j.message || 'Action failed.'); }
              return res.ok;
          })
          .catch(function () { DP.toast && DP.toast.error('Network error — please retry.'); return false; });
    }

    document.getElementById('btnBulkStatus').addEventListener('click', function () {
        var status = document.getElementById('bulkStatus').value, ids = selectedIds();
        if (!status || !ids.length) { DP.toast && DP.toast.info('Select orders and a target status first.'); return; }
        post(BULK_STATUS, { ids: ids, status: status }).then(function (ok) { ok && refresh(); });
    });
    document.getElementById('btnBulkArchive').addEventListener('click', function () {
        var ids = selectedIds();
        if (!ids.length) { DP.toast && DP.toast.info('Select at least one order.'); return; }
        if (window.Swal) {
            Swal.fire({ title: 'Archive orders?', text: ids.length + ' order(s) will be archived. Completed / Offer Rejected only.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Archive' })
                .then(function (r) { if (r.isConfirmed) post(BULK_ARCH, { ids: ids }).then(function (ok) { ok && refresh(); }); });
        } else {
            post(BULK_ARCH, { ids: ids }).then(function (ok) { ok && refresh(); });
        }
    });

    // --- single row archive toggle (called from rendered rows)
    window.dpToggleArchive = function (id) {
        var url = '{{ route('admin.orders.archive', ['id' => ':ID']) }}'.replace(':ID', id);
        fetch(url, { method: 'PUT', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok && j.ok, j: j }; }); })
            .then(function (res) {
                DP.toast && (res.ok ? DP.toast.success(res.j.message) : DP.toast.error(res.j.message));
                refresh();
            })
            .catch(function () { DP.toast && DP.toast.error('Network error — please retry.'); });
    };

    refresh();
})();
</script>
@endpush
