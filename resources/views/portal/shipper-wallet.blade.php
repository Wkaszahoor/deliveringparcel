@extends('layouts.portal')

@section('title', 'My Wallet')
@section('page_title', 'My Wallet')
@section('page_subtitle', 'Earnings, holds and payout history')

@section('content')
<div class="dp-kpi-grid mb-3">
    <div class="dp-kpi"><span class="dp-kpi-icon text-success"><i class="fas fa-coins"></i></span><div><div class="dp-kpi-value">{{ number_format($balance, 2) }}</div><div class="dp-kpi-label">Available Balance</div></div></div>
    <div class="dp-kpi"><span class="dp-kpi-icon text-warning"><i class="fas fa-hourglass-half"></i></span><div><div class="dp-kpi-value">{{ number_format($held, 2) }}</div><div class="dp-kpi-label">On Hold (7 days)</div></div></div>
    <div class="dp-kpi"><span class="dp-kpi-icon text-primary"><i class="fas fa-arrow-down"></i></span><div><div class="dp-kpi-value">{{ number_format($kpis['paid_out'], 2) }}</div><div class="dp-kpi-label">Paid Out</div></div></div>
</div>

<div class="card dp-card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:14px;">
            <thead class="thead-light"><tr>
                <th>Date</th><th>Type</th><th>Amount</th><th>Detail</th>
            </tr></thead>
            <tbody id="tbody">
                <tr class="dp-loading-row"><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Loading transactions…</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('portal_scripts')
<script>
(function () {
    var dataUrl = @json(route('shipper.wallet.data'));
    function esc(v) { return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
    var phRow = document.querySelector('#tbody tr.dp-loading-row');
    function render(r) {
        if (phRow && phRow.parentNode) { phRow.parentNode.removeChild(phRow); phRow = null; }
        var badge = r.type === 'credit' ? 'success' : (r.type === 'debit' || r.type === 'payout' ? 'danger' : 'warning');
        var h = '<tr>';
        h += '<td><small>' + esc(r.created) + '</small></td>';
        h += '<td><span class="badge badge-' + badge + '">' + esc(r.type_label) + '</span></td>';
        h += '<td><strong>' + esc(r.amount) + '</strong></td>';
        h += '<td class="text-muted">' + esc(r.detail) + '</td></tr>';
        return h;
    }
    document.addEventListener('DOMContentLoaded', function () {
        DP.infiniteScroll({ url: dataUrl, target: '#tbody', render: render });
    });
})();
</script>
@endpush
