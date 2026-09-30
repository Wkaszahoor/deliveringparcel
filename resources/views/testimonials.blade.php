@extends('layouts.fmaster')

@section('title', 'Customer Reviews & Testimonials | Delivering Parcel')
@section('meta_description', 'Real customer reviews and feedback from Delivering Parcel shoppers worldwide across Google, Trustpilot, SiteJabber, and direct verified orders.')
@section('keywords', 'Delivering Parcel reviews, customer testimonials, parcel forwarding reviews, trustpilot reviews, google reviews, sitejabber reviews')

@section('content')
<div class="min-h-screen bg-slate-50/70 pt-28 pb-20 sm:pt-32 sm:pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header & Trust Summary --}}
        <div class="text-center max-w-3xl mx-auto mb-12" data-aos="fade-up">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 mb-4 shadow-xs">
                <i class="fas fa-certificate text-blue-600 text-xs"></i> Verified Global Feedback
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                What Our Customers Say
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto">
                Real stories, ratings, and reviews from international shoppers, collectors, and businesses using Delivering Parcel across 220+ countries.
            </p>

            {{-- Trust Badges Summary Banner --}}
            <div class="mt-6 flex justify-center">
                @include('partials.review-badges')
            </div>
        </div>

        {{-- Filters Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 sm:p-5 mb-10" data-aos="fade-up">
            <form method="GET" action="{{ route('testimonials') }}" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Platform tabs --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider mr-1 hidden sm:inline">Platform:</span>

                    <a href="{{ route('testimonials', array_filter(['country' => $currentCountry])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ $currentSource === '' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        All Sources
                    </a>

                    @if (in_array('google', $enabledSources, true))
                        <a href="{{ route('testimonials', array_filter(['source' => 'google', 'country' => $currentCountry])) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ $currentSource === 'google' ? 'bg-blue-600 text-white shadow-xs ring-2 ring-blue-600/20' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200/60' }}">
                            <i class="fab fa-google text-[11px]"></i> Google
                        </a>
                    @endif

                    @if (in_array('trustpilot', $enabledSources, true))
                        <a href="{{ route('testimonials', array_filter(['source' => 'trustpilot', 'country' => $currentCountry])) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ $currentSource === 'trustpilot' ? 'bg-emerald-600 text-white shadow-xs ring-2 ring-emerald-600/20' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60' }}">
                            <i class="fas fa-star text-[11px]"></i> Trustpilot
                        </a>
                    @endif

                    @if (in_array('sitejabber', $enabledSources, true))
                        <a href="{{ route('testimonials', array_filter(['source' => 'sitejabber', 'country' => $currentCountry])) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ $currentSource === 'sitejabber' ? 'bg-orange-600 text-white shadow-xs ring-2 ring-orange-600/20' : 'bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200/60' }}">
                            <i class="fas fa-star text-[11px]"></i> SiteJabber
                        </a>
                    @endif

                    @if (in_array('manual', $enabledSources, true))
                        <a href="{{ route('testimonials', array_filter(['source' => 'manual', 'country' => $currentCountry])) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ $currentSource === 'manual' ? 'bg-indigo-600 text-white shadow-xs ring-2 ring-indigo-600/20' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/60' }}">
                            <i class="fas fa-shield-halved text-[11px]"></i> Direct Reviews
                        </a>
                    @endif
                </div>

                {{-- Country dropdown & reset --}}
                <div class="flex items-center gap-3">
                    @if ($countries->isNotEmpty())
                        <div class="relative">
                            <select name="country" onchange="this.form.submit()"
                                    class="appearance-none rounded-xl border border-slate-300 bg-white pl-3.5 pr-8 py-2 text-xs font-medium text-slate-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                                <option value="">All Countries ({{ $countries->count() }})</option>
                                @foreach ($countries as $c)
                                    <option value="{{ $c }}" {{ $currentCountry === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </div>
                        @if ($currentSource)
                            <input type="hidden" name="source" value="{{ $currentSource }}">
                        @endif
                    @endif

                    @if ($currentSource || $currentCountry)
                        <a href="{{ route('testimonials') }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-2 rounded-xl transition">
                            <i class="fas fa-times text-[10px]"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Testimonials Grid --}}
        @if ($testimonials->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8" data-aos="fade-up">
                @foreach ($testimonials as $t)
                    @php
                        $sourceBadges = [
                            'google'     => ['label' => 'Google', 'icon' => 'fab fa-google', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200/80'],
                            'trustpilot' => ['label' => 'Trustpilot', 'icon' => 'fas fa-star', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80'],
                            'sitejabber' => ['label' => 'SiteJabber', 'icon' => 'fas fa-star', 'badge' => 'bg-orange-50 text-orange-700 border-orange-200/80'],
                            'manual'     => ['label' => 'Verified Direct', 'icon' => 'fas fa-shield-halved', 'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80'],
                        ];
                        $sb = $sourceBadges[$t->source] ?? $sourceBadges['manual'];
                    @endphp
                    <div class="group bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col justify-between shadow-xs hover:shadow-xl hover:border-blue-200 hover:-translate-y-1 transition-all duration-300">
                        <div>
                            {{-- Card Top Row: Rating Stars + Platform Badge --}}
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-xs {{ $i <= ($t->rating ?? 5) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                    @endfor
                                    @if ($t->rating)
                                        <span class="text-xs font-bold text-slate-700 ml-1.5 bg-slate-100 px-1.5 py-0.5 rounded">
                                            {{ number_format($t->rating, 1) }}
                                        </span>
                                    @endif
                                </div>

                                <span class="inline-flex items-center gap-1 rounded-full border {{ $sb['badge'] }} px-2.5 py-0.5 text-[11px] font-semibold">
                                    <i class="{{ $sb['icon'] }} text-[10px]"></i> {{ $sb['label'] }}
                                </span>
                            </div>

                            {{-- Quote Content --}}
                            <div class="relative">
                                <i class="fas fa-quote-left text-slate-200 text-lg mb-2 block group-hover:text-blue-100 transition-colors"></i>
                                <p class="text-slate-700 text-sm leading-relaxed mb-6 font-normal">
                                    “{{ $t->content }}”
                                </p>
                            </div>
                        </div>

                        {{-- Card Footer: User Avatar, Name, Location, Verified Status --}}
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                            <div class="flex items-center gap-3">
                                @if ($t->user_avatar)
                                    <img src="{{ asset($t->user_avatar) }}" alt="{{ $t->user_name }}"
                                         width="40" height="40" class="w-10 h-10 rounded-full object-cover ring-2 ring-slate-100">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                        {{ strtoupper(substr($t->user_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $t->user_name }}</h4>
                                    @if ($t->country)
                                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                            <i class="fas fa-map-marker-alt text-slate-400 text-[10px]"></i> {{ $t->country }}
                                        </p>
                                    @elseif ($t->role_or_company)
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $t->role_or_company }}</p>
                                    @else
                                        <p class="text-xs text-emerald-600 flex items-center gap-1 mt-0.5">
                                            <i class="fas fa-check-circle text-[10px]"></i> Verified Shopper
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if ($t->source_url)
                                <a href="{{ $t->source_url }}" target="_blank" rel="noopener nofollow"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-blue-600 p-2 rounded-lg hover:bg-slate-50 transition"
                                   title="View original review on {{ $sb['label'] }}">
                                    <span class="hidden sm:inline text-[11px]">View</span>
                                    <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                    <i class="fas fa-check-circle text-[10px]"></i> Verified
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12 flex justify-center">
                {{ $testimonials->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white rounded-3xl border border-slate-200/90 p-12 text-center max-w-lg mx-auto shadow-sm my-8">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">No reviews found</h3>
                <p class="text-sm text-slate-500 mb-6">No customer reviews match your active platform or country filter.</p>
                <a href="{{ route('testimonials') }}"
                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-xs transition">
                    View All Testimonials
                </a>
            </div>
        @endif

        {{-- High-Impact Conversion CTA --}}
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 border border-slate-800 p-8 sm:p-14 text-center mt-20 shadow-2xl">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-orange-500/15 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-orange-400 border border-white/15 mb-4">
                    <i class="fas fa-bolt text-xs"></i> Fast &amp; Affordable Shipping
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                    Ready to ship from anywhere to everywhere?
                </h2>
                <p class="mt-3 text-slate-300 text-sm sm:text-base leading-relaxed">
                    Join thousands of happy international shoppers and businesses who use Delivering Parcel for seamless, affordable package reshipping.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="/request"
                       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm px-6 py-3.5 rounded-xl shadow-lg shadow-orange-500/25 hover:shadow-orange-500/35 transition transform hover:-translate-y-0.5">
                        Get a Free Quote <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                    <a href="{{ url('track-order') }}"
                       class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/15 text-white font-semibold text-sm px-6 py-3.5 rounded-xl border border-white/15 transition">
                        <i class="fas fa-magnifying-glass text-xs"></i> Track a Shipment
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
