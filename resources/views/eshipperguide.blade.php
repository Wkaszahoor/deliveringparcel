@extends('layouts.fmaster')

@section('title', 'Become a Shipper | Earn by Forwarding Parcels Worldwide — Delivering Parcel')

@section('meta_description', 'Turn your local address into global income. Register as a Delivering Parcel shipper, forward or buy parcels for international shoppers, and grow from Level 1 to unlimited orders across unlimited countries.')

@section('keywords', 'become a shipper, parcel forwarding, proxy buyer, proxy purchaser, package forwarding service, shipper registration, international reshipping')

@section('og_title', 'Become a Shipper — Earn by Forwarding Parcels Worldwide')
@section('og_description', 'Join a global, peer-to-peer network of shippers. Flexible schedule, no special equipment, and an income that scales with your reputation.')

@push('head')
{{-- Kept in sync with the visible FAQ accordion further down this page —
     same convention as ebecomeshopper.blade.php's FAQPage block. --}}
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {"@type":"Question","name":"How will I know if I have a new request?","acceptedAnswer":{"@type":"Answer","text":"Once your account is verified, you'll receive notifications via email or your dashboard for any incoming Buy for Me or Ship for Me request."}},
        {"@type":"Question","name":"What should I do if the item URL or description doesn't match?","acceptedAnswer":{"@type":"Answer","text":"Always double-check the URL and item description provided by the shopper. If there's any mismatch or confusion, contact the shopper immediately to confirm the correct details before proceeding."}},
        {"@type":"Question","name":"What if the item is sold out or unavailable?","acceptedAnswer":{"@type":"Answer","text":"Notify the Delivering Parcel team immediately and process a refund as per our policy for unattended orders."}},
        {"@type":"Question","name":"How do I calculate the total cost for the shopper?","acceptedAnswer":{"@type":"Answer","text":"Add the item price, local delivery charges, and local taxes. For Buy for Me, also include moderation fees, payment gateway charges and your forwarding service fee. For Ship for Me, calculate only the shipping cost and your forwarding service fee."}},
        {"@type":"Question","name":"What happens after the shopper accepts the total cost?","acceptedAnswer":{"@type":"Answer","text":"For Buy for Me, the item cost is transferred to your wallet and you must purchase it within 2 working days, or it's refunded and the order is canceled. For Ship for Me, the shopper orders the item directly to your provided address."}},
        {"@type":"Question","name":"What should I do when I receive a parcel?","acceptedAnswer":{"@type":"Answer","text":"Notify the Delivering Parcel team the same day, check every package for damage or missing items, then open the parcel and confirm its contents with the shopper — clear photos are a mandatory step."}},
        {"@type":"Question","name":"What are value-added services, and how do I handle them?","acceptedAnswer":{"@type":"Answer","text":"Services like repacking, extra bubble wrap or safety tape should be completed exactly as agreed. Fill out the customs declaration form accurately and get the shopper's confirmation before shipping."}},
        {"@type":"Question","name":"How does Delivering Parcel handle fees and charges?","acceptedAnswer":{"@type":"Answer","text":"Delivering Parcel takes a share of every transaction, and a detailed breakdown of the fees is provided to both you and the shopper for full transparency."}}
    ]
}
</script>
@endpush

@section('content')

