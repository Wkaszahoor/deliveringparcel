@extends('layouts.tailwind.app')

@section('title', 'Edit: ' . ($cmsPost->title ?? 'Post'))
@section('page_title', 'Edit: ' . ($cmsPost->title ?? 'Post'))
@section('page_subtitle', ($postTypes[$postType] ?? 'Post') . ' — last updated ' . optional($cmsPost->updated_at)->format('d M Y H:i'))

@section('content')
@include('admin.cms.posts._form')

{{-- ================= SEO Content Intelligence ================= --}}
<x-admin.card class="mt-4" id="dpSeoCard">
    <div class="mb-3 flex items-center">
        <h3 class="text-sm font-semibold text-slate-800"><i class="fas fa-shield-alt mr-1 text-slate-400"></i> SEO Content Intelligence</h3>
        <button type="button" id="dpSeoRun" class="ml-auto inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-dark">
            <i class="fas fa-search"></i> Analyze
        </button>
        <button type="button" id="dpSeoAuto" class="ml-2 inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">
            <i class="fas fa-magic"></i> 1-Click Auto-Fix
        </button>
    </div>
    <div id="dpSeoBody">
        <p class="mb-0 text-sm text-slate-500">Run the analyzer to score this page on on-page SEO, content quality, links, images, trust &amp; schema, technical indexability and spam signals — before you publish.
        <span class="mt-1 block text-xs text-slate-400">No fake "Google rules": findings are labeled &#128308; technical issue &middot; &#128992; recommendation &middot; &#128996; heuristic.</span></p>
    </div>
</x-admin.card>
@endsection

