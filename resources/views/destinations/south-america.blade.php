@extends('layouts.fmaster')

@section('title', 'Parcel Forwarding from South America | Delivering Parcel')
@section('meta_description', 'Get a free forwarding address in Brazil, Mexico or Argentina and shop stores that don\'t ship internationally — Delivering Parcel forwards it all to your door.')

@section('content')
<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <span class="dp-legal-meta-chip"><i class="fas fa-earth-americas text-xs" aria-hidden="true"></i> Parcel Forwarding · South America</span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mt-4 mb-4">Parcel Forwarding from South America</h1>
                <p class="max-w-2xl text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                    Fallen in love with a product online, only to find the store won't ship to your country? Many sought-after stores in Brazil, Mexico and Argentina offer unique products that shoppers outside these countries can't otherwise access. Delivering Parcel bridges that gap with a local address and reliable forwarding.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ url('request') }}" class="dp-btn dp-btn-accent">Get a Free Quote <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i></a>
                    <a href="{{ route('register') }}" class="dp-btn dp-btn-outline">Create Free Account</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ======= Body ======= --}}
    <section class="relative py-12 lg:py-16" style="background:#fff">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-10 lg:gap-14 items-start">

                <nav class="hidden lg:block sticky top-28 self-start" aria-label="Countries on this page">
                    <p class="text-xs font-bold uppercase tracking-wide mb-3 px-3" style="color:var(--dp-ink-soft)">Countries covered</p>
                    <div class="flex flex-col gap-0.5">
                        <a href="#brazil" class="dp-legal-toc-link">Brazil</a>
                        <a href="#mexico" class="dp-legal-toc-link">Mexico</a>
                        <a href="#argentina" class="dp-legal-toc-link">Argentina</a>
                        <a href="#tips" class="dp-legal-toc-link">Tips for shoppers</a>
                    </div>
                </nav>

                <article class="dp-legal-body max-w-3xl">

                    <h2 id="brazil">Brazil</h2>
                    <p>Brazil's booming e-commerce scene offers everything from electronics to uniquely Brazilian artisan goods, but its leading online stores operate locally and don't ship globally. Delivering Parcel adds visibility, reliability and savings when shipping from Brazil, with real-time tracking, proactive inventory management, and solutions across ocean, air and ground freight.</p>
                    <p class="dp-store-label">Leading Brazilian stores</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Americanas</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Magazine Luiza (Magalu)</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Mercado Livre (Brazil)</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Netshoes</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Submarino</span>
                    </div>

                    <h2 id="mexico">Mexico</h2>
                    <p>Mexico has become a digital shopping hub bridging Latin America and the USA. Parcel forwarding lets Mexican shoppers access U.S. brands, and lets international buyers reach Mexican retailers that don't offer international shipping — including Liverpool, Coppel, El Palacio de Hierro, Sears Mexico and Farmacias Guadalajara.</p>
                    <ol>
                        <li><strong>Sign up</strong> with Deliveringparcel.com.</li>
                        <li><strong>Shop</strong> using the provided Mexican address as your shipping destination.</li>
                        <li><strong>Forward</strong> — we ship your package to your actual address.</li>
                        <li><strong>Track</strong> your package in real time until it arrives at your doorstep.</li>
                    </ol>
                    <p class="dp-store-label">Popular stores in Mexico</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Amazon.mx</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Mercado Libre</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Liverpool</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Coppel</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> El Palacio de Hierro</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Sears Mexico</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Farmacias Guadalajara</span>
                    </div>

                    <h2 id="argentina">Argentina</h2>
                    <p>Argentina is a treasure trove of unique goods — from leather to wine — that catch the eye of international buyers, but shipping challenges often deter global shoppers. A parcel forwarder helps you reach global markets, access Argentinian specialties abroad, and simplify logistics with timely, efficient delivery. Stores like Mercado Libre (Argentina), Fravega, Walmart Argentina, Zara Argentina and Musimundo don't ship internationally on their own.</p>
                    <div class="dp-legal-callout">
                        <span class="dp-legal-callout-icon"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></span>
                        <span>Example: a shopper in the USA craving Argentinian leather goods can use parcel forwarding to ship those items directly home.</span>
                    </div>

                    <h2 id="tips">Tips for international shoppers and exporters</h2>
                    <ul>
                        <li><strong>Take advantage of seasonal sales:</strong> score deals during Black Friday, Cyber Monday or holiday sales, then consolidate items from different sales into one shipment.</li>
                        <li><strong>Save with package consolidation:</strong> combine multiple items into a single package to lower shipping fees.</li>
                        <li><strong>Understand customs and duties:</strong> familiarize yourself with customs rules and taxes at home, and lean on your forwarding service to keep declarations accurate.</li>
                    </ul>
                    <p>Delivering Parcel also offers concierge shopping services — we purchase items for you, ship them to our local facility, then send the goods on to your international address. Whether you're buying artisanal goods from Brazil, snagging cosmetics from Mexico, or shipping premium leather from Argentina, parcel forwarding brings South America to your doorstep.</p>

                </article>
            </div>
        </div>
    </section>

    {{-- ======= CTA ======= --}}
    <section class="py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-2xl sm:text-3xl mb-3">Ready to shop from South America?</h2>
                <p class="mb-6 text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">Sign up for a free forwarding address and start shopping in minutes — no membership fees, tax-free shipping, and 45 days of free storage.</p>
                <a href="{{ route('register') }}" class="dp-btn dp-btn-accent">Get Started Free <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}
@endsection
