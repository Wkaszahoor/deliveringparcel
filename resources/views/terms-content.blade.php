@extends('layouts.fmaster')

@section('title', 'Terms of Business | Delivering Parcel')

@section('meta_description', 'The legally binding agreement between you, registered Shippers, and Deliveringparcel Limited governing our parcel forwarding and purchase assistance marketplace.')

@section('keywords', 'terms of business, terms and conditions, deliveringparcel terms')

@section('content')

@php
    try {
        $dpTermsUpdatedAt = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'terms_content')->value('updated_at');
    } catch (\Throwable $e) {
        $dpTermsUpdatedAt = null;
    }

    // Kept in sync with resources/views/term-condition.blade.php's TOC — the
    // admin-editable $content is authored with the same section ids, so the
    // same nav works whichever of the two views ends up serving the route
    // (see the terms-and-conditions route in routes/web.php).
    $dpLegalToc = [
        ['id' => 'introduction', 'label' => '1. Introduction & company information'],
        ['id' => 'marketplace-overview', 'label' => '2. Marketplace overview & our role'],
        ['id' => 'shopper-responsibilities', 'label' => '3. Shopper responsibilities'],
        ['id' => 'shipper-responsibilities', 'label' => '4. Shipper responsibilities'],
        ['id' => 'forwarding-storage', 'label' => '5. Forwarding, storage & abandoned goods'],
        ['id' => 'customs-taxes', 'label' => '6. Customs, taxes & import duties'],
        ['id' => 'dispute-resolution', 'label' => '7. Dispute resolution & moderation'],
        ['id' => 'prohibited-items', 'label' => '8. Prohibited & restricted items'],
        ['id' => 'summary-matrix', 'label' => 'Summary matrix of core responsibilities'],
    ];
@endphp

<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mb-4">Terms of Business</h1>
                <span class="dp-legal-meta-chip"><i class="fa-solid fa-arrows-rotate text-xs" aria-hidden="true"></i> Last updated: {{ $dpTermsUpdatedAt ? \Carbon\Carbon::parse($dpTermsUpdatedAt)->format('F j, Y') : date('F j, Y') }}</span>
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
                        @foreach ($dpLegalToc as $item)
                            <a href="#{{ $item['id'] }}" class="dp-legal-toc-link">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </nav>

                <article class="dp-legal-body max-w-3xl">
                    {!! $content !!}
                </article>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}

@endsection
