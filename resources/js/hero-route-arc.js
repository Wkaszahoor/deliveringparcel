// Hero "live route arc" — a purely decorative Canvas2D overlay on top of the
// static hero-shopper-globe.png illustration (resources/views/home.blade.php,
// #dp-hero-route-stage). A glowing parcel travels a bowed arc from one of the
// four continent groupings used elsewhere on this page ("Parcel forwarding
// across four continents") to the shopper's laptop, idle-cycling through all
// four until the visitor actually picks a country in the "ship from" console
// (dpShipFromPicker in app-public.js dispatches `dp-shipfrom-selected` on
// window), at which point it locks onto whichever continent that country
// belongs to.
//
// Plain vanilla JS, not an Alpine component: @alpinejs/csp's expression
// parser only exists to sandbox strings written inline in Blade markup — it
// has no bearing on ordinary functions in a real script file, so there's no
// restriction here beyond what Alpine's own directives would allow.
//
// Anchor positions are eyeballed against the actual artwork (each sits near
// the existing store-logo cluster it represents) rather than real
// cartographic coordinates — the arc is a stylized flourish on an already
// stylized illustration, not a map.
const ANCHORS = {
    europe: { x: 0.25, y: 0.20 },
    'north-america': { x: 0.52, y: 0.20 },
    asia: { x: 0.63, y: 0.30 },
    oceania: { x: 0.70, y: 0.55 },
    worldwide: { x: 0.22, y: 0.45 },
};
const IDLE_ORDER = ['europe', 'north-america', 'asia', 'oceania', 'worldwide'];
const DESTINATION = { x: 0.49, y: 0.60 };
const LAP_MS = 2600;
const BOW_RATIO = 0.16;

