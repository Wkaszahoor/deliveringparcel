@extends('layouts.fmaster')

@section('title', 'Become a Shopper | Shop Any Store, Ship Anywhere — Delivering Parcel')

@section('meta_description', 'Shop from Amazon, Zara, Nike, IKEA and hundreds of other stores that won\'t ship to you — Delivering Parcel connects you with a global network of shippers who buy or forward on your behalf. Free to join, money-back guaranteed.')

@section('keywords', 'become a shopper, international shopping, parcel forwarding, buy for me, ship for me, purchase assistance, package forwarding service')

@section('og_title', 'Become a Shopper — Shop Any Store, Ship Anywhere')
@section('og_description', 'A global network of registered shippers who buy or forward your international purchases. Free to join, no hidden fees, full money-back guarantee.')

@push('head')
{{-- Kept in sync with the visible FAQ accordion further down this page —
     same convention as home.blade.php's FAQPage block (see that file's
     comment for why it isn't translated: no hreflang alternates exist yet
     for this pilot, and this page isn't part of it in the first place). --}}
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {"@type":"Question","name":"Do I have to pay anything to submit a request?","acceptedAnswer":{"@type":"Answer","text":"No. Creating a request is completely free, and you won't be asked to pay until you've reviewed and accepted an offer from a shipper."}},
        {"@type":"Question","name":"What happens if my item isn't available or the shipper can't complete my request?","acceptedAnswer":{"@type":"Answer","text":"You'll receive a full refund. Delivering Parcel guarantees a full money-back refund if the item is unavailable or the shipper is unable to provide the service."}},
        {"@type":"Question","name":"How long does a Buy for Me purchase take?","acceptedAnswer":{"@type":"Answer","text":"Your shipper will complete the purchase within 2 working days of payment. If they're unable to do so, you'll be issued a full refund."}},
        {"@type":"Question","name":"What if the item I receive doesn't match the description?","acceptedAnswer":{"@type":"Answer","text":"Before your parcel is dispatched, you'll have the chance to review the item's colour, quantity, size and photos on your dashboard. If something doesn't match, you can flag the issue at this stage, before shipping."}},
        {"@type":"Question","name":"Will my shipping cost change after I accept an offer?","acceptedAnswer":{"@type":"Answer","text":"Your offer is based on the weight you provide, so as long as this is accurate, there shouldn't be any hidden costs. Costs may only change if the actual weight of your item differs from what was originally submitted."}},
        {"@type":"Question","name":"Can I choose which courier is used for delivery?","acceptedAnswer":{"@type":"Answer","text":"Your shipper will dispatch your parcel via an agreed courier — DHL, FedEx, UPS, USPS or Royal Mail — and tracking details will be attached to your dashboard so you can follow its journey."}}
    ]
}
</script>
@endpush

@section('content')

