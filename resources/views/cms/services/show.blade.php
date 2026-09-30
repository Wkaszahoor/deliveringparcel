@extends('layouts.fmaster')

@section('title', $service->meta_title ?: $service->title . ' | Delivering Parcel')
@section('meta_description', \Str::limit(strip_tags($service->meta_description ?: $service->smart_excerpt), 155))

@push('meta')
    <meta name="keywords" content="{{ $service->meta_keywords }}">
    <meta name="robots" content="{{ $service->robots }}">
    <meta property="og:title" content="{{ $service->meta_title ?: $service->title }}">
    <meta property="og:description" content="{{ \Str::limit(strip_tags($service->meta_description ?: $service->smart_excerpt), 200) }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $service->url }}">
    @if ($service->og_image || $service->featured_image)
        @php $serviceOgImage = $service->og_image ?: $service->featured_image; @endphp
        <meta property="og:image" content="{{ str_starts_with($serviceOgImage, 'http') ? $serviceOgImage : asset($serviceOgImage) }}">
    @endif
    <link rel="canonical" href="{{ $service->url }}">
@endpush

@push('scripts')
    <script type="application/ld+json">
    {!! json_encode([
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $service->title,
        'description' => \Str::limit(strip_tags($service->meta_description ?: $service->smart_excerpt), 300),
        'url'         => $service->url,
        'provider'    => ['@type' => 'Organization', 'name' => 'DeliveringParcel'],
        'serviceType' => $service->parent ? $service->parent->title : $service->title,
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
<div class="dp-tw min-h-screen bg-slate-50/70 pt-28 pb-20 sm:pt-32 sm:pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumbs Navigation --}}
        <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-8" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-blue-600 transition">Home</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
            <a href="{{ route('cms.services.index') }}" class="hover:text-blue-600 transition">Services</a>
            @if ($service->parent)
                <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                <a href="{{ route('cms.services.show', $service->parent->slug) }}" class="hover:text-blue-600 transition">{{ $service->parent->title }}</a>
            @endif
            <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
            <span class="text-slate-900 font-semibold truncate max-w-xs">{{ $service->title }}</span>
        </nav>

        @php
            $ctaLabel = $service->meta['cta_label'] ?? 'Get a Shipping Quote';
            $ctaUrl   = $service->meta['cta_url'] ?? '/request';
            $ctaHref  = str_starts_with($ctaUrl, 'http') ? $ctaUrl : url($ctaUrl);
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            {{-- Main Content Column --}}
            <div class="lg:col-span-8">
                {{-- Featured Image / Visual Banner --}}
                @if ($service->featured_image)
                    <div class="rounded-3xl overflow-hidden shadow-sm border border-slate-200/90 mb-8 bg-slate-100 max-h-[420px]">
                        <img src="{{ str_starts_with($service->featured_image, 'http') ? $service->featured_image : asset($service->featured_image) }}"
                             alt="{{ $service->title }}"
                             class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-10 shadow-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 mb-4">
                        <i class="fas fa-boxes-packing text-blue-600 text-xs"></i> Official DP Service
                    </span>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                        {{ $service->title }}
                    </h1>

                    @if ($service->excerpt)
                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed pb-6 mb-6 border-b border-slate-100 font-normal">
                            {{ $service->excerpt }}
                        </p>
                    @endif

                    {{-- Service Content Body --}}
                    <div class="text-slate-700 text-base leading-relaxed space-y-4 prose prose-slate max-w-none">
                        {!! \App\Support\HtmlSanitizer::clean($service->content ?? '') !!}
                    </div>

                    {{-- Sub-services if present --}}
                    @if ($service->children->isNotEmpty())
                        <div class="mt-12 pt-8 border-t border-slate-100">
                            <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                                <i class="fas fa-layer-group text-blue-600 text-sm"></i> Available Sub-Services
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($service->children as $child)
                                    <a href="{{ route('cms.services.show', $child->slug) }}"
                                       class="group p-4 rounded-2xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/30 transition-all flex flex-col justify-between">
                                        <div>
                                            <h3 class="font-bold text-slate-900 group-hover:text-blue-600 transition text-sm">
                                                {{ $child->title }}
                                            </h3>
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                                {{ \Str::limit($child->smart_excerpt, 100) }}
                                            </p>
                                        </div>
                                        <span class="text-xs font-semibold text-blue-600 flex items-center gap-1 mt-3">
                                            Learn more <i class="fas fa-arrow-right text-[10px]"></i>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Fast Quote Action Card --}}
                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs sticky top-28">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl mb-4 shadow-xs">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Ship with Delivering Parcel</h3>
                    <p class="text-xs text-slate-500 mt-1 mb-6 leading-relaxed">
                        Get discounted courier shipping rates from USA, UK, and European hubs directly to your door.
                    </p>

                    <a href="{{ $ctaHref }}"
                       class="w-full inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold py-3.5 px-4 rounded-xl shadow-md shadow-orange-500/25 transition text-sm">
                        {{ $ctaLabel }} <i class="fas fa-arrow-right text-xs"></i>
                    </a>

                    <a href="{{ url('track-order') }}"
                       class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl transition text-xs mt-3">
                        <i class="fas fa-magnifying-glass text-xs"></i> Track Existing Shipment
                    </a>

                    <div class="mt-8 pt-6 border-t border-slate-100 space-y-3">
                        <div class="flex items-center gap-2.5 text-xs text-slate-600">
                            <i class="fas fa-shield-halved text-emerald-500 text-sm"></i>
                            <span>100% Package Insurance Available</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-slate-600">
                            <i class="fas fa-warehouse text-blue-500 text-sm"></i>
                            <span>45-Days Free Storage &amp; Consolidation</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-slate-600">
                            <i class="fas fa-headset text-orange-500 text-sm"></i>
                            <span>Dedicated Support &amp; Live Chat</span>
                        </div>
                    </div>
                </div>

                {{-- Help Card --}}
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 text-white text-center shadow-lg">
                    <h4 class="font-bold text-base text-white mb-1">Have Questions?</h4>
                    <p class="text-xs text-slate-300 mb-4 leading-relaxed">Need custom pricing, oversized forwarding, or assistance? Our team is available 24/7.</p>
                    <a href="{{ url('contact-details') }}"
                       class="inline-block bg-white/15 hover:bg-white/20 text-white font-semibold text-xs px-4 py-2.5 rounded-xl border border-white/10 transition">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