@push('admin_styles')
<style>
.dp-seo-score { display:flex; align-items:center; gap:1rem; margin-bottom:1rem; }
.dp-seo-ring { width:84px; height:84px; border-radius:50%; display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; font-weight:800; color:#fff; }
.dp-seo-cat { display:flex; align-items:center; gap:.5rem; margin-bottom:.35rem; font-size:.85rem; }
.dp-seo-bar { flex:1; height:8px; border-radius:4px; background:#e9ecef; overflow:hidden; }
.dp-seo-bar > span { display:block; height:100%; border-radius:4px; }
.dp-seo-finding { border-left:4px solid #ccc; padding:.5rem .75rem; margin-bottom:.5rem; background:#f8f9fa; border-radius:.25rem; font-size:.9rem; }
.dp-seo-critical { border-left-color:#dc3545; }
.dp-seo-recommendation { border-left-color:#fd7e14; }
.dp-seo-heuristic { border-left-color:#ffc107; }
.dp-seo-pass { border-left-color:#28a745; }
.dp-seo-toggle { cursor:pointer; }
.dp-seo-cats-row { display:flex; flex-wrap:wrap; margin: 0 -0.5rem 0.75rem; }
.dp-seo-cats-row > .dp-seo-cat { width:33.3333%; padding:0 0.5rem; box-sizing:border-box; }
@media (max-width: 640px) { .dp-seo-cats-row > .dp-seo-cat { width:50%; } }
</style>
@endpush

@push('admin_scripts')
<script>
(function () {
    var run = document.getElementById('dpSeoRun');
    if (!run) return;
    var body = document.getElementById('dpSeoBody');
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); };
    var icon = { critical: '&#128308;', recommendation: '&#128992;', heuristic: '&#128996;', pass: '&#128994;', info: '&#8505;&#65039;' };
    var color = function (v) { return v >= 85 ? '#28a745' : v >= 60 ? '#f59e0b' : '#dc3545'; };

    function render(d) {
        var h = '';
        h += '<div class="dp-seo-score">' +
             '<div class="dp-seo-ring" style="background:' + color(d.score) + '">' + d.score + '</div>' +
             '<div><h4 class="mb-1 text-base font-semibold text-slate-900">' + esc(d.grade) + '</h4>' +
             '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">' + d.priority.critical + ' critical</span> ' +
             '<span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">' + d.priority.recommendation + ' recommended</span> ' +
             '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">' + d.priority.heuristic + ' heuristics</span> ' +
             '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">' + d.priority.pass + ' passed</span>' +
             (d.stats.http_status ? ' <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">HTTP ' + d.stats.http_status + '</span>' : '') +
             '</div></div>';

        h += '<div class="dp-seo-cats-row">';
        Object.keys(d.categories).forEach(function (k) {
            var v = d.categories[k];
            h += '<div class="dp-seo-cat"><span style="width:130px">' + esc(k) + '</span>' +
                 '<div class="dp-seo-bar"><span style="width:' + v + '%;background:' + color(v) + '"></span></div>' +
                 '<b>' + v + '</b></div>';
        });
        h += '</div>';

        h += '<div class="mb-2 text-xs text-slate-500">' +
             (d.stats.words || 0) + ' words · ' + esc(d.stats.reading_time || '—') + ' reading · ' +
             'H1: ' + (d.stats.h1 || 0) + ' · H2: ' + (d.stats.h2 || 0) + ' · ' +
             'internal links: ' + (d.stats.internal_links || 0) + ' · external: ' + (d.stats.external_links || 0) +
             (d.stats.rendered_url ? ' · analyzed live: <code>' + esc(d.stats.rendered_url) + '</code>' : '') +
             '</div>';

        var order = { critical: 0, recommendation: 1, heuristic: 2, pass: 3, info: 4 };
        var open = { critical: true, recommendation: true, heuristic: false, pass: false, info: false };
        var sevLabel = { critical: 'Technical issue', recommendation: 'Recommended', heuristic: 'Heuristic', pass: 'Good', info: 'Info' };
        d.findings.sort(function (a, b) { return order[a.severity] - order[b.severity]; }).forEach(function (f) {
            h += '<div class="dp-seo-finding dp-seo-' + f.severity + '">' +
                 '<div class="dp-seo-toggle" data-sev="' + f.severity + '">' + icon[f.severity] + ' <b>' + esc(f.title) + '</b>' +
                 ' <small class="text-slate-500">(' + sevLabel[f.severity] + ')</small></div>' +
                 '<div class="dp-seo-detail" style="display:' + (open[f.severity] ? 'block' : 'none') + '">' +
                 '<div>' + esc(f.detail) + '</div>' +
                 (f.fix ? '<div class="mt-1 text-xs text-slate-500"><i class="fas fa-wrench"></i> ' + esc(f.fix) + '</div>' : '') +
                 (f.suggestions ? '<ul class="mt-1 mb-0 text-xs">' + f.suggestions.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('') + '</ul>' : '') +
                 '</div></div>';
        });

        body.innerHTML = h;
        body.querySelectorAll('.dp-seo-toggle').forEach(function (t) {
            t.addEventListener('click', function () {
                var d2 = t.nextElementSibling;
                d2.style.display = d2.style.display === 'none' ? 'block' : 'none';
            });
        });
    }

    /* ---------- 1-Click Auto-Fix ---------- */
    var auto = document.getElementById('dpSeoAuto');
    var CHOICES = [
        ['meta_title', 'Meta title'],
        ['meta_description', 'Meta description'],
        ['slug', 'Slug (de-duplicated)'],
        ['meta_keywords', 'Focus topic'],
        ['featured_image', 'Featured image (first image in content)'],
        ['author_id', 'Author = me (fixes trust)'],
        ['strip_filler', 'Remove filler sentences'],
        ['add_internal_links', 'Add internal links block']
    ];

    auto.addEventListener('click', function () {
        auto.disabled = true;
        body.innerHTML = '<div class="text-slate-500"><i class="fas fa-spinner fa-spin mr-2"></i>Preparing fix proposals…</div>';
        fetch('{{ route('admin.cms.posts.autofill', $cmsPost->id) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { auto.disabled = false; renderProposal(j.data); })
            .catch(function () {
                auto.disabled = false;
                body.innerHTML = '<div class="text-red-600">Could not prepare proposals.</div>';
            });
    });

    function renderProposal(d) {
        var dupHtml = '';
        if (d.duplicates && d.duplicates.length) {
            dupHtml = '<div class="mb-2 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800"><b>Duplicate titles found:</b> ' +
                d.duplicates.map(function (x) { return '#' + x.id + ' ' + esc(x.title) + ' (' + esc(x.type) + ')'; }).join(' · ') +
                '<br>Applying will give this page a unique slug; review the titles listed.</div>';
        }
        var h = dupHtml + '<table class="mb-2 w-full text-left text-sm"><tbody>';
        CHOICES.forEach(function (c) {
            var key = c[0], label = c[1];
            var p = d[key];
            if (!p) return;
            var cur = typeof p === 'object' ? (p.current ?? '') : '';
            var pro = typeof p === 'object' ? (p.proposed ?? '') : '';
            if (typeof pro === 'string' && pro.length > 140) pro = pro.slice(0, 140) + '…';
            var skip = (String(cur) === String(pro)) && key !== 'author_id' && key !== 'strip_filler' && key !== 'add_internal_links';
            if (key === 'strip_filler' || key === 'add_internal_links') {
                if (!p.count) return;
                cur = 'needed'; pro = p.count + ' fix(es)';
            }
            if (key === 'author_id' && p.proposed && !p.current) { cur = 'none'; pro = 'you'; }
            h += '<tr class="border-b border-slate-100">' +
                '<td class="w-9 py-1.5"><input type="checkbox" class="dp-af" data-key="' + key + '"' + (skip ? '' : ' checked') + (skip ? ' disabled' : '') + '></td>' +
                '<td class="py-1.5"><b>' + esc(label) + '</b>' +
                '<div class="text-xs text-slate-500">current: ' + esc(String(cur) === '' ? '(empty)' : cur) + '</div>' +
                '<div class="text-xs">new: <b>' + esc(String(pro)) + '</b></div>' +
                '</td></tr>';
        });
        h += '</tbody></table>' +
            '<button type="button" id="dpAfApply" class="inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700"><i class="fas fa-check"></i> Apply selected & re-analyze</button> ' +
            '<button type="button" id="dpAfCancel" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">Cancel</button>' +
            '<div class="mt-2 text-xs text-slate-500">Everything is rule-based (no AI): texts are derived from your own title/excerpt/first sentences; the image comes from your content; filler sentences are deleted; a Related-links block is appended.</div>';
        body.innerHTML = h;

        document.getElementById('dpAfCancel').addEventListener('click', function () { location.reload(); });
        document.getElementById('dpAfApply').addEventListener('click', function () {
            var btn = this;
            btn.disabled = true;
            var fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            document.querySelectorAll('.dp-af:checked').forEach(function (cb) { fd.append(cb.getAttribute('data-key'), '1'); });
            fd.append('meta_title', '1'); fd.append('meta_description', '1');
            fetch('{{ route('admin.cms.posts.autofix', $cmsPost->id) }}', {
                method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); }).then(function (j) {
                if (!j.ok) { btn.disabled = false; body.innerHTML = '<div class="text-red-600">Apply failed.</div>'; return; }
                // reflect saved values into the form fields (content stays as typed)
                return fetch('{{ route('admin.cms.posts.autofill', $cmsPost->id) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (pr) {
                        var set = function (id, v) { var el = document.getElementById(id); if (el && v) el.value = v; };
                        set('cms-meta-title', pr.data.meta_title.proposed);
                        set('cms-meta-description', pr.data.meta_description.proposed);
                        set('cms-slug', pr.data.slug.proposed);
                        set('cms-featured-image', pr.data.featured_image.proposed);
                        var applied = '<div class="mb-2 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800"><b>Applied:</b> ' + (j.applied || []).map(esc).join(' · ') +
                            (j.applied.indexOf('Filler sentences removed') > -1 || j.applied.indexOf('Internal links block added') > -1
                                ? '<br><span class="text-xs">Content changed in the database — reload the page after saving to see it in the editor (your unsaved form edits are preserved).</span>' : '') +
                            '</div>';
                        render(j.report);
                        body.insertAdjacentHTML('afterbegin', applied);
                    });
            }).catch(function () { btn.disabled = false; body.innerHTML = '<div class="text-red-600">Apply failed (network).</div>'; });
        });
    }

    run.addEventListener('click', function () {
        run.disabled = true;
        body.innerHTML = '<div class="text-slate-500"><i class="fas fa-spinner fa-spin mr-2"></i>Analyzing fields + rendered page…</div>';
        fetch('{{ route('admin.cms.posts.seo', $cmsPost->id) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { run.disabled = false; render(j.data); })
            .catch(function () {
                run.disabled = false;
                body.innerHTML = '<div class="text-red-600">Analysis failed. Try again.</div>';
            });
    });
})();
</script>
@endpush
