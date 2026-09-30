{{-- CMS Shop index — spec SECTION 8. Adapted to home2 custom CSS layout (h2-* classes, no Bootstrap). --}}
@extends('home2.layouts.app')
@section('title', 'Shop')
@section('meta_description', 'Browse packaging supplies and courier service products.')

@push('meta')
    <meta property="og:title" content="Shop | DeliveringParcel">
    <meta property="og:description" content="Browse packaging supplies and courier service products.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
@endpush

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <p class="muted small" style="margin:.2rem 0 1rem">
            <a href="{{ url('/') }}" style="text-decoration:none; color:inherit">Home</a> › <span>Shop</span>
        </p>
        <h1 style="font-size:2rem; margin:0 0 1.5rem">Shop</h1>

        <div style="display:flex; gap:1.5rem; align-items:flex-start; flex-wrap:wrap">
            {{-- Category sidebar --}}
            @if ($categories->isNotEmpty())
                <aside class="h2-card" style="flex:0 0 210px; min-width:210px; padding:1rem">
                    <h2 style="font-size:1rem; margin:0 0 .75rem">Categories</h2>
                    <ul class="list-unstyled" style="margin:0; padding:0; list-style:none">
                        <li style="margin:.3rem 0">
                            <a href="{{ route('cms.shop.index') }}" style="text-decoration:none; color:inherit; font-weight:{{ request('category') ? 'normal' : 'bold' }}">All products</a>
                        </li>
                        @foreach ($categories as $category)
                            <li style="margin:.3rem 0">
                                <a href="{{ route('cms.shop.index', ['category' => $category->slug]) }}"
                                   style="text-decoration:none; color:inherit; font-weight:{{ request('category') === $category->slug ? 'bold' : 'normal' }}">
                                    {{ $category->name }} <span class="muted small">({{ $category->posts_count }})</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif

            {{-- Product grid (3 columns desktop, wraps on smaller screens) --}}
            <div style="flex:1; min-width:260px">
                @if (request()->filled('category'))
                    @php $activeCategory = $categories->firstWhere('slug', request('category')); @endphp
                    <p class="muted" style="margin:0 0 1rem">
                        Category: <strong>{{ optional($activeCategory)->name ?? request('category') }}</strong> ·
                        <a href="{{ route('cms.shop.index') }}">Show all</a>
                    </p>
                @endif

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:1rem; margin-bottom:1.5rem">
                    @forelse ($products as $product)
                        @php
                            $currency = $product->meta['currency'] ?? 'GBP';
                            $symbols = ['GBP' => '£', 'USD' => '$', 'EUR' => '€'];
                            $symbol = $symbols[$currency] ?? ($currency . ' ');
                            $price = $product->meta['price'] ?? null;
                            $salePrice = $product->meta['sale_price'] ?? null;
                        @endphp
                        <article class="h2-card" style="display:flex; flex-direction:column; overflow:hidden">
                            <a href="{{ route('cms.shop.product', $product->slug) }}" style="position:relative; display:block">
                                @if ($product->featured_image)
                                    <img src="{{ str_starts_with($product->featured_image, 'http') ? $product->featured_image : asset($product->featured_image) }}"
                                         alt="{{ $product->title }}" loading="lazy"
                                         style="width:100%; height:180px; object-fit:cover; border-radius:8px 8px 0 0"
                                         onerror="this.parentElement.style.display='none'">
                                @else
                                    <div style="height:180px; display:flex; align-items:center; justify-content:center; font-size:2.4rem; background:linear-gradient(135deg, var(--h2-border), transparent)">📦</div>
                                @endif
                                @if ($salePrice !== null && $price !== null && (float) $salePrice < (float) $price)
                                    <span class="h2-badge" style="position:absolute; top:.5rem; left:.5rem; background:#dc2626">SALE</span>
                                @endif
                            </a>
                            <div class="h2-card-body" style="display:flex; flex-direction:column; gap:.4rem; flex:1">
                                <a href="{{ route('cms.shop.product', $product->slug) }}" style="text-decoration:none; color:inherit">
                                    <strong>{{ \Str::limit($product->title, 48) }}</strong>
                                </a>
                                @if ($price !== null)
                                    <div>
                                        @if ($salePrice !== null && (float) $salePrice < (float) $price)
                                            <span style="text-decoration:line-through; color:inherit; opacity:.6">{{ $symbol }}{{ number_format((float) $price, 2) }}</span>
                                            <strong style="color:#dc2626">{{ $symbol }}{{ number_format((float) $salePrice, 2) }}</strong>
                                        @else
                                            <strong>{{ $symbol }}{{ number_format((float) $price, 2) }}</strong>
                                        @endif
                                    </div>
                                @endif
                                <p class="muted" style="font-size:.88rem; margin:0">{{ \Str::limit($product->smart_excerpt, 90) }}</p>
                                <a class="h2-btn h2-btn-outline" href="{{ route('cms.shop.product', $product->slug) }}" style="text-align:center; margin-top:auto">View Details</a>
                            </div>
                        </article>
                    @empty
                        <div class="h2-card" style="grid-column:1 / -1; padding:3rem 1.5rem; text-align:center">
                            <div style="font-size:2.6rem">📦</div>
                            <h3 style="margin:.6rem 0 .3rem">No products found</h3>
                            <p class="muted" style="margin:0">Our shop is being stocked — check back soon.</p>
                        </div>
                    @endforelse
                </div>

                @include('home2.partials.pagination', ['paginator' => $products])
            </div>
        </div>
    </div>
</section>
@endsection
