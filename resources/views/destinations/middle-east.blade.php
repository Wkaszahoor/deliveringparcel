@extends('layouts.fmaster')

@section('title', 'Shop and Ship from the Middle East | Delivering Parcel')
@section('meta_description', 'Get a free forwarding address in the UAE, Saudi Arabia, Qatar, Kuwait, Bahrain or Oman and shop the region\'s best stores — Delivering Parcel forwards it all to your door, anywhere in the world.')

@section('content')
<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <span class="dp-legal-meta-chip"><i class="fas fa-earth-asia text-xs" aria-hidden="true"></i> Parcel Forwarding · Middle East</span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mt-4 mb-4">Shop and Ship from the Middle East</h1>
                <p class="max-w-2xl text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                    Whether you're shopping from the UAE, Saudi Arabia, Qatar, Bahrain, Kuwait, Oman or Egypt, Delivering Parcel's tailored shop-and-ship, parcel-forwarding and proxy-buyer services ensure your purchases reach your doorstep seamlessly — just the way you need them to.
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

                <nav class="hidden lg:block sticky top-28 self-start" aria-label="Sections">
                    <p class="text-xs font-bold uppercase tracking-wide mb-3 px-3" style="color:var(--dp-ink-soft)">On this page</p>
                    <div class="flex flex-col gap-0.5">
                        <a href="#who-benefits" class="dp-legal-toc-link">Who benefits</a>
                        <a href="#uae" class="dp-legal-toc-link">United Arab Emirates</a>
                        <a href="#saudi-arabia" class="dp-legal-toc-link">Saudi Arabia</a>
                        <a href="#qatar" class="dp-legal-toc-link">Qatar</a>
                        <a href="#kuwait" class="dp-legal-toc-link">Kuwait</a>
                        <a href="#bahrain" class="dp-legal-toc-link">Bahrain</a>
                        <a href="#oman" class="dp-legal-toc-link">Oman</a>
                        <a href="#why-us" class="dp-legal-toc-link">Why Delivering Parcel</a>
                    </div>
                </nav>

                <article class="dp-legal-body max-w-3xl">

                    <h2 id="who-benefits">Who can benefit from our services?</h2>
                    <ul>
                        <li><strong>Global shoppers</strong> hunting for unique items unavailable locally.</li>
                        <li><strong>US and Canada buyers</strong> eager to explore diverse marketplaces in the Middle East.</li>
                        <li><strong>European and Asian customers</strong> looking for quality products at competitive prices.</li>
                        <li><strong>Proxy-buyer and parcel-forwarding seekers</strong> who need efficient, reliable service.</li>
                    </ul>

                    <h2 id="uae">United Arab Emirates — ship to anywhere</h2>
                    <p>Shop these famous UAE stores and reship them to anywhere in the world.</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Dubai Mall Stores (Sephora, Zara, Bloomingdale's)</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Amazon.ae</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Noon</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Mall of the Emirates Boutiques</span>
                    </div>

                    <h2 id="saudi-arabia">Saudi Arabia</h2>
                    <p>International shoppers and buyers can now enjoy Kingdom shopping from anywhere in the world with the help of our forwarding service.</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Jarir Bookstore</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Namshi</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Extra</span>
                    </div>

                    <h2 id="qatar">Qatar</h2>
                    <p>Shop Qatar stores and ship to the USA and worldwide today.</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Villaggio Mall Stores</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Blue Salon</span>
                    </div>

                    <h2 id="kuwait">Kuwait</h2>
                    <p>Use our package forwarder and reshipper service from Kuwait and the wider Gulf.</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> 360 Mall</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Xcite</span>
                    </div>

                    <h2 id="bahrain">Bahrain</h2>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> The Avenues Bahrain</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Zain Bahrain and Batelco</span>
                    </div>

                    <h2 id="oman">Oman</h2>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Muscat City Centre Stores</span>
                    </div>

                    <h2 id="why-us">Why choose Delivering Parcel</h2>
                    <ul>
                        <li><strong>Variety of services:</strong> shop-and-ship, proxy-buyer assistance for stores that don't accept international payments, and real-time shipment tracking.</li>
                        <li><strong>Affordable solutions:</strong> competitive shipping rates, and combine multiple orders into one shipment to save even more.</li>
                        <li><strong>Expert curation:</strong> tasting notes for gourmet food shipments, ensuring quality meets expectations.</li>
                        <li><strong>Customer-centric approach:</strong> friendly, responsive support and a growing community of global shoppers.</li>
                        <li><strong>Exclusive subscriber perks:</strong> early access to deals during major Middle Eastern sales.</li>
                    </ul>

                </article>
            </div>
        </div>
    </section>

    {{-- ======= CTA ======= --}}
    <section class="py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-2xl sm:text-3xl mb-3">Ready to shop from the Middle East?</h2>
                <p class="mb-6 text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">Sign up for a free forwarding address and start shopping in minutes — no membership fees, tax-free shipping, and 45 days of free storage.</p>
                <a href="{{ route('register') }}" class="dp-btn dp-btn-accent">Get Started Free <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}
@endsection