@php
    // All copy below is synthesized from the four shipper reference guides
    // supplied by the site owner (shipper main page, how-to-register,
    // instructions, and FAQ) — real product facts only, nothing invented.
    $dpShipperRoles = [
        ['icon' => 'fa-house-flag', 'title' => 'Parcel forwarder', 'desc' => 'You provide a local, proxy shipping address. Retailers see a local order — the parcel just continues on to your international shopper afterward.'],
        ['icon' => 'fa-bag-shopping', 'title' => 'Proxy buyer', 'desc' => 'You purchase items locally on the shopper\'s behalf, in-store or online, whenever a store won\'t sell to overseas customers or accept their payment method.'],
        ['icon' => 'fa-comments-dollar', 'title' => 'Proxy purchaser', 'desc' => 'You handle the whole transaction — bidding in auctions, navigating local checkout, or negotiating in the local language — for shoppers facing barriers you don\'t.'],
    ];

    $dpShipperEssential = [
        'When online retailers don\'t deliver to the customer\'s home country.',
        'To circumvent high shipping fees for international orders.',
        'For package consolidation, combining multiple purchases into lower overall shipping costs.',
        'When expedited or customized delivery services are required.',
    ];

    $dpShipperServices = [
        ['icon' => 'fa-box', 'title' => 'Package forwarding'],
        ['icon' => 'fa-layer-group', 'title' => 'Package consolidation'],
        ['icon' => 'fa-warehouse', 'title' => 'Storage facility'],
        ['icon' => 'fa-rotate-left', 'title' => 'Return handling'],
        ['icon' => 'fa-truck-fast', 'title' => 'Flexible delivery options'],
        ['icon' => 'fa-user-tie', 'title' => 'Personal shopper service'],
        ['icon' => 'fa-gem', 'title' => 'Luxury shopper service'],
        ['icon' => 'fa-store', 'title' => 'In-store collection'],
        ['icon' => 'fa-map-pin', 'title' => 'Pick-up from defined location'],
        ['icon' => 'fa-cart-shopping', 'title' => 'Buy-for-Me / Ship-for-Me'],
        ['icon' => 'fa-magnifying-glass', 'title' => 'Purchase assistance'],
        ['icon' => 'fa-address-card', 'title' => 'Residential address provision'],
        ['icon' => 'fa-file-invoice', 'title' => 'Same billing & shipping address'],
        ['icon' => 'fa-receipt', 'title' => 'Local tax number'],
        ['icon' => 'fa-tags', 'title' => 'Local promotions & sales'],
        ['icon' => 'fa-language', 'title' => 'Language assistance'],
        ['icon' => 'fa-camera-retro', 'title' => 'Product inspection & photos'],
    ];

    $dpShipperBenefits = [
        ['icon' => 'fa-diagram-project', 'title' => 'Streamlined shipping processes', 'desc' => 'Manage every shipment from one centralized dashboard instead of juggling multiple platforms.'],
        ['icon' => 'fa-network-wired', 'title' => 'Access to a wide network of carriers', 'desc' => 'Tap into an extensive carrier network locally and globally, with better rates and delivery timelines.'],
        ['icon' => 'fa-sack-dollar', 'title' => 'Cost-effective solutions', 'desc' => 'Delivering Parcel leverages its network and resources for competitive pricing, without compromising service quality.'],
        ['icon' => 'fa-chart-line', 'title' => 'Real-time tracking and analytics', 'desc' => 'Detailed reporting keeps you informed and in control of every shipment, every step of the way.'],
        ['icon' => 'fa-arrows-up-down-left-right', 'title' => 'Scalable services', 'desc' => 'Whether you ship a few packages or manage high volumes, the platform grows alongside your business.'],
    ];

    $dpShipperSteps = [
        ['icon' => 'fa-door-open', 'title' => 'Access the registration portal', 'desc' => 'Visit the Delivering Parcel website and click "Sign Up" or "Register" on the homepage to open the registration form.'],
        ['icon' => 'fa-list-check', 'title' => 'Complete the registration form', 'desc' => 'Fill in your personal and business credentials — full name, contact information, company name (if applicable), and business address.'],
        ['icon' => 'fa-id-card', 'title' => 'Submit identification information', 'desc' => 'Upload a clear, valid government-issued ID (passport, national ID or driver\'s license) for identity verification — mandatory for every shipper.'],
        ['icon' => 'fa-share-nodes', 'title' => 'Add verification via social media', 'desc' => 'Provide at least two active social media account links — for example your professional Facebook and LinkedIn — to strengthen your registration profile.'],
        ['icon' => 'fa-file-signature', 'title' => 'Consent and authorization', 'desc' => 'Sign a consent form agreeing to provide accurate details and authorizing Delivering Parcel to verify your identity and credentials.'],
        ['icon' => 'fa-users', 'title' => 'Provide references', 'desc' => 'List two references who can vouch for your professionalism and reliability, with full name, phone number and Facebook ID.'],
        ['icon' => 'fa-camera', 'title' => 'Upload a recent photo', 'desc' => 'A clear, recent photo of yourself helps confirm your identity and adds an extra layer of account security.'],
        ['icon' => 'fa-circle-check', 'title' => 'Review and submit', 'desc' => 'Carefully review every entered detail and uploaded document, then submit for review. We\'ll verify your information and update you on your status.'],
    ];

    $dpShipperQuestionnaire = [
        'How many countries are you able to provide shipping services to?',
        'Can you offer "Buy for Me" or purchase assistance services?',
        'Are you able to provide storage facilities for shipments?',
        'What type of residence do you have (apartment, house, villa)?',
        'Can you act as a personal shopper for your clients?',
    ];

    $dpShipperLevels = [
        [
            'level' => '1', 'title' => 'Starter',
            'desc' => 'You can take Buy for Me orders — purchasing items on behalf of shoppers as requested. Payments are processed securely via Stripe, and the item cost transfers straight into your shipper wallet for purchasing.',
            'icon' => 'fa-seedling',
        ],
        [
            'level' => '2', 'title' => 'Trusted',
            'desc' => 'Once you meet the performance criteria, you gain Ship for Me — letting shoppers use your address directly. Manage up to 10 orders at a time (slots fully restore on each completion), across up to three countries.',
            'icon' => 'fa-medal',
        ],
        [
            'level' => '3', 'title' => 'Elite',
            'desc' => 'Consistently excellent, compliant service earns you unlimited orders and services from an unlimited number of countries — the top tier, reserved for the most dedicated, high-performing shippers.',
            'icon' => 'fa-crown',
        ],
    ];

    $dpShipperGuidelines = [
        'Fully understand and adhere to all Delivering Parcel business terms and conditions.',
        'Fulfil orders with honesty and integrity at all times.',
        'Accurately place and manage orders, clearly communicating all fees to shoppers.',
        'Stay updated on customs clearance procedures and current shipping rates.',
        'Take and share detailed, clear package photos with shoppers prior to shipment.',
        'Use the Delivering Parcel dashboard for every step of order management and tracking.',
        'Remain calm, courteous and respectful in every interaction.',
        'Consistently maintain high standards of service and strong communication skills.',
    ];

    $dpShipperFaqs = [
        ['q' => 'How will I know if I have a new request?', 'a' => 'Once your account is verified, you\'ll receive notifications via email or your dashboard for any incoming Buy for Me or Ship for Me request.'],
        ['q' => 'What should I do if the item URL or description doesn\'t match?', 'a' => 'Always double-check the URL and item description provided by the shopper. If there\'s any mismatch or confusion, contact the shopper immediately to confirm the correct details before proceeding.'],
        ['q' => 'What if the item is sold out or unavailable?', 'a' => 'Notify the Delivering Parcel team immediately and process a refund as per our policy for unattended orders.'],
        ['q' => 'How do I calculate the total cost for the shopper?', 'a' => 'Add the item price, local delivery charges, and local taxes. For Buy for Me, also include moderation fees, payment gateway charges and your forwarding service fee. For Ship for Me, calculate only the shipping cost and your forwarding service fee.'],
        ['q' => 'What happens after the shopper accepts the total cost?', 'a' => 'For Buy for Me, the item cost is transferred to your wallet and you must purchase it within 2 working days, or it\'s refunded and the order is canceled. For Ship for Me, the shopper orders the item directly to your provided address.'],
        ['q' => 'What should I do when I receive a parcel?', 'a' => 'Notify the Delivering Parcel team the same day, check every package for damage or missing items, then open the parcel and confirm its contents with the shopper — clear photos are a mandatory step.'],
        ['q' => 'What are value-added services, and how do I handle them?', 'a' => 'Services like repacking, extra bubble wrap or safety tape should be completed exactly as agreed. Fill out the customs declaration form accurately and get the shopper\'s confirmation before shipping.'],
        ['q' => 'How does Delivering Parcel handle fees and charges?', 'a' => 'Delivering Parcel takes a share of every transaction, and a detailed breakdown of the fees is provided to both you and the shopper for full transparency.'],
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
                        Your local address is worth more than you think.
                    </h1>
                    <p class="dp-hero-in dp-hero-in-2 mt-5 text-base leading-relaxed text-white/70 max-w-xl">
                        Become a Delivering Parcel shipper and turn it into global income. Buy or forward parcels for shoppers who can't get a store to ship to them — work when you want, from wherever you are, no special equipment required.
                    </p>
                    <div class="dp-hero-in dp-hero-in-3 mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('shipper.register.form') }}" class="dp-btn dp-btn-accent group">
                            Apply to become a shipper <i class="fas fa-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
                        </a>
                        <a href="#steps" class="dp-btn" style="background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.35)">
                            See how it works <i class="fas fa-arrow-down text-xs"></i>
                        </a>
                    </div>
                    <div class="dp-hero-in dp-hero-in-4 mt-9 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm text-white/60">
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-xs" style="color:var(--dp-accent)" aria-hidden="true"></i> Flexible schedule</span>
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-xs" style="color:var(--dp-accent)" aria-hidden="true"></i> No special equipment needed</span>
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-xs" style="color:var(--dp-accent)" aria-hidden="true"></i> Serve shoppers internationally</span>
                    </div>
                </div>

                {{-- User-supplied registration illustration, with two
                     floating fact chips (.dp-card already carries the
                     shadow/border/radius this needs, so no new CSS). Wider,
                     landscape framing (16:9) to match the source art's own
                     aspect ratio rather than force-cropping it into the
                     previous portrait photo's frame. --}}
                <div class="dp-hero-in dp-hero-in-art relative mx-auto hidden lg:block" style="width:30rem">
                    <img src="{{ asset('frontend/assets/img/shipper-guide-hero.jpg') }}"
                         alt="A shipper registering their account on a laptop at a standing desk"
                         class="relative z-10 mx-auto block w-full rounded-2xl object-cover shadow-2xl"
                         style="aspect-ratio:16/9"
                         loading="lazy" width="480" height="270">

                    <div class="dp-card absolute -left-6 -top-6 z-20 hidden xl:flex items-center gap-3 px-4 py-3" style="max-width:13rem">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" style="background:var(--dp-accent-soft)">
                            <i class="fa-solid fa-earth-americas text-sm" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                        </span>
                        <span class="text-xs font-semibold leading-snug" style="color:var(--dp-ink)">Not limited to your own country — serve shoppers anywhere</span>
                    </div>

                    <div class="dp-card absolute -right-4 -bottom-6 z-20 hidden xl:flex items-center gap-3 px-4 py-3" style="max-width:13rem">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" style="background:var(--dp-cyan-soft)">
                            <i class="fa-solid fa-arrow-trend-up text-sm" style="color:var(--dp-cyan-dark)" aria-hidden="true"></i>
                        </span>
                        <span class="text-xs font-semibold leading-snug" style="color:var(--dp-ink)">Income scales with your reputation and performance</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======= Who are shippers ======= --}}
    <section class="relative py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4 relative" data-aos="fade-up">
            <div class="max-w-2xl mb-12">
                <h2 class="text-3xl sm:text-4xl mb-4">Who are shippers?</h2>
                <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                    Shippers are everyday individuals or businesses who bridge the gap between global retailers and international buyers — providing a proxy shipping address for customers who can't otherwise get an item shipped to their country. One registration can cover any (or all) of these three roles.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-14">
                @foreach ($dpShipperRoles as $role)
                    <div class="dp-card p-6">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl mb-4" style="background:var(--dp-accent-soft)">
                            <i class="fa-solid {{ $role['icon'] }} text-lg" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                        </div>
                        <h3 class="dp-display text-lg mb-2">{{ $role['title'] }}</h3>
                        <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{{ $role['desc'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="dp-card p-7 sm:p-8" style="background:#fff">
                <h3 class="dp-display text-lg mb-4">Shippers are especially essential when:</h3>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
                    @foreach ($dpShipperEssential as $reason)
                        <li class="flex items-start gap-3 text-sm leading-relaxed" style="color:var(--dp-ink-soft)">
                            <i class="fa-solid fa-circle-check mt-0.5 text-xs shrink-0" style="color:var(--dp-accent)" aria-hidden="true"></i>
                            {{ $reason }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ======= Services shippers can provide ======= --}}
    <section class="relative py-16 lg:py-20" style="background:#fff">
        <div class="container mx-auto px-4" data-aos="fade-up">
            <div class="max-w-xl mb-10">
                <h2 class="text-3xl sm:text-4xl mb-4">What services can you provide?</h2>
                <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">By registering, you can offer as few or as many of these services as fit your situation — set your own preferences when you apply.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach ($dpShipperServices as $service)
                    <div class="dp-card flex items-center gap-3 px-4 py-3.5">
                        <i class="fa-solid {{ $service['icon'] }} text-sm shrink-0" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                        <span class="text-xs font-semibold leading-snug" style="color:var(--dp-ink)">{{ $service['title'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======= Why register with Delivering Parcel ======= --}}
    <section class="relative py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4" data-aos="fade-up">
            <div class="max-w-xl mb-10">
                <h2 class="text-3xl sm:text-4xl">Why register with Delivering Parcel</h2>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2">
                @foreach ($dpShipperBenefits as $i => $item)
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

    {{-- ======= Registration steps — vertical timeline with live ticker,
         same dpStepsTicker component reused from ebecomeshopper.blade.php ======= --}}
    <section class="relative py-16 lg:py-20" id="steps" style="background:#fff">
        <div class="container mx-auto px-4 relative" data-aos="fade-up">
            <div class="max-w-xl mb-12">
                <h2 class="text-3xl sm:text-4xl mb-4">Registering, step by step</h2>
                <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">Clear and secure, with the same rules and requirements in every country where registration is available.</p>
            </div>

            <div x-data="dpStepsTicker" data-steps="{{ json_encode(array_map(fn ($s) => ['icon' => $s['icon'], 'title' => $s['title']], $dpShipperSteps)) }}">
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
                        @foreach ($dpShipperSteps as $i => $step)
                            <div class="relative flex items-start gap-5 sm:gap-6">
                                <div class="dp-journey-node relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-full"
                                     :class="{ 'dp-step-ticker-progress': current === {{ $i }} && phase === 'progress', 'dp-step-ticker-done': current === {{ $i }} && phase === 'done' }">
                                    <i class="fa-solid {{ $step['icon'] }} text-lg" aria-hidden="true"></i>
                                    <span class="dp-journey-step-num">{{ $i + 1 }}</span>
                                </div>
                                <div class="pt-2">
                                    <h3 class="dp-display text-lg mb-1.5">{{ $step['title'] }}</h3>
                                    <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="max-w-2xl mx-auto mt-14 dp-card p-7 sm:p-8">
                <h3 class="dp-display text-base mb-3">After you submit: a short questionnaire</h3>
                <p class="text-sm leading-relaxed mb-4" style="color:var(--dp-ink-soft)">A few quick questions help us understand exactly what you can offer, and connect you with the shoppers who need it:</p>
                <ul class="flex flex-col gap-2.5">
                    @foreach ($dpShipperQuestionnaire as $q)
                        <li class="flex items-start gap-3 text-sm leading-relaxed" style="color:var(--dp-ink-soft)">
                            <i class="fa-solid fa-angle-right mt-1 text-xs shrink-0" style="color:var(--dp-accent)" aria-hidden="true"></i>
                            {{ $q }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="text-center mt-14">
                <a href="{{ route('shipper.register.form') }}" class="dp-btn dp-btn-accent">Start your application <i class="fas fa-arrow-right text-sm"></i></a>
            </div>
        </div>
    </section>

    {{-- ======= Shipper levels ======= --}}
    <section class="relative py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4" data-aos="fade-up">
            <div class="max-w-xl mb-12">
                <h2 class="text-3xl sm:text-4xl mb-4">Grow from Level 1 to unlimited</h2>
                <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">Once verified, you're a shipper — from there, reliability and high service standards unlock more.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($dpShipperLevels as $lvl)
                    <div class="dp-card p-7 sm:p-8">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background:var(--dp-accent-soft)">
                                <i class="fa-solid {{ $lvl['icon'] }} text-lg" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                            </div>
                            <h3 class="dp-display text-xl">Level {{ $lvl['level'] }} <span class="text-sm font-normal" style="color:var(--dp-ink-soft)">— {{ $lvl['title'] }}</span></h3>
                        </div>
                        <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{{ $lvl['desc'] }}</p>
                    </div>
                @endforeach
            </div>
            <p class="text-sm text-center mt-8 max-w-xl mx-auto" style="color:var(--dp-ink-soft)">
                Every shopper rates their shipper after each order — communication, accuracy of fees, overall value and delivery time. Stronger ratings unlock higher levels, more order opportunities, and eligibility for advanced services.
            </p>
        </div>
    </section>

    {{-- ======= Guidelines + pricing transparency ======= --}}
    <section class="relative py-16 lg:py-20" style="background:#fff">
        <div class="container mx-auto px-4" data-aos="fade-up">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-3xl sm:text-4xl mb-6">Guidelines for every shipper</h2>
                    <ul class="flex flex-col gap-3.5">
                        @foreach ($dpShipperGuidelines as $g)
                            <li class="flex items-start gap-3 text-sm leading-relaxed" style="color:var(--dp-ink-soft)">
                                <i class="fa-solid fa-circle-check mt-0.5 text-xs shrink-0" style="color:var(--dp-accent)" aria-hidden="true"></i>
                                {{ $g }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="flex flex-col gap-6">
                    <div class="dp-card p-7 sm:p-8">
                        <h3 class="dp-display text-lg mb-2">Pricing accuracy &amp; transparency</h3>
                        <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">
                            Research shipping rates thoroughly before quoting. The price you offer a shopper must be accurate and final — no additional charges can be added once an offer is accepted. Only make an offer once you're certain about the total cost.
                        </p>
                    </div>
                    <div class="dp-card p-7 sm:p-8">
                        <h3 class="dp-display text-lg mb-2">Handling unexpected parcel dimensions</h3>
                        <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">
                            If a parcel's actual size differs from what was originally provided, you may request an additional payment through the dashboard's message system — always clear, polite, and through official channels. Personal information is never shared outside the platform.
                        </p>
                    </div>
                </div>
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
                <div class="lg:w-9/12 w-full" x-data="dpFaqAccordion" data-faqs="{{ json_encode($dpShipperFaqs) }}">
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
                    <h2 class="dp-display text-2xl sm:text-3xl text-white mb-3">Start your shipper journey today</h2>
                    <p class="text-white/60 text-sm mb-8 max-w-xl mx-auto">It's quick, easy, and rewarding — all you need is an internet connection and some free time.</p>
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <a href="{{ route('shipper.register.form') }}" class="dp-btn dp-btn-accent">Register now <i class="fas fa-arrow-right text-sm"></i></a>
                        <a href="{{ route('shipper.program') }}" class="dp-btn" style="background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.35)">See perks &amp; payouts</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}

@endsection
