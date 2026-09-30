{{--
  HOW TO USE ROUTE MANAGER IN ANY BLADE FILE
  ══════════════════════════════════════════
  This is a REFERENCE COMMENT FILE — not rendered, just documents usage.

  $routeLinks is auto-injected into EVERY Blade view by the
  RouteManagerServiceProvider ViewComposer. No controller change needed.

  1. GET ACTIVE URL FOR A LINK:
     <a href="{{ $routeLinks['blog']['url'] ?? '/blog' }}">Blog</a>

  2. CHECK VERSION:
     @if(($routeLinks['client_panel']['version'] ?? 'legacy') === 'legacy')
       {{-- show legacy client panel link --}}
     @endif

  3. SHOW LINK ONLY IF HEADER-ENABLED:
     @if(!empty($routeLinks['services']['show_in_header']))
       <a href="{{ $routeLinks['services']['url'] }}">Services</a>
     @endif

  4. IN MAIN HOMEPAGE (testimonials link example):
     The testimonials section CTA button should use:
     <a href="{{ $routeLinks['testimonials']['url'] ?? '/testimonials' }}">
       Read Reviews
     </a>
     When admin switches testimonials to 'new', this link auto-updates.

  5. HELPER METHOD (alternative to $routeLinks array):
     use App\Models\RouteManagerSetting;
     $url = RouteManagerSetting::activeUrlCached('blog');

  6. BLOG/SERVICES links in homepage sections:
     Replace hardcoded /blog and /services hrefs with:
     href="{{ $routeLinks['blog']['url'] ?? '/blog' }}"
     href="{{ $routeLinks['services']['url'] ?? '/services' }}"

  7. DEBUG BADGE (visible only when APP_DEBUG=true):
     Shows [NEW] or [LEGACY] badge next to link in dev mode.
     @if(config('app.debug'))
       <span class="badge">
         {{ strtoupper($routeLinks['blog']['version'] ?? 'legacy') }}
       </span>
     @endif

  8. READY-MADE PARTIALS (standalone — wiring into layouts is a later decision):
     resources/views/partials/header-nav.blade.php   — header nav with legacy fallback
     resources/views/partials/footer-links.blade.php — footer links with legacy fallback
     @include('partials.header-nav')
     @include('partials.footer-links')

  9. ADMIN UI:
     /admin/route-manager — toggle legacy/new per route, header/footer/nav flags,
     bulk switch per group (skips locked routes). Locked routes (homepage,
     mobile_api) require typing "I CONFIRM" in the edit modal.

  10. MOBILE APPS:
     GET /api/mobile/v1/route-config (public) — returns { ok, routes: { key: { url, version, label } } }.
--}}
