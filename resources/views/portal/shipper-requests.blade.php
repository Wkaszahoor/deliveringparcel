@extends('layouts.portal')

@section('title', 'Marketplace')
@section('page_title', 'Shipping Marketplace')
@section('page_subtitle', 'Open requests in your service countries — quote to win the job')

@section('content')
<div class="card dp-card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row" style="gap:8px; align-items:flex-end;">
            <div class="col-md col-sm-6 col-12">
                <label class="dp-label mb-1">Search</label>
                <input id="fQ" class="form-control form-control-sm" placeholder="Reference…">
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <label class="dp-label mb-1">Service</label>
                <select id="fType" class="form-control form-control-sm">
                    <option value="">All services</option>
                    <option value="buy_for_me">Buy for Me</option>
                    <option value="ship_for_me">Ship for Me</option>
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
                <th>Reference</th><th>Service</th><th>Country</th><th>Level</th><th>Quotes</th><th>Expires</th><th></th>
            </tr></thead>
            <tbody id="tbody">
                <tr class="dp-loading-row"><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Loading requests…</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('portal_scripts')
<script>
(function () {
    var dataUrl = @json(route('shipper.requests.data'));
    function esc(v) { return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
    var phRow = document.querySelector('#tbody tr.dp-loading-row');
    function render(r) {
        if (phRow && phRow.parentNode) { phRow.parentNode.removeChild(phRow); phRow = null; }
        var typeBadge = r.service_type === 'buy_for_me' ? 'info' : 'primary';
        var h = '<tr>';
        h += '<td><strong>' + esc(r.reference) + '</strong></td>';
        h += '<td><span class="badge badge-' + typeBadge + '">' + esc(r.service_label) + '</span></td>';
        h += '<td>' + esc(r.country) + '</td>';
        h += '<td>L' + esc(r.level) + '</td>';
        h += '<td>' + esc(r.quotes_count) + '</td>';
        h += '<td><small>' + esc(r.expires) + '</small></td>';
        h += '<td class="text-right"><a class="btn btn-sm btn-outline-primary" href="' + esc(r.view_url) + '"><i class="fas fa-eye"></i> View / Quote</a></td></tr>';
        return h;
    }
    function filters() {
        return { q: document.getElementById('fQ').value, service_type: document.getElementById('fType').value };
    }
    var scroller = null;
    function loadList() {
        var url = dataUrl + '?' + new URLSearchParams(filters()).toString();
        if (!scroller) { scroller = DP.infiniteScroll({ url: url, target: '#tbody', render: render }); }
        else { scroller.reload(url); }
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('fApply').addEventListener('click', loadList);
        document.getElementById('fReset').addEventListener('click', function () {
            document.getElementById('fQ').value = ''; document.getElementById('fType').value = ''; loadList();
        });
        document.getElementById('fQ').addEventListener('keydown', function (e) { if (e.key === 'Enter') loadList(); });
        loadList();
    });
})();
</script>
@endpush
