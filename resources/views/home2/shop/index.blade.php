@extends('home2.layouts.app')
@section('title', 'Shop')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
            <h2 style="margin:0">Shop</h2>
            <form method="GET" style="display:flex;gap:.5rem">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search products…" style="min-width:220px">
                <button class="h2-btn h2-btn-outline" type="submit">Search</button>
            </form>
        </div>

        @if ($products->isEmpty())
            <p class="muted" style="margin-top:1.5rem">No products found.</p>
        @else
            <div style="margin-top:1.5rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:1rem">
                @foreach ($products as $p)
                    <div class="h2-card">
                        @if (!empty($p->images[0]))
                            <a href="{{ route('home2.shop.show', $p->slug) }}">
                                <img src="{{ $p->images[0] }}" alt="{{ $p->name }}" loading="lazy" style="width:100%;height:150px;object-fit:cover;border-radius:8px 8px 0 0">
                            </a>
                        @endif
                        <div class="h2-card-body">
                            <a href="{{ route('home2.shop.show', $p->slug) }}" style="text-decoration:none;color:inherit"><strong>{{ \Str::limit($p->name, 48) }}</strong></a>
                            @if ($p->featured)<span class="h2-badge" style="margin-left:.4rem">★</span>@endif
                            <p class="muted" style="margin:.4rem 0;font-size:.88rem">{{ \Str::limit(strip_tags((string) $p->description), 90) }}</p>
                            <div style="display:flex;justify-content:space-between;align-items:center">
                                <strong>${{ number_format((float) $p->price, 2) }}</strong>
                                <span class="muted small">{{ (int) $p->stock > 0 ? (int) $p->stock . ' in stock' : 'Out of stock' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @include('home2.partials.pagination', ['paginator' => $products])
        @endif
    </div>
</section>
@endsection
