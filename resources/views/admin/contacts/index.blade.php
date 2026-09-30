@extends('layouts.tailwind.app')

@section('title', 'Messages')
@section('page_title', 'Contact Messages')
@section('page_subtitle', 'Classify, read and reply to customer messages')

@push('admin_styles')
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @php
            $kpiTiles = [
                ['Unread (new)', $kpis['unread'], 'fa-envelope', 'text-red-500'],
                ['Replied', $kpis['replied'], 'fa-reply', 'text-green-500'],
                ['Read', $kpis['read'], 'fa-envelope-open', 'text-blue-500'],
                ['Archived', $kpis['archived'], 'fa-archive', 'text-slate-500'],
                ['Total messages', $kpis['total'], 'fa-inbox', 'text-brand'],
            ];
        @endphp
        @foreach ($kpiTiles as $tile)
            <x-admin.card>
                <div class="flex items-center gap-2">
                    <i class="fas {{ $tile[2] }} {{ $tile[3] }}"></i>
                    <div>
                        <div class="text-lg font-semibold text-slate-900">{{ number_format($tile[1]) }}</div>
                        <div class="text-xs text-slate-500">{{ $tile[0] }}</div>
                    </div>
                </div>
            </x-admin.card>
        @endforeach
    </div>

    <x-admin.card class="mb-4">
        <form id="dpContactFilters" class="flex flex-wrap items-end gap-2" onsubmit="return false;">
            <div>
                <label for="dpFilterQ" class="mb-1 block text-xs font-medium text-slate-600">Search</label>
                <input type="text" id="dpFilterQ" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Name, email or message...">
            </div>
            <div>
                <label for="dpFilterCategory" class="mb-1 block text-xs font-medium text-slate-600">Category</label>
                <select id="dpFilterCategory" class="dp-select2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dpFilterStatus" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select id="dpFilterStatus" class="dp-select2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dpFilterRange" class="mb-1 block text-xs font-medium text-slate-600">Date range</label>
                <input type="text" id="dpFilterRange" readonly placeholder="From — To" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <input type="hidden" id="dpDateFrom">
                <input type="hidden" id="dpDateTo">
            </div>
            <div class="ml-auto flex gap-2">
                <x-admin.button type="button" id="dpApplyFilters"><i class="fas fa-filter"></i> Apply</x-admin.button>
                <x-admin.button type="button" variant="secondary" id="dpResetFilters"><i class="fas fa-undo"></i> Reset</x-admin.button>
            </div>
        </form>
    </x-admin.card>

    <div class="mb-3 flex flex-wrap items-center gap-3 rounded-md bg-slate-50 px-3 py-2">
        <label class="flex items-center gap-1.5 text-sm text-slate-600">
            <input type="checkbox" id="dpCheckAll"> Select all
        </label>
        <span class="text-sm text-slate-500"><span id="dpSelectedCount">0</span> selected</span>
        <button type="button" class="rounded-md border border-blue-200 px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" data-dp-bulk="mark_read"><i class="fas fa-envelope-open"></i> Mark as read</button>
        <button type="button" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100" data-dp-bulk="archive"><i class="fas fa-archive"></i> Archive</button>
        <a href="{{ route('admin.contacts.templates.index') }}" class="ml-auto rounded-md border border-brand px-2.5 py-1 text-xs font-medium text-brand hover:bg-brand-light"><i class="fas fa-file-alt"></i> Reply templates</a>
    </div>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="w-9 py-2"></th>
                        <th class="py-2 pr-4">Customer</th>
                        <th class="py-2 pr-4">Message</th>
                        <th class="py-2 pr-4">Category</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Received</th>
                        <th class="py-2 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody" class="divide-y divide-slate-100">
                    <tr class="dp-loading-row"><td colspan="7" class="py-6 text-center text-slate-400"><i class="fas fa-spinner fa-spin"></i> Loading messages...</td></tr>
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

