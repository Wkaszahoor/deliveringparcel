{{-- block: stats-row --}}
<div class="h2-container" style="padding-block:1rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;text-align:center">
    @foreach (array_slice($widget, 0, 4) as $label => $value)
        <div class="h2-card"><div class="h2-card-body">
            <div style="font-size:1.6rem;font-weight:700">{{ $value }}</div>
            <div class="muted">{{ $label }}</div>
        </div></div>
    @endforeach
</div>
