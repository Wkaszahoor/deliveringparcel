@extends('admin.layouts.app')

@section('title', 'SEO Shield')
@section('page_title', 'SEO Shield')
@section('page_subtitle', 'SEO Health Score — technical checks, content guidance & internal linking ideas')

@section('content')
<?php
/* Recent critical count: score present and below 55 */
$criticalCount = $stats['recent']->filter(function ($r) {
    return $r->overall_score !== null && $r->overall_score < 55;
})->count();
?>

<div class="dp-kpi-grid mb-4">
    <div class="dp-kpi">
        <span class="dp-kpi-icon bg-primary"><i class="fas fa-shield-alt"></i></span>
        <div class="dp-kpi-label">Total analyses</div>
        <div class="dp-kpi-value">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="dp-kpi">
        <span class="dp-kpi-icon bg-info"><i class="fas fa-heartbeat"></i></span>
        <div class="dp-kpi-label">Average SEO Health Score</div>
        <div class="dp-kpi-value">{{ $stats['avg_score'] ?? '&mdash;' }}</div>
    </div>
    <div class="dp-kpi">
        <span class="dp-kpi-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
        <div class="dp-kpi-label">Recent critical (score &lt; 55)</div>
        <div class="dp-kpi-value">{{ $criticalCount }}</div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search mr-1"></i> Run an analysis</h3>
            </div>
            <div class="card-body">
                <div class="btn-group btn-group-sm mb-3" role="group">
                    <button type="button" class="btn btn-outline-primary dp-seo-tab active" data-target="dpSeoPanePost">Existing post</button>
                    <button type="button" class="btn btn-outline-primary dp-seo-tab" data-target="dpSeoPaneUrl">URL</button>
                    <button type="button" class="btn btn-outline-primary dp-seo-tab" data-target="dpSeoPaneContent">Paste content</button>
                </div>

                {{-- Tab 1: existing CMS post --}}
                <div class="dp-seo-pane" id="dpSeoPanePost" data-mode="post">
                    <div class="form-group">
                        <label for="dpSeoPost" class="mb-1">Post</label>
                        <select id="dpSeoPost" class="form-control form-control-sm" data-field="post_id">
                            @forelse ($posts as $post)
                                <option value="{{ $post->id }}">{{ $post->title }} ({{ $post->post_type }}, {{ $post->status }})</option>
                            @empty
                                <option value="">No posts found</option>
                            @endforelse
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm dp-seo-run"><i class="fas fa-play mr-1"></i> Analyze</button>
                    <span class="dp-seo-spinner d-none text-muted ml-2"><span class="spinner-border spinner-border-sm align-middle"></span> Analyzing&hellip;</span>
                </div>

                {{-- Tab 2: URL --}}
                <div class="dp-seo-pane d-none" id="dpSeoPaneUrl" data-mode="url">
                    <div class="form-group">
                        <label for="dpSeoUrl" class="mb-1">Page URL</label>
                        <input type="url" id="dpSeoUrl" class="form-control form-control-sm" data-field="url" placeholder="https://deliveringparcel.com/blog/..." value="">
                    </div>
                    <button type="button" class="btn btn-primary btn-sm dp-seo-run"><i class="fas fa-play mr-1"></i> Analyze</button>
                    <span class="dp-seo-spinner d-none text-muted ml-2"><span class="spinner-border spinner-border-sm align-middle"></span> Analyzing&hellip;</span>
                </div>

                {{-- Tab 3: pasted content --}}
                <div class="dp-seo-pane d-none" id="dpSeoPaneContent" data-mode="content">
                    <div class="form-group">
                        <label for="dpSeoTitle" class="mb-1">Title <span class="text-danger">*</span></label>
                        <input type="text" id="dpSeoTitle" class="form-control form-control-sm" data-field="title" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="dpSeoMetaTitle" class="mb-1">Meta title</label>
                        <input type="text" id="dpSeoMetaTitle" class="form-control form-control-sm" data-field="meta_title">
                    </div>
                    <div class="form-group">
                        <label for="dpSeoMetaDescription" class="mb-1">Meta description</label>
                        <textarea id="dpSeoMetaDescription" class="form-control form-control-sm" rows="2" data-field="meta_description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="dpSeoSlug" class="mb-1">Slug</label>
                        <input type="text" id="dpSeoSlug" class="form-control form-control-sm" data-field="slug">
                    </div>
                    <div class="form-group">
                        <label for="dpSeoTopic" class="mb-1">Focus topic</label>
                        <input type="text" id="dpSeoTopic" class="form-control form-control-sm" data-field="topic">
                    </div>
                    <div class="form-group">
                        <label for="dpSeoContent" class="mb-1">Content <span class="text-danger">*</span></label>
                        <textarea id="dpSeoContent" class="form-control form-control-sm" rows="8" data-field="content"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm dp-seo-run"><i class="fas fa-play mr-1"></i> Analyze</button>
                    <span class="dp-seo-spinner d-none text-muted ml-2"><span class="spinner-border spinner-border-sm align-middle"></span> Analyzing&hellip;</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-default d-none" id="dpSeoResults">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Results</h3>
            </div>
            <div class="card-body" id="dpSeoResultsBody"></div>
        </div>
    </div>