@php
    // All copy below is synthesized from two source guides ("The Delivering
    // Parcel Shopper Guide" and "How to Become a Shopper" — both supplied by
    // the site owner), merged into one coherent 8-step walkthrough rather
    // than reproduced twice. Real product facts only (2-working-day Buy for
    // Me window, named couriers, etc.) — nothing invented.
    $dpShopperPersonas = [
        ['icon' => 'fa-bag-shopping', 'title' => 'Everyday shoppers', 'desc' => 'Want access to international deals and products a local store just doesn\'t carry.'],
        ['icon' => 'fa-gift', 'title' => 'Gift buyers', 'desc' => 'Sourcing something special from abroad for a loved one, birthday or holiday.'],
        ['icon' => 'fa-trophy', 'title' => 'Collectors', 'desc' => 'Hunting for limited-edition drops or region-exclusive items only sold in certain countries.'],
        ['icon' => 'fa-boxes-stacked', 'title' => 'Small resellers', 'desc' => 'Sourcing stock at better prices from overseas retailers.'],
        ['icon' => 'fa-earth-americas', 'title' => 'Expats & travelers', 'desc' => 'Who miss shopping from the stores back home, now that they\'ve relocated.'],
    ];

    $dpShopperDifferent = [
        ['icon' => 'fa-globe', 'title' => 'A global network of trusted shippers', 'desc' => 'Registered shippers located across the world, ready to bridge the gap between you and the stores you want to shop from.'],
        ['icon' => 'fa-sliders', 'title' => 'Two flexible service options', 'desc' => 'Choose the level of involvement that suits you — let a shipper handle the entire purchase, or place the order yourself and have it forwarded.'],
        ['icon' => 'fa-sack-dollar', 'title' => 'Money-back guarantee', 'desc' => 'If an item can\'t be purchased or a shipper can\'t fulfil your request, you receive a full refund. No hidden costs, no surprises.'],
        ['icon' => 'fa-eye', 'title' => 'Full visibility, every step of the way', 'desc' => 'Track your request, review offers, and monitor your parcel\'s journey through your personal dashboard.'],
        ['icon' => 'fa-magnifying-glass', 'title' => 'Item verification before dispatch', 'desc' => 'Shippers confirm your item\'s colour, quantity, size and condition before it\'s shipped, so you know exactly what to expect.'],
    ];

    $dpShopperSteps = [
        ['icon' => 'fa-user-plus', 'title' => 'Register your free account', 'desc' => 'No sign-up fees, no monthly subscriptions. It only takes a couple of minutes to unlock your dashboard.'],
        ['icon' => 'fa-clipboard-list', 'title' => 'Submit your request', 'desc' => 'Product URL, description, colour, size, weight and quantity, plus the country you\'re shopping from and shipping to. The more detail, the more accurate your offers.'],
        ['icon' => 'fa-hourglass-half', 'title' => 'Wait for an offer', 'desc' => 'Registered shippers in that country review your request and send an offer. You\'ll be notified by email, or check your dashboard any time — submitting a request costs nothing.'],
        ['icon' => 'fa-handshake', 'title' => 'Review and accept an offer', 'desc' => 'Message the shipper directly if you have questions. No obligation to accept anything that doesn\'t feel right.'],
        ['icon' => 'fa-right-left', 'title' => 'Choose your service', 'desc' => 'Buy for Me — your shipper purchases it for you — or Ship for Me — you order it yourself and they forward it on. See the comparison below.'],
        ['icon' => 'fa-box-open', 'title' => 'Item verification', 'desc' => 'Once your shipper receives the parcel, check its colour, quantity, size and photos on your dashboard before it ships onward.'],
        ['icon' => 'fa-file-signature', 'title' => 'Customs &amp; delivery confirmation', 'desc' => 'Sign the customs declaration and confirm your delivery address. Your shipper dispatches via the agreed courier — DHL, FedEx, UPS, USPS or Royal Mail.'],
        ['icon' => 'fa-truck-fast', 'title' => 'Track and receive', 'desc' => 'Follow your parcel\'s journey on your dashboard. Once it arrives, confirm receipt — or flag a revision if something isn\'t right.'],
    ];

    $dpShopperTips = [
        ['icon' => 'fa-pen-to-square', 'title' => 'Be detailed in your request', 'desc' => 'Accurate weight, size and colour help shippers give precise offers and avoid cost adjustments later.'],
        ['icon' => 'fa-gauge-high', 'title' => 'Check your dashboard regularly', 'desc' => 'You\'ll get email notifications for offers, but your dashboard is the central hub for every stage of your request.'],
        ['icon' => 'fa-message', 'title' => 'Use the message tab', 'desc' => 'Talk directly with your shipper — it\'s the fastest way to clarify an offer or item detail.'],
        ['icon' => 'fa-camera', 'title' => 'Verify before dispatch', 'desc' => 'Review photos and details on your dashboard before the parcel ships — much easier to flag an issue now than after delivery.'],
    ];

    $dpShopperFaqs = [
        ['q' => 'Do I have to pay anything to submit a request?', 'a' => 'No. Creating a request is completely free, and you won\'t be asked to pay until you\'ve reviewed and accepted an offer from a shipper.'],
        ['q' => 'What happens if my item isn\'t available or the shipper can\'t complete my request?', 'a' => 'You\'ll receive a full refund. Delivering Parcel guarantees a full money-back refund if the item is unavailable or the shipper is unable to provide the service.'],
        ['q' => 'How long does a Buy for Me purchase take?', 'a' => 'Your shipper will complete the purchase within 2 working days of payment. If they\'re unable to do so, you\'ll be issued a full refund.'],
        ['q' => 'What if the item I receive doesn\'t match the description?', 'a' => 'Before your parcel is dispatched, you\'ll have the chance to review the item\'s colour, quantity, size and photos on your dashboard. If something doesn\'t match, you can flag the issue at this stage, before shipping.'],
        ['q' => 'Will my shipping cost change after I accept an offer?', 'a' => 'Your offer is based on the weight you provide, so as long as this is accurate, there shouldn\'t be any hidden costs. Costs may only change if the actual weight of your item differs from what was originally submitted.'],
        ['q' => 'Can I choose which courier is used for delivery?', 'a' => 'Your shipper will dispatch your parcel via an agreed courier — DHL, FedEx, UPS, USPS or Royal Mail — and tracking details will be attached to your dashboard so you can follow its journey.'],
    ];

    // Same real logo assets already shipped for home.blade.php's "Shop any
    // store" marquee — reused, not duplicated as new files.
    $dpShopperStores = [
        ['href' => 'https://www.amazon.com/', 'img' => 'amazon-png-logo-vector-6701', 'alt' => 'Shop on Amazon'],
        ['href' => 'https://www.zara.com/uk/', 'img' => '33', 'alt' => 'Shop on Zara'],
        ['href' => 'https://www.etsy.com/', 'img' => '17', 'alt' => 'Shop on Etsy'],
        ['href' => 'https://www.walmart.com/', 'img' => 'ebay', 'alt' => 'Shop on eBay'],
        ['href' => 'https://www.carrefour.com/en', 'img' => '2', 'alt' => 'Shop on Carrefour'],
        ['href' => 'https://www.flipkart.com/', 'img' => '7', 'alt' => 'Shop on Flipkart'],
        ['href' => 'https://www.lazada.com/en/', 'img' => '8', 'alt' => 'Shop on Lazada'],
    ];
