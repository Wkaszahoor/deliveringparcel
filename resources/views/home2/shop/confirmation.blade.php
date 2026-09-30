@extends('home2.layouts.app')
@section('title', 'Order ' . $order->code)

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <div class="h2-alert h2-alert-success">Order placed — reference <strong>{{ $order->code }}</strong></div>
        <div class="h2-card" style="margin-top:1rem"><div class="h2-card-body">
            <h3>Items</h3>
            @foreach ($order->items as $item)
                <div style="display:flex;justify-content:space-between;margin:.35rem 0">
                    <span>{{ $item->name }} × {{ $item->qty }}</span>
                    <span>${{ number_format((float) $item->price * (int) $item->qty, 2) }}</span>
                </div>
            @endforeach
            <hr>
            <div style="display:flex;justify-content:space-between;font-weight:700">
                <span>Total due</span><span>${{ number_format((float) $order->total, 2) }}</span>
            </div>
            <p class="muted small">Status: {{ ucfirst($order->status) }}@if($order->status === 'paid') — paid ✓ @endif</p>
        </div></div>

        {{-- Advanced mode: pay now through the engine (PM-015) --}}
        @if (!empty($engine) && $order->status === 'pending')
            <div class="h2-card" style="margin-top:1rem"><div class="h2-card-body">
                <h3>Pay now</h3>
                @if(!empty($engine['forced']))
                    <div class="h2-alert h2-alert-error" style="margin-top:0">Our team has set the payment method for this purchase — only <strong>{{ $engine['forced'] === 'bank_transfer' ? 'Bank transfer' : ucfirst($engine['forced']) }}</strong> can be used.</div>
                @endif

                @if (count($engine['methods']) === 0)
                    <p class="muted">No payment method is currently available. Please contact support.</p>
                @else
                    <form id="shop-pay-form">
                        @csrf
                        @foreach ($engine['methods'] as $m)
                            @php
                                $method = $m['method'];
                                $disabled = !empty($m['reason']);
                            @endphp
                            <label style="display:flex;align-items:center;gap:.75rem;padding:.75rem;border:1px solid var(--h2-border);border-radius:8px;margin-bottom:.5rem;{{ $disabled ? 'opacity:.55' : '' }}">
                                <input type="radio" name="method" value="{{ $method->code }}" required {{ $disabled ? 'disabled' : '' }} {{ $loop->first && !$disabled ? 'checked' : '' }} data-method-code="{{ $method->code }}">
                                <span>
                                    <strong>{{ $method->name }}</strong>
                                    @if (!empty($m['reason']))<br><small class="muted">{{ $m['reason'] }}</small>@endif
                                    @if ($method->code === 'wallet' && isset($m['wallet_balance']))<br><small class="muted">Available balance: {{ number_format((float) $m['wallet_balance'], 2) }}</small>@endif
                                </span>
                            </label>
                        @endforeach

                        @if (!empty($engine['publishable_key']))
                            <div id="stripe-card-container" style="display:none;margin-top:1rem;padding:1rem;border:1px solid var(--h2-border);border-radius:8px">
                                <label style="display:block;margin-bottom:.5rem;font-weight:600">Card details</label>
                                <div id="card-element" style="padding:.5rem;border:1px solid #ddd;border-radius:4px;background:#fff"></div>
                                <div id="card-errors" role="alert" style="color:#dc3545;margin-top:.5rem;font-size:.875rem"></div>
                            </div>
                        @endif

                        <button class="h2-btn h2-btn-primary" type="submit" id="shop-pay-go" style="width:100%;margin-top:.75rem">Pay ${{ number_format((float) $order->total, 2) }}</button>
                    </form>
                    <div class="h2-alert h2-alert-success" id="shop-pay-status" style="display:none;margin-top:.75rem"></div>
                @endif
            </div></div>
        @endif

        @if ($order->status === 'pending' && !empty($bank['iban']))
            <div class="h2-card" style="margin-top:1rem"><div class="h2-card-body">
                <h3>Pay by bank transfer</h3>
                <table class="h2-table">
                    <tr><td><strong>Bank</strong></td><td style="text-align:right">{{ $bank['bank_name'] }}</td></tr>
                    <tr><td><strong>Account</strong></td><td style="text-align:right">{{ $bank['account'] }}</td></tr>
                    <tr><td><strong>IBAN</strong></td><td style="text-align:right">{{ $bank['iban'] }}</td></tr>
                    <tr><td><strong>Reference</strong></td><td style="text-align:right">{{ $engine['bank_instructions']['transfer_reference'] ?? $order->code }}</td></tr>
                </table>
                @if (!empty($engine['bank_payment_id']))
                    <form method="POST" enctype="multipart/form-data" action="{{ route('home2.pay.proof', $engine['bank_payment_id']) }}" style="margin-top:.75rem">
                        @csrf
                        <label for="shop-proof" class="small">Upload transfer receipt (jpg/png/pdf)</label>
                        <input id="shop-proof" type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required>
                        <button class="h2-btn h2-btn-outline" type="submit" style="width:100%;margin-top:.5rem">Submit receipt for verification</button>
                    </form>
                @else
                    <p class="muted small">Your order ships once our team verifies the transfer.</p>
                @endif
            </div></div>
        @endif

        <p style="margin-top:1.5rem"><a href="{{ route('home2.shop.index') }}" class="h2-btn h2-btn-outline">Continue shopping</a></p>
    </div>