function easeInOutCubic(t) {
    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

function quadPoint(p0, p1, p2, t) {
    const mt = 1 - t;
    return {
        x: mt * mt * p0.x + 2 * mt * t * p1.x + t * t * p2.x,
        y: mt * mt * p0.y + 2 * mt * t * p1.y + t * t * p2.y,
    };
}

class HeroRouteArc {
    constructor(canvas, stage) {
        this.canvas = canvas;
        this.stage = stage;
        this.ctx = canvas.getContext('2d');
        if (!this.ctx) return;

        let regionMap = {};
        try {
            regionMap = JSON.parse(stage.dataset.regionMap || '{}');
        } catch (e) {
            regionMap = {};
        }
        this.regionMap = regionMap;

        const style = getComputedStyle(document.documentElement);
        this.accent = style.getPropertyValue('--dp-accent').trim() || '#f5951e';
        this.cyan = style.getPropertyValue('--dp-cyan').trim() || '#29abe2';

        this.dpr = Math.min(window.devicePixelRatio || 1, 2);
        this.width = 0;
        this.height = 0;
        this.idleIndex = 0;
        this.locked = false;
        this.activeKey = IDLE_ORDER[0];
        this.lapStart = performance.now();
        this.running = false;
        this.raf = null;

        this.resize();
        this.resizeObserver = new ResizeObserver(() => this.resize());
        this.resizeObserver.observe(stage);

        this.intersectionObserver = new IntersectionObserver(
            (entries) => {
                const visible = entries.some((e) => e.isIntersecting);
                if (visible) this.play();
                else this.pause();
            },
            { threshold: 0.1 }
        );
        this.intersectionObserver.observe(stage);

        window.addEventListener('dp-shipfrom-selected', (e) => {
            const name = e.detail && e.detail.name;
            const key = (name && this.regionMap[name]) || 'worldwide';
            this.locked = true;
            this.activeKey = key;
            this.lapStart = performance.now();
        });

        this.tick = this.tick.bind(this);
    }

    resize() {
        const rect = this.stage.getBoundingClientRect();
        this.width = rect.width;
        this.height = rect.height;
        this.canvas.width = Math.max(1, Math.round(this.width * this.dpr));
        this.canvas.height = Math.max(1, Math.round(this.height * this.dpr));
        this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0);
    }

    play() {
        if (this.running) return;
        this.running = true;
        this.lapStart = performance.now();
        this.raf = requestAnimationFrame(this.tick);
    }

    pause() {
        this.running = false;
        if (this.raf) cancelAnimationFrame(this.raf);
        this.raf = null;
    }

    tick(now) {
        if (!this.running) return;
        const elapsed = now - this.lapStart;
        let t = elapsed / LAP_MS;

        if (t >= 1) {
            t = 0;
            this.lapStart = now;
            if (!this.locked) {
                this.idleIndex = (this.idleIndex + 1) % IDLE_ORDER.length;
                this.activeKey = IDLE_ORDER[this.idleIndex];
            }
        }

        this.draw(t);
        this.raf = requestAnimationFrame(this.tick);
    }

    draw(t) {
        const ctx = this.ctx;
        const w = this.width;
        const h = this.height;
        ctx.clearRect(0, 0, w, h);
        if (w === 0 || h === 0) return;

        const anchor = ANCHORS[this.activeKey] || ANCHORS.worldwide;
        const p0 = { x: anchor.x * w, y: anchor.y * h };
        const p2 = { x: DESTINATION.x * w, y: DESTINATION.y * h };
        const mid = { x: (p0.x + p2.x) / 2, y: (p0.y + p2.y) / 2 - h * BOW_RATIO };

        // Guide path — a faint, always-visible cyan thread; the parcel is the
        // bright, moving part, this just shows where it travels.
        ctx.save();
        ctx.setLineDash([6, 7]);
        ctx.lineWidth = 1.5;
        ctx.strokeStyle = this.hexToRgba(this.cyan, 0.35);
        ctx.beginPath();
        ctx.moveTo(p0.x, p0.y);
        ctx.quadraticCurveTo(mid.x, mid.y, p2.x, p2.y);
        ctx.stroke();
        ctx.restore();

        const eased = easeInOutCubic(Math.min(Math.max(t, 0), 1));
        const pos = quadPoint(p0, mid, p2, eased);

        // Fade the travelling dot in/out at the very start/end of the lap so
        // it never appears to snap between anchors.
        const fadeWindow = 0.06;
        let alpha = 1;
        if (t < fadeWindow) alpha = t / fadeWindow;
        else if (t > 1 - fadeWindow) alpha = (1 - t) / fadeWindow;

        this.drawPing(p0.x, p0.y, t, 0);
        this.drawPing(p2.x, p2.y, t, 1);

        ctx.save();
        ctx.globalAlpha = alpha;
        const glow = ctx.createRadialGradient(pos.x, pos.y, 0, pos.x, pos.y, 12);
        glow.addColorStop(0, this.hexToRgba(this.accent, 0.9));
        glow.addColorStop(1, this.hexToRgba(this.accent, 0));
        ctx.fillStyle = glow;
        ctx.beginPath();
        ctx.arc(pos.x, pos.y, 12, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = this.accent;
        ctx.beginPath();
        ctx.arc(pos.x, pos.y, 3.5, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    // A short expanding ring right as the parcel departs (t≈0) or arrives (t≈1).
    drawPing(x, y, t, edge) {
        const window_ = 0.18;
        const localT = edge === 0 ? t / window_ : (1 - t) / window_;
        if (localT < 0 || localT > 1) return;
        const ctx = this.ctx;
        const radius = 4 + localT * 10;
        const alpha = (1 - localT) * 0.6;
        ctx.save();
        ctx.globalAlpha = alpha;
        ctx.strokeStyle = this.cyan;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(x, y, radius, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();
    }

    hexToRgba(hex, alpha) {
        const clean = hex.replace('#', '');
        const bigint = parseInt(clean.length === 3
            ? clean.split('').map((c) => c + c).join('')
            : clean, 16);
        const r = (bigint >> 16) & 255;
        const g = (bigint >> 8) & 255;
        const b = bigint & 255;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
}

export function initHeroRouteArc() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const stage = document.getElementById('dp-hero-route-stage');
    if (!stage) return;
    const canvas = stage.querySelector('.dp-route-canvas');
    if (!canvas || !canvas.getContext) return;
    if (typeof ResizeObserver === 'undefined' || typeof IntersectionObserver === 'undefined') return;

    new HeroRouteArc(canvas, stage);
}
