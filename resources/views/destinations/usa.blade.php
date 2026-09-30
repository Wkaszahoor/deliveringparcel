@extends('layouts.fmaster')

@section('title', 'USA Parcel Forwarding Company | Delivering Parcel')
@section('meta_description', 'Get a free virtual address in the USA and shop Amazon, Walmart, Nike and every other American retailer — Delivering Parcel forwards your purchases anywhere in the world.')

@section('content')
<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <span class="dp-legal-meta-chip"><i class="fas fa-earth-americas text-xs" aria-hidden="true"></i> Parcel Forwarding · USA</span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mt-4 mb-4">USA Parcel Forwarding Company</h1>
                <p class="max-w-2xl text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                    The latest electronics, fashionable clothing or limited-edition US brands — a free virtual address in the USA lets you shop Amazon, eBay and every other American store, then have it all forwarded to your physical address regardless of where you live.
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
                        <a href="#benefits" class="dp-legal-toc-link">Benefits</a>
                        <a href="#brands" class="dp-legal-toc-link">Brands you can reach</a>
                        <a href="#how-it-works" class="dp-legal-toc-link">How it works</a>
                        <a href="#stores" class="dp-legal-toc-link">Top US stores</a>
                    </div>
                </nav>

                <article class="dp-legal-body max-w-3xl">

                    <h2 id="benefits">Benefits of USA parcel forwarding</h2>
                    <p>The most remarkable benefit is tapping into exclusive American products that aren't sold elsewhere — items often limited to U.S. residents. Beyond selection, forwarding offers real value: affordable shipping rates versus direct international shipping, and package consolidation that combines several orders into one shipment.</p>
                    <p>A U.S. virtual address also makes checkout simple — no more overseas-address rejections. Your package is handled through reputable reshippers to ensure safe transit, and responsive customer support helps resolve any questions along the way.</p>

                    <h2 id="brands">Brands you can reach</h2>
                    <ul>
                        <li><strong>Fashion:</strong> Ralph Lauren, Levi's, Nike and other iconic American labels.</li>
                        <li><strong>Beauty:</strong> Estée Lauder, MAC and other brands with limited editions exclusive to US stores.</li>
                        <li><strong>Home goods:</strong> West Elm, Crate &amp; Barrel and other contemporary furniture and décor brands.</li>
                        <li><strong>Gourmet:</strong> BBQ sauces, handcrafted chocolate and other American specialty foods, shipped overseas through trusted forwarding.</li>
                    </ul>

                    <h2 id="how-it-works">How it works</h2>
                    <ol>
                        <li><strong>Sign up</strong> for a virtual US address with Delivering Parcel.</li>
                        <li><strong>Shop</strong> your favorite US retailers and use your new address at checkout.</li>
                        <li><strong>Consolidate</strong> — combine several orders into one shipment to cut costs; we can repackage efficiently to save materials and space.</li>
                        <li><strong>Ship and track</strong> — once your items arrive at our facility, we forward them to your international doorstep with tracking the whole way.</li>
                    </ol>
                    <p>Free storage at our USA facility gives you a few days to shop around or wait for other items before sending everything together, and pricing stays transparent — always confirm what the base fee covers versus customs charges or special handling.</p>

                    <h2 id="stores">Top online shopping websites in the USA</h2>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Amazon</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Walmart</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> eBay</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Target</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Best Buy</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Home Depot</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Macy's</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Wayfair</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Nordstrom</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Kohl's</span>
                    </div>

                </article>
            </div>
        </div>
    </section>

    {{-- ======= CTA ======= --}}
    <section class="py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-2xl sm:text-3xl mb-3">Ready to shop from the USA?</h2>
                <p class="mb-6 text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">Create a free request to get a reship quote today — no membership fees, tax-free shipping, and 45 days of free storage.</p>
                <a href="{{ route('register') }}" class="dp-btn dp-btn-accent">Get Started Free <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}
@endsection
