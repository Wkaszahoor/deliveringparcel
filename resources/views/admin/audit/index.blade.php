@extends('admin.layouts.app')
@section('title', 'Audit Log')
@section('page_title', 'Audit Log')
@section('page_subtitle', 'Before/after snapshots of admin model changes — sensitive fields masked')

@section('content')
<div class="dp-toolbar">
    <input id="f-q" class="form-control form-control-sm" placeholder="Search user / entity…" style="max-width:220px">
    <select id="f-action" class="custom-select custom-select-sm" style="max-width:140px">
        <option value="">Action: all</option>
        @foreach ($entityMap as $action => [$label, $color])<option value="{{ $action }}">{{ $label }}</option>@endforeach
    </select>
    <select id="f-type" class="custom-select custom-select-sm" style="max-width:180px">
        <option value="">Entity: all</option>
        @foreach ($types as $t)<option value="{{ $t }}">{{ class_basename($t) }}</option>@endforeach
    </select>
    <input type="date" id="f-from" class="form-control form-control-sm" style="max-width:150px" title="From">
    <input type="date" id="f-to" class="form-control form-control-sm" style="max-width:150px" title="To">
</div>

<div class="dp-table-wrap p-2 p-md-0">
    <table class="table table-hover dp-table dp-card-mobile mb-0">
        <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th><th class="text-right">Details</th></tr></thead>
        <tbody id="dp-tbody"></tbody>
    </table>
</div>

<div class="modal fade" id="dp-audit-modal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Change details</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="dp-audit-body"></div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
(function () {
    var base = @json(route('admin.audit.data'));
    var actionMap = @json($entityMap);

    function url() {
        var u = new URL(base);
        if (document.getElementById('f-q').value.trim()) u.searchParams.set('q', document.getElementById('f-q').value.trim());
        ['action', 'type', 'from', 'to'].forEach(function (k) {
            var v = document.getElementById('f-' + k).value;
            if (v) u.searchParams.set(k, v);
        });
        return u.toString();
    }

    var rows = {};
    var sc = DP.infiniteScroll({
        url: url(), target: '#dp-tbody',
        render: function (l) {
            rows[l.id] = l;
            var meta = actionMap[l.action] || [l.action, 'bg-secondary'];
            return '<tr>'
                + '<td data-label="When" class="text-muted small">' + DP.esc(l.at) + '</td>'
                + '<td data-label="User">' + DP.esc(l.user) + '</td>'
                + '<td data-label="Action"><span class="dp-badge ' + meta[1] + ' text-white">' + DP.esc(meta[0]) + '</span></td>'
                + '<td data-label="Entity"><strong>' + DP.esc(l.entity) + '</strong></td>'
                + '<td data-label="IP" class="small">' + DP.esc(l.ip || '—') + '</td>'
                + '<td data-label="Details" class="dp-actions text-right"><button class="btn btn-sm btn-outline-info dp-detail" data-id="' + l.id + '"><i class="fas fa-eye"></i></button></td>'
                + '</tr>';
        }
    });

    document.getElementById('dp-tbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.dp-detail');
        if (!btn) return;
        var l = rows[btn.dataset.id];
        var html = '<div class="row">';
        ['old', 'new'].forEach(function (k) {
            html += '<div class="col-12 col-md-6"><h6 class="' + (k === 'old' ? 'text-danger' : 'text-success') + '">' + (k === 'old' ? 'Before' : 'After') + '</h6>';
            var vals = l[k] || {};
            if (!Object.keys(vals).length) {
                html += '<p class="text-muted small mb-0">—</p>';
            } else {
                html += '<table class="table table-sm table-bordered small mb-0"><tbody>';
                Object.keys(vals).forEach(function (f) {
                    html += '<tr><th style="width:40%">' + DP.esc(f) + '</th><td>' + DP.esc(typeof vals[f] === 'object' ? JSON.stringify(vals[f]) : vals[f]) + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            html += '</div>';
        });
        html += '</div>';
        document.getElementById('dp-audit-body').innerHTML = html;
        $('#dp-audit-modal').modal('show');
    });

    var q = document.getElementById('f-q'), t;
    q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { sc.reload(url()); }, 350); });
    ['action', 'type', 'from', 'to'].forEach(function (k) {
        document.getElementById('f-' + k).addEventListener('change', function () { sc.reload(url()); });
    });
})();
</script>
@endpush
