/**
 * Public-site Alpine entry — deliberately separate from resources/js/app.js.
 *
 * The admin dashboard's inline x-data blocks (orders, settings, menus, modals)
 * still rely on standard Alpine's full expression evaluator (arrow functions,
 * getters, etc. parsed via `new Function`), which is exactly what trips a
 * "Content Security Policy blocks eval" warning under any CSP that forbids
 * 'unsafe-eval' (e.g. a browser extension enforcing its own policy). Rather
 * than risk the admin panel's non-trivial x-data usage on a sitewide swap,
 * only the public site (fcommon/head.blade.php) loads this bundle, built on
 * Alpine's official CSP-safe runtime (@alpinejs/csp) instead of `alpinejs`.
 *
 * @alpinejs/csp never calls eval/new Function — but its inline directive
 * expressions (x-data="...", x-show="...", @click="...") are parsed with a
 * restricted, safe-subset parser: no arrow functions, destructuring, template
 * literals, or bare global access (window/document/console/Math, etc.) inside
 * those HTML attribute strings. Simple property access, comparisons,
 * ternaries, assignments, and calling a method already defined on the
 * component all still work fine inline.
 *
 * The fix for anything more complex isn't to avoid Alpine — it's to move that
 * logic out of the attribute string and into a registered Alpine.data()
 * factory below, which is ordinary, unrestricted JavaScript executed
 * directly by the browser (never parsed as a string), then reference it from
 * the markup with a bare `x-data="componentName"`.
 */
import Alpine from '@alpinejs/csp';
import { initHeroRouteArc } from './hero-route-arc.js';

window.Alpine = Alpine;

// Hero "shop anywhere" form's no-JS-fallback validation. @alpinejs/csp can't
// parse an inline `if (...) {...} else {...}` statement in @submit (its
// parser only accepts one expression, see dpShipFromPicker below), so the
// branch lives here instead.
Alpine.data('dpHeroForm', () => ({
    tried: false,
    trySubmit(event) {
        if (!event.target.shipfrom.value) {
            event.preventDefault();
            this.tried = true;
        } else {
            this.tried = false;
        }
    },
}));

// Hero "ship from" country picker (home.blade.php). Countries are supplied
// via a `data-countries` JSON attribute rather than as an x-data() call
// argument, since a call expression in the x-data string itself is outside
// the CSP build's documented-safe pattern (a bare component-name reference).
Alpine.data('dpShipFromPicker', () => ({
    open: false,
    query: '',
    selected: '',
    countries: [],
    init() {
        try {
            this.countries = JSON.parse(this.$el.dataset.countries || '[]');
        } catch (e) {
            this.countries = [];
        }
    },
    get filtered() {
        const q = this.query.trim().toLowerCase();
        return q === '' ? this.countries : this.countries.filter((c) => c.name.toLowerCase().includes(q));
    },
    get selectedFlag() {
        const c = this.countries.find((c) => c.name === this.selected);
        return c ? c.code : '';
    },
    // @alpinejs/csp's expression parser accepts one expression per attribute
    // (plus an optional trailing semicolon), not a statement sequence — so
    // `@click="selected = c.name; open = false; query = ''"` throws a parse
    // error and silently no-ops. Multi-step interactions have to be plain
    // methods instead.
    close() {
        this.open = false;
        this.query = '';
    },
    choose(country) {
        this.selected = country.name;
        this.close();
        // Lets the hero's live route-arc canvas (hero-route-arc.js) lock
        // onto this country's continent instead of idle-cycling through all
        // four — decoupled via a DOM event rather than a direct import so
        // the picker has no dependency on the canvas overlay existing.
        window.dispatchEvent(new CustomEvent('dp-shipfrom-selected', { detail: { name: country.name } }));
    },
}));

// Homepage FAQ accordion. Content stays authored in Blade/PHP (single-sourced
// with the page's FAQPage JSON-LD block) and is handed over the same way, via
// a data attribute parsed on init.
Alpine.data('dpFaqAccordion', () => ({
    open: 1,
    faqs: [],
    init() {
        try {
            this.faqs = JSON.parse(this.$el.dataset.faqs || '[]');
        } catch (e) {
            this.faqs = [];
        }
    },
}));