@push('admin_scripts')
    <script src="{{ asset('dashbord/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('dashbord/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.js') }}"></script>
    {{-- window.DP (infinite scroll + toast helpers) is only loaded by the old AdminLTE layouts;
         this page relies on DP.infiniteScroll for its table and DP.toast for feedback, so both
         toastr and dp-lazy.js must be pulled in explicitly here. --}}
    <script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
    <script>
        (function () {
            var dataUrl = @json(route('admin.contacts.data'));
            var bulkUrl = @json(route('admin.contacts.bulk'));
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function esc(v) {
                return String(v == null ? '' : v)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function badgeClasses(bootstrapColor) {
                var map = {
                    secondary: 'bg-slate-100 text-slate-700',
                    success: 'bg-green-100 text-green-700',
                    info: 'bg-blue-100 text-blue-700',
                    danger: 'bg-red-100 text-red-700',
                    warning: 'bg-yellow-100 text-yellow-800',
                    primary: 'bg-blue-100 text-blue-700',
                };
                return 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + (map[bootstrapColor] || map.secondary);
            }

            var phRow = document.querySelector('#tbody tr.dp-loading-row');
            function render(row) {
                if (phRow && phRow.parentNode) { phRow.parentNode.removeChild(phRow); phRow = null; }
                var html = '<tr data-id="' + esc(row.id) + '">';
                html += '<td data-label=""><input type="checkbox" class="dp-row-check" value="' + esc(row.id) + '"></td>';
                html += '<td data-label="Customer"><strong>' + esc(row.name) + '</strong><br><small class="text-slate-500">' + esc(row.email) + '</small></td>';
                html += '<td data-label="Message">' + esc(row.detail_short || '') + '</td>';
                html += '<td data-label="Category"><span class="' + badgeClasses(row.category_color) + '">' + esc(row.category_label) + '</span></td>';
                html += '<td data-label="Status"><span class="' + badgeClasses(row.status_color) + '">' + esc(row.status_label) + '</span>' + (row.replied ? ' <i class="fas fa-reply text-green-600" title="Has reply"></i>' : '') + '</td>';
                html += '<td data-label="Received"><small>' + esc(row.created_at) + '</small></td>';
                html += '<td data-label="Actions" class="text-right"><a href="' + esc(row.show_url) + '" class="rounded-md border border-brand px-2 py-1 text-xs font-medium text-brand hover:bg-brand-light"><i class="fas fa-eye"></i> View</a></td>';
                html += '</tr>';
                return html;
            }

            function filters() {
                return {
                    q: document.getElementById('dpFilterQ').value,
                    category: document.getElementById('dpFilterCategory').value,
                    status: document.getElementById('dpFilterStatus').value,
                    date_from: document.getElementById('dpDateFrom').value,
                    date_to: document.getElementById('dpDateTo').value
                };
            }

            function loadList() {
                if (typeof DP === 'undefined' || typeof DP.infiniteScroll !== 'function') { return; }
                var url = dataUrl + '?' + new URLSearchParams(filters()).toString();
                DP.infiniteScroll({ url: url, target: '#tbody', render: render });
            }

            // init
            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && jQuery().select2) {
                    jQuery('.dp-select2').select2({ width: '100%', minimumResultsForSearch: 6 });
                }
                if (window.jQuery && jQuery().daterangepicker) {
                    jQuery('#dpFilterRange').daterangepicker({
                        autoUpdateInput: false,
                        locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' },
                        ranges: {
                            'Today': [moment(), moment()],
                            'Last 7 days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 days': [moment().subtract(29, 'days'), moment()],
                            'This month': [moment().startOf('month'), moment().endOf('month')]
                        }
                    });
                    jQuery('#dpFilterRange').on('apply.daterangepicker', function (ev, picker) {
                        jQuery(this).val(picker.startDate.format('YYYY-MM-DD') + ' — ' + picker.endDate.format('YYYY-MM-DD'));
                        jQuery('#dpDateFrom').val(picker.startDate.format('YYYY-MM-DD'));
                        jQuery('#dpDateTo').val(picker.endDate.format('YYYY-MM-DD'));
                        loadList();
                    });
                    jQuery('#dpFilterRange').on('cancel.daterangepicker', function () {
                        jQuery(this).val('');
                        jQuery('#dpDateFrom').val('');
                        jQuery('#dpDateTo').val('');
                        loadList();
                    });
                }

                document.getElementById('dpApplyFilters').addEventListener('click', loadList);
                document.getElementById('dpFilterQ').addEventListener('keyup', function (e) {
                    if (e.key === 'Enter') { loadList(); }
                });
                document.getElementById('dpResetFilters').addEventListener('click', function () {
                    document.getElementById('dpFilterQ').value = '';
                    document.getElementById('dpFilterCategory').value = '';
                    document.getElementById('dpFilterStatus').value = '';
                    document.getElementById('dpDateFrom').value = '';
                    document.getElementById('dpDateTo').value = '';
                    document.getElementById('dpFilterRange').value = '';
                    if (window.jQuery && jQuery('.dp-select2').val) { jQuery('.dp-select2').trigger('change'); }
                    loadList();
                });

                // bulk selection + actions
                document.getElementById('tbody').addEventListener('change', function (e) {
                    if (e.target.classList.contains('dp-row-check')) { updateCount(); }
                });
                document.getElementById('dpCheckAll').addEventListener('change', function () {
                    var checked = this.checked;
                    document.querySelectorAll('#tbody .dp-row-check').forEach(function (cb) { cb.checked = checked; });
                    updateCount();
                });

                function updateCount() {
                    var n = document.querySelectorAll('#tbody .dp-row-check:checked').length;
                    document.getElementById('dpSelectedCount').textContent = n;
                }

                document.querySelectorAll('[data-dp-bulk]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var ids = Array.prototype.map.call(
                            document.querySelectorAll('#tbody .dp-row-check:checked'),
                            function (cb) { return parseInt(cb.value, 10); }
                        );
                        if (!ids.length) {
                            if (window.DP && DP.toast) { DP.toast.info('Select at least one message first.'); } else { alert('Select at least one message first.'); }
                            return;
                        }
                        var action = btn.getAttribute('data-dp-bulk');
                        fetch(bulkUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: action, ids: ids })
                        }).then(function (res) { return res.json(); }).then(function (json) {
                            if (window.DP && DP.toast) { DP.toast.success(json.updated + ' message(s) updated.'); }
                            document.getElementById('dpCheckAll').checked = false;
                            loadList();
                            setTimeout(function () { window.location.reload(); }, 800);
                        }).catch(function () {
                            if (window.DP && DP.toast) { DP.toast.error('Bulk action failed.'); }
                        });
                    });
                });

                loadList();
            });
        })();
    </script>
@endpush
