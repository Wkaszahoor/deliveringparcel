{{-- CMS Product detail — spec SECTION 8. Adapted to home2 custom CSS layout (h2-* classes, no Bootstrap). --}}
@extends('home2.layouts.app')
@section('title', $product->meta_title ?: $product->title)
@section('meta_description', \Str::limit(strip_tags($product->meta_description ?: $product->smart_excerpt), 155))

@push('meta')
    <meta name="keywords" content="{{ $product->meta_keywords }}">
    <meta name="robots" content="{{ $product->robots }}">
    <meta property="og:title" content="{{ $product->meta_title ?: $product->title }}">
    <meta property="og:description" content="{{ \Str::limit(strip_tags($product->meta_description ?: $product->smart_excerpt), 200) }}">
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ $product->url }}">
    @if ($product->og_image || $product->featured_image)
        @php $productOgImage = $product->og_image ?: $product->featured_image; @endphp
        <meta property="og:image" content="{{ str_starts_with($productOgImage, 'http') ? $productOgImage : asset($productOgImage) }}">
    @endif
    <link rel="canonical" href="{{ $product->url }}">
@endpush

@push('scripts')
    @php
        $price = (float) ($product->meta['price'] ?? 0);
        $salePrice = $product->meta['sale_price'] ?? null;
        $finalPrice = $salePrice !== null && (float) $salePrice < $price ? (float) $salePrice : $price;
        $currency = $product->meta['currency'] ?? 'GBP';
        $symbols = ['GBP' => '£', 'USD' => '$', 'EUR' => '€'];
        $symbol = $symbols[$currency] ?? ($currency . ' ');
        $sku = $product->meta['sku'] ?? '';
        $stock = (int) ($product->meta['stock'] ?? 0);
        $quoteUrl = $product->meta['cta_url'] ?? '/get-quote';
        $quoteHref = str_starts_with($quoteUrl, 'http') ? $quoteUrl : url($quoteUrl);
    @endphp
    <script type="application/ld+json">
    {!! json_encode([
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product->title,
        'description' => \Str::limit(strip_tags($product->meta_description ?: $product->smart_excerpt), 300),
        'sku'         => $sku ?: $product->slug,
        'image'       => $product->featured_image
                            ? (str_starts_with($product->featured_image, 'http') ? $product->featured_image : asset($product->featured_image))
                            : null,
        'offers'      => [
            '@type'         => 'Offer',
            'price'         => number_format($finalPrice, 2, '.', ''),
            'priceCurrency' => $currency,
            'availability'  => $stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url'           => $product->url,
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <p class="muted small" style="margin:.2rem 0 1rem">
            <a href="{{ url('/') }}" style="text-decoration:none; color:inherit">Home</a> ›
            <a href="{{ route('cms.shop.index') }}" style="text-decoration:none; color:inherit">Shop</a> ›
            <span>{{ \Str::limit($product->title, 50) }}</span>
        </p>

        {{-- Image left + info right --}}
        <div style="display:flex; gap:2rem; flex-wrap:wrap; margin-bottom:2rem">
            <div style="flex:1 1 300px; min-width:280px">
                @if ($product->featured_image)
                    <img src="{{ str_starts_with($product->featured_image, 'http') ? $product->featured_image : asset($product->featured_image) }}"
                         alt="{{ $product->title }}"
                         style="width:100%; max-height:420px; object-fit:cover; border-radius:10px"
                         onerror="this.remove()">
                @else
                    <div style="width:100%; height:320px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:4rem; background:linear-gradient(135deg, var(--h2-border), transparent)">📦</div>
                @endif
            </div>

            <div class="h2-card" style="flex:1 1 280px; min-width:260px">
                <div class="h2-card-body" style="display:flex; flex-direction:column; gap:.6rem">
                    @if ($product->categories->isNotEmpty())
                        <div style="display:flex; gap:.35rem; flex-wrap:wrap">
                            @foreach ($product->categories as $category)
                                <span class="h2-badge">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    <h1 style="font-size:1.6rem; margin:0">{{ $product->title }}</h1>

                    <div style="font-size:1.3rem">
                        @if ($salePrice !== null && (float) $salePrice < (float) $price)
                            <span style="text-decoration:line-through; opacity:.6; font-size:1rem">{{ $symbol }}{{ number_format((float) $price, 2) }}</span>
                            <strong style="color:#dc2626">{{ $symbol }}{{ number_format((float) $salePrice, 2) }}</strong>
                            <span class="h2-badge" style="background:#dc2626">SALE</span>
                        @else
                            <strong>{{ $symbol }}{{ number_format((float) $price, 2) }}</strong>
                        @endif
                    </div>

                    <p class="muted" style="margin:0">
                        SKU: {{ $sku ?: '—' }} ·
                        {{ $stock > 0 ? $stock . ' in stock' : 'Out of stock' }}
                    </p>

                    <p style="margin:.4rem 0 0">
                        <a class="h2-btn h2-btn-primary" href="{{ $quoteHref }}">Add to Quote</a>
                    </p>
                </div>
            </div>
        </div>

        {{-- Full description --}}
        @if ($product->excerpt)
            <p class="muted" style="font-size:1.05rem; margin:0 0 1rem">{{ $product->excerpt }}</p>
        @endif
        <div class="h2-article" style="line-height:1.7">
            {!! \App\Support\HtmlSanitizer::clean($product->content ?? '') !!}
        </div>
    </div>
</section>
@endsection