</div>

<div class="card card-default mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent analyses (latest 15)</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Subject</th>
                    <th>Score</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stats['recent'] as $r)
                    <?php $sc = $r->overall_score; $scColor = ($sc === null) ? '#6c757d' : ($sc >= 85 ? '#28a745' : ($sc >= 60 ? '#e0a800' : '#dc3545')); ?>
                    <tr>
                        <td class="text-nowrap">{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                        <td><span class="badge badge-info">{{ strtoupper($r->source_type) }}</span></td>
                        <td>
                            @if ($r->subject_title)
                                {{ \Illuminate\Support\Str::limit($r->subject_title, 60) }}
                            @elseif ($r->source_url)
                                <span class="text-muted">{{ \Illuminate\Support\Str::limit($r->source_url, 60) }}</span>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td style="font-weight:600;color: {{ $scColor }};">{{ $sc ?? '&mdash;' }}</td>
                        <td><span class="badge" style="background: {{ $scColor }}; color: #fff;">{{ $r->grade ?? '&mdash;' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No analyses yet — run one above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('admin_styles')
<style>
.dp-seo-ring {
    width: 84px; height: 84px; border-radius: 50%;
    border: 7px solid #28a745; background: #fff;
    display: flex; align-items: center; justify-content: center; flex: 0 0 auto;
}
.dp-seo-ring-num { font-size: 1.45rem; font-weight: 700; line-height: 1; }
.dp-seo-chip {
    display: inline-block; padding: .05rem .5rem; border-radius: 1rem;
    background: #e9ecef; color: #495057; font-size: .72rem;
}
.dp-seo-finding {
    padding: .5rem .75rem; margin-bottom: .45rem;
    border: 1px solid #e3e6ea; border-left-width: 3px; border-radius: .4rem; background: #fff;
}
.dp-seo-stat { display: inline-block; margin-right: 1rem; }
.dp-seo-tag { font-weight: 400; }
</style>
@endpush

@push('admin_scripts')
<script>
(function () {
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var ENDPOINT = '{{ route('admin.seo-shield.analyze') }}';

    /* Language rules: never "Google SEO Score", never "Google requires X". */
    var LEVELS = {
        critical:       { icon: '&#128308;', label: 'Technical issue', color: '#dc3545' },
        recommendation: { icon: '&#128992;', label: 'Recommended',     color: '#fd7e14' },
        heuristic:      { icon: '&#128993;', label: 'Heuristic',       color: '#e0a800' },
        pass:           { icon: '&#128994;', label: 'Good',            color: '#28a745' }
    };

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = (s === null || s === undefined) ? '' : String(s);
        return d.innerHTML;
    }

    function scoreColor(score) {
        if (score === null || score === undefined || isNaN(score)) return '#6c757d';
        if (score >= 85) return '#28a745';
        if (score >= 60) return '#e0a800';
        return '#dc3545';
    }

    function labelize(key) {
        return String(key).replace(/[_-]+/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    /* ---- Tab switching (plain JS, no bootstrap dependency) ---- */
    document.querySelectorAll('.dp-seo-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.dp-seo-tab').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            document.querySelectorAll('.dp-seo-pane').forEach(function (p) { p.classList.add('d-none'); });
            var pane = document.getElementById(btn.getAttribute('data-target'));
            if (pane) pane.classList.remove('d-none');
        });
    });

    /* ---- Category bars ---- */
    function renderCategories(d) {
        var html = '';
        if (!d.categories || typeof d.categories !== 'object') return html;
        html += '<h6 class="mt-3 mb-2">Category breakdown</h6>';
        Object.keys(d.categories).forEach(function (k) {
            var v = d.categories[k];
            var name = esc(labelize(k));
            if (v === null || v === undefined || v === '') {
                html += '<div class="mb-1 small">' + name + ' <span class="dp-seo-chip">unavailable</span></div>';
                return;
            }
            var n = Number(v);
            if (isNaN(n)) {
                html += '<div class="mb-1 small">' + name + ' <span class="dp-seo-chip">unavailable</span></div>';
                return;
            }
            var c = scoreColor(n);
            var pct = Math.max(0, Math.min(100, n));
            html += '<div class="dp-seo-cat mb-2">';
            html += '<div class="d-flex justify-content-between small"><span>' + name + '</span>' +
                    '<span style="color:' + c + ';font-weight:600">' + n + '</span></div>';
            html += '<div class="progress" style="height:8px"><div class="progress-bar" role="progressbar" ' +
                    'style="width:' + pct + '%;background:' + c + '"></div></div>';
            html += '</div>';
        });
        return html;
    }

    /* ---- Findings grouped critical / recommendation / heuristic / pass ---- */
    function renderFindings(d) {
        var html = '';
        var raw = d.findings || {};
        var groups = { critical: [], recommendation: [], heuristic: [], pass: [] };

        if (Array.isArray(raw)) {
            raw.forEach(function (x) {
                var lv = (x && x.level && LEVELS[x.level]) ? x.level : 'recommendation';
                groups[lv].push(x);
            });
        } else if (raw && typeof raw === 'object') {
            Object.keys(LEVELS).forEach(function (lv) {
                if (Array.isArray(raw[lv])) groups[lv] = raw[lv];
            });
        }

        Object.keys(LEVELS).forEach(function (lv) {
            var items = groups[lv];
            if (!items || !items.length) return;
            var m = LEVELS[lv];
            html += '<div class="mt-3"><h6 class="mb-2">' + m.icon + ' ' + m.label +
                    ' <span class="badge badge-light border">' + items.length + '</span></h6>';
            items.forEach(function (it) {
                var title = it.title || it.check || it.name || 'Finding';
                var detail = it.detail || it.message || it.description || '';
                var fix = it.fix || it.how || '';
                html += '<div class="dp-seo-finding" style="border-left-color:' + m.color + '">';
                html += '<div><strong>' + esc(title) + '</strong>';
                if (it.category) html += ' <span class="badge badge-secondary dp-seo-tag">' + esc(it.category) + '</span>';
                html += '</div>';
                if (detail) html += '<div class="small text-muted">' + esc(detail) + '</div>';
                if (fix) html += '<div class="small"><span style="color:#28a745;font-weight:600">Fix:</span> ' + esc(fix) + '</div>';
                html += '</div>';
            });
            html += '</div>';
        });
        return html;
    }

    /* ---- Stats line ---- */
    function renderStats(d) {
        var st = d.stats || {};
        var fetched = d.fetched || {};
        var bits = [];
        function add(label, val) {
            if (val === undefined || val === null || val === '') return;
            bits.push('<span class="dp-seo-stat">' + label + ': <strong>' + esc(val) + '</strong></span>');
        }
        if (st.words !== undefined && st.words !== null) {
            bits.push('<span class="dp-seo-stat"><strong>' + esc(st.words) + '</strong> words</span>');
        }
        add('Reading time', st.reading_time);
        add('H1', st.h1);
        add('H2', st.h2);
        add('Links', st.links);
        var finalUrl = fetched.final || fetched.url || st.final_url;
        if (finalUrl) add('URL', finalUrl);
        var httpStatus = fetched.status || st.http_status;
        if (httpStatus) add('HTTP', httpStatus);
        if (st.ttfb_ms !== undefined && st.ttfb_ms !== null) add('TTFB', st.ttfb_ms + ' ms');
        if (st.html_size_kb !== undefined && st.html_size_kb !== null) add('HTML size', st.html_size_kb + ' KB');
        if (!bits.length) return '';
        return '<div class="mt-3 pt-2 border-top small">' + bits.join('') + '</div>';
    }

    function renderResults(payload) {
        var card = document.getElementById('dpSeoResults');
        var body = document.getElementById('dpSeoResultsBody');
        var d = payload && payload.data ? payload.data : null;

        if (!d || typeof d !== 'object') {
            body.innerHTML = '<div class="alert alert-warning mb-0">Unexpected analyzer response.</div>';
            card.classList.remove('d-none');
            return;
        }

        var html = '';
        var score = (d.score === undefined || d.score === null) ? null : Number(d.score);
        var color = scoreColor(score);

        html += '<div class="d-flex align-items-center">';
        html += '<div class="dp-seo-ring" style="border-color:' + color + '">' +
                '<div class="dp-seo-ring-num" style="color:' + color + '">' + (score === null || isNaN(score) ? '&mdash;' : score) + '</div></div>';
        html += '<div class="ml-3">';
        html += '<h5 class="mb-1">SEO Health Score</h5>';
        html += '<span class="badge" style="background:' + color + ';color:#fff">' + esc(d.grade || 'Not graded') + '</span>';
        if (payload.analysis_id) html += '<div class="small text-muted mt-1">Saved as analysis #' + esc(payload.analysis_id) + '</div>';
        html += '</div></div>';

        html += renderCategories(d);
        html += renderFindings(d);
        html += renderStats(d);

        body.innerHTML = html;
        card.classList.remove('d-none');
    }

    function renderError(payload) {
        var card = document.getElementById('dpSeoResults');
        var body = document.getElementById('dpSeoResultsBody');
        var msg = '';
        if (payload && payload.error) {
            msg = esc(payload.error);
        } else if (payload && payload.errors) {
            var parts = [];
            Object.keys(payload.errors).forEach(function (k) {
                (payload.errors[k] || []).forEach(function (m) { parts.push(esc(m)); });
            });
            msg = parts.join(' ');
        }
        body.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>' +
                (msg || 'Analysis failed. Please try again.') + '</div>';
        card.classList.remove('d-none');
    }

    /* ---- Run analysis ---- */
    document.querySelectorAll('.dp-seo-run').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var pane = btn.closest('.dp-seo-pane');
            if (!pane) return;
            var spinner = pane.querySelector('.dp-seo-spinner');

            var fd = new FormData();
            fd.append('_token', CSRF);
            fd.append('mode', pane.getAttribute('data-mode'));
            pane.querySelectorAll('[data-field]').forEach(function (el) {
                fd.append(el.getAttribute('data-field'), el.value);
            });

            btn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');

            fetch(ENDPOINT, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) {
                return r.json().then(function (j) { return { status: r.status, body: j }; });
            })
            .then(function (res) {
                if (res.body && res.body.ok) {
                    renderResults(res.body);
                } else {
                    renderError(res.body);
                }
            })
            .catch(function () {
                renderError(null);
            })
            .then(function () {
                btn.disabled = false;
                if (spinner) spinner.classList.add('d-none');
            });
        });
    });
})();
</script>
@endpush
