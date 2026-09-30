/**
 * DeliveringParcel Admin — shared lazy-load & UX helpers (v2).
 * Exposes window.DP. Loaded by admin/layouts/app.blade.php.
 */
(function (window, document) {
    'use strict';

    var DP = window.DP || {};

    /* ---------- HTML escape (use in every JS row template!) ---------- */
    DP.esc = function (s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        });
    };

    /* ---------- Lazy images ---------- */
    DP.lazyImages = function (root) {
        var imgs = (root || document).querySelectorAll('img.dp-lazy:not(.dp-loaded):not([data-src=""])');
        if (!('IntersectionObserver' in window)) {
            Array.prototype.forEach.call(imgs, loadImg);
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { loadImg(e.target); io.unobserve(e.target); }
            });
        }, { rootMargin: '150px 0px' });
        Array.prototype.forEach.call(imgs, function (img) {
            if (img.dataset.dpBound) return;
            img.dataset.dpBound = '1';
            io.observe(img);
        });
    };

    function loadImg(img) {
        if (img.dataset.src) {
            img.addEventListener('load', function () { img.classList.add('dp-loaded'); }, { once: true });
            img.src = img.dataset.src;
        } else {
            img.classList.add('dp-loaded');
        }
    }

    /* ---------- Infinite scroll v2 (supports .reload(url) for filters) ----------
       var sc = DP.infiniteScroll({ url, target: '#tbody', render: function (item) { return '<tr>…</tr>'; } });
       sc.reload(newUrl); // clears + refetches page 1                          */
    DP.infiniteScroll = function (opts) {
        var target = document.querySelector(opts.target);
        var noop = { reload: function () {}, load: function () {} };
        if (!target) return noop;

        var state = { page: 1, loading: false, done: false, url: opts.url };

        var sentinel = document.createElement('div');
        sentinel.className = 'dp-scroll-sentinel';
        sentinel.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';
        (target.parentElement || document.body).appendChild(sentinel);

        function fetchPage() {
            if (state.loading || state.done) return;
            state.loading = true;
            sentinel.style.display = 'flex';
            var url = state.url + (state.url.indexOf('?') > -1 ? '&' : '?') + 'page=' + state.page;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    var items = (res && res.data) || [];
                    items.forEach(function (item) {
                        target.insertAdjacentHTML('beforeend', opts.render(item));
                    });
                    DP.lazyImages(target);
                    if (state.page >= (res && res.last_page || 1) || items.length === 0) {
                        state.done = true;
                        sentinel.innerHTML = '<span class="text-muted small">No more records</span>';
                    }
                    state.page++;
                    state.loading = false;
                })
                .catch(function () {
                    state.loading = false;
                    sentinel.style.display = 'none';
                });
        }

        function reload(url) {
            state.url = url;
            state.page = 1;
            state.done = false;
            target.innerHTML = '';
            sentinel.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';
            fetchPage();
        }

        if (!('IntersectionObserver' in window)) { fetchPage(); return { reload: reload, load: fetchPage }; }
        var io = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) fetchPage();
        }, { rootMargin: '300px 0px' });
        io.observe(sentinel);

        return { reload: reload, load: fetchPage };
    };

    /* ---------- CSRF-protected fetch helper ---------- */
    DP.request = function (url, method, body) {
        var headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        };
        var payload;
        if (body instanceof FormData) {
            payload = body;
        } else if (body) {
            headers['Content-Type'] = 'application/json';
            payload = JSON.stringify(body);
        }
        return fetch(url, { method: method || 'POST', headers: headers, body: payload, credentials: 'same-origin' });
    };

    /* ---------- Buttons: loading state ---------- */
    DP.btnLoading = function (btn, on) {
        if (!btn) return;
        if (on) {
            btn.dataset.origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Please wait…';
        } else {
            btn.disabled = false;
            if (btn.dataset.origHtml) btn.innerHTML = btn.dataset.origHtml;
        }
    };

    /* ---------- SweetAlert2 confirm (DELETE etc.) ---------- */
    DP.bindConfirms = function (root) {
        (root || document).querySelectorAll('[data-dp-confirm]').forEach(function (el) {
            if (el.dataset.dpBound) return;
            el.dataset.dpBound = '1';
            el.addEventListener('click', function (ev) {
                ev.preventDefault();
                Swal.fire({
                    title: el.dataset.title || 'Are you sure?',
                    text: el.dataset.text || 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: el.dataset.confirmText || 'Yes, proceed'
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    if (el.dataset.url) {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = el.dataset.url;
                        form.innerHTML = '<input type="hidden" name="_token" value="' +
                            document.querySelector('meta[name=csrf-token]').content + '">' +
                            '<input type="hidden" name="_method" value="' + (el.dataset.method || 'DELETE') + '">';
                        // data-params (JSON object) → extra hidden inputs, e.g.
                        // data-params='{"approve": true}' sends approve=1.
                        if (el.dataset.params) {
                            try {
                                var params = JSON.parse(el.dataset.params);
                                if (params && typeof params === 'object') {
                                    Object.keys(params).forEach(function (key) {
                                        // never allow overriding _token/_method
                                        if (key.charAt(0) === '_' || params[key] === undefined) return;
                                        var input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = key;
                                        // booleans as 1/0 — Laravel's boolean rule
                                        // accepts '1'/'0' but not 'true'/'false' strings
                                        input.value = params[key] === true ? '1'
                                            : (params[key] === false ? '0'
                                                : (params[key] === null ? '' : String(params[key])));
                                        form.appendChild(input);
                                    });
                                }
                            } catch (e) { /* malformed JSON → submit without extra params */ }
                        }
                        document.body.appendChild(form);
                        form.submit();
                    } else if (el.tagName === 'FORM') {
                        el.submit();
                    } else {
                        // Confirm button INSIDE a form (e.g. Set password):
                        // no data-url — submit the enclosing form instead.
                        // requestSubmit keeps HTML5 validation (required/minlength);
                        // fall back where unsupported.
                        var f = el.closest('form');
                        if (f) { if (f.requestSubmit) { f.requestSubmit(el); } else { f.submit(); } }
                    }
                });
            });
        });
    };

    /* ---------- Toastr ---------- */
    DP.toast = {
        success: function (m) { toastr.success(m); },
        error:   function (m) { toastr.error(m); },
        info:    function (m) { toastr.info(m); }
    };

    /* ---------- Auto flash messages ---------- */
    function flashToasts() {
        var f = document.getElementById('dp-flash');
        if (!f) return;
        try {
            var data = JSON.parse(f.dataset.flash || '{}');
            Object.keys(data).forEach(function (key) {
                if (data[key]) (DP.toast[key] || DP.toast.info)(String(data[key]).slice(0, 200));
            });
        } catch (e) { /* noop */ }
    }

    /* ---------- Auto-init ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        DP.lazyImages();
        DP.bindConfirms();
        flashToasts();

        var pre = document.getElementById('dpPreloader');
        if (pre) setTimeout(function () { pre.classList.add('dp-hide'); }, 150);

        var toggle = document.querySelector('.dp-sidebar-toggle');
        if (toggle) toggle.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('dp-sidebar-open');
        });
        var content = document.querySelector('.content-wrapper');
        if (content) content.addEventListener('click', function () {
            if (window.innerWidth < 992 && document.body.classList.contains('dp-sidebar-open')) {
                document.body.classList.remove('dp-sidebar-open');
            }
        });
    });

    window.DP = DP;
})(window, document);
