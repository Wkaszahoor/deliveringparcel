@extends('layouts.fmaster')

@section('title', 'Best Parcel Forwarding Service in Europe | Delivering Parcel')
@section('meta_description', 'Get a free forwarding address in Spain, France, Germany, Italy, Norway, Sweden or Poland and shop European stores that don\'t ship outside Europe — Delivering Parcel forwards it all to your door.')

@section('content')
<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <span class="dp-legal-meta-chip"><i class="fas fa-earth-europe text-xs" aria-hidden="true"></i> Parcel Forwarding · Europe</span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mt-4 mb-4">Best Parcel Forwarding Service in Europe</h1>
                <p class="max-w-2xl text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                    Found a European store that won't ship outside Europe? You're not alone. Delivering Parcel gives you a local address in Spain, France, Germany, Italy, Norway, Sweden or Poland, so exclusive products and trendy finds make it safely into your hands, wherever you are.
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
                        <a href="#spain" class="dp-legal-toc-link">Spain</a>
                        <a href="#france" class="dp-legal-toc-link">France</a>
                        <a href="#germany" class="dp-legal-toc-link">Germany</a>
                        <a href="#italy" class="dp-legal-toc-link">Italy</a>
                        <a href="#norway" class="dp-legal-toc-link">Norway</a>
                        <a href="#sweden" class="dp-legal-toc-link">Sweden</a>
                        <a href="#poland" class="dp-legal-toc-link">Poland</a>
                    </div>
                </nav>

                <article class="dp-legal-body max-w-3xl">

                    <h2 id="spain">Spain</h2>
                    <p>Sign up for a Spanish address and use it to shop Spanish retailers that don't offer international shipping — Delivering Parcel forwards the parcels on to you. A reliable return address is just as valuable: items can be redirected home seamlessly, so you can shop without limitations.</p>
                    <p class="dp-store-label">Popular stores in Spain</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Amazon Spain — 149M+ monthly visits</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> AliExpress Spain</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> El Corte Inglés</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Milanuncios</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Leroy Merlin Spain</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> MediaMarkt Spain</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> PcComponentes</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> eBay Spain</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Carrefour Spain</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Decathlon Spain</span>
                    </div>

                    <h2 id="france">France</h2>
                    <p>France is home to exclusive boutiques and renowned brands that often restrict shipping outside Europe. With our France reship service, you shop freely without worrying about geographical limitations — once you make a purchase, Delivering Parcel provides a local French address to use at checkout.</p>
                    <p class="dp-store-label">Popular stores in France</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Amazon France</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Leboncoin</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Cdiscount</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Vinted</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> E.Leclerc</span>
                    </div>

                    <h2 id="germany">Germany</h2>
                    <p>With a comprehensive proxy-buyer service, you can navigate German marketplaces with ease and access exclusive products that aren't available at home. Add a personal-shopper service tailored to your preferences, plus free storage and warehousing to consolidate multiple purchases into one shipment and cut costs.</p>
                    <p class="dp-store-label">Popular stores in Germany</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Otto</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Zalando</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> MediaMarkt</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Saturn</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> About You</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Lidl</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Home24</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Conrad Electronic</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Tchibo</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Douglas</span>
                    </div>

                    <h2 id="italy">Italy</h2>
                    <p>From hard-to-find items to everyday essentials, our proxy-buyer and personal-shopper services help you navigate the Italian market with expert guidance through local shopping nuances — plus complimentary storage and warehousing so you can consolidate purchases before shipping worldwide.</p>
                    <p class="dp-store-label">Popular stores in Italy</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Amazon Italy</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Zalando Italia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Euronics</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> OVS</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> MediaWorld</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Carrefour Italia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Uniqlo Italia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Yoox</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Coin</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Leroy Merlin Italia</span>
                    </div>

                    <h2 id="norway">Norway</h2>
                    <p>As one of the only providers specializing in Norway parcel reshipment — to nearly 120 countries worldwide — we give you a dedicated residential address, free storage, shopping consolidation, and a proxy-buyer service for professional purchase support whenever you need it.</p>
                    <p class="dp-store-label">Popular stores in Norway</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> FINN.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Komplett.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Elkjøp.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Power.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Oda.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Prisjakt.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Outland.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Neo-Tokyo.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Collectible.no</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Gamezone.no</span>
                    </div>

                    <h2 id="sweden">Sweden</h2>
                    <p>Your Sweden address is your personal gateway to Nordic retail: purchases from any store are delivered to our Swedish storage facility, then repacked and consolidated into one shipment for cost-effective, faster delivery. Our proxy-buyer service also steps in when a store won't accept an international card or billing address.</p>
                    <p class="dp-store-label">Popular stores in Sweden</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> H&amp;M</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Zalando</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Elgiganten</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> CDON</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Kjell &amp; Company</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Lagerhaus</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> NetOnNet</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Apotea</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Boozt</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Tradera</span>
                    </div>

                    <h2 id="poland">Poland</h2>
                    <p>Our "shop for me" purchase assistance is built for expats and digital nomads navigating Poland and Europe. A dedicated residential address in Poland gives you a reliable alternative to virtual-only addresses, and our professional proxy-buyer team will shop and curate selections on your behalf, then pack and ship them anywhere you're stationed.</p>
                    <p class="dp-store-label">Popular stores in Poland</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Allegro</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Zalando</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Empik</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> MediaMarkt</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> eMag</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Ceneo</span>
                    </div>

                </article>
            </div>
        </div>
    </section>

    {{-- ======= CTA ======= --}}
    <section class="py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-2xl sm:text-3xl mb-3">Ready to shop from Europe?</h2>
                <p class="mb-6 text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">Sign up for a free forwarding address and start shopping in minutes — no membership fees, tax-free shipping, and 45 days of free storage.</p>
                <a href="{{ route('register') }}" class="dp-btn dp-btn-accent">Get Started Free <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}
@endsection
