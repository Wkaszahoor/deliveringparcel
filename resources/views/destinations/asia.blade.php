@extends('layouts.fmaster')

@section('title', 'Shop and Ship from Asia | Delivering Parcel')
@section('meta_description', 'Get a free forwarding address in Japan, Malaysia, Taiwan, Indonesia, Thailand, the Philippines, Vietnam, Singapore or Hong Kong and ship your purchases anywhere in the world with Delivering Parcel.')

@section('content')
<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl grid grid-cols-1 lg:grid-cols-[1.1fr_1fr] items-center">
                <div class="absolute inset-x-0 top-0 h-1.5 z-10" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <div class="px-6 py-8 sm:px-10 sm:py-10">
                    <span class="dp-legal-meta-chip"><i class="fas fa-earth-asia text-xs" aria-hidden="true"></i> Parcel Forwarding · Asia</span>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl mt-4 mb-4">Shop and Ship from Asia</h1>
                    <p class="max-w-2xl text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                        Asia's online marketplaces are packed with exclusive fashion, tech and collectibles that most stores won't ship abroad. Get a free local address in Japan, Malaysia, Taiwan, Indonesia, Thailand, the Philippines, Vietnam, Singapore or Hong Kong, shop like a local, and let Delivering Parcel consolidate and forward everything to your door.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ url('request') }}" class="dp-btn dp-btn-accent">Get a Free Quote <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i></a>
                        <a href="{{ route('register') }}" class="dp-btn dp-btn-outline">Create Free Account</a>
                    </div>
                </div>
                <div class="order-first lg:order-last flex items-center justify-center p-6 sm:p-8 lg:p-10">
                    <img src="{{ asset('frontend/assets/img/destinations/asia-hero.jpg') }}"
                         alt="A woman shopping online from an armchair, surrounded by the flags of Japan, South Korea, Thailand, Vietnam, Taiwan, the Philippines, Malaysia and Singapore"
                         class="w-full max-w-[220px] sm:max-w-xs rounded-xl object-cover" style="box-shadow:0 20px 40px -16px rgba(20,23,26,.35)"
                         loading="eager" width="1100" height="613">
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
                        <a href="#japan" class="dp-legal-toc-link">Japan</a>
                        <a href="#malaysia" class="dp-legal-toc-link">Malaysia</a>
                        <a href="#taiwan" class="dp-legal-toc-link">Taiwan</a>
                        <a href="#indonesia" class="dp-legal-toc-link">Indonesia</a>
                        <a href="#thailand" class="dp-legal-toc-link">Thailand</a>
                        <a href="#philippines" class="dp-legal-toc-link">Philippines</a>
                        <a href="#vietnam" class="dp-legal-toc-link">Vietnam</a>
                        <a href="#singapore" class="dp-legal-toc-link">Singapore</a>
                        <a href="#hong-kong" class="dp-legal-toc-link">Hong Kong</a>
                    </div>
                </nav>

                <article class="dp-legal-body max-w-3xl">

                    <h2 id="japan">Japan</h2>
                    <p>Japan pairs cutting-edge tech with distinctive fashion, but most Japanese retailers don't ship internationally. A Delivering Parcel address in Japan lets you buy from JP Mercari, Japan eBay and every major local store, then have it all forwarded to you — luxury goods and everyday finds alike.</p>
                    <p class="dp-store-label">Popular stores in Japan</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Rakuten (楽天市場)</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Amazon Japan</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Yahoo! Shopping Japan</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> ZOZOTOWN</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Mercari Japan</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Uniqlo</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> LOHACO</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Bic Camera Online Shop</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> IKEA Japan</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Rakuten Global Market</span>
                    </div>

                    <h2 id="malaysia">Malaysia</h2>
                    <p>Malaysian marketplaces like Shopee are full of hard-to-find products, but shipping straight to the USA or Europe is rarely on offer. Route your order through a Malaysian forwarding address and Delivering Parcel handles the rest — or use our personal-shopper assistance if a store won't take an international card at all.</p>
                    <p class="dp-store-label">Popular stores in Malaysia</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Shopee Malaysia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Lazada Malaysia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Mudah.my</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> TikTok Shop</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Carousell Malaysia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Qoo10 Malaysia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Zalora Malaysia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> PrestoMall</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> GoShop</span>
                    </div>

                    <h2 id="taiwan">Taiwan</h2>
                    <p>From tech gadgets to niche collectibles, Taiwan's online scene rewards buyers who can reach it. Get a local Taiwanese address, check out normally, and reship your haul to Canada, Europe or anywhere else — no more maze of unsupported international checkouts.</p>
                    <p class="dp-store-label">Popular stores in Taiwan</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Shopee Taiwan</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Momo (momoshop.com.tw)</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> PChome</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Ruten (Yahoo! Auctions Taiwan)</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Taobao (via forwarding)</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Books.com.tw</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> 7-Eleven Taiwan Online Store</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Coupang Taiwan</span>
                    </div>

                    <h2 id="indonesia">Indonesia</h2>
                    <p>Indonesia's marketplaces cover everything from streetwear to home goods, but almost none of them ship past the archipelago. With a Delivering Parcel address in Indonesia, you check out exactly as a local shopper would, and we consolidate and forward your order internationally. Need something bought on your behalf instead? Our proxy-buyer team can place the order for you.</p>
                    <p class="dp-store-label">Popular stores in Indonesia</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Tokopedia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Bukalapak</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Shopee Indonesia</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Lazada Indonesia</span>
                    </div>

                    <h2 id="thailand">Thailand</h2>
                    <p>Thailand's retailers rarely offer international checkout, so Delivering Parcel provides a residential forwarding address in Thailand for international shoppers, diaspora, expats and digital nomads alike. Shop as if you were local, and we'll consolidate and ship your order wherever you are.</p>

                    <h2 id="philippines">Philippines</h2>
                    <p>Filipino marketplaces are known for standout deals on electronics and fashion, plus exclusive local beauty brands that rarely ship overseas. With a Philippine forwarding address, you can shop Shopee's daily bargains, grab Lazada-exclusive fashion, and let Delivering Parcel handle delivery to any country.</p>
                    <p class="dp-store-label">Popular stores in the Philippines</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Shopee Philippines</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Lazada Philippines</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Zalora</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Carousell</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> BeautyMNL</span>
                    </div>

                    <h2 id="vietnam">Vietnam</h2>
                    <p>Vietnam is known worldwide for herbal and rice-based skincare, fashion-forward finds, and traditional craftwork like lacquerware and woven textiles. A Vietnamese forwarding address puts all of it within reach — Delivering Parcel provides the local address so you can shop and reship without a middleman guessing at your order.</p>
                    <p class="dp-store-label">Popular stores in Vietnam</p>
                    <div class="dp-store-chips">
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Shopee.vn</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Lazada.vn</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Tiki.vn</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Thegioididong.com</span>
                        <span class="dp-store-chip"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Cellphones.com.vn</span>
                    </div>

                    <h2 id="singapore">Singapore</h2>
                    <p>Singapore's retail market carries exclusive local and regional brands that aren't sold anywhere else in Asia. With your own Singapore address, purchases are consolidated, safely stored, and forwarded to any global location — fashion, electronics, or anything else on your list, with costs stated clearly up front.</p>

                    <h2 id="hong-kong">Hong Kong</h2>
                    <p>Hong Kong's shopping scene rewards buyers who can consolidate: combining multiple purchases into one shipment keeps per-parcel costs down and simplifies customs. Delivering Parcel provides a local Hong Kong address plus short-term free storage, so you can gather everything before it all ships together.</p>

                </article>
            </div>
        </div>
    </section>

    {{-- ======= CTA ======= --}}
    <section class="py-16 lg:py-20" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-2xl sm:text-3xl mb-3">Ready to shop from Asia?</h2>
                <p class="mb-6 text-sm sm:text-base leading-relaxed" style="color:var(--dp-ink-soft)">Sign up for a free forwarding address and start shopping in minutes — no membership fees, tax-free shipping, and 45 days of free storage.</p>
                <a href="{{ route('register') }}" class="dp-btn dp-btn-accent">Get Started Free <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}
@endsection
