@extends('home2.layouts.app')
@section('title', 'Checkout')

@section('content')
<section class="h2-section alt">
    <div class="h2-container">
        <h2>Checkout</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2rem;margin-top:1rem">
            <form method="POST" action="{{ route('home2.shop.checkout.place') }}" class="h2-form">
                @csrf
                <label>Full name *</label>
                <input type="text" name="name" class="form-control" required value="{{ $name }}">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required value="{{ $email }}">
                <label>Shipping address *</label>
                <input type="text" name="address" class="form-control" required placeholder="Street and number">
                <label>City *</label>
                <input type="text" name="city" class="form-control" required>
                <label>Country *</label>
                <input type="text" name="country" class="form-control" required>
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
                <label>Notes</label>
                <textarea name="notes" rows="3" class="form-control" maxlength="1000"></textarea>
                <button class="h2-btn h2-btn-primary" type="submit" style="margin-top:1.25rem;width:100%">Place order — ${{ number_format($cart['total'], 2) }}</button>
                <p class="muted small" style="margin-top:.5rem">Total is computed server-side from current prices and stock.</p>
            </form>
            <div>
                <div class="h2-card"><div class="h2-card-body">
                    <h4>Order summary</h4>
                    @foreach ($cart['rows'] as $row)
                        <div style="display:flex;justify-content:space-between;margin:.35rem 0">
                            <span>{{ $row->name }} × {{ $row->qty }}</span>
                            <span>${{ number_format($row->line, 2) }}</span>
                        </div>
                    @endforeach
                    <hr>
                    <div style="display:flex;justify-content:space-between;font-weight:700">
                        <span>Total</span><span>${{ number_format($cart['total'], 2) }}</span>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</section>
@endsection
