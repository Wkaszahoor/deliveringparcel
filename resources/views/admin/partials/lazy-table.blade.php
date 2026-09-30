{{--
    Generic lazy-loaded responsive table.
    Params:
      $url     : data endpoint (paginated JSON {data,last_page})
      $cols    : [[type, label, key, extraKey?], ...]  type: text|money|badge|img|bool|sub
                 'sub' renders r[key] + <small>r[extraKey]</small>
      $acts    : [[icon, title, urlKey, method?, confirm?], ...]
                 method DELETE/PUT triggers data-dp-confirm when confirm=1
      $filterIds: optional array of element ids wired to sc.reload() (debounced input / change)
      $id      : unique table suffix (default 't')
--}}
@php
    $tid = 'dp-tbody-' . ($id ?? 't');
    $cols = $cols ?? [];
    $acts = $acts ?? [];
    $filterIds = $filterIds ?? [];
@endphp
<div class="dp-table-wrap p-2 p-md-0">
    <table class="table table-hover dp-table dp-card-mobile mb-0">
        <thead>
            <tr>
                @foreach ($cols as $c)<th>{{ $c[1] }}</th>@endforeach
                @if ($acts)<th class="text-right">Actions</th>@endif
            </tr>
        </thead>
        <tbody id="{{ $tid }}"></tbody>
    </table>
</div>

@push('admin_scripts')
<script>
(function () {
    var tid = @json($tid);
    var url = @json($url);
    var cols = @json($cols);
    var acts = @json($acts);
    var filterIds = @json($filterIds);

    function buildUrl() {
        var u = new URL(url);
        filterIds.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            var v = el.value.trim();
            if (v) u.searchParams.set(el.dataset.param || id.replace(/^f-/, ''), v);
        });
        return u.toString();
    }

    var icons = { pen: 'fa-pen', trash: 'fa-trash', eye: 'fa-eye', check: 'fa-check', times: 'fa-times', power: 'fa-power-off', up: 'fa-arrow-up', down: 'fa-arrow-down', star: 'fa-star' };

    var sc = DP.infiniteScroll({
        url: buildUrl(),
        target: '#' + tid,
        render: function (r) {
            var tds = '';
            cols.forEach(function (c) {
                var v = r[c[2]];
                switch (c[0]) {
                    case 'badge':
                        tds += '<td data-label="' + c[1] + '"><span class="dp-badge ' + (r[c[3]] || 'bg-secondary') + ' text-white">' + DP.esc(v) + '</span></td>';
                        break;
                    case 'badge2':
                        tds += '<td data-label="' + c[1] + '"><span class="dp-badge ' + (v ? c[3] : 'bg-secondary') + ' text-white">' + (v ? c[4] : c[5]) + '</span></td>';
                        break;
                    case 'money':
                        tds += '<td data-label="' + c[1] + '">$' + DP.esc(v) + '</td>';
                        break;
                    case 'img':
                        tds += '<td data-label="' + c[1] + '">' + (v ? '<img class="dp-thumb dp-lazy" data-src="' + DP.esc(v) + '">' : '<span class="text-muted">—</span>') + '</td>';
                        break;
                    case 'sub':
                        tds += '<td data-label="' + c[1] + '">' + DP.esc(v) + (r[c[3]] ? '<br><small class="text-muted">' + DP.esc(r[c[3]]) + '</small>' : '') + '</td>';
                        break;
                    default:
                        tds += '<td data-label="' + c[1] + '">' + DP.esc(v === null || v === undefined || v === '' ? '—' : v) + '</td>';
                }
            });
            var actsHtml = '';
            acts.forEach(function (a) {
                var href = r.urls[a[2]];
                if (!href) return;
                if (a[3] && a[3] !== 'GET') {
                    actsHtml += '<a href="#" data-dp-confirm ' + (a[4] ? 'data-title="' + DP.esc(a[1]) + '" ' : '') +
                        'data-url="' + href + '" data-method="' + a[3] + '" class="btn btn-sm btn-outline-' + (a[5] || 'primary') + '" title="' + DP.esc(a[1]) + '"><i class="fas ' + (icons[a[0]] || 'fa-cog') + '"></i></a> ';
                } else {
                    actsHtml += '<a href="' + href + '" class="btn btn-sm btn-outline-' + (a[5] || 'primary') + '" title="' + DP.esc(a[1]) + '"><i class="fas ' + (icons[a[0]] || 'fa-cog') + '"></i></a> ';
                }
            });
            return '<tr>' + tds + (acts.length ? '<td data-label="Actions" class="dp-actions text-right text-nowrap">' + actsHtml + '</td>' : '') + '</tr>';
        }
    });

    filterIds.forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        var ev = (el.tagName === 'SELECT' || el.type === 'date') ? 'change' : 'input';
        var t;
        el.addEventListener(ev, function () {
            clearTimeout(t);
            t = setTimeout(function () { sc.reload(buildUrl()); }, 350);
        });
    });
})();
</script>
@endpush
