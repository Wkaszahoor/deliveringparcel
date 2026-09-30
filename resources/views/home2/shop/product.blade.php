@extends('home2.layouts.app')
@section('title', $product->name)

@section('content')
<section class="h2-section alt">
    <div class="h2-container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:2rem">
            <div>
                @if (!empty($product->images))
                    @foreach ($product->images as $i => $img)
                        <img src="{{ $img }}" alt="{{ $product->name }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}" style="width:100%;border-radius:12px;margin-bottom:.5rem">
                    @endforeach
                @endif
            </div>
            <div>
                <h2 style="margin-top:0">{{ $product->name }}</h2>
                <p style="font-size:1.5rem;font-weight:700">${{ number_format((float) $product->price, 2) }}
                    @if ($product->compare_price && (float) $product->compare_price > (float) $product->price)
                        <span class="muted" style="font-size:1rem;text-decoration:line-through">${{ number_format((float) $product->compare_price, 2) }}</span>
                    @endif
                </p>
                <p class="muted small">SKU {{ $product->sku }} · {{ (int) $product->stock > 0 ? (int) $product->stock . ' in stock' : 'Out of stock' }}</p>
                <div>{{ $product->description }}</div>

                @if ((int) $product->stock > 0)
                    <form method="POST" action="{{ route('home2.shop.add', $product->id) }}" style="margin-top:1.5rem;display:flex;gap:.75rem;max-width:320px">
                        @csrf
                        <input type="number" name="qty" value="1" min="1" max="{{ min(99, (int) $product->stock) }}" class="form-control" style="width:90px">
                        <button class="h2-btn h2-btn-primary" type="submit">Add to cart</button>
                    </form>
                @else
                    <p class="h2-alert h2-alert-error" style="margin-top:1.5rem">Currently out of stock.</p>
                @endif
                <p style="margin-top:1rem"><a href="{{ route('home2.shop.index') }}" class="h2-btn h2-btn-outline">← Back to shop</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
