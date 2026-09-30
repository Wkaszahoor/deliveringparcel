{{-- Route Manager: footer links partial (standalone — not yet wired into any layout). --}}
<div class="footer-links">
    @if(!empty($routeLinks))
        @foreach($routeLinks as $key => $link)
            @if(!empty($link['show_in_footer']))
                <a href="{{ $link['url'] }}" class="footer-link">{{ $link['label'] }}</a>
            @endif
        @endforeach
    @else
        {{-- FALLBACK: hardcoded legacy — shown when table not yet migrated / composer empty --}}
        <a href="/services" class="footer-link">Services</a>
        <a href="/testimonials" class="footer-link">Testimonials</a>
        <a href="/tracking" class="footer-link">Track Order</a>
        <a href="/contact" class="footer-link">Contact</a>
        <a href="/about" class="footer-link">About</a>
    @endif
</div>
