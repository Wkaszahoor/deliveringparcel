<!DOCTYPE html>
{{-- Homepage i18n pilot (see App\Http\Middleware\SetLocale and
     resources/views/home.blade.php): `dir="rtl"` only fires for Arabic AND
     only on the homepage itself — other pages have no translated/RTL-
     reviewed content yet, so force-mirroring their layout would do more
     harm than good until they're part of the pilot too. --}}
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' && request()->is('/') ? 'rtl' : 'ltr' }}">

    @include('fcommon.head')

    {{-- dp-has-orders-bar: homepage only — see resources/views/home.blade.php's
         #dp-orders-bar (a fixed strip above the header) and the matching
         `body.dp-has-orders-bar #header` / `#hero` CSS overrides in app.css
         that push the header and hero content down to make room for it.
         Empty string on every other page, so this touches nothing there. --}}
    <body class="{{ request()->is('/') ? 'dp-has-orders-bar' : '' }}">
    <a href="#main" class="dp-skip-link">Skip to main content</a>
    @include('common.noscript')
    @include('common.a11y')

    {{-- ======= Cookie Consent Banner ======= --}}
    <div id="cookieConsentBanner" style="display:none;position:fixed;bottom:0;width:100%;background-color:#000;color:#fff;padding:10px;z-index:1000;text-align:center;">
        <p style="margin:0 0 8px;">
            We use cookies to improve your experience. By continuing, you agree to our use of cookies.
            <a href="{{ route('privacy-policy') }}" style="color:#f0a500;">Learn more</a>.
        </p>
        <button id="acceptCookies" style="background-color:#f0a500;border:none;padding:8px 15px;color:#000;cursor:pointer;margin-right:6px;">Accept</button>
        <button id="rejectCookies" style="background-color:#fff;border:1px solid #f0a500;padding:8px 15px;color:#000;cursor:pointer;">Reject</button>
    </div>

    {{-- ======= Header ======= --}}
    {{-- Homepage i18n pilot (App\Http\Middleware\SetLocale, resources/lang/*.json).
         Only the homepage itself has translated copy so far — the language
         switcher (fcommon.lang-switcher, self-contained) lives here in the
         shared header so it's reachable from every page (a visitor's choice
         persists site-wide via cookie), but selecting a language elsewhere
         won't change that page's text until it's part of the pilot too. --}}
    <header id="header" class="header flex items-center fixed top-0 left-0 right-0 z-[1030]">
        <div class="container relative mx-auto max-w-full flex items-center justify-between px-4">

            <a href="{{ route('/') }}" class="logo flex items-center">
                <img src="{{ url('images/dp.svg') }}" alt="Delivering Parcel Logo" width="40" height="40">
                {{-- Wordmark in the logo's own orange (matches the icon's
                     orange chevron, var(--dp-accent)) rather than plain
                     white — the header's background is always dark, either
                     transparent-over-hero or the sticky rgba(14,29,52,.9)
                     navy (see .header.sticked in main.css), so this reads
                     the same reliably legible way in both states. --}}
                <span class="text-base mb-0 ml-2" style="font-weight:700;color:var(--dp-accent);text-shadow:0 1px 3px rgba(0,0,0,.55), 0 1px 12px rgba(0,0,0,.35);">Delivering Parcel</span>
            </a>

            <i class="mobile-nav-toggle mobile-nav-show fas fa-bars" aria-label="Open navigation" role="button"></i>
            <i class="mobile-nav-toggle mobile-nav-hide d-none fas fa-times" aria-label="Close navigation" role="button"></i>

            <nav id="navbar" class="navbar" aria-label="Main navigation">
                <ul>
                    <li>
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('/') }}">{{ __('Home') }}</a>
                    </li>
                    <li>
                        <a class="nav-link {{ request()->is('services*') ? 'active' : '' }}" href="{{ url('services') }}">{{ __('Services') }}</a>
                    </li>
                    {{-- Locations: country/region shop-and-ship guides. Its own icon-grid
                         panel (.dp-loc-panel, resources/css/app.css) rather than the
                         shared .navbar .dropdown ul list Resources uses below — six
                         countries read better as icon + label than a plain text list.
                         Routes stay at /destinations/* (unchanged, not user-facing);
                         only the visible trigger label reads "Locations". --}}
                    <li class="dropdown">
                        <a href="#" class="nav-link {{ request()->is('destinations*') ? 'active' : '' }}">
                            <span>{{ __('Locations') }}</span> <i class="fas fa-chevron-down dp-nav-caret" aria-hidden="true"></i>
                        </a>
                        <div class="dp-loc-panel">
                            <p class="dp-loc-panel-label">{{ __('Shop and ship worldwide') }}</p>
                            <div class="dp-loc-grid">
                                <a class="dp-loc-item {{ request()->is('destinations/asia') ? 'active' : '' }}" href="{{ route('destinations.asia') }}">
                                    <span class="dp-loc-icon dp-loc-icon-accent"><i class="fas fa-earth-asia" aria-hidden="true"></i></span>
                                    {{ __('Asia') }}
                                </a>
                                <a class="dp-loc-item {{ request()->is('destinations/australia') ? 'active' : '' }}" href="{{ route('destinations.australia') }}">
                                    <span class="dp-loc-icon dp-loc-icon-cyan"><i class="fas fa-earth-oceania" aria-hidden="true"></i></span>
                                    {{ __('Australia') }}
                                </a>
                                <a class="dp-loc-item {{ request()->is('destinations/europe') ? 'active' : '' }}" href="{{ route('destinations.europe') }}">
                                    <span class="dp-loc-icon dp-loc-icon-flag"><span class="flag-icon flag-icon-eu flag-icon-squared" aria-hidden="true"></span></span>
                                    {{ __('Europe') }}
                                </a>
                                <a class="dp-loc-item {{ request()->is('destinations/middle-east') ? 'active' : '' }}" href="{{ route('destinations.middle-east') }}">
                                    <span class="dp-loc-icon dp-loc-icon-cyan"><i class="fas fa-mosque" aria-hidden="true"></i></span>
                                    {{ __('Middle East') }}
                                </a>
                                <a class="dp-loc-item {{ request()->is('destinations/south-america') ? 'active' : '' }}" href="{{ route('destinations.south-america') }}">
                                    <span class="dp-loc-icon dp-loc-icon-flag"><span class="flag-icon flag-icon-br flag-icon-squared" aria-hidden="true"></span></span>
                                    {{ __('South America') }}
                                </a>
                                <a class="dp-loc-item {{ request()->is('destinations/usa') ? 'active' : '' }}" href="{{ route('destinations.usa') }}">
                                    <span class="dp-loc-icon dp-loc-icon-cyan"><i class="fas fa-flag-usa" aria-hidden="true"></i></span>
                                    {{ __('USA') }}
                                </a>
                            </div>
                        </div>
                    </li>
                    {{-- Resources: groups Blog + Testimonials under the theme's own
                         native dropdown (.navbar .dropdown, main.css/main.js) — hover
                         reveals it on desktop, tap toggles .dropdown-active on mobile. --}}
                    <li class="dropdown">
                        <a href="#" class="nav-link {{ request()->is('blog*') || request()->is('testimonials*') ? 'active' : '' }}">
                            <span>{{ __('Resources') }}</span> <i class="fas fa-chevron-down dp-nav-caret" aria-hidden="true"></i>
                        </a>
                        <ul>
                            <li><a class="{{ request()->is('blog*') ? 'active' : '' }}" href="{{ url('blog') }}">{{ __('Blog') }}</a></li>
                            <li><a class="{{ request()->is('testimonials*') ? 'active' : '' }}" href="{{ route('testimonials') }}">{{ __('Testimonials') }}</a></li>
                        </ul>
                    </li>
                    <li>
                        <a class="nav-link {{ request()->is('track-order*') ? 'active' : '' }}" href="{{ url('track-order') }}">{{ __('Track Order') }}</a>
                    </li>
                    <li>
                        <a class="nav-link {{ request()->is('contact-details*') ? 'active' : '' }}" href="{{ route('contact-details') }}">{{ __('Contact us') }}</a>
                    </li>
                @foreach (app(\App\Services\Cms\CmsNavService::class)->getMenu('header_top') as $topItem)
                    <li><a class="nav-link" href="{{ url($topItem->url) }}">{{ $topItem->label }}</a></li>
                @endforeach
                    <li class="nav-item-login">
                        @if(Auth::check())
                            @if(Auth::user()->roles[0]->slug == 'admin')
                                <a class="nav-link" href="{{ route('admin-orders') }}">
                                    <i class="fa fa-microchip" aria-hidden="true"></i> {{ __('Dashboard') }}
                                </a>
                            @else
                                <a class="nav-link" href="{{ route('dashboard') }}">
                                    <i class="fa fa-microchip" aria-hidden="true"></i> {{ __('Dashboard') }}
                                </a>
                            @endif
                        @else
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login / Register') }}</a>
                        @endif
                    </li>
                    <li class="nav-item-cta">
                        <a class="get-a-quote" href="/request">{{ __('Get a Quote') }}</a>
                    </li>
                    @unless (request()->is('/'))
                    <li class="nav-item-lang">
                        @include('fcommon.lang-switcher')
                    </li>
                    @endunless
                </ul>
            </nav>

            {{-- Desktop-only actions, pinned right of the centered floating nav pill.
                 Duplicated (not moved) so the mobile off-canvas menu above still
                 carries Login/Get a Quote via the same #navbar a click-to-close JS. --}}
            <div class="header-actions hidden items-center gap-3 xl:flex">
                @unless (request()->is('/'))
                    @include('fcommon.lang-switcher')
                @endunless
                @if(Auth::check())
                    <a class="header-action-link" href="{{ route(Auth::user()->roles[0]->slug == 'admin' ? 'admin-orders' : 'dashboard') }}">
                        <i class="fa fa-microchip" aria-hidden="true"></i> {{ __('Dashboard') }}
                    </a>
                @else
                    <a class="header-action-link" href="{{ route('login') }}">{{ __('Login / Register') }}</a>
                @endif
                <a class="get-a-quote-btn" href="/request">{{ __('Get a Quote') }} <i class="fas fa-arrow-right"></i></a>
            </div>

        </div>
    </header>

    {{-- ======= Page Content ======= --}}
    <main id="main">
        @yield('content')
    </main>

    {{-- ======= Footer (contains all vendor JS + main.js already) ======= --}}
    @include('fcommon.footer')

    {{-- ================================================================
         WHATSAPP WIDGET — deferred until first user interaction
         footer.blade.php already loads main.js — no vendor JS here.
    ================================================================ --}}
    <script>
        (function () {
            var _loaded = false;

            var _options = {
                "enabled": true,
                "chatButtonSetting": {
                    "backgroundColor": "#4dc247",
                    "ctaText": "",
                    "borderRadius": "25",
                    "marginLeft": "0",
                    "marginBottom": "96",
                    "marginRight": "50",
                    "position": "right"
                },
                "brandSetting": {
                    "brandName": "Delivering Parcel",
                    "brandSubTitle": "Typically replies within a day",
                    "brandImg": "{{ url('images/dp.svg') }}",
                    "welcomeText": "How can we help you?",
                    "messageText": "Hello, I have a question about https://deliveringparcel.com",
                    "backgroundColor": "#0a2f9e",
                    "ctaText": "Start Chat",
                    "borderRadius": "25",
                    "autoShow": false,
                    "phoneNumber": "+34682556521"
                }
            };

            function _loadWhatsApp() {
                if (_loaded) return;
                _loaded = true;
                var s = document.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.src = 'https://wati-integration-service.clare.ai/ShopifyWidget/shopifyWidget.js?28150';
                s.onload = function () { CreateWhatsappChatWidget(_options); };
                document.head.appendChild(s);
            }

            window.addEventListener('scroll',     _loadWhatsApp, { once: true, passive: true });
            window.addEventListener('mousemove',  _loadWhatsApp, { once: true, passive: true });
            window.addEventListener('touchstart', _loadWhatsApp, { once: true, passive: true });
            setTimeout(_loadWhatsApp, 5000);
        })();
    </script>

    {{-- ================================================================
         COOKIE CONSENT
    ================================================================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var banner    = document.getElementById('cookieConsentBanner');
            var acceptBtn = document.getElementById('acceptCookies');
            var rejectBtn = document.getElementById('rejectCookies');

            if (!localStorage.getItem('cookieConsent')) {
                setTimeout(function () { banner.style.display = 'block'; }, 1500);
            }

            acceptBtn.addEventListener('click', function () {
                localStorage.setItem('cookieConsent', 'accepted');
                banner.style.display = 'none';
                if (typeof gtag === 'function') {
                    gtag('consent', 'update', {
                        'analytics_storage': 'granted',
                        'ad_storage': 'granted'
                    });
                }
            });

            rejectBtn.addEventListener('click', function () {
                localStorage.setItem('cookieConsent', 'rejected');
                banner.style.display = 'none';
            });
        });
    </script>

    @stack('scripts')

    </body>
</html>