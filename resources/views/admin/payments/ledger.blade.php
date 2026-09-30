@extends('layouts.tailwind.app')

@section('title', 'Payments Ledger')
@section('page_title', 'Payments Ledger')
@section('page_subtitle', 'Authoritative payment ledger with verification and refund actions')

@push('admin_styles')
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
    <x-admin.alert type="info" class="mb-4">
        This is the <strong>authoritative payment ledger</strong>. Bank transfers require admin verification before being marked as paid.
    </x-admin.alert>

    <x-admin.card class="mb-4">
        <form class="flex flex-wrap items-end gap-2" onsubmit="return false;">
            <div>
                <label for="dpFilterQ" class="mb-1 block text-xs font-medium text-slate-600">Search</label>
                <input type="text" id="dpFilterQ" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Reference, order, customer...">
            </div>
            <div>
                <label for="dpFilterStatus" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select id="dpFilterStatus" class="dp-select2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All statuses</option>
                    @foreach (config('admin_payments_engine.states') as $key => $state)
                        <option value="{{ $key }}">{{ $state['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dpFilterMethod" class="mb-1 block text-xs font-medium text-slate-600">Method</label>
                <select id="dpFilterMethod" class="dp-select2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All methods</option>
                    <option value="stripe">Stripe</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <x-admin.button type="button" id="dpApplyFilters"><i class="fas fa-filter"></i> Apply</x-admin.button>
                <x-admin.button type="button" variant="secondary" id="dpResetFilters"><i class="fas fa-undo"></i></x-admin.button>
                <a href="{{ route('admin.payments.config.index') }}" title="Payment configuration"
                    class="inline-flex items-center gap-1.5 rounded-md border border-brand px-3 py-2 text-sm font-medium text-brand hover:bg-brand-light">
                    <i class="fas fa-sliders-h"></i>
                </a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Reference</th>
                        <th class="py-2 pr-4">Order</th>
                        <th class="py-2 pr-4">Customer</th>
                        <th class="py-2 pr-4">Method</th>
                        <th class="py-2 pr-4">Amount</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Proof</th>
                        <th class="py-2 pr-4">Date</th>
                        <th class="py-2 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody" class="divide-y divide-slate-100">
                    <tr><td colspan="9" class="py-6 text-center text-slate-400"><i class="fas fa-spinner fa-spin mr-1"></i> Loading ledger...</td></tr>
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

{{-- window.DP (infinite scroll + confirm-dialog helpers), toastr and SweetAlert2 are only
     loaded by the old AdminLTE layouts; this page relies on DP.infiniteScroll for its table and
     DP.bindConfirms (backs the data-dp-confirm verify/refund buttons via Swal), so all three must
     be pulled in explicitly here. Pushed via @push('admin_scripts') so they load AFTER the
     layout's jQuery <script> tag. --}}
@push('admin_scripts')
    <script src="{{ asset('dashbord/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('dashbord/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
    <script>
        (function () {
            var dataUrl = @json(route('admin.payments.ledger.data'));
            var states = @json(config('admin_payments_engine.states'));

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
                var state = states[row.status] || { label: row.status, color: 'secondary' };
                var html = '<tr data-id="' + esc(row.id) + '">';
                html += '<td class="py-2 pr-4" data-label="Reference"><strong>' + esc(row.reference) + '</strong></td>';
                html += '<td class="py-2 pr-4" data-label="Order">' + esc(row.order_ref) + '</td>';
                html += '<td class="py-2 pr-4" data-label="Customer">' + esc(row.customer) + '</td>';
                html += '<td class="py-2 pr-4" data-label="Method"><span class="text-xs text-slate-500">' + esc(row.method) + '</span></td>';
                html += '<td class="py-2 pr-4" data-label="Amount"><strong>' + esc(row.currency) + ' ' + esc(row.amount) + '</strong>' + (row.refunded ? '<div class="text-xs text-red-500">refunded ' + esc(row.currency) + ' ' + esc(row.refunded) + '</div>' : '') + '</td>';
                html += '<td class="py-2 pr-4" data-label="Status"><span class="' + badgeClasses(state.color) + '">' + esc(state.label) + '</span>' + (row.set_by ? '<div class="text-xs text-slate-400">by ' + esc(row.set_by) + '</div>' : '') + '</td>';

                html += '<td class="py-2 pr-4" data-label="Proof">';
                if (row.proof_url) {
                    html += '<a href="' + esc(row.proof_url) + '" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"><i class="fas fa-receipt"></i> View</a>';
                } else {
                    html += '<span class="text-xs text-slate-400">&mdash;</span>';
                }
                html += '</td>';

                html += '<td class="py-2 pr-4" data-label="Date"><span class="text-xs text-slate-500">' + esc(row.created_at) + '</span></td>';
                html += '<td class="py-2 pr-4 text-right" data-label="Actions">';

                if (row.can_verify) {
                    html += '<button type="button" class="mr-1 rounded-md border border-green-300 px-2.5 py-1 text-xs font-medium text-green-600 hover:bg-green-50" data-dp-confirm data-url="' + esc(row.urls.verify) + '" data-method="PUT" data-title="Verify and mark this bank transfer as paid?" data-params=\'{"approve": true}\'>';
                    html += '<i class="fas fa-check mr-1"></i>Approve</button>';
                    html += '<button type="button" class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50" data-dp-confirm data-url="' + esc(row.urls.verify) + '" data-method="PUT" data-title="Reject this payment?" data-params=\'{"approve": false}\'>';
                    html += '<i class="fas fa-times mr-1"></i>Reject</button>';
                } else if (row.can_refund) {
                    html += '<button type="button" class="mr-1 rounded-md border border-yellow-300 px-2.5 py-1 text-xs font-medium text-yellow-700 hover:bg-yellow-50" data-dp-confirm data-url="' + esc(row.urls.refund) + '" data-method="PUT" data-title="Refund ' + esc(row.currency) + ' ' + esc(row.amount) + ' via the ORIGINAL method (card/bank/wallet)?" data-params=\'{"destination":"original"}\'>';
                    html += '<i class="fas fa-undo mr-1"></i>Refund</button>';
                    html += '<button type="button" class="rounded-md border border-blue-300 px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" data-dp-confirm data-url="' + esc(row.urls.refund) + '" data-method="PUT" data-title="Refund ' + esc(row.currency) + ' ' + esc(row.amount) + ' INTO the customer wallet?" data-params=\'{"destination":"wallet"}\' title="Credit the customer wallet instead of the original rails">';
                    html += '<i class="fas fa-wallet mr-1"></i>To Wallet</button>';
                } else if (row.status === 'processing') {
                    html += '<span class="text-xs text-slate-400">Processing...</span>';
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
                    method: document.getElementById('dpFilterMethod').value
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

                document.getElementById('dpApplyFilters').addEventListener('click', loadList);
                document.getElementById('dpFilterQ').addEventListener('keyup', function (e) {
                    if (e.key === 'Enter') { loadList(); }
                });
                document.getElementById('dpResetFilters').addEventListener('click', function () {
                    document.getElementById('dpFilterQ').value = '';
                    document.getElementById('dpFilterStatus').value = '';
                    document.getElementById('dpFilterMethod').value = '';
                    if (window.jQuery) { jQuery('.dp-select2').trigger('change'); }
                    loadList();
                });

                if (window.DP && typeof DP.bindConfirms === 'function') { DP.bindConfirms(); }

                loadList();
            });
        })();
    </script>
@endpush
