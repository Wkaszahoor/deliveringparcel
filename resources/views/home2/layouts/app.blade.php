<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DeliveringParcel') | DeliveringParcel</title>
    <meta name="description" content="@yield('meta_description', 'International parcel forwarding and shipping services.')">
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/home2.css') }}">
    {{-- FontAwesome + flag-icon: this layout never loaded either, so every
         fa-* icon in this portal (and the language switcher's flag/chevron)
         was rendering as nothing. Both are already self-hosted for the
         public site — reused here, not duplicated. --}}
    <link href="{{ asset('frontend/assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/flag-icon-css/css/flag-icon.css') }}">
    <style>
        /* A slim strip above the header, switcher at its right edge — the
           same pattern the homepage itself uses (its orders-ticker bar
           sits above the main header with the switcher on its right), not
           an overlay that could collide with the header's own content. */
        .h2-lang-bar { background: var(--h2-dark); padding: .4rem 1rem; display: flex; justify-content: flex-end; }
    </style>
</head>
<body>
<div class="h2-lang-bar">@include('fcommon.lang-switcher', ['dpLangVariant' => 'bar'])</div>
@include('common.noscript')
@include('partials.marquee')
<header class="h2-header">
    <div class="h2-container h2-nav">
        <a href="{{ url('/') }}" class="h2-brand">
            <img src="{{ asset('images/deliveringlogo.png') }}" alt="DeliveringParcel" height="34">
        </a>
        <button class="h2-burger" id="h2Burger" aria-label="Menu"><span></span><span></span><span></span></button>
        <nav class="h2-menu" id="h2Menu">
            <a href="{{ url('/') }}">Home</a>
            <a href="{{ url('services') }}">Services</a>
            <a href="{{ url('blog') }}">Blog</a>
            <a href="{{ route('testimonials') }}">Testimonials</a>
            <a href="{{ url('track-order') }}">Track Order</a>
            <a href="{{ url('contact-details') }}">Contact</a>
            @auth
                <a href="{{ route('home2.dashboard') }}" class="h2-btn h2-btn-outline">My Account</a>
                <a href="{{ route('home2.wallet') }}" class="h2-btn h2-btn-outline">💳 Wallet</a>
                <form id="h2-logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
                <a href="#" class="h2-btn h2-btn-primary" onclick="event.preventDefault();document.getElementById('h2-logout-form').submit();">Logout ({{ auth()->user()->name }})</a>
            @else
                <a href="{{ url('login') }}" class="h2-btn h2-btn-outline">Login</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    @if (session('success'))
        <div class="h2-container"><div class="h2-alert h2-alert-success">{{ session('success') }}</div></div>
    @endif
    @if (session('error'))
        <div class="h2-container"><div class="h2-alert h2-alert-error">{{ session('error') }}</div></div>
    @endif
    @if (($errors ?? collect())->any())
        <div class="h2-container">
            <div class="h2-alert h2-alert-error">
                <ul class="mb-0">@foreach (($errors ?? collect())->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    {{-- Theme/widget areas (TW-006): admin-managed, sanitized/escaped by renderer contract --}}
    {!! \App\Services\WidgetRenderer::area('home_top') !!}
    @yield('content')
    {!! \App\Services\WidgetRenderer::area('home_bottom') !!}
</main>

<footer class="h2-footer">
    <div class="h2-container h2-footer-grid">
        <div>
            <img src="{{ asset('images/deliveringlogo.png') }}" alt="DeliveringParcel" height="30">
            <p class="muted">International parcel forwarding &amp; shipping.</p>
        </div>
        <div>
            <h6>Quick links</h6>
            <a href="{{ url('services') }}">Services</a>
            <a href="{{ url('blog') }}">Blog</a>
            <a href="{{ route('testimonials') }}">Testimonials</a>
            <a href="{{ url('track-order') }}">Track order</a>
            <a href="{{ url('contact-details') }}">Contact</a>
        </div>
        @auth
        <div>
            <h6>My account</h6>
            <a href="{{ route('home2.dashboard') }}">Dashboard</a>
            <a href="{{ url('dashboard') }}">Orders &amp; payments</a>
            <a href="{{ route('home2.wallet') }}">Wallet</a>
            <a href="{{ route('home2.offers.index') }}">My offers</a>
            <a href="{{ route('home2.returns') }}">Returns</a>
            <a href="{{ route('home2.quotes') }}">My quotes</a>
            <a href="{{ url('logout') }}">Logout</a>
        </div>
        @endauth
    </div>
    <div class="h2-container h2-footer-bottom">© {{ date('Y') }} DeliveringParcel</div>
</footer>

<script>
(function () {
    var b = document.getElementById('h2Burger'), m = document.getElementById('h2Menu');
    if (b) b.addEventListener('click', function () { m.classList.toggle('open'); });
})();
</script>
@stack('scripts')
</body>
</html>
