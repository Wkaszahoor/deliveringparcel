<head>
@php
    // og:title/og:description used to default to '' whenever a view (e.g. every
    // CMS-published service/page/blog post) set 'title'/'meta_description' but
    // not the separate 'og_title'/'og_description' sections — every dashboard-
    // published page shared one blank social-preview card. Falling back to the
    // already-resolved title/description instead means any page that sets
    // those two gets a correct, page-specific preview for free.
    $dpTitle = trim(strip_tags($__env->yieldContent('title', 'Delivering Parcel')));
    $dpMetaDescription = trim(strip_tags($__env->yieldContent('meta_description', '')));
    $dpOgTitle = trim(strip_tags($__env->yieldContent('og_title', ''))) ?: $dpTitle;
    $dpOgDescription = trim(strip_tags($__env->yieldContent('og_description', ''))) ?: ($dpMetaDescription ?: 'Delivering Parcel — international parcel forwarding and reshipping.');
@endphp
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{!! $dpTitle !!}</title>
<meta name="description" content="{!! $dpMetaDescription !!}">
<meta name="keywords" content="@yield('keywords', '')">
<meta property="og:title" content="{!! $dpOgTitle !!}">
<meta property="og:description" content="{!! $dpOgDescription !!}">
<meta name="author" content="deliveringparcel" />
 <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
<link rel="preload" as="image"
      href="{{ asset('frontend/assets/img/hero-img.svg') }}"
      fetchpriority="high">

    <!--<link rel="canonical" href="{{ url()->current() }}" />-->
    <link rel="canonical" href="{{ config('app.url') . request()->getPathInfo() }}" />

    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Delivering Parcel" />

    <!--<meta property="og:url" content="{{ url()->current() }}" />-->
    <meta property="og:url" content="{{ config('app.url') . request()->getPathInfo() }}" />

    <meta property="og:image" content="@yield('og_image', asset('images/house@2x.png'))" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{!! $dpOgTitle !!}" />
    <meta name="twitter:description" content="{!! $dpOgDescription !!}" />
    <meta name="twitter:image" content="@yield('og_image', asset('images/house@2x.png'))" />

    <meta name="p:domain_verify" content="893879193819c768d908f92c08d293dc" />

    <link rel="icon" type="image/svg+xml" href="{{ url('images/dp.svg') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('images/favicon.ico') }}">

    {{-- 2026-08-21 — Google Fonts removed for fully-local assets; falls back to system fonts.
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    --}}

    {{-- Public site loads its own Alpine entry (CSP-safe build) instead of
         resources/js/app.js, which the admin dashboard's layout still uses
         with standard Alpine — see resources/js/app-public.js for why. --}}
    @vite(['resources/css/app.css', 'resources/js/app-public.js'])

    <link href="{{ asset('frontend/assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">

    {{-- Self-hosted flag sprites — used by the homepage's hero "ship from"
         picker and region cards, and (site-wide) by the language switcher in
         the header below. Loaded here rather than per-page since the
         switcher appears on every page. --}}
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/flag-icon-css/css/flag-icon.css') }}">

    <link href="{{ asset('frontend/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/assets/vendor/aos/aos.css') }}" rel="stylesheet">

    <link href="{{ asset('frontend/assets/css/main.css') }}?v=20260929b" rel="stylesheet">
    <link href="{{ asset('frontend/assets/css/font-display-fix.css') }}" rel="stylesheet">

    <link rel="preload" as="image" href="{{ asset('frontend/assets/img/hero-img.svg') }}" />

    {{-- ── Google Analytics (FIXED: was loaded twice — now once only) --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-TR59VBL84T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-TR59VBL84T');
    </script>

    {{-- ── Meta Pixel (Facebook) ──────────────────────────────────── --}}
    <!--<script>-->
    <!--    !function(f,b,e,v,n,t,s)-->
    <!--    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?-->
    <!--    n.callMethod.apply(n,arguments):n.queue.push(arguments)};-->
    <!--    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';-->
    <!--    n.queue=[];t=b.createElement(e);t.async=!0;-->
    <!--    t.src=v;s=b.getElementsByTagName(e)[0];-->
    <!--    s.parentNode.insertBefore(t,s)}(window,document,'script',-->
    <!--    'https://connect.facebook.net/en_US/fbevents.js');-->
    <!--    fbq('init', '856078692386354');-->
    <!--    fbq('track', 'PageView');-->
    <!--</script>-->
    <!--<noscript>-->
    <!--    <img height="1" width="1" style="display:none"-->
    <!--         src="https://www.facebook.com/tr?id=856078692386354&ev=PageView&noscript=1" />-->
    <!--</noscript>-->

   <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Organization",
  "name": "Delivering Parcel",
  "url": "https://deliveringparcel.com/",
  "logo": "https://deliveringparcel.com/images/dp.svg",
  "description": "International parcel forwarding and package reshipping service.",
  "sameAs": [
    "https://www.facebook.com/deliveringparcel",
    "https://www.instagram.com/delivering_parcel/",
    "https://www.youtube.com/@deliveringparcel-ur8wc",
    "https://www.trustpilot.com/review/deliveringparcel.com"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+44 2039 875200",
    "contactType": "customer support",
    "availableLanguage": ["en","ar","fr","es","de","it"]
  }
}
</script>

    @stack('head')

</head>