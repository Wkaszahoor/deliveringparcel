@extends('home2.layouts.app')
@section('title', 'My Account')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <h2>Hello, {{ explode(' ', trim(auth()->user()->name ?? ''))[0] ?? 'there' }}</h2>
        <p class="muted">Your account overview.</p>
        <div class="h2-grid" style="margin-top:1.5rem">
            <a class="h2-card" href="{{ route('home2.request2.create') }}"><div class="h2-card-body"><h3>📦 New request</h3><p class="muted">Package consolidation request</p></div></a>
            <a class="h2-card" href="{{ route('home2.returns') }}"><div class="h2-card-body"><h3>📦 Returns</h3><p class="muted">{{ $counts['returns'] }} request(s)</p></div></a>
            <a class="h2-card" href="{{ route('home2.claims') }}"><div class="h2-card-body"><h3>🧾 Claims</h3><p class="muted">{{ $counts['claims'] }} claim(s)</p></div></a>
            <a class="h2-card" href="{{ route('home2.quotes') }}"><div class="h2-card-body"><h3>📄 My quotes</h3><p class="muted">{{ $counts['quotes'] }} quote(s)</p></div></a>
            <a class="h2-card" href="{{ route('home2.payments') }}"><div class="h2-card-body"><h3>💳 Orders &amp; payments</h3><p class="muted">{{ $counts['orders'] }} order(s)</p></div></a>
            @if (!empty($walletEnabled))
            <a class="h2-card" href="{{ route('home2.wallet') }}"><div class="h2-card-body"><h3>👛 My wallet</h3><p class="muted">Balance: <strong>${{ number_format($walletBalance ?? 0, 2) }}</strong> — top up or pay orders</p></div></a>
            @endif
            <a class="h2-card" href="{{ route('home2.notifications') }}"><div class="h2-card-body"><h3>🔔 Notifications</h3><p class="muted">{{ $counts['unread'] }} unread</p></div></a>
        </div>
    </div>
</section>
@endsection
