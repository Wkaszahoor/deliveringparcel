@extends('layouts.fmaster')

@section('title', 'International Parcel Forwarding & Shipping Services | Delivering Parcel')
@section('meta_description', 'Explore our comprehensive parcel forwarding, reshipping, proxy shopping, and package consolidation services from USA, UK, and Europe to over 170 countries.')
@section('keywords', 'parcel forwarding services, reshipping services, shop for me, proxy buyer, package consolidation, cheap international shipping, buy from usa, virtual shipping address')

@push('meta')
    <meta property="og:title" content="International Parcel Forwarding &amp; Shipping Services | Delivering Parcel">
    <meta property="og:description" content="Explore our comprehensive parcel forwarding, reshipping, proxy shopping, and package consolidation services from USA, UK, and Europe to over 170 countries.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
@endpush

@push('scripts')
    <script type="application/ld+json">
    {!! json_encode([
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'itemListElement' => $services->map(function ($service, $i) {
            return [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'item'     => [
                    '@type'       => 'Service',
                    'name'        => $service->title,
                    'description' => \Str::limit(strip_tags($service->smart_excerpt), 200),
                    'url'         => $service->url,
                    'provider'    => ['@type' => 'Organization', 'name' => 'DeliveringParcel'],
                ],
            ];
        })->all(),
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
{{-- x-data is a bare component reference (resources/js/app-public.js's
     dpServicesPage) — the public site's CSP-safe Alpine build can't parse an
     inline method definition here. --}}
<div class="dp-tw min-h-screen bg-slate-50/70 pt-28 pb-20 sm:pt-32 sm:pb-24 overflow-hidden"
     x-data="dpServicesPage">

    {{-- Ambient Background Glows --}}
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="absolute -top-10 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-40 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

        {{-- ── 1. HERO SECTION ─────────────────────────────────────────── --}}
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16" data-aos="fade-up">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 mb-5 shadow-xs">
                <i class="fas fa-boxes-packing text-blue-600"></i> Global Parcel Forwarding Network
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                Worldwide Forwarding &amp; Reshipping Services
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto">
                Shop tax-free from the USA, UK, and European online retailers. We receive, inspect, consolidate, and deliver your packages directly to your door in 170+ countries.
            </p>

            {{-- Hero Quick Actions --}}
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="/request"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-3.5 rounded-xl shadow-lg shadow-blue-600/25 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-calculator text-xs"></i> Calculate Shipping Quote
                </a>
                <a href="{{ url('track-order') }}"
                   class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm px-5 py-3.5 rounded-xl border border-slate-300 shadow-xs transition">
                    <i class="fas fa-barcode text-xs text-slate-400"></i> Track a Shipment
                </a>
                <a href="{{ route('testimonials') }}"
                   class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm px-5 py-3.5 rounded-xl border border-slate-300 shadow-xs transition">
                    <i class="fas fa-star text-xs text-amber-400"></i> Verified Customer Reviews
                </a>
            </div>

            {{-- 4 Core Metric Highlights --}}
            <div class="mt-12 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 text-center shadow-xs">
                    <span class="text-2xl sm:text-3xl font-black text-slate-900">170+</span>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Countries Served</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Express &amp; Standard routes</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 text-center shadow-xs">
                    <span class="text-2xl sm:text-3xl font-black text-blue-600">45 Days</span>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Free Storage</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Hold &amp; combine items free</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 text-center shadow-xs">
                    <span class="text-2xl sm:text-3xl font-black text-emerald-600">Up to 80%</span>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Consolidation Savings</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Save on international postage</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 text-center shadow-xs">
                    <span class="text-2xl sm:text-3xl font-black text-orange-500">100% Safe</span>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Insured &amp; Verified</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Package photos upon arrival</p>
                </div>
            </div>
        </div>

        {{-- ── 2. INTERACTIVE CATEGORY FILTER & SEARCH BAR ───────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-4 sm:p-6 shadow-sm mb-12" data-aos="fade-up">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Category Pill Tabs --}}
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button"
                            @click="activeCategory = 'all'"
                            :class="activeCategory === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                        All Services ({{ $services->count() }})
                    </button>
                    <button type="button"
                            @click="activeCategory = 'forwarding'"
                            :class="activeCategory === 'forwarding' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-blue-50 hover:text-blue-700'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                        Forwarding &amp; Reshipping
                    </button>
                    <button type="button"
                            @click="activeCategory = 'shopping'"
                            :class="activeCategory === 'shopping' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-rose-50 hover:text-rose-700'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                        Proxy Buying ("Buy For Me")
                    </button>
                    <button type="button"
                            @click="activeCategory = 'consolidation'"
                            :class="activeCategory === 'consolidation' ? 'bg-orange-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-orange-50 hover:text-orange-700'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                        Consolidation &amp; Repack
                    </button>
                    <button type="button"
                            @click="activeCategory = 'b2b'"
                            :class="activeCategory === 'b2b' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer">
                        Business &amp; Dropshipping
                    </button>
                </div>

                {{-- Live Search Input --}}
                <div class="relative w-full md:w-72">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Filter services by keyword…"
                           class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                        <i class="fas fa-magnifying-glass"></i>
                    </span>
                    <button type="button"
                            x-show="searchQuery.length > 0"
                            @click="searchQuery = ''"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── 3. SERVICES GRID ────────────────────────────────────────── --}}
        @php
            // Detailed design metadata map per service
            $serviceMeta = [
                'parcel-forwarding' => [
                    'category'   => 'forwarding',
                    'cat_label'  => 'Core Forwarding',
                    'cat_color'  => 'bg-blue-50 text-blue-700 border-blue-200/80',
                    'gradient'   => 'from-blue-600 to-indigo-600 shadow-blue-500/20',
                    'icon'       => 'fa-boxes-packing',
                    'badge'      => 'Most Popular',
                    'points'     => [
                        'Free local delivery addresses in US, UK & EU',
                        'High-resolution photos upon package arrival',
                        'Automated customs documentation assistance'
                    ]
                ],
                'forward-parcel' => [
                    'category'   => 'forwarding',
                    'cat_label'  => 'Express Reship',
                    'cat_color'  => 'bg-sky-50 text-sky-700 border-sky-200/80',
                    'gradient'   => 'from-sky-500 to-blue-600 shadow-sky-500/20',
                    'icon'       => 'fa-truck-fast',
                    'badge'      => 'Fast Turnaround',
                    'points'     => [
                        'Same-day or 24h parcel processing',
                        'Major carrier options: DHL, FedEx, UPS & DPD',
                        'Live tracking updates sent directly to your phone'
                    ]
                ],
                'international-parcel-forwarding' => [
                    'category'   => 'forwarding',
                    'cat_label'  => 'Cross-Border',
                    'cat_color'  => 'bg-violet-50 text-violet-700 border-violet-200/80',
                    'gradient'   => 'from-violet-600 to-purple-600 shadow-violet-500/20',
                    'icon'       => 'fa-earth-americas',
                    'badge'      => '170+ Destinations',
                    'points'     => [
                        'Global coverage spanning North America, Europe, Asia & ME',
                        'Choice of Express (2-4 days) or Economy (6-10 days)',
                        'Duty-paid (DDP) or duty-unpaid (DDU) options'
                    ]
                ],
                'reshipper-service' => [
                    'category'   => 'forwarding',
                    'cat_label'  => 'Reshipping',
                    'cat_color'  => 'bg-amber-50 text-amber-800 border-amber-200/80',
                    'gradient'   => 'from-amber-500 to-orange-600 shadow-amber-500/20',
                    'icon'       => 'fa-arrow-right-arrow-left',
                    'badge'      => 'Custom Routing',
                    'points'     => [
                        'Redirect packages to any address or recipient',
                        'White-label packaging without store invoices',
                        'Protection against international purchase restrictions'
                    ]
                ],
                'package-forwarding' => [
                    'category'   => 'forwarding',
                    'cat_label'  => 'Handling & Care',
                    'cat_color'  => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
                    'gradient'   => 'from-indigo-600 to-blue-700 shadow-indigo-500/20',
                    'icon'       => 'fa-dolly',
                    'badge'      => 'Insured Shipping',
                    'points'     => [
                        'Specialized care for fragile, oversized, or high-value items',
                        'Reinforced double-wall boxing & protective bubble wrap',
                        'Comprehensive loss & damage insurance available'
                    ]
                ],
                'buy-for-me' => [
                    'category'   => 'shopping',
                    'cat_label'  => 'Assisted Purchase',
                    'cat_color'  => 'bg-rose-50 text-rose-700 border-rose-200/80',
                    'gradient'   => 'from-rose-500 to-pink-600 shadow-rose-500/20',
                    'icon'       => 'fa-cart-shopping',
                    'badge'      => 'Zero Card Rejection',
                    'points'     => [
                        'We buy directly when stores reject foreign credit cards',
                        'Dedicated shopping assistants place orders on your behalf',
                        'Seamless forwarding once items arrive at our hub'
                    ]
                ],
                'proxy-address-for-shopping' => [
                    'category'   => 'shopping',
                    'cat_label'  => 'Virtual Address',
                    'cat_color'  => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                    'gradient'   => 'from-emerald-500 to-teal-600 shadow-emerald-500/20',
                    'icon'       => 'fa-location-dot',
                    'badge'      => '0% US Sales Tax',
                    'points'     => [
                        'Genuine physical addresses in tax-free USA, UK, and Germany',
                        'Accepted on Amazon, eBay, Apple, Nike, Sephora & more',
                        'Unique virtual suite number with automated notifications'
                    ]
                ],
                'consolidate-and-repackage' => [
                    'category'   => 'consolidation',
                    'cat_label'  => 'Package Consolidation',
                    'cat_color'  => 'bg-orange-50 text-orange-700 border-orange-200/80',
                    'gradient'   => 'from-orange-500 to-amber-600 shadow-orange-500/20',
                    'icon'       => 'fa-layer-group',
                    'badge'      => 'Save Up To 80%',
                    'points'     => [
                        'Combine 5 to 10+ retail purchases into one single carton',
                        '45 days free warehouse storage while waiting for orders',
                        'Remove bulky merchant packaging to cut dimensional weight'
                    ]
                ],
                'dropshipping-and-reshipping' => [
                    'category'   => 'b2b',
                    'cat_label'  => 'B2B & E-commerce',
                    'cat_color'  => 'bg-slate-100 text-slate-800 border-slate-200/80',
                    'gradient'   => 'from-slate-800 to-slate-900 shadow-slate-900/20',
                    'icon'       => 'fa-building-shield',
                    'badge'      => 'Business Ready',
                    'points'     => [
                        'Fulfillment & forwarding for eBay, Shopify, Etsy & Amazon sellers',
                        'Custom branded packing slips & thank-you notes',
                        'Commercial volume discounts with dedicated account manager'
                    ]
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-16" data-aos="fade-up">
            @foreach ($services as $service)
                @php
                    $m = $serviceMeta[$service->slug] ?? [
                        'category'  => 'forwarding',
                        'cat_label' => 'Service',
                        'cat_color' => 'bg-slate-50 text-slate-700 border-slate-200',
                        'gradient'  => 'from-blue-600 to-indigo-600 shadow-blue-500/20',
                        'icon'      => 'fa-boxes-packing',
                        'badge'     => 'Verified',
                        'points'    => [
                            'Direct international forwarding to 170+ countries',
                            'Real-time shipment tracking with courier updates',
                            'Fast, reliable warehouse handling & storage'
                        ]
                    ];
                    $ctaLabel = $service->meta['cta_label'] ?? 'Get a Quote';
                    $ctaUrl   = $service->meta['cta_url'] ?? '/request';
                    $ctaHref  = str_starts_with($ctaUrl, 'http') ? $ctaUrl : url($ctaUrl);
                @endphp

                <article x-show="matches('{{ $m['category'] }}', '{{ addslashes($service->title) }}', '{{ addslashes($service->smart_excerpt) }}')"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="group bg-white rounded-3xl border border-slate-200/90 p-7 sm:p-8 flex flex-col justify-between shadow-xs hover:shadow-2xl hover:border-blue-200 hover:-translate-y-1.5 transition-all duration-300 relative overflow-hidden">

                    <div>
                        {{-- Top Badges: Category & Feature Chip --}}
                        <div class="flex items-center justify-between gap-2 mb-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $m['cat_color'] }}">
                                {{ $m['cat_label'] }}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                                {{ $m['badge'] }}
                            </span>
                        </div>

                        {{-- Service Icon Box --}}
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $m['gradient'] }} text-white flex items-center justify-center text-2xl shadow-lg mb-6 group-hover:scale-110 transition-transform">
                            <i class="fas {{ $m['icon'] }}"></i>
                        </div>

                        {{-- Title & Excerpt --}}
                        <h2 class="text-xl font-extrabold text-slate-900 group-hover:text-blue-600 transition-colors leading-snug">
                            <a href="{{ route('cms.services.show', $service->slug) }}">
                                {{ $service->title }}
                            </a>
                        </h2>
                        <p class="mt-2.5 text-slate-600 text-sm leading-relaxed font-normal">
                            {{ $service->smart_excerpt }}
                        </p>

                        {{-- Feature Checklist --}}
                        <div class="mt-5 pt-4 border-t border-slate-100 space-y-2">
                            @foreach ($m['points'] as $pt)
                                <div class="flex items-start gap-2 text-xs text-slate-600">
                                    <i class="fas fa-check-circle text-emerald-500 text-xs mt-0.5 shrink-0"></i>
                                    <span>{{ $pt }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card Bottom Actions --}}
                    <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-between gap-3">
                        <a href="{{ route('cms.services.show', $service->slug) }}"
                           class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700 transition">
                            Learn more <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                        <a href="{{ $ctaHref }}"
                           class="inline-flex items-center gap-1.5 bg-slate-900 group-hover:bg-blue-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition-colors">
                            {{ $ctaLabel }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- ── 4. INTEGRATED GLOBAL CARRIERS STRIP ─────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-8 sm:p-10 shadow-xs mb-16" data-aos="fade-up">
            <div class="text-center max-w-xl mx-auto mb-8">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Trusted Courier Logistics</span>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">Direct Carrier Integrations</h3>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">We partner with premier global couriers to negotiate up to 80% discounted shipping rates for you.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-center">
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60 hover:bg-white hover:shadow-xs transition">
                    <i class="fas fa-truck-fast text-2xl text-blue-600 mb-2"></i>
                    <p class="font-extrabold text-sm text-slate-900">DHL Express</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">2 - 4 Days</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60 hover:bg-white hover:shadow-xs transition">
                    <i class="fas fa-plane text-2xl text-purple-600 mb-2"></i>
                    <p class="font-extrabold text-sm text-slate-900">FedEx Priority</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">2 - 5 Days</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60 hover:bg-white hover:shadow-xs transition">
                    <i class="fas fa-box text-2xl text-amber-700 mb-2"></i>
                    <p class="font-extrabold text-sm text-slate-900">UPS Saver</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">3 - 6 Days</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60 hover:bg-white hover:shadow-xs transition">
                    <i class="fas fa-paper-plane text-2xl text-rose-500 mb-2"></i>
                    <p class="font-extrabold text-sm text-slate-900">DPD Europe</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">3 - 7 Days</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60 hover:bg-white hover:shadow-xs transition">
                    <i class="fas fa-shield text-2xl text-emerald-600 mb-2"></i>
                    <p class="font-extrabold text-sm text-slate-900">Royal Mail</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">5 - 10 Days</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60 hover:bg-white hover:shadow-xs transition">
                    <i class="fas fa-mail-bulk text-2xl text-blue-800 mb-2"></i>
                    <p class="font-extrabold text-sm text-slate-900">USPS Priority</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">6 - 12 Days</p>
                </div>
            </div>
        </div>

        {{-- ── 5. COMPARISON TABLE ─────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-8 sm:p-12 shadow-sm mb-16" data-aos="fade-up">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Unbeatable Value</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Why Choose Delivering Parcel?</h3>
                <p class="text-slate-500 text-sm mt-2">See how our parcel forwarding service compares against traditional shipping options.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider text-slate-400">Feature &amp; Benefits</th>
                            <th class="py-4 px-4 text-sm font-black text-blue-600 bg-blue-50/50 rounded-t-2xl">Delivering Parcel</th>
                            <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider text-slate-500">Other Reshippers</th>
                            <th class="py-4 px-4 text-xs font-bold uppercase tracking-wider text-slate-500">Direct Merchant Shipping</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-800">Free Virtual Address (US, UK, EU)</td>
                            <td class="py-4 px-4 font-bold text-emerald-600 bg-blue-50/30"><i class="fas fa-check-circle"></i> Included Free</td>
                            <td class="py-4 px-4 text-slate-500">Monthly subscription fee</td>
                            <td class="py-4 px-4 text-slate-400"><i class="fas fa-times-circle text-rose-400"></i> No address provided</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-800">Package Consolidation &amp; Repacking</td>
                            <td class="py-4 px-4 font-bold text-emerald-600 bg-blue-50/30"><i class="fas fa-check-circle"></i> Save up to 80%</td>
                            <td class="py-4 px-4 text-slate-500">High per-item fee</td>
                            <td class="py-4 px-4 text-slate-400"><i class="fas fa-times-circle text-rose-400"></i> Separate boxes &amp; fees</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-800">Free Warehouse Storage</td>
                            <td class="py-4 px-4 font-bold text-emerald-600 bg-blue-50/30"><i class="fas fa-check-circle"></i> 45 Days Free</td>
                            <td class="py-4 px-4 text-slate-500">7 - 14 Days max</td>
                            <td class="py-4 px-4 text-slate-400"><i class="fas fa-times-circle text-rose-400"></i> None</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-800">Assisted Purchase ("Buy For Me")</td>
                            <td class="py-4 px-4 font-bold text-emerald-600 bg-blue-50/30"><i class="fas fa-check-circle"></i> Fully Supported</td>
                            <td class="py-4 px-4 text-slate-500">Extra 10% - 15% charge</td>
                            <td class="py-4 px-4 text-slate-400"><i class="fas fa-times-circle text-rose-400"></i> Rejects non-domestic cards</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-semibold text-slate-800">Carrier Choice (DHL, FedEx, UPS)</td>
                            <td class="py-4 px-4 font-bold text-emerald-600 bg-blue-50/30"><i class="fas fa-check-circle"></i> Multiple Couriers</td>
                            <td class="py-4 px-4 text-slate-500">Limited options</td>
                            <td class="py-4 px-4 text-slate-400">Single non-negotiable courier</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 5b. HOW IT WORKS ────────────────────────────────────────── --}}
        <div class="mb-16" data-aos="fade-up">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">The Process</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">How Delivering Parcel Works</h3>
                <p class="text-slate-500 text-sm mt-2">From sign-up to your doorstep, in four steps.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ([
                    ['n' => '1', 'title' => 'Get your free forwarding address', 'desc' => 'Sign up with Delivering Parcel and receive an address to use when shopping online.'],
                    ['n' => '2', 'title' => 'Shop online', 'desc' => 'Purchase from any store around the world. At checkout, use the forwarding address we gave you.'],
                    ['n' => '3', 'title' => 'We handle the rest', 'desc' => 'Once your package arrives, we photograph it, help with labelling and sorting, and complete customs declarations if needed.'],
                    ['n' => '4', 'title' => 'Ship to any destination', 'desc' => 'Your package is forwarded to your final destination quickly and securely.'],
                ] as $step)
                    <div class="relative bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs">
                        <span class="text-3xl font-black text-blue-100">{{ $step['n'] }}</span>
                        <h4 class="mt-2 font-bold text-slate-900 text-sm">{{ $step['title'] }}</h4>
                        <p class="mt-1.5 text-xs text-slate-500 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── 5c. WHERE WE OPERATE ────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-8 sm:p-12 shadow-xs mb-16" data-aos="fade-up">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Global Network</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Parcel Forwarding Around the Globe</h3>
                <p class="text-slate-500 text-sm mt-2">Whether you need a proxy shipping address, reshipping, or free package storage, we make it happen in these countries and more.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ([
                    ['icon' => 'fa-earth-europe', 'title' => 'Europe', 'countries' => 'UK, Spain, Italy, Norway, Denmark, Sweden, Poland, Romania'],
                    ['icon' => 'fa-earth-asia', 'title' => 'Asia', 'countries' => 'Malaysia, Taiwan, Vietnam, Singapore'],
                    ['icon' => 'fa-earth-americas', 'title' => 'North America', 'countries' => 'USA, Canada'],
                    ['icon' => 'fa-earth-oceania', 'title' => 'Australia & beyond', 'countries' => '170+ additional destinations worldwide'],
                ] as $region)
                    <div class="rounded-2xl bg-slate-50/80 border border-slate-200/60 p-5">
                        <i class="fas {{ $region['icon'] }} text-xl text-blue-600 mb-2"></i>
                        <h4 class="font-bold text-slate-900 text-sm">{{ $region['title'] }}</h4>
                        <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ $region['countries'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── 6. FREQUENTLY ASKED QUESTIONS (ACCORDION) ──────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-8 sm:p-12 shadow-sm mb-16" data-aos="fade-up">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Help &amp; Answers</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Frequently Asked Questions</h3>
                <p class="text-slate-500 text-sm mt-2">Have a question regarding parcel forwarding or our services? Here are the most common answers.</p>
            </div>

            <div class="max-w-3xl mx-auto space-y-4">
                {{-- FAQ 1 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition">
                    <button type="button"
                            @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50 transition cursor-pointer">
                        <span class="text-sm sm:text-base">How does parcel forwarding with Delivering Parcel work?</span>
                        <i class="fas text-xs text-slate-400 transition-transform duration-200"
                           :class="openFaq === 1 ? 'fa-chevron-up text-blue-600' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openFaq === 1" x-collapse class="px-5 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                        When you sign up, you receive a free local shipping address in the United States, United Kingdom, or Europe. When shopping online, enter your Delivering Parcel address as the shipping destination. Once your package arrives at our warehouse, we log it, upload photos, notify you, and forward it to your home address worldwide using the courier of your choice.
                    </div>
                </div>

                {{-- FAQ 2 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition">
                    <button type="button"
                            @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50 transition cursor-pointer">
                        <span class="text-sm sm:text-base">What is the "Buy For Me" proxy purchase service?</span>
                        <i class="fas text-xs text-slate-400 transition-transform duration-200"
                           :class="openFaq === 2 ? 'fa-chevron-up text-blue-600' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="px-5 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                        Certain international stores (like Apple US, Sephora, Target, or Nike) reject international credit cards or block orders with non-domestic billing addresses. With our "Buy For Me" service, tell us the exact items you wish to purchase, and our local agents will buy them on your behalf using local domestic payment methods.
                    </div>
                </div>

                {{-- FAQ 3 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition">
                    <button type="button"
                            @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50 transition cursor-pointer">
                        <span class="text-sm sm:text-base">How much can I save by consolidating multiple purchases?</span>
                        <i class="fas text-xs text-slate-400 transition-transform duration-200"
                           :class="openFaq === 3 ? 'fa-chevron-up text-blue-600' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="px-5 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                        International couriers charge base rates for the first half kilogram of any shipment. If you ship 5 separate packages, you pay that base fee 5 times. By consolidating multiple orders into one optimized master carton and removing unnecessary retail packaging, you can save up to 80% on shipping costs.
                    </div>
                </div>

                {{-- FAQ 4 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition">
                    <button type="button"
                            @click="openFaq = openFaq === 4 ? null : 4"
                            class="w-full p-5 text-left font-bold text-slate-900 flex items-center justify-between gap-4 hover:bg-slate-50 transition cursor-pointer">
                        <span class="text-sm sm:text-base">Which countries do you forward and reship parcels to?</span>
                        <i class="fas text-xs text-slate-400 transition-transform duration-200"
                           :class="openFaq === 4 ? 'fa-chevron-up text-blue-600' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openFaq === 4" x-collapse class="px-5 pb-5 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-3">
                        We forward packages to over 170 countries and territories worldwide, including Canada, Australia, Japan, Saudi Arabia, the United Arab Emirates, Kuwait, Singapore, New Zealand, and all European countries.
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 7. VERIFIED REVIEWS & SOCIAL PROOF SNAPSHOT ───────────────
             Real ratings/quote only — no invented star score or "verified
             shopper" quote. See PRODUCT.md: testimonials must never be
             fabricated (an explicit past correction on this project). --}}
        @php
            $svcTestimonial = \App\Models\Testimonial::where('is_published', true)
                ->orderByDesc('sort')->orderByDesc('created_at')->first();
            $svcTpRating = \App\Models\Setting::getBool('reviews_trustpilot_enabled', false)
                ? \App\Models\Setting::get('reviews_trustpilot_rating') : null;
            $svcSjRating = \App\Models\Setting::getBool('reviews_sitejabber_enabled', false)
                ? \App\Models\Setting::get('reviews_sitejabber_rating') : null;
        @endphp
        <div class="bg-gradient-to-r from-blue-900 to-indigo-950 rounded-3xl p-8 sm:p-12 text-white shadow-xl mb-16 flex flex-col lg:flex-row items-center justify-between gap-8" data-aos="fade-up">
            <div class="max-w-xl">
                <div class="flex flex-wrap gap-2 mb-3">
                    @if ($svcTpRating)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-amber-300 border border-white/15">
                            <i class="fas fa-star text-xs"></i> Trustpilot {{ $svcTpRating }}/5
                        </span>
                    @endif
                    @if ($svcSjRating)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-amber-300 border border-white/15">
                            <i class="fas fa-star text-xs"></i> SiteJabber {{ $svcSjRating }}/5
                        </span>
                    @endif
                </div>
                <h3 class="text-2xl sm:text-3xl font-black text-white">Backed by real global shoppers</h3>
                @if ($svcTestimonial)
                    <p class="text-slate-300 text-sm mt-2 leading-relaxed">
                        “{{ $svcTestimonial->content }}”
                    </p>
                    <p class="text-xs font-bold text-slate-400 mt-2">— {{ $svcTestimonial->user_name }}@if($svcTestimonial->role_or_company), {{ $svcTestimonial->role_or_company }}@endif</p>
                @endif
            </div>
            <div class="shrink-0 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('testimonials') }}"
                   class="inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-100 text-slate-900 font-bold text-xs px-5 py-3.5 rounded-xl shadow-xs transition">
                    Read Customer Reviews <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
                <a href="/request"
                   class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs px-5 py-3.5 rounded-xl shadow-md transition">
                    Get a Quote Today
                </a>
            </div>
        </div>

        {{-- ── 8. BOTTOM HIGH-CONVERSION CTA BANNER ────────────────────── --}}
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 border border-slate-800 p-8 sm:p-14 text-center shadow-2xl">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-orange-500/15 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-orange-400 border border-white/15 mb-4">
                    <i class="fas fa-bolt text-xs"></i> Start Shipping in Minutes
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight">
                    Ready to start forwarding packages?
                </h2>
                <p class="mt-3 text-slate-300 text-sm sm:text-base leading-relaxed">
                    Get your free international forwarding address and start shopping worldwide today without geographical borders.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="/request"
                       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm px-6 py-3.5 rounded-xl shadow-lg shadow-orange-500/25 hover:shadow-orange-500/35 transition transform hover:-translate-y-0.5">
                        Get a Free Quote <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                    <a href="{{ url('contact-details') }}"
                       class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/15 text-white font-semibold text-sm px-6 py-3.5 rounded-xl border border-white/15 transition">
                        <i class="fas fa-headset text-xs"></i> Contact Customer Support
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
