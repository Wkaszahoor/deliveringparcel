@extends('layouts.portal')

@section('title', 'My Orders')
@section('page_title', 'My Orders')
@section('page_subtitle', 'Track every shipment in one place')

@section('content')
<div class="dp-kpi-grid mb-3">
    <div class="dp-kpi"><span class="dp-kpi-icon text-primary"><i class="fas fa-box"></i></span><div><div class="dp-kpi-value">{{ number_format($kpis['total']) }}</div><div class="dp-kpi-label">Total Orders</div></div></div>
    <div class="dp-kpi"><span class="dp-kpi-icon text-success"><i class="fas fa-check-circle"></i></span><div><div class="dp-kpi-value">{{ number_format($kpis['paid']) }}</div><div class="dp-kpi-label">Paid</div></div></div>
    <div class="dp-kpi"><span class="dp-kpi-icon text-warning"><i class="fas fa-hourglass-half"></i></span><div><div class="dp-kpi-value">{{ number_format($kpis['awaiting']) }}</div><div class="dp-kpi-label">Awaiting Payment</div></div></div>
</div>

<div class="card dp-card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row" style="gap:8px; align-items:flex-end;">
            <div class="col-md col-sm-6 col-12">
                <label class="dp-label mb-1">Search</label>
                <input id="fQ" class="form-control form-control-sm" placeholder="Order # or tracking…">
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <label class="dp-label mb-1">Status</label>
                <select id="fStatus" class="form-control form-control-sm">
                    <option value="">All statuses</option>
                    <option value="paid">Paid</option>
                    <option value="awaiting">Awaiting payment</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button id="fApply" class="btn btn-primary btn-sm"><i class="fas fa-filter mr-1"></i> Apply</button>
                <button id="fReset" class="btn btn-outline-secondary btn-sm">Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="card dp-card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:14px;">
            <thead class="thead-light"><tr>
                <th>Order</th><th>Ship To</th><th>Weight</th><th>Total</th><th>Placed</th><th>Status</th><th></th>
            </tr></thead>
            <tbody id="tbody">
                <tr class="dp-loading-row"><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Loading orders…</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('portal_scripts')
<script>
(function () {
    var dataUrl = @json(route('dashboard.data'));
    function esc(v) { return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
    var phRow = document.querySelector('#tbody tr.dp-loading-row');
    function render(r) {
        if (phRow && phRow.parentNode) { phRow.parentNode.removeChild(phRow); phRow = null; }
        var badge = r.status === 'Paid' ? 'success' : (r.status === 'Awaiting payment' ? 'warning' : 'secondary');
        var h = '<tr data-id="' + esc(r.id) + '">';
        h += '<td><strong>#' + esc(r.ref) + '</strong></td>';
        h += '<td>' + esc(r.ship_to) + '</td>';
        h += '<td>' + esc(r.weight) + '</td>';
        h += '<td>' + esc(r.total) + '</td>';
        h += '<td><small>' + esc(r.created) + '</small></td>';
        h += '<td><span class="badge badge-' + badge + '">' + esc(r.status) + '</span></td>';
        h += '<td class="text-right"><a class="btn btn-sm btn-outline-primary" href="' + esc(r.view_url) + '"><i class="fas fa-eye"></i> View</a></td></tr>';
        return h;
    }
    function filters() {
        return { q: document.getElementById('fQ').value, status: document.getElementById('fStatus').value };
    }
    var scroller = null;
    function loadList() {
        var url = dataUrl + '?' + new URLSearchParams(filters()).toString();
        if (!scroller) {
            scroller = DP.infiniteScroll({ url: url, target: '#tbody', render: render });
        } else { scroller.reload(url); }
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('fApply').addEventListener('click', loadList);
        document.getElementById('fReset').addEventListener('click', function () {
            document.getElementById('fQ').value = ''; document.getElementById('fStatus').value = ''; loadList();
        });
        document.getElementById('fQ').addEventListener('keydown', function (e) { if (e.key === 'Enter') loadList(); });
        loadList();
    });
})();
</script>
@endpush