</section>
@endsection

@push('scripts')
@if (!empty($engine) && $order->status === 'pending' && count($engine['methods']) > 0)
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    var f = document.getElementById('shop-pay-form'), b = document.getElementById('shop-pay-go');
    if (!f) return;

    var publishableKey = @json($engine['publishable_key'] ?? '');
    var stripe = null, card = null;
    if (publishableKey) {
        try {
            stripe = Stripe(publishableKey);
            card = stripe.elements().create('card', { style: { base: { fontSize: '16px', color: '#32325d' } } });
            card.mount('#card-element');
            card.addEventListener('change', function (e) {
                var el = document.getElementById('card-errors');
                if (el) el.textContent = e.error ? e.error.message : '';
            });
        } catch (e) { console.error('Stripe init failed:', e); }
    }

    function selected() {
        var r = document.querySelector('input[name="method"]:checked');
        return r ? r.dataset.methodCode : null;
    }
    function syncCard() {
        var c = document.getElementById('stripe-card-container');
        if (c) c.style.display = (selected() === 'stripe' && stripe) ? 'block' : 'none';
    }
    f.querySelectorAll('input[name="method"]').forEach(function (r) { r.addEventListener('change', syncCard); });
    syncCard();

    function status(msg, err) {
        var s = document.getElementById('shop-pay-status');
        if (!s) return;
        s.style.display = 'block';
        s.textContent = msg;
        s.className = 'h2-alert ' + (err ? 'h2-alert-error' : 'h2-alert-success');
    }
    function reEnable() {
        if (b) { b.disabled = false; b.innerHTML = 'Pay ${{ number_format((float) $order->total, 2) }}'; }
    }
    function poll(tries) {
        if (tries <= 0) { window.location.reload(); return; }
        fetch(@json(route('home2.shop.pay.status', $order->code)), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.paid) { window.location.reload(); }
            else { setTimeout(function () { poll(tries - 1); }, 3000); }
        })
        .catch(function () { setTimeout(function () { poll(tries - 1); }, 3000); });
    }

    f.addEventListener('submit', function (e) {
        e.preventDefault();
        var method = selected();
        if (!method) return;
        if (b) { b.disabled = true; b.innerHTML = 'Processing… do not click again'; }

        var isStripe = method === 'stripe' && stripe && card;
        fetch(@json(route('home2.shop.pay', $order->code)), {
            method: 'POST',
            body: new FormData(f),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.ok) throw new Error(data.message || 'Payment failed');

            if (data.next === 'wallet' || data.status === 'paid') {
                // Wallet settles server-side instantly.
                window.location.reload();
                return;
            }
            if (data.next === 'intent' && data.payload && data.payload.client_secret) {
                if (!isStripe) throw new Error('Card payment could not be started — please refresh.');
                status('Confirming your card…', false);
                return stripe.confirmCardPayment(data.payload.client_secret, { payment_method: { card: card } });
            }
            if (data.next === 'instructions') {
                // Bank transfer — instructions render after reload (open payment exists).
                window.location.reload();
                return;
            }
            throw new Error('Unexpected payment response.');
        })
        .then(function (result) {
            if (result && result.error) throw new Error(result.error.message);
            if (result) {
                status('Payment confirmed — updating your order…', false);
                poll(40);
            }
        })
        .catch(function (err) {
            status(err.message, true);
            reEnable();
        });
    });
})();
</script>
@endif
@endpush
