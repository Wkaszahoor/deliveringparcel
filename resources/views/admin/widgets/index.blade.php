@extends('admin.layouts.app')
@section('title', 'Widgets')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Theme widgets</h3>
        <a href="{{ route('admin.widgets.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> New widget</a>
    </div>
    <div class="card-body">
        <div id="dp-widgets-table" data-url="{{ route('admin.widgets.data') }}">
            <table class="table table-hover dp-card-mobile">
                <thead><tr><th>Title</th><th>Type</th><th>Area</th><th>Scope</th><th>Sort</th><th>Status</th><th style="width:110px"></th></tr></thead>
                <tbody><tr><td colspan="7" class="text-muted">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('dp-widgets-table');
    var url  = wrap.dataset.url;
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
    function render(rows) {
        var tb = wrap.querySelector('tbody');
        tb.innerHTML = rows.length ? rows.map(function (r) {
            var acts = [
                '<a href="' + r.urls.edit + '" class="btn btn-xs btn-info" title="Edit"><i class="fas fa-pen"></i></a>',
                '<form method="POST" action="' + r.urls.toggle + '" style="display:inline">' + window.DP_CSRF +
                    '<button class="btn btn-xs btn-warning" title="Toggle"><i class="fas fa-power-off"></i></button></form>',
                '<form method="POST" action="' + r.urls.destroy + '" style="display:inline" onsubmit="return confirm(\'Delete widget?\')">' + window.DP_CSRF +
                    '<button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button></form>'
            ].join(' ');
            return '<tr>' + r.cols.map(function (c) { return '<td>' + c + '</td>'; }).concat(['<td class="text-right">' + acts + '</td>']).join('') + '</tr>';
        }).join('') : '<tr><td colspan="7" class="text-muted">No widgets yet.</td></tr>';
    }
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (d) { render(d.rows || []); })
        .catch(function () { wrap.querySelector('tbody').innerHTML = '<tr><td colspan="7" class="text-danger">Failed to load.</td></tr>'; });
})();
</script>
@endpush