@endphp

<div class="dp-home">

    {{-- ======= Hero ======= --}}
    <section class="dp-grain relative overflow-hidden pt-36 pb-20 lg:pt-44 lg:pb-24" style="background:var(--dp-navy)">
        <div class="dp-blob dp-blob-1 absolute -left-24 top-0 -z-0 h-96 w-96 opacity-25" style="background: radial-gradient(circle, var(--dp-accent), transparent 70%)" aria-hidden="true"></div>
        <div class="dp-blob dp-blob-2 absolute -right-24 bottom-0 -z-0 h-96 w-96 opacity-20" style="background: radial-gradient(circle, var(--dp-cyan), transparent 70%)" aria-hidden="true"></div>

        <div class="container mx-auto px-4 relative">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                <div class="max-w-2xl">
                    <h1 class="dp-hero-in dp-hero-in-1 text-white text-[2.3rem] leading-[1.08] sm:text-5xl">
                        Found the perfect item, but the store won't ship to you?
                    </h1>
                    <p class="dp-hero-in dp-hero-in-2 mt-5 text-base leading-relaxed text-white/70 max-w-xl">
                        Millions of shoppers hit that wall every day — a limited-edition sneaker drop, a home decor piece, a skincare brand that just isn't sold locally. Delivering Parcel connects you with a global network of registered shippers who buy or forward it for you, no matter where you are.
                    </p>
                    <div class="dp-hero-in dp-hero-in-3 mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('country') }}" class="dp-btn dp-btn-accent group">
                            Submit your first request <i class="fas fa-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
                        </a>
                        <a href="#steps" class="dp-btn" style="background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.35)">
                            See how it works <i class="fas fa-arrow-down text-xs"></i>
                        </a>
                    </div>
                    <div class="dp-hero-in dp-hero-in-4 mt-9 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm text-white/60">
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-xs" style="color:var(--dp-accent)" aria-hidden="true"></i> Free to join</span>
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-xs" style="color:var(--dp-accent)" aria-hidden="true"></i> Money-back guarantee</span>
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-xs" style="color:var(--dp-accent)" aria-hidden="true"></i> No card charged until you accept an offer</span>
                    </div>
                </div>

                {{-- Shopper avatar with two real orbit rings — flags revolve
                     clockwise on the outer ring, store badges revolve
                     counter-clockwise on the inner ring, around her as the
                     fixed center. Each badge is `rotate() translateX(radius)
                     rotate(-)` animated (the classic single-element CSS
                     orbit: the trailing counter-rotation cancels the
                     leading one so the flag/logo itself always stays
                     upright while its position sweeps the circle), and every
                     badge on a ring shares one keyframe/duration — only its
                     negative animation-delay differs, spaced evenly around
                     the ring so they read as one continuous orbit rather
                     than N things happening to move in sync. Purely
                     decorative, so it pauses (not jumps to a bunched-up
                     frame 0) under prefers-reduced-motion — see
                     .dp-orbit-item in app.css. --}}
                <div class="dp-hero-in dp-hero-in-art relative mx-auto hidden lg:block" style="width:26rem;height:26rem">
                    <div class="dp-orbit-glow absolute inset-8 -z-0 rounded-full" aria-hidden="true"></div>

                    <img src="{{ asset('frontend/assets/img/shopper-avatar-laptop.png') }}"
                         alt="An illustrated shopper working on a laptop, surrounded by flags and store badges from around the world"
                         class="relative z-10 mx-auto block h-full w-full object-contain drop-shadow-2xl"
                         loading="lazy" width="420" height="420">

                    @php
                        $dpOrbitFlags = [
                            ['code' => 'us'], ['code' => 'gb'], ['code' => 'jp'],
                            ['code' => 'fr'], ['code' => 'br'], ['code' => 'in'],
                        ];
                        $dpOrbitStores = [
                            ['img' => 'amazon-png-logo-vector-6701', 'alt' => 'Amazon'],
                            ['img' => '17', 'alt' => 'Etsy'],
                            ['img' => '33', 'alt' => 'Zara'],
                        ];
                        $dpFlagOrbitDuration = 24; // seconds per full revolution
                        $dpStoreOrbitDuration = 17;
                    @endphp

                    @foreach ($dpOrbitFlags as $i => $flag)
                        <span class="dp-orbit-item dp-orbit-item-flag absolute z-20 flex items-center justify-center rounded-full shadow-lg"
                              style="animation-delay:{{ -round($i * $dpFlagOrbitDuration / count($dpOrbitFlags), 2) }}s"
                              aria-hidden="true">
                            <span class="flag-icon flag-icon-{{ $flag['code'] }} w-full h-full rounded-full" style="background-size:cover;background-position:center"></span>
                        </span>
                    @endforeach

                    @foreach ($dpOrbitStores as $i => $store)
                        <span class="dp-orbit-item dp-orbit-item-store absolute z-20 flex items-center justify-center rounded-full bg-white shadow-lg"
                              style="animation-delay:{{ -round($i * $dpStoreOrbitDuration / count($dpOrbitStores), 2) }}s"
                              aria-hidden="true">
                            <img src="{{ asset('frontend/assets/img/' . $store['img'] . '.png') }}" alt="" class="max-h-full max-w-full object-contain" loading="lazy">
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ======= Who is a shopper ======= --}}
    <section class="relative py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4 relative" data-aos="fade-up">
            <div class="max-w-xl mb-12">
                <h2 class="text-3xl sm:text-4xl mb-4">Who is a shopper?</h2>
                <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                    Anyone who's ever been blocked by a "we don't ship to your region" message. No special qualifications or business registration needed — just an item you want, a country you want it shipped to, and a shipper ready to make it happen.
                </p>
            </div>

            {{-- No nested data-aos on these badges — the section container above
                 already reveals as one fade-up (see the double-reveal note
                 elsewhere on the site for why a second, independently-timed
                 scroll trigger nested inside it is a bug, not an enhancement). --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-x-6 gap-y-10">
                @foreach ($dpShopperPersonas as $persona)
                    <div class="flex flex-col items-center text-center">
                        <div class="dp-region-badge relative flex h-16 w-16 items-center justify-center rounded-2xl mb-4">
                            <span class="dp-region-badge-ping absolute inset-0 rounded-2xl" aria-hidden="true"></span>
                            <i class="fa-solid {{ $persona['icon'] }} text-xl text-white" aria-hidden="true"></i>
                        </div>
                        <h3 class="dp-display text-sm mb-1.5">{{ $persona['title'] }}</h3>
                        <p class="text-xs leading-relaxed" style="color:var(--dp-ink-soft)">{!! $persona['desc'] !!}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======= What is parcel forwarding — 3-node mini flow ======= --}}
    <section class="relative py-16" style="background:#fff">
        <div class="container mx-auto px-4 relative" data-aos="fade-up">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl sm:text-4xl mb-4">What is parcel forwarding?</h2>
                    <p class="text-base leading-relaxed mb-4" style="color:var(--dp-ink-soft)">
                        Parcel forwarding gives you a local address in the country where you want to shop. Your item arrives there and gets forwarded on to your actual location, wherever that is in the world — you "shop local" from the retailer's perspective, even from the other side of the planet.
                    </p>
                    <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                        Delivering Parcel adds one more layer: if a store won't accept your card either, a shipper can purchase the item for you too — a service called <strong style="color:var(--dp-ink)">Purchase Assistance</strong>, or "Buy for Me". Either way, you're never stuck just because of where you live.
                    </p>
                </div>

                {{-- One authored motion for this diagram: a dashed connector
                     runs the length of the row and marches continuously,
                     standing in for the parcel's own journey between nodes —
                     not three separately-animated icons doing their own thing. --}}
                <div class="dp-forward-flow relative flex items-center justify-between px-2">
                    <div class="dp-forward-line absolute left-8 right-8 top-8 -z-0" aria-hidden="true"></div>
                    @foreach ([
                        ['icon' => 'fa-store', 'label' => 'You shop'],
                        ['icon' => 'fa-house-chimney', 'label' => 'Local address receives'],
                        ['icon' => 'fa-paper-plane', 'label' => 'Forwarded to you'],
                    ] as $node)
                        <div class="relative z-10 flex flex-col items-center text-center w-1/3">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full mb-3" style="background:#fff;border:1.5px solid var(--dp-line)">
                                <i class="fa-solid {{ $node['icon'] }} text-lg" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                            </div>
                            <span class="text-xs font-semibold leading-snug px-1" style="color:var(--dp-ink)">{{ $node['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ======= Two ways to shop ======= --}}
    <section class="relative py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4 relative" data-aos="fade-up">
            <div class="max-w-xl mb-12">
                <h2 class="text-3xl sm:text-4xl mb-4">Two ways to shop</h2>
                <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">Choose the level of involvement that suits you — both lead to the same doorstep.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="dp-card p-7 sm:p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl mb-5" style="background:var(--dp-accent-soft)">
                        <i class="fa-solid fa-cart-shopping text-lg" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                    </div>
                    <h3 class="dp-display text-xl mb-3">Buy for Me <span class="text-sm font-normal" style="color:var(--dp-ink-soft)">(Purchase Assistance)</span></h3>
                    <p class="text-sm leading-relaxed mb-4" style="color:var(--dp-ink-soft)">
                        Pay the agreed amount upfront, and your shipper purchases the item within 2 working days. Once purchased, they attach the confirmation and tracking to your request. If they can't complete the purchase, you get a full refund — no questions asked.
                    </p>
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--dp-accent-dark)">Best for hands-off shoppers, or stores that won't take your card</p>
                </div>

                <div class="dp-card p-7 sm:p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl mb-5" style="background:var(--dp-cyan-soft)">
                        <i class="fa-solid fa-right-left text-lg" style="color:var(--dp-cyan-dark)" aria-hidden="true"></i>
                    </div>
                    <h3 class="dp-display text-xl mb-3">Ship for Me <span class="text-sm font-normal" style="color:var(--dp-ink-soft)">(Parcel Forwarding)</span></h3>
                    <p class="text-sm leading-relaxed mb-4" style="color:var(--dp-ink-soft)">
                        Once you pay, your shipper's address in the destination country is revealed to you. Place the order yourself directly with the store, attach your receipt, then provide the tracking number once it ships to your shipper.
                    </p>
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--dp-cyan-dark)">Best for shoppers who want to manage the purchase themselves</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ======= Shop from stores you already love (real logo assets, same
         marquee pattern as home.blade.php's "Shop any store" section) ======= --}}
    <section class="dp-grain relative py-14 overflow-hidden" style="background:var(--dp-navy)">
        <div class="container mx-auto px-4 relative mb-8">
            <div class="flex justify-center" data-aos="fade-up">
                <div class="lg:w-8/12 w-full mx-auto text-center">
                    <h2 class="dp-display text-2xl sm:text-3xl text-white mb-2">Shop from stores you already love</h2>
                    <p class="text-white/60 text-sm max-w-lg mx-auto">Amazon, eBay, Walmart, Zara, H&amp;M, Nike, Adidas, Best Buy, IKEA, Target, Costco, Etsy, Flipkart, Lazada, Shopee, Zalando, Carrefour — and hundreds more.</p>
                </div>
            </div>
        </div>
        <div class="dp-marquee relative overflow-hidden" data-aos="fade-up" data-aos-delay="100">
            <div class="dp-marquee-fade left-0" style="background: linear-gradient(to right, var(--dp-navy), transparent)"></div>
            <div class="dp-marquee-fade right-0" style="background: linear-gradient(to left, var(--dp-navy), transparent)"></div>
            <div class="dp-marquee-track">
                @for ($rep = 0; $rep < 2; $rep++)
                    @foreach ($dpShopperStores as $logo)
                        <a href="{{ $logo['href'] }}" target="_blank" rel="noopener noreferrer" class="curier-img mx-2.5 shrink-0">
                            <picture>
                                <source srcset="{{ asset('frontend/assets/img/' . $logo['img'] . '.webp') }}" type="image/webp">
                                <img src="{{ asset('frontend/assets/img/' . $logo['img'] . '.png') }}" alt="{{ $logo['alt'] }}" loading="lazy" width="160" height="50">
                            </picture>
                        </a>
                    @endforeach
                @endfor
            </div>
        </div>
    </section>

    {{-- ======= What makes it different ======= --}}
    <section class="relative py-16 lg:py-20" style="background:#fff">
        <div class="container mx-auto px-4" data-aos="fade-up">
            <div class="max-w-xl mb-10">
                <h2 class="text-3xl sm:text-4xl">What makes Delivering Parcel different</h2>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2">
                @foreach ($dpShopperDifferent as $i => $item)
                    <div class="flex items-start gap-4 border-t py-6 {{ $i % 2 === 0 ? 'lg:pr-10' : 'lg:pl-10' }}" style="border-color:var(--dp-line)">
                        <i class="fa-solid {{ $item['icon'] }} mt-1 w-5 text-center" style="color:var(--dp-accent)" aria-hidden="true"></i>
                        <div>
                            <h3 class="dp-display text-lg mb-1">{{ $item['title'] }}</h3>
                            <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======= Your 8-step journey — vertical timeline at every breakpoint
         (this page is a full walkthrough, not a landing-page teaser, so the
         accessible always-vertical layout earns its place here on its own,
         rather than forcing the homepage's 6-wide alternating ribbon to fit
         eight steps). Live ticker chip drives in sync with the timeline
         nodes below it, same dpStepsTicker component as the homepage. ======= --}}
    <section class="relative py-16 lg:py-20" id="steps" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4 relative" data-aos="fade-up">
            <div class="max-w-xl mb-12">
                <h2 class="text-3xl sm:text-4xl mb-4">Your journey, step by step</h2>
                <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">From the moment you sign up to the moment your parcel lands on your doorstep.</p>
            </div>

            <div x-data="dpStepsTicker" data-steps="{{ json_encode(array_map(fn ($s) => ['icon' => $s['icon'], 'title' => $s['title']], $dpShopperSteps)) }}">
                <div class="mb-12 flex justify-center">
                    <div class="dp-ticker inline-flex items-center gap-3 rounded-full px-4 py-2.5 sm:px-5">
                        <span class="dp-ticker-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-full">
                            <i class="fa-solid text-sm" :class="currentStep.icon" aria-hidden="true"></i>
                        </span>
                        <span class="text-sm font-semibold whitespace-nowrap" style="color:var(--dp-ink)">
                            <span x-text="'Step ' + (current + 1) + ':'"></span>
                            <span x-text="currentStep.title"></span>
                        </span>
                        <span class="dp-ticker-status-progress inline-flex items-center gap-1.5" x-show="phase === 'progress'" x-transition.opacity.duration.300ms>
                            <span class="dp-ticker-dot" aria-hidden="true"></span> In Progress
                        </span>
                        <span class="dp-ticker-status-done inline-flex items-center gap-1.5" x-show="phase === 'done'" x-cloak x-transition.opacity.duration.300ms>
                            <i class="fa-solid fa-check text-xs" aria-hidden="true"></i> Done
                        </span>
                    </div>
                </div>

                <div class="relative mx-auto max-w-2xl">
                    <div class="dp-journey-line absolute left-7 top-2 bottom-2 w-0.5" aria-hidden="true"></div>
                    <span class="dp-journey-dot absolute left-7" aria-hidden="true"></span>

                    <div class="flex flex-col gap-10 sm:gap-12">
                        @foreach ($dpShopperSteps as $i => $step)
                            <div class="relative flex items-start gap-5 sm:gap-6">
                                <div class="dp-journey-node relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-full"
                                     :class="{ 'dp-step-ticker-progress': current === {{ $i }} && phase === 'progress', 'dp-step-ticker-done': current === {{ $i }} && phase === 'done' }">
                                    <i class="fa-solid {{ $step['icon'] }} text-lg" aria-hidden="true"></i>
                                    <span class="dp-journey-step-num">{{ $i + 1 }}</span>
                                </div>
                                <div class="pt-2">
                                    <h3 class="dp-display text-lg mb-1.5">{!! $step['title'] !!}</h3>
                                    <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{!! $step['desc'] !!}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="text-center mt-14">
                <a href="{{ route('country') }}" class="dp-btn dp-btn-accent">Get Started <i class="fas fa-arrow-right text-sm"></i></a>
            </div>
        </div>
    </section>

    {{-- ======= Tips ======= --}}
    <section class="relative py-16" style="background:#fff">
        <div class="container mx-auto px-4" data-aos="fade-up">
            <div class="max-w-xl mb-10">
                <h2 class="text-3xl sm:text-4xl">Tips to get the most out of Delivering Parcel</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach ($dpShopperTips as $tip)
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background:var(--dp-accent-soft)">
                            <i class="fa-solid {{ $tip['icon'] }}" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 class="dp-display text-base mb-1">{{ $tip['title'] }}</h3>
                            <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{{ $tip['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======= FAQ ======= --}}
    <section class="relative py-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4 relative" data-aos="fade-up">
            <div class="max-w-xl mb-10">
                <h2 class="text-3xl sm:text-4xl">Frequently asked questions</h2>
            </div>
            <div class="flex justify-center">
                <div class="lg:w-9/12 w-full" x-data="dpFaqAccordion" data-faqs="{{ json_encode($dpShopperFaqs) }}">
                    <div class="flex flex-col gap-3">
                        <template x-for="(faq, index) in faqs" :key="index">
                            <div class="dp-card overflow-hidden">
                                <button type="button" class="w-full text-left flex items-center justify-between gap-3 px-5 py-4"
                                        @click="open = (open === index ? null : index)"
                                        :aria-expanded="open === index">
                                    <span class="dp-display text-sm sm:text-base" x-text="faq.q"></span>
                                    <i class="fas text-sm shrink-0 transition-transform" :class="open === index ? 'fa-minus' : 'fa-plus'" style="color:var(--dp-accent)"></i>
                                </button>
                                <div x-show="open === index" x-transition x-cloak class="px-5 pb-5 text-sm leading-relaxed" style="color:var(--dp-ink-soft)" x-text="faq.a"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======= Closing CTA ======= --}}
    <section class="dp-grain relative py-16 overflow-hidden" style="background:var(--dp-navy)">
        <div class="dp-blob dp-blob-1 absolute left-1/2 top-0 -z-0 h-96 w-96 -translate-x-1/2 opacity-20" style="background: radial-gradient(circle, var(--dp-accent), transparent 70%)" aria-hidden="true"></div>
        <div class="container mx-auto px-4 relative text-center">
            <div class="flex justify-center" data-aos="fade-up">
                <div class="lg:w-7/12 w-full mx-auto">
                    <h2 class="dp-display text-2xl sm:text-3xl text-white mb-3">Start shopping without borders</h2>
                    <p class="text-white/60 text-sm mb-8 max-w-xl mx-auto">Your next favorite find doesn't have to stay out of reach just because of where you live. Register your free account and submit your first request today.</p>
                    <a href="{{ route('country') }}" class="dp-btn dp-btn-accent">Submit your first request <i class="fas fa-arrow-right text-sm"></i></a>
                </div>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}

@endsection
