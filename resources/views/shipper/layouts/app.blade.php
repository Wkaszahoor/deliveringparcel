<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shipper Portal') | DeliveringParcel</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    {{-- This layout never loaded FontAwesome, flag-icon, app.css, or Alpine —
         every fa-* icon in the portal (bars, camera, arrows, the ones added
         in the registration-form bolder pass) was rendering as nothing, and
         a language switcher couldn't have worked here at all (its dropdown
         needs Alpine's x-data/x-show, which this stack never shipped).
         app-public.js — the CSP-safe build, same one the public site's own
         header uses for this exact component — not app.js (the admin
         panel's eval-capable build), so nothing here crosses that line. --}}
    @vite(['resources/css/app.css', 'resources/js/app-public.js'])
    <link href="{{ asset('frontend/assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/flag-icon-css/css/flag-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home2.css') }}">
    <style>
        .shp-nav{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
        .shp-nav .links a{margin-right:14px;font-size:14px;color:var(--h2-muted);text-decoration:none;font-weight:600}
        .shp-nav .links a.on{color:var(--h2-primary)}
        .shp-nav .links a:hover{color:var(--h2-primary-dark)}
        .shp-wrap{max-width:1100px;margin:0 auto;padding:14px}
        .shp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px}
        .shp-tile{border-radius:12px;padding:16px;color:#fff;min-height:90px}
        .shp-tile .v{font-size:26px;font-weight:800}
        .shp-tile .l{font-size:12px;opacity:.9}
        .shp-msg{border-radius:10px;padding:10px 12px;margin:6px 0;font-size:14px}
        .shp-msg.admin{background:#e7f1ff}
        .shp-msg.shipper{background:#e6f4ea}
        .star{color:#f5b301}
        /* A slim strip above the navy header, switcher at its right edge —
           the same pattern the homepage itself uses (its orders-ticker bar
           sits above the main header with the switcher on its right), not
           an overlay that could collide with the header's own Back/Log in
           or profile-dropdown content sitting in that same corner. */
        .shp-lang-bar { background: #081b38; padding: .4rem 1rem; display: flex; justify-content: flex-end; }
    </style>
</head>
<body>
<div class="shp-lang-bar">@include('fcommon.lang-switcher', ['dpLangVariant' => 'bar'])</div>
<header class="dp-shipper-header @guest shp-header-guest @endguest">
<?php
$dpShipperNav = [
    'dashboard'   => ['url' => route('shipper.dashboard'),   'label' => 'Dashboard',   'active' => request()->is('shipper/dashboard') ? 'active' : ''],
    'requests'    => ['url' => route('shipper.requests.index'), 'label' => 'Requests',    'active' => request()->is('shipper/requests*') && !request()->is('shipper/my-quotes') ? 'active' : ''],
    'assignments' => ['url' => route('shipper.assignments.index'), 'label' => 'Assignments', 'active' => request()->is('shipper/assignments*') ? 'active' : ''],
    'wallet'      => ['url' => route('shipper.wallet'),     'label' => 'Wallet',      'active' => request()->is('shipper/wallet*') ? 'active' : ''],
    'profile'     => ['url' => route('shipper.profile'),    'label' => 'Profile',     'active' => request()->is('shipper/profile*') ? 'active' : ''],
];
try {
    $dpSo = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'shipper_bar_order')->value('value');
    $dpSo = $dpSo ? array_values(array_filter(explode(',', $dpSo))) : [];
} catch (\Throwable $dpE) { $dpSo = []; }
$dpSo = array_values(array_unique(array_merge(
    array_intersect($dpSo, array_keys($dpShipperNav)),
    array_diff(array_keys($dpShipperNav), $dpSo)
)));
// Portal nav (Dashboard/Requests/Assignments/Wallet/Profile/Shop) only makes
// sense once a shipper is actually signed in — every other view on this
// layout requires auth via route middleware, but the registration form
// itself (shipper.register.form) is guest-accessible, so an unauthenticated
// visitor was seeing a full internal nav to pages they can't reach yet.
$dpHeaderLinks = [];
if (auth()->check()) {
    foreach ($dpSo as $dpK) { $dpHeaderLinks[] = $dpShipperNav[$dpK]; }
    $dpHeaderLinks[] = ['url' => url('/dashboard'), 'label' => 'Shop', 'active' => ''];
}
$dpProfileUrl = route('shipper.profile');
$dpGuestLinks = [
    ['url' => url('/'), 'label' => 'Back', 'class' => 'shp-nav-back'],
    ['url' => route('login'), 'label' => 'Log in', 'class' => 'shp-nav-login'],
];
?>
@include('layouts.partials.dp-header')
</header>
<style>
.dp-shipper-header { background: #0d2a52; }
.dp-shipper-header .dp-main-header { background: transparent; }
.dp-shipper-header .dp-header-brand span { color: #fff !important; }
/* dp-header's own <style> paints an admin-theme blue→green gradient border
   under every header regardless of host page — reads as a stray mismatched
   line against navy. A single subtle divider fits this bar instead. */
.dp-shipper-header .dp-main-header { border-bottom: 1px solid rgba(255,255,255,.14) !important; border-image: none !important; }

/* dp-header.blade.php (layouts/partials/dp-header) is shared with the
   Bootstrap/AdminLTE-based admin panel, which is where its .navbar-nav /
   .nav-link / .dropdown-menu classes get their layout and spacing from.
   This portal never loads Bootstrap (only home2.css) and never did — so
   every list here rendered as a bare, bulleted <ul><li>, and the center
   nav links (styled dark-gray for AdminLTE's white header) were nearly
   invisible against the navy override above. The <nav> itself also never
   got Bootstrap's flex layout, so the brand row and the Back/Log in row
   stacked as two separate block-level lines instead of sharing one row —
   that's why Log in landed far from the logo instead of tight beside it.
   Rebuilt as self-contained
   flex/inline rules scoped to .dp-shipper-header only — nothing here
   touches the admin panel's own rendering of the same partial. */
.dp-shipper-header .dp-main-header {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    padding: 0 1rem;
    gap: .5rem;
}
.dp-shipper-header .navbar-nav {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: .15rem;
}
/* ml-auto pushes the right-hand list (profile dropdown once signed in, or
   the guest Back/Log in links) to the far edge — standard behavior, kept
   as the default. */
.dp-shipper-header .navbar-nav.ml-auto { margin-left: auto; }
/* Guest state only (the registration form): Log in sits tight beside the
   logo instead of stranded at the far edge of an otherwise-empty bar —
   signed-in pages keep the profile menu pushed right as normal. */
.dp-shipper-header.shp-header-guest .navbar-nav.ml-auto { margin-left: 0; }
.dp-shipper-header li.nav-item { list-style: none; }
.dp-shipper-header .dp-header-links.nav-item { display: flex; }
.dp-shipper-header .nav-link {
    color: rgba(255,255,255,.75) !important;
    text-decoration: none !important;
    font-weight: 600;
    padding: .55rem .7rem;
    border-radius: 8px;
    transition: color .15s ease, background-color .15s ease;
}
.dp-shipper-header .nav-link:hover { color: #fff !important; background: rgba(255,255,255,.1); }
.dp-shipper-header .nav-link.on,
.dp-shipper-header .nav-link.active { color: #fff !important; }
.dp-shipper-header .dp-header-links .nav-link.on,
.dp-shipper-header .dp-header-links .nav-link.active { border-bottom-color: var(--h2-primary, #0d6efd); }

/* Guest state (registration form): "Log in" is the one action that matters
   here, so it gets the system's own real button at full strength — reused
   from home2.css's .h2-btn-primary, not a new component — while "Back"
   stays a quiet ghost link so the page has one confident accent, not two
   competing ones. */
.dp-shipper-header .shp-nav-back { color: rgba(255,255,255,.6) !important; }
.dp-shipper-header .shp-nav-back:hover { color: #fff !important; background: rgba(255,255,255,.08); }
.dp-shipper-header .shp-nav-login {
    background: var(--h2-primary);
    color: #fff !important;
    padding: .5rem 1.15rem;
    border-radius: 999px;
    font-weight: 800;
    box-shadow: 0 10px 22px -10px rgba(13,110,253,.75);
    transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
}
.dp-shipper-header .shp-nav-login:hover {
    background: var(--h2-primary-dark);
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 14px 28px -10px rgba(13,110,253,.85);
}
</style>

<div class="shp-wrap">
    @if(session('success'))<div class="h2-alert h2-alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="h2-alert" style="background:#e7f1ff;color:#0a58ca;border:1px solid #bcd6f7">{{ session('info') }}</div>@endif
    @if(session('error'))<div class="h2-alert h2-alert-error">{{ session('error') }}</div>@endif
    @yield('content')
</div>

<footer style="text-align:center;color:var(--h2-muted);font-size:13px;padding:24px 0">
    &copy; {{ date('Y') }} DeliveringParcel — Shipper Portal
</footer>
</body>
</html>
