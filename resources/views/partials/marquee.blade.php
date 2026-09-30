{{-- Site-wide scrolling announcement bar. Controlled from
     Admin → Settings → Preferences (site_marquee_enabled / site_marquee_text). --}}
@php
    $dpMarqueeOn = \App\Models\Setting::getBool('site_marquee_enabled', false);
    $dpMarqueeText = trim((string) \App\Models\Setting::get('site_marquee_text', ''));
    // Seconds per full loop (admin-controlled; higher = slower). Clamp so a
    // bad value can never produce a broken 0s/negative animation.
    $dpMarqueeSpeed = (float) \App\Models\Setting::get('site_marquee_speed', 26);
    if ($dpMarqueeSpeed < 5) { $dpMarqueeSpeed = 5; }
    if ($dpMarqueeSpeed > 180) { $dpMarqueeSpeed = 180; }
@endphp
@if($dpMarqueeOn && $dpMarqueeText !== '')
<style>
    .dp-marquee{overflow:hidden;background:#b3001b;color:#fff;padding:.45rem 0;font-size:.9rem;font-weight:600;letter-spacing:.02em}
    .dp-marquee-track{display:inline-block;white-space:nowrap;padding-left:100%;animation:dp-marquee-scroll {{ $dpMarqueeSpeed }}s linear infinite}
    @keyframes dp-marquee-scroll{0%{transform:translate(0,0)}100%{transform:translate(-100%,0)}}
    @media (prefers-reduced-motion:reduce){.dp-marquee-track{animation:none;padding-left:0;text-align:center}}
</style>
<div class="dp-marquee" role="status">
    <div class="dp-marquee-track">{{ $dpMarqueeText }} &nbsp;&nbsp;&#9990;&nbsp;&nbsp;{{ $dpMarqueeText }}</div>
</div>
@endif
