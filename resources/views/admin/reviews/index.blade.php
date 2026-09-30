@extends('admin.layouts.app')
@section('title', 'Reviews')
@section('page_title', 'Customer Reviews')
@section('page_subtitle', 'Moderation queue — pending → approved / rejected, verified-purchase & featured flags')

@section('content')

{{-- ================= RV-008: stats KPI row (SQL aggregates) ================= --}}
<div class="row mb-3">
    <div class="col-6 col-md-3">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['avg_rating'], 1) }} <span class="text-white-50" style="font-size:.5em">/ 5</span></h3><p>Avg rating (approved)</p></div>
            <div class="icon"><i class="fas fa-star"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $stats['pending'] }}</h3><p>Pending moderation</p></div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $stats['approved_count'] }}</h3><p>Approved ({{ $stats['verified'] }} verified)</p></div>
            <div class="icon"><i class="fas fa-check-double"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $stats['verified_share'] }}%</h3><p>Verified purchases</p></div>
            <div class="icon"><i class="fas fa-certificate"></i></div>
        </div>
    </div>
</div>

<div class="row mb-3">
    {{-- Rating distribution bars --}}
    <div class="col-12 col-lg-7 mb-3">
        <div class="card shadow-sm">
            <div class="card-header py-2"><i class="fas fa-chart-bar mr-1"></i> Rating distribution (approved) — {{ $stats['five_star_share'] }}% five-star</div>
            <div class="card-body py-2">
                @php $distTotal = max(1, array_sum($dist)); @endphp
                @foreach (collect($dist)->sortKeysDesc() as $stars => $count)
                    <div class="d-flex align-items-center mb-1">
                        <span class="text-nowrap" style="width:58px">{{ $stars }} <i class="fas fa-star text-warning"></i></span>
                        <div class="progress w-100 mx-2" style="height:14px">
                            <div class="progress-bar bg-warning" style="width:{{ round(100 * $count / $distTotal) }}%"></div>
                        </div>
                        <span class="text-muted small text-right" style="width:34px">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    {{-- Monthly trend --}}
    <div class="col-12 col-lg-5 mb-3">
        <div class="card shadow-sm">
            <div class="card-header py-2"><i class="fas fa-chart-line mr-1"></i> Approved per month (avg rating)</div>
            <div class="card-body py-2">
                @foreach ($monthly as $ym => $m)
                    <div class="d-flex align-items-center mb-1">
                        <span class="text-muted small" style="width:70px">{{ $ym }}</span>
                        <div class="progress w-100 mx-2" style="height:14px">
                            @php $maxN = max(1, collect($monthly)->max('count')); @endphp
                            <div class="progress-bar bg-info" style="width:{{ round(100 * $m['count'] / $maxN) }}%"></div>
                        </div>
                        <span class="small text-right" style="width:78px">{{ $m['count'] }} · {{ number_format($m['avg'], 1) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ================= RV-005: combined filter toolbar ================= --}}
