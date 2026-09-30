@extends('admin.layouts.app')

@section('title', 'Image Optimizer')
@section('page_title', 'Image Optimizer')
@section('page_subtitle', 'Compress in place, create WebP copies + responsive variants — originals are backed up')

@section('content')
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="dp-kpi"><span class="dp-kpi-icon" style="background:#0b5fff"><i class="fas fa-images"></i></span>
            <div class="dp-kpi-label">Images found</div><div class="dp-kpi-value" id="ioTotal">–</div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="dp-kpi"><span class="dp-kpi-icon" style="background:#16a34a"><i class="fas fa-weight-hanging"></i></span>
            <div class="dp-kpi-label">Total size</div><div class="dp-kpi-value" id="ioSize">–</div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="dp-kpi"><span class="dp-kpi-icon" style="background:#8b5cf6"><i class="fas fa-bolt"></i></span>
            <div class="dp-kpi-label">WebP ready</div><div class="dp-kpi-value" id="ioWebp">–</div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="dp-kpi"><span class="dp-kpi-icon" style="background:#f59e0b"><i class="fas fa-floppy-disk"></i></span>
            <div class="dp-kpi-label">Session saved</div><div class="dp-kpi-value" id="ioSaved">0 KB</div></div>
    </div>
</div>

<div class="dp-toolbar d-flex flex-wrap align-items-center mb-3">
    <form method="GET" class="form-inline mr-auto">
        <div class="input-group input-group-sm mr-2 mb-2" style="max-width:240px;">
            <span class="input-group-text">public/</span>
            <input type="text" name="path" value="{{ $path }}" class="form-control" placeholder="uploads">
            <div class="input-group-append"><button class="btn btn-outline-primary" type="submit">Scan</button></div>
        </div>
    </form>
    <button type="button" id="ioBulk" class="btn btn-success btn-sm mb-2"><i class="fas fa-magic"></i> Optimize all (this page)</button>
    <span class="text-muted small ml-2" id="ioMsg"></span>
</div>

<div class="dp-table-wrap">
    <table class="table dp-table dp-card-mobile align-middle">
        <thead>
            <tr>
                <th>Image</th>
                <th>Size</th>
                <th>Dimensions</th>
                <th>WebP</th>
                <th>Backup</th>
                <th class="text-right">Action</th>
            </tr>
        </thead>
        <tbody id="ioRows"><tr><td colspan="6" class="text-muted p-4">Loading…</td></tr></tbody>
    </table>
</div>
@endsection

@push('admin_scripts')
<script>
(function () {
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); };
    var saved = 0;

    function load() {
        fetch('{{ url('admin/image-optimizer/data') }}?path={{ $path }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { render(j); });
    }

    function render(j) {
        document.getElementById('ioTotal').textContent = j.total;
        document.getElementById('ioSize').textContent = j.total_kb + ' KB';
        document.getElementById('ioWebp').textContent = j.webp;
        var rows = (j.images || []).slice(0, 60).map(function (i) {
            return '<tr>' +
                '<td class="text-truncate" style="max-width:340px"><a href="' + esc(i.url) + '" target="_blank">' + esc(i.path) + '</a></td>' +
                '<td>' + i.size_kb + ' KB</td>' +
                '<td>' + (i.width ? i.width + '×' + i.height : '—') + '</td>' +
                '<td>' + (i.is_webp || i.has_webp ? '<span class="badge badge-success">yes</span>' : '<span class="badge badge-secondary">no</span>') + '</td>' +
                '<td>' + (i.has_backup ? '<span class="badge badge-info">safe</span>' : '<span class="badge badge-light border">—</span>') + '</td>' +
                '<td class="text-right">' + (i.is_webp ? '<span class="text-muted small">already WebP</span>' :
                    '<button type="button" class="btn btn-primary btn-sm io-opt" data-path="' + esc(i.path) + '">Optimize</button>') + '</td></tr>';
        }).join('');
        document.getElementById('ioRows').innerHTML = rows || '<tr><td colspan="6" class="text-muted p-4">No images found.</td></tr>';
        bind();
    }

    function optimize(path, btn) {
        var fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('path', path);
        btn.disabled = true; btn.textContent = '…';
        fetch('{{ url('admin/image-optimizer/optimize') }}', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.ok) {
                    saved += (j.orig_kb - j.new_kb);
                    document.getElementById('ioSaved').textContent = Math.round(saved) + ' KB';
                    btn.textContent = '-' + j.saved_pct + '%';
                    btn.classList.remove('btn-primary'); btn.classList.add('btn-success');
                } else {
                    btn.textContent = 'failed';
                }
                btn.disabled = false;
            }).catch(function () { btn.disabled = false; btn.textContent = 'retry'; });
    }

    function bind() {
        document.querySelectorAll('.io-opt').forEach(function (b) {
            b.addEventListener('click', function () { optimize(b.getAttribute('data-path'), b); });
        });
    }

    document.getElementById('ioBulk').addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        document.getElementById('ioMsg').textContent = 'Optimizing up to 40 images…';
        var fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('path', '{{ $path }}');
        fetch('{{ url('admin/image-optimizer/bulk') }}', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                btn.disabled = false;
                document.getElementById('ioMsg').textContent = j.ok
                    ? 'Optimized ' + j.optimized + ' image(s), saved ' + j.saved_kb + ' KB.' + (j.failed.length ? ' Failed: ' + j.failed.length : '')
                    : 'Bulk failed.';
                load();
            }).catch(function () { btn.disabled = false; });
    });

    load();
})();
</script>
@endpush