// Testimonials slider (home.blade.php). No server data needed, so this one
// takes no arguments at all — just behavior.
Alpine.data('dpTestimonialSlider', () => ({
    paused: false,
    advance(dir) {
        const el = this.$refs.dpTestimonialTrack;
        el.scrollBy({ left: dir * el.clientWidth * 0.9, behavior: 'smooth' });
    },
    init() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        setInterval(() => {
            if (this.paused) return;
            const el = this.$refs.dpTestimonialTrack;
            const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4;
            el.scrollTo({ left: atEnd ? 0 : el.scrollLeft + el.clientWidth * 0.9, behavior: 'smooth' });
        }, 5500);
    },
}));

// "Six steps from cart to your door" live status ticker. Cycles the current
// step between In Progress -> Done, then advances to the next step, looping
// forever. Step titles/icons come from a data attribute (see dpFaqAccordion
// above for why); `current`/`phase` are also read by :class bindings on the
// matching step node/block elsewhere in the section to highlight it in sync.
//
// Deliberately NOT gated behind prefers-reduced-motion, unlike this file's
// other animations: those are pure decoration, but this interval is what
// actually advances the ticker's content (which step is current). Gating it
// the same way froze the whole thing on step 1/"In Progress" forever for any
// visitor (or browser/OS default) with reduced motion set — the pulsing dot
// and scale-up bump are the decorative part, and those already have their
// own reduced-motion handling in app.css (.dp-ticker-dot, .dp-step-ticker-*).
Alpine.data('dpStepsTicker', () => ({
    steps: [],
    current: 0,
    phase: 'progress',
    init() {
        try {
            this.steps = JSON.parse(this.$el.dataset.steps || '[]');
        } catch (e) {
            this.steps = [];
        }
        setInterval(() => {
            if (this.phase === 'progress') {
                this.phase = 'done';
            } else {
                this.phase = 'progress';
                this.current = (this.current + 1) % this.steps.length;
            }
        }, 1800);
    },
    get currentStep() {
        return this.steps[this.current] || {};
    },
}));

// /services catalog: category filter, search, and FAQ accordion. No server
// data dependency, so it takes no arguments either.
Alpine.data('dpServicesPage', () => ({
    activeCategory: 'all',
    searchQuery: '',
    openFaq: null,
    matches(category, title, excerpt) {
        const matchesCat = this.activeCategory === 'all' || this.activeCategory === category;
        if (!this.searchQuery.trim()) return matchesCat;
        const q = this.searchQuery.toLowerCase();
        const text = (title + ' ' + excerpt).toLowerCase();
        return matchesCat && text.includes(q);
    },
}));

// /request multi-step form (specialrequest.blade.php). Purely a display/
// navigation layer over the existing, untouched jQuery totals logic and
// native HTML5 validation — this component never reads or writes a field
// value itself, so nothing about how totals are computed or the form is
// submitted changes.
Alpine.data('dpRequestWizard', () => ({
    step: 1,
    totalSteps: 4,
    // Advancing re-validates only the step being left — via each field's own
    // native constraint validation (:invalid), not a hand-rolled rule set —
    // so a step with an empty required field (or an invalid email, etc.)
    // blocks Next and shows the browser's own inline error, exactly like a
    // normal submit attempt would.
    next() {
        const current = this.$refs['dpStep' + this.step];
        if (current) {
            const invalid = current.querySelector(':invalid');
            if (invalid) {
                invalid.reportValidity();
                invalid.focus({ preventScroll: true });
                invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }
        if (this.step < this.totalSteps) this.step++;
        this.scrollToTop();
    },
    back() {
        if (this.step > 1) this.step--;
        this.scrollToTop();
    },
    // Only lets a visited step be re-opened, not skipped ahead to — Next is
    // still the only way forward, so a later step is never reachable with an
    // earlier one left invalid.
    goTo(n) {
        if (n < this.step) {
            this.step = n;
            this.scrollToTop();
        }
    },
    scrollToTop() {
        const el = this.$refs.dpWizardTop;
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
}));

Alpine.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeroRouteArc);
} else {
    initHeroRouteArc();
}
