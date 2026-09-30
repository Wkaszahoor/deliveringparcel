/* DeliveringParcel accessibility widget (dp-a11y).
   Text size scales via body zoom because this app's CSS is px-based
   (rem font-size scaling would be a no-op here). Saved in localStorage. */
(function () {
    'use strict';
    var KEY = 'dp_a11y';
    var MIN = 70, MAX = 150, STEP = 10, DEFAULT = 100;

    function load() {
        try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; }
    }
    function save(s) {
        try { localStorage.setItem(KEY, JSON.stringify(s)); } catch (e) { /* private mode */ }
    }

    function apply() {
        var s = load();
        var size = parseInt(s.size, 10) || DEFAULT;
        if (size < MIN) size = MIN;
        if (size > MAX) size = MAX;
        document.body.style.zoom = (size === DEFAULT) ? '' : (size / DEFAULT);

        var root = document.documentElement;
        root.classList.toggle('dp-a11y-underline', !!s.underline);
        root.classList.toggle('dp-a11y-contrast', !!s.contrast);

        var lbl = document.getElementById('dpA11ySize');
        if (lbl) lbl.textContent = 'Text size: ' + size + '%';
        var u = document.getElementById('dpA11yUnderline');
        if (u) u.checked = !!s.underline;
        var c = document.getElementById('dpA11yContrast');
        if (c) c.checked = !!s.contrast;
    }

    function changeSize(delta) {
        var s = load();
        var size = (parseInt(s.size, 10) || DEFAULT) + delta;
        if (size < MIN) size = MIN;
        if (size > MAX) size = MAX;
        s.size = size;
        save(s);
        apply();
    }

    function setFlag(key, value) {
        var s = load();
        if (value) { s[key] = value; } else { delete s[key]; }
        save(s);
        apply();
    }

    function resetAll() {
        try { localStorage.removeItem(KEY); } catch (e) { /* ignore */ }
        apply();
    }

    function setPanel(open) {
        var panel = document.getElementById('dpA11yPanel');
        var btn = document.getElementById('dpA11yBtn');
        if (!panel || !btn) return;
        panel.hidden = !open;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            var first = panel.querySelector('button, input');
            if (first) first.focus();
        }
    }

    function init() {
        apply();

        var btn = document.getElementById('dpA11yBtn');
        var close = document.getElementById('dpA11yClose');
        var panel = document.getElementById('dpA11yPanel');
        var smaller = document.getElementById('dpA11ySmaller');
        var larger = document.getElementById('dpA11yLarger');
        var deflt = document.getElementById('dpA11yDefault');
        var underline = document.getElementById('dpA11yUnderline');
        var contrast = document.getElementById('dpA11yContrast');
        var reset = document.getElementById('dpA11yReset');
        if (!btn || !panel) return;

        btn.addEventListener('click', function () { setPanel(panel.hidden); });
        if (close) close.addEventListener('click', function () { setPanel(false); });
        if (smaller) smaller.addEventListener('click', function () { changeSize(-STEP); });
        if (larger) larger.addEventListener('click', function () { changeSize(+STEP); });
        if (deflt) deflt.addEventListener('click', function () { setFlag('size', false); });
        if (underline) underline.addEventListener('change', function () { setFlag('underline', underline.checked); });
        if (contrast) contrast.addEventListener('change', function () { setFlag('contrast', contrast.checked); });
        if (reset) reset.addEventListener('click', resetAll);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.hidden) setPanel(false);
        });
        document.addEventListener('click', function (e) {
            if (!panel.hidden && !panel.contains(e.target) && !btn.contains(e.target)) setPanel(false);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
