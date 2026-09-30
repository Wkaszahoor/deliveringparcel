@extends('home2.layouts.app')
@section('title', 'Cart')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <h2>Your cart</h2>
        @if ($cart['rows']->isEmpty())
            <p class="muted">Cart is empty. <a href="{{ route('home2.shop.index') }}">Browse the shop</a>.</p>
        @else
            <form method="POST" action="{{ route('home2.shop.cart.update') }}">
                @csrf
                <div class="h2-table-wrap" style="margin-top:1rem">
                    <table class="h2-table">
                        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Line</th></tr></thead>
                        <tbody>
                            @foreach ($cart['rows'] as $row)
                                <tr>
                                    <td data-label="Product"><a href="{{ route('home2.shop.show', $row->slug) }}">{{ $row->name }}</a></td>
                                    <td data-label="Price">${{ number_format($row->price, 2) }}</td>
                                    <td data-label="Qty"><input type="number" name="qty[{{ $row->id }}]" value="{{ $row->qty }}" min="0" max="99" class="form-control" style="width:80px"></td>
                                    <td data-label="Line"><strong>${{ number_format($row->line, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;flex-wrap:wrap;gap:1rem">
                    <div>
                        <button class="h2-btn h2-btn-outline" type="submit">Update cart</button>
                        <a href="{{ route('home2.shop.cart.clear') }}" class="h2-btn h2-btn-outline" onclick="return confirm('Clear the cart?')">Clear</a>
                    </div>
                    <div style="font-size:1.2rem">Total: <strong>${{ number_format($cart['total'], 2) }}</strong></div>
                </div>
            </form>
            <p style="margin-top:1.5rem"><a href="{{ route('home2.shop.checkout') }}" class="h2-btn h2-btn-primary">Proceed to checkout</a></p>
        @endif
    </div>
</section>
@endsection
