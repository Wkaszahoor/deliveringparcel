{{-- block: cta-strip --}}
<div class="h2-container" style="padding-block:1.25rem">
    <div class="h2-hero" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;padding:1.25rem;border-radius:12px;background:linear-gradient(90deg,var(--h2-accent,#0b5fff),#3f8cff);color:#fff">
        <div>
            <h3 style="margin:0;color:#fff">{{ $widget['title'] ?? 'Ready to ship?' }}</h3>
            <p style="margin:.35rem 0 0;opacity:.9">{{ $widget['text'] ?? 'Get an instant quote and book your delivery today.' }}</p>
        </div>
        <a class="h2-btn" style="background:#fff;color:#0b5fff;padding:.65rem 1.4rem;border-radius:8px;text-decoration:none" href="{{ $widget['url'] ?? url('/services') }}">{{ $widget['button'] ?? 'Get a quote' }}</a>
    </div>
</div>