<div class="card shadow-sm mb-3">
    <div class="card-header py-2">
        <i class="fas fa-filter mr-1"></i> Filters
        <span class="text-muted small ml-2">All filters combine with AND — stack status + rating range + verified + products + date window …</span>
    </div>
    <div class="card-body py-2">
        <div class="form-row align-items-end">
            <div class="form-group col-md-3 mb-2">
                <label class="small mb-1">Search (title / body / user)</label>
                <input id="f-q" class="form-control form-control-sm" placeholder="Search…" value="{{ request('q') }}">
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Status</label>
                <select id="f-status" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($statusMap as $key => $m)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $m['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Type</label>
                <select id="f-type" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Rating =</label>
                <select id="f-rating" class="form-control form-control-sm">
                    <option value="">All</option>
                    @for ($r = config('admin_reviews.rating_max'); $r >= config('admin_reviews.rating_min'); $r--)
                        <option value="{{ $r }}" {{ (string) request('rating') === (string) $r ? 'selected' : '' }}>{{ $r }} star</option>
                    @endfor
                </select>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Rating between</label>
                <div class="d-flex">
                    <select id="f-rating-min" class="form-control form-control-sm mr-1">
                        <option value="">min</option>
                        @for ($r = config('admin_reviews.rating_min'); $r <= config('admin_reviews.rating_max'); $r++)
                            <option value="{{ $r }}" {{ (string) request('rating_min') === (string) $r ? 'selected' : '' }}>&ge; {{ $r }}</option>
                        @endfor
                    </select>
                    <select id="f-rating-max" class="form-control form-control-sm">
                        <option value="">max</option>
                        @for ($r = config('admin_reviews.rating_min'); $r <= config('admin_reviews.rating_max'); $r++)
                            <option value="{{ $r }}" {{ (string) request('rating_max') === (string) $r ? 'selected' : '' }}>&le; {{ $r }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="form-group col-md-1 mb-2">
                <label class="small mb-1">Verified</label>
                <select id="f-verified" class="form-control form-control-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        <div class="form-row align-items-end">
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Featured</label>
                <select id="f-featured" class="form-control form-control-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Not featured</option>
                </select>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">User ID</label>
                <input id="f-user-id" type="number" min="1" class="form-control form-control-sm" value="{{ request('user_id') }}">
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Order ID</label>
                <input id="f-order-id" type="number" min="1" class="form-control form-control-sm" value="{{ request('order_id') }}">
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Products (multi)</label>
                <select id="f-product-ids" class="form-control form-control-sm" multiple size="3">
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" {{ in_array((string) $p->id, array_map('strval', (array) request('product_ids', [])) ) ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Services (multi)</label>
                <select id="f-service-ids" class="form-control form-control-sm" multiple size="3">
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}" {{ in_array((string) $s->id, array_map('strval', (array) request('service_ids', [])) ) ? 'selected' : '' }}>{{ $s->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Date preset</label>
                <select id="f-preset" class="form-control form-control-sm">
                    <option value="">All time</option>
                    @foreach (config('admin_reviews.date_presets') as $p)
                        <option value="{{ $p }}" {{ request('preset') === $p ? 'selected' : '' }}>{{ strtoupper($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Custom date range</label>
                <div class="d-flex">
                    <input id="f-date-from" type="date" class="form-control form-control-sm mr-1" value="{{ request('date_from') }}">
                    <input id="f-date-to" type="date" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
            </div>
        </div>
        <div class="form-row align-items-end">
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Sort</label>
                <select id="f-sort" class="form-control form-control-sm">
                    @foreach ($sortOpts as $key => $label)
                        <option value="{{ $key }}" {{ request('sort', 'newest') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Saved preset</label>
                <div class="d-flex">
                    <select id="f-saved-preset" class="form-control form-control-sm mr-1">
                        <option value="">—</option>
                        @foreach ($presets as $preset)
                            <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="btn-preset-load" class="btn btn-sm btn-outline-primary">Load</button>
                </div>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">Save current filters as</label>
                <div class="d-flex">
                    <input id="f-preset-name" class="form-control form-control-sm mr-1" placeholder="my_preset" pattern="[A-Za-z0-9_\-]+">
                    <button type="button" id="btn-preset-save" class="btn btn-sm btn-outline-success">Save</button>
                </div>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small mb-1">&nbsp;</label>
                <button type="button" id="btn-filters-clear" class="btn btn-sm btn-outline-secondary btn-block">Clear filters</button>
            </div>

            {{-- RV-004: bulk action bar --}}
            <div class="form-group col-md-4 mb-2">
                <label class="small mb-1">Bulk action on <span id="bulk-count">0</span> selected</label>
                <div class="d-flex">
                    <select id="bulk-action" class="form-control form-control-sm mr-1">
                        <option value="">Choose…</option>
                        <option value="approve">Approve</option>
                        <option value="reject">Reject</option>
                        <option value="hide">Hide</option>
                        <option value="restore">Restore to pending</option>
                        <option value="spam">Mark spam</option>
                        <option value="feature">Feature</option>
                        <option value="unfeature">Unfeature</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="button" id="btn-bulk-apply" class="btn btn-sm btn-outline-danger">Apply</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= RV-004: lazy-table (DP.infiniteScroll pattern) ================= --}}
<div class="dp-table-wrap p-2 p-md-0">
    <table class="table table-hover dp-table dp-card-mobile mb-0">
        <thead>
            <tr>
                <th style="width:28px"><input type="checkbox" id="check-all" title="Select all loaded"></th>
                <th>#</th><th>Customer</th><th>Type</th><th>Rating</th><th>Title</th>
                <th>Status</th><th>Flags</th><th>Order</th><th>Created</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="dp-tbody"></tbody>
    </table>
</div>

@endsection

@push('admin_scripts')
<script>
(function () {
    var base = @json(route('admin.reviews.data'));
    var bulkUrl = @json(route('admin.reviews.bulk'));
    var presetStoreUrl = @json(route('admin.reviews.presets.store'));
    var presetLoadBase = @json(url('admin/reviews/presets'));
    var csrf = document.querySelector('meta[name=csrf-token]').content;
    var selected = {};

    function esc(s) { return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    /* ---- RV-005: collect EVERY filter (AND-combinable) ---- */
    function buildUrl() {
        var u = new URL(base);
        function set(id, param) {
            var el = document.getElementById(id);
            if (!el) return;
            var v = el.value.trim();
            if (v) u.searchParams.set(param, v);
        }
        set('f-q', 'q'); set('f-status', 'status'); set('f-type', 'type');
        set('f-rating', 'rating'); set('f-rating-min', 'rating_min'); set('f-rating-max', 'rating_max');
        set('f-verified', 'verified'); set('f-featured', 'featured');
        set('f-user-id', 'user_id'); set('f-order-id', 'order_id');
        set('f-preset', 'preset'); set('f-date-from', 'date_from'); set('f-date-to', 'date_to');
        set('f-sort', 'sort');
        ['f-product-ids', 'f-service-ids'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            var param = id === 'f-product-ids' ? 'product_ids' : 'service_ids';
            Array.prototype.forEach.call(el.selectedOptions || [], function (o) {
                u.searchParams.append(param + '[]', o.value);
            });
        });
        return u.toString();
    }

    function stars(n) {
        var h = '';
        for (var i = 1; i <= 5; i++) h += '<i class="fas fa-star ' + (i <= n ? 'text-warning' : 'text-muted') + '" style="font-size:.7rem"></i>';
        return h;
    }

    function actionBtn(icon, title, url, method, color) {
        return '<a href="#" data-rv-action="' + esc(url) + '" data-rv-method="' + method + '" data-rv-title="' + esc(title) + '" ' +
            'class="btn btn-sm btn-outline-' + color + '" title="' + esc(title) + '"><i class="fas ' + icon + '"></i></a> ';
    }

    var sc = DP.infiniteScroll({
        url: buildUrl(), target: '#dp-tbody',
        render: function (r) {
            var flags = '';
            if (r.verified) flags += '<span class="badge badge-success" title="Verified purchase"><i class="fas fa-certificate"></i> VP</span> ';
            if (r.featured) flags += '<span class="badge badge-warning" title="Featured"><i class="fas fa-star"></i></span>';
            var acts = actionBtn('fa-eye', 'View', r.urls.show, 'GET', 'primary');
            if (r.urls.approve) acts += actionBtn('fa-check', 'Approve', r.urls.approve, 'PUT', 'success');
            if (r.urls.reject)  acts += actionBtn('fa-times', 'Reject', r.urls.reject, 'PUT', 'danger');
            if (r.urls.hide)    acts += actionBtn('fa-power-off', 'Hide', r.urls.hide, 'PUT', 'secondary');
            if (r.urls.restore) acts += actionBtn('fa-undo', 'Restore to pending', r.urls.restore, 'PUT', 'info');
            if (r.urls.spam)    acts += actionBtn('fa-ban', 'Mark spam', r.urls.spam, 'PUT', 'dark');
            if (r.urls.unfeature) acts += actionBtn('fa-star', 'Unfeature', r.urls.unfeature, 'PUT', 'warning');
            else if (r.urls.feature) acts += actionBtn('fa-star', 'Feature', r.urls.feature, 'PUT', 'warning');
            return '<tr>'
                + '<td data-label="Select"><input type="checkbox" class="rv-check" data-id="' + esc(r.id) + '"' + (selected[r.id] ? ' checked' : '') + '></td>'
                + '<td data-label="#"><strong>#' + esc(r.id) + '</strong></td>'
                + '<td data-label="Customer">' + esc(r.user) + '<br><small class="text-muted">' + esc(r.email || '') + '</small></td>'
                + '<td data-label="Type">' + esc(r.type) + '</td>'
                + '<td data-label="Rating">' + stars(r.rating) + '</td>'
                + '<td data-label="Title">' + esc(r.title || '—') + '</td>'
                + '<td data-label="Status"><span class="dp-badge ' + esc(r.color) + ' text-white">' + esc(r.status_lbl) + '</span></td>'
                + '<td data-label="Flags">' + (flags || '—') + '</td>'
                + '<td data-label="Order">' + (r.order_id ? '#' + esc(r.order_id) : '—') + '</td>'
                + '<td data-label="Created" class="text-muted small">' + esc(r.created_at) + '</td>'
                + '<td data-label="Actions" class="dp-actions text-right text-nowrap">' + acts + '</td>'
                + '</tr>';
        }
    });

    /* ---- filter wiring: any input change reloads with ALL filters ---- */
    var t;
    function schedule() { clearTimeout(t); t = setTimeout(function () { sc.reload(buildUrl()); }, 350); }
    ['f-q', 'f-user-id', 'f-order-id'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', schedule);
    });
    ['f-status', 'f-type', 'f-rating', 'f-rating-min', 'f-rating-max', 'f-verified', 'f-featured',
     'f-preset', 'f-date-from', 'f-date-to', 'f-sort', 'f-product-ids', 'f-service-ids'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', schedule);
    });

    document.getElementById('btn-filters-clear').addEventListener('click', function () {
        ['f-q', 'f-status', 'f-type', 'f-rating', 'f-rating-min', 'f-rating-max', 'f-verified', 'f-featured',
         'f-user-id', 'f-order-id', 'f-preset', 'f-date-from', 'f-date-to', 'f-saved-preset', 'f-preset-name'].forEach(function (id) {
            var el = document.getElementById(id); if (el) el.value = '';
        });
        ['f-product-ids', 'f-service-ids'].forEach(function (id) {
            var el = document.getElementById(id);
            Array.prototype.forEach.call(el.options || [], function (o) { o.selected = false; });
        });
        sc.reload(buildUrl());
    });

    /* ---- moderation shortcuts (self-contained; PUT/DELETE via _method POST) ---- */
    document.getElementById('dp-tbody').addEventListener('click', function (e) {
        var a = e.target.closest('a[data-rv-action]');
        if (!a) return;
        e.preventDefault();
        if (!window.confirm(a.dataset.rvTitle + ' — are you sure?')) return;
        var body = new URLSearchParams();
        body.append('_method', a.dataset.rvMethod);
        body.append('_token', csrf);
        fetch(a.dataset.rvAction, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: body.toString() })
            .then(function (res) { return res.redirected || res.ok ? res.text() : Promise.reject(res.status); })
            .then(function () { sc.reload(buildUrl()); })
            .catch(function () { alert('Action failed.'); });
    });

    /* ---- bulk selection ---- */
    document.getElementById('dp-tbody').addEventListener('change', function (e) {
        if (!e.target.classList.contains('rv-check')) return;
        e.target.checked ? selected[e.target.dataset.id] = true : delete selected[e.target.dataset.id];
        document.getElementById('bulk-count').textContent = Object.keys(selected).length;
    });
    document.getElementById('check-all').addEventListener('change', function () {
        var on = this.checked;
        document.querySelectorAll('.rv-check').forEach(function (c) {
            c.checked = on;
            on ? selected[c.dataset.id] = true : delete selected[c.dataset.id];
        });
        document.getElementById('bulk-count').textContent = Object.keys(selected).length;
    });

    document.getElementById('btn-bulk-apply').addEventListener('click', function () {
        var action = document.getElementById('bulk-action').value;
        var ids = Object.keys(selected);
        if (!action || !ids.length) { alert('Pick an action and at least one review.'); return; }
        if (!window.confirm('Bulk ' + action + ' on ' + ids.length + ' review(s)?')) return;
        var body = new URLSearchParams();
        body.append('_token', csrf);
        body.append('action', action);
        ids.forEach(function (id) { body.append('ids[]', id); });
        fetch(bulkUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: body.toString() })
            .then(function (res) { selected = {}; return res.ok || res.redirected ? res.text() : Promise.reject(res.status); })
            .then(function () { sc.reload(buildUrl()); })
            .catch(function () { alert('Bulk action failed.'); });
    });

    /* ---- presets ---- */
    document.getElementById('btn-preset-save').addEventListener('click', function () {
        var name = document.getElementById('f-preset-name').value.trim();
        if (!/^[A-Za-z0-9_\-]{2,120}$/.test(name)) { alert('Preset name: 2-120 chars, letters/digits/_/- only.'); return; }
        var u = new URL(buildUrl());
        var filters = {};
        u.searchParams.forEach(function (v, k) { if (k !== 'page' && k !== 'per_page') { filters[k] = /\[\]$/.test(k) ? (filters[k] || []).concat([v]) : v; } });
        var body = new URLSearchParams();
        body.append('_token', csrf);
        body.append('name', name);
        Object.keys(filters).forEach(function (k) {
            Array.isArray(filters[k]) ? filters[k].forEach(function (v) { body.append('filters[' + k + '][]', v); }) : body.append('filters[' + k + ']', filters[k]);
        });
        fetch(presetStoreUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf }, body: body.toString() })
            .then(function () { window.location.reload(); });
    });

    document.getElementById('btn-preset-load').addEventListener('click', function () {
        var id = document.getElementById('f-saved-preset').value;
        if (id) window.location.href = presetLoadBase + '/' + id + '/load';
    });
})();
</script>
@endpush
