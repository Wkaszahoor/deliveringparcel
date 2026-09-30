@extends('layouts.fmaster')

@section('title', 'Australia Parcel Forwarding Service | Delivering Parcel')
@section('meta_description', 'Get a free residential address in Australia and shop Myer, David Jones, JB Hi-Fi and boutique Australian stores that don\'t ship internationally — Delivering Parcel forwards it all to your door.')

@section('content')
<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <span class="dp-legal-meta-chip"><i class="fas fa-earth-oceania text-xs" aria-hidden="true"></i> Parcel Forwarding · Australia</span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mt-4 mb-4">Australia Parcel Forwarding Service</h1>
                <p class="max-w-2xl text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                    Missing out on your favorite Australian stores because they don't ship internationally? Delivering Parcel gives you a residential Australian address, so you can shop confidently and enjoy exclusive, authentic Australian goods no matter where you live.
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
                        <a href="#why-us" class="dp-legal-toc-link">Why Delivering Parcel</a>
                        <a href="#shopping" class="dp-legal-toc-link">Shopping without limitations</a>
                        <a href="#how-it-works" class="dp-legal-toc-link">How it works</a>
                    </div>
                </nav>

                <article class="dp-legal-body max-w-3xl">

                    <h2 id="why-us">Why choose Delivering Parcel</h2>
                    <p>We're more than a standard parcel forwarding service — we're your partner in international shopping from Australia. Here's what sets us apart:</p>
                    <ul>
                        <li><strong>Residential address in Australia:</strong> a local forwarding address for parcels, even if the retailer doesn't deliver internationally — perfect for stores that only ship within Australia.</li>
                        <li><strong>Buy For Me service:</strong> found something a seller won't sell you directly? Our personal-shopper assistance handles the purchase and ships it to your doorstep.</li>
                        <li><strong>Ship For Me:</strong> already bought it? We'll receive the package in Australia and forward it to your location hassle-free.</li>
                    </ul>

                    <h2 id="shopping">Shopping without limitations</h2>
                    <p>Many popular Australian retailers, boutique stores and exclusive online marketplaces don't offer international shipping. With Delivering Parcel, you can finally shop from:</p>
                    <ul>
                        <li><strong>Australian retail giants:</strong> household names like Myer, David Jones or JB Hi-Fi — even if they won't ship outside Australia.</li>
                        <li><strong>Exclusive local boutiques:</strong> unique clothing, accessories and specialty items from small Australian businesses, without worrying about delivery policies.</li>
                        <li><strong>Niche websites:</strong> Australian niche stores offering one-of-a-kind items you've been searching for.</li>
                    </ul>

                    <h2 id="how-it-works">How it works</h2>
                    <ol>
                        <li><strong>Sign up for a residential address in Australia.</strong> Once you register, you'll be assigned a local Australian address to use as your shipping destination.</li>
                        <li><strong>Shop your favorite stores.</strong> Browse and shop from any Australian website, add items to your cart, and use your new address at checkout.</li>
                        <li><strong>Forward or Buy For Me.</strong> If you made the purchase yourself, we'll receive the package and ship it to you wherever you are — or use our Buy For Me service and we'll complete the transaction on your behalf.</li>
                        <li><strong>Swift, tracked delivery.</strong> We carefully prepare and forward your items internationally, with tracking every step of the way.</li>
                    </ol>

                    <div class="dp-legal-callout">
                        <span class="dp-legal-callout-icon"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></span>
                        <span>Beyond parcel forwarding, we're about creating ease and possibility for shoppers across the globe. Don't miss out on shopping from the best Australia has to offer.</span>
                    </div>

                </article>
            </div>
        </div>
    </section>

    {{-- ======= CTA ======= --}}
    <section class="py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-2xl sm:text-3xl mb-3">Make your next Australian purchase stress-free</h2>
                <p class="mb-6 text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">Sign up with Delivering Parcel and start enjoying access to your must-have Australian products — wherever, whenever.</p>
                <a href="{{ route('register') }}" class="dp-btn dp-btn-accent">Get Started Free <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}
@endsection
