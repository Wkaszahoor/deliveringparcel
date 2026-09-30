@extends('layouts.tailwind.app')

@section('title', 'Payments')
@section('page_title', 'Payments')
@section('page_subtitle', 'Collected, pending and refunded payments across orders')

@push('admin_styles')
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fas fa-dollar-sign text-green-500"></i>
                <div>
                    <div class="text-lg font-semibold text-slate-900">{{ $currency }} {{ number_format($kpis['collected_month'], 2) }}</div>
                    <div class="text-xs text-slate-500">{{ config('admin_payments.statuses.paid.kpi_label', 'Collected this month') }}</div>
                </div>
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fas fa-hourglass-half text-yellow-500"></i>
                <div>
                    <div class="text-lg font-semibold text-slate-900">{{ number_format($kpis['pending_count']) }}</div>
                    <div class="text-xs text-slate-500">{{ config('admin_payments.statuses.pending.kpi_label', 'Pending payments') }} — {{ $currency }} {{ number_format($kpis['pending_total'], 2) }}</div>
                </div>
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fas fa-undo text-blue-500"></i>
                <div>
                    <div class="text-lg font-semibold text-slate-900">{{ number_format($kpis['refunded_count']) }}</div>
                    <div class="text-xs text-slate-500">{{ config('admin_payments.statuses.refunded.kpi_label', 'Refunded') }} — {{ $currency }} {{ number_format($kpis['refunded_total'], 2) }}</div>
                </div>
            </div>
        </x-admin.card>
    </div>

    <x-admin.card class="mb-4">
        <form class="flex flex-wrap items-end gap-2" onsubmit="return false;">
            <div>
                <label for="dpFilterQ" class="mb-1 block text-xs font-medium text-slate-600">Search</label>
                <input type="text" id="dpFilterQ" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Order ref, customer name or email...">
            </div>
            <div>
                <label for="dpFilterStatus" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select id="dpFilterStatus" class="dp-select2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $status)
                        <option value="{{ $key }}">{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dpFilterRange" class="mb-1 block text-xs font-medium text-slate-600">Date range</label>
                <input type="text" id="dpFilterRange" readonly placeholder="From — To" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <input type="hidden" id="dpDateFrom">
                <input type="hidden" id="dpDateTo">
            </div>
            <div class="ml-auto flex items-center gap-2">
                <x-admin.button type="button" id="dpApplyFilters"><i class="fas fa-filter"></i> Apply</x-admin.button>
                <x-admin.button type="button" variant="secondary" id="dpResetFilters"><i class="fas fa-undo"></i></x-admin.button>
                <a href="{{ route('admin.payments.config.index') }}" title="Payment methods &amp; service rules"
                    class="inline-flex items-center gap-1.5 rounded-md border border-brand px-3 py-2 text-sm font-medium text-brand hover:bg-brand-light">
                    <i class="fas fa-sliders-h"></i>
                </a>
            </div>
        </form>
    </x-admin.card>

    @unless ($refundAvailable)
        <x-admin.alert type="info">
            Refunds are currently <strong>disabled</strong>: they require a Stripe charge reference column on <code>orders</code>,
            the <code>services.stripe.secret</code> env value and the <em>Stripe payments</em> toggle in
            <a href="{{ route('admin.settings.edit') }}#tab-api" class="underline">Settings &rarr; API Integrations</a>.
        </x-admin.alert>
    @endunless

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Order</th>
                        <th class="py-2 pr-4">Customer</th>
                        <th class="py-2 pr-4">Amount</th>
                        <th class="py-2 pr-4">Method</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date</th>
                        <th class="py-2 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody" class="divide-y divide-slate-100">
                    <tr><td colspan="7" class="py-6 text-center text-slate-400"><i class="fas fa-spinner fa-spin mr-1"></i> Loading payments...</td></tr>
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

{{-- window.DP (infinite scroll + confirm-dialog helpers), toastr and SweetAlert2 are only
     loaded by the old AdminLTE layouts; this page relies on DP.infiniteScroll for its table and
     DP.bindConfirms (backs the data-dp-confirm refund buttons via Swal), so all three must be
     pulled in explicitly here. Pushed via @push('admin_scripts') so they load AFTER the layout's
     jQuery <script> tag (layout yields content, then jQuery, then @stack('admin_scripts')). --}}
@push('admin_scripts')
    <script src="{{ asset('dashbord/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('dashbord/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
    <script>
        (function () {
            var dataUrl = @json(route('admin.payments.data'));
            var refundAvailable = {{ $refundAvailable ? 'true' : 'false' }};

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

            function render(row) {
                var html = '<tr data-id="' + esc(row.id) + '">';
                html += '<td class="py-2 pr-4" data-label="Order"><strong>' + esc(row.order_ref) + '</strong></td>';
                html += '<td class="py-2 pr-4" data-label="Customer">' + esc(row.customer) + '</td>';
                html += '<td class="py-2 pr-4" data-label="Amount"><strong>' + esc(row.currency) + ' ' + esc(row.amount) + '</strong></td>';
                html += '<td class="py-2 pr-4" data-label="Method"><span class="text-xs text-slate-500">' + esc(row.method) + '</span></td>';
                html += '<td class="py-2 pr-4" data-label="Status"><span class="' + badgeClasses(row.status_color) + '">' + esc(row.status_label) + '</span></td>';
                html += '<td class="py-2 pr-4" data-label="Date"><span class="text-xs text-slate-500">' + esc(row.created_at) + '</span></td>';
                html += '<td class="py-2 pr-4 text-right" data-label="Actions">';
                if (row.refundable) {
                    html += '<button type="button" class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50" data-dp-confirm data-url="' + esc(row.refund_url) + '" data-method="PUT" data-title="Refund ' + esc(row.currency) + ' ' + esc(row.amount) + ' for order ' + esc(row.order_ref) + '?">';
                    html += '<i class="fas fa-undo mr-1"></i>Refund</button>';
                } else if (refundAvailable && row.status === 'paid') {
                    html += '<span class="text-xs text-slate-400">&mdash;</span>';
                } else if (row.status === 'paid') {
                    html += '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700" title="Refunds are not enabled">Refunds off</span>';
                } else {
                    html += '<span class="text-xs text-slate-400">&mdash;</span>';
                }
                html += '</td></tr>';
                return html;
            }

            function filters() {
                return {
                    q: document.getElementById('dpFilterQ').value,
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

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && jQuery().select2) {
                    jQuery('.dp-select2').select2({ width: '100%', minimumResultsForSearch: 6 });
                }
                if (window.jQuery && jQuery().daterangepicker) {
                    jQuery('#dpFilterRange').daterangepicker({
                        autoUpdateInput: false,
                        locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' },
                        ranges: {
                            'This month': [moment().startOf('month'), moment().endOf('month')],
                            'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                            'Last 30 days': [moment().subtract(29, 'days'), moment()]
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
                    document.getElementById('dpFilterStatus').value = '';
                    document.getElementById('dpDateFrom').value = '';
                    document.getElementById('dpDateTo').value = '';
                    document.getElementById('dpFilterRange').value = '';
                    if (window.jQuery) { jQuery('.dp-select2').trigger('change'); }
                    loadList();
                });

                if (window.DP && typeof DP.bindConfirms === 'function') { DP.bindConfirms(); }

                loadList();
            });
        })();
    </script>
@endpush
