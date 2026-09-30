@extends('shipper.layouts.app')
@section('title', 'Request ' . $request->reference)

@section('content')
<h2 style="margin:8px 0">{{ $request->reference }}
    <span class="h2-badge">{{ $request->service_type === 'buy_for_me' ? 'Buy for Me' : 'Ship for Me' }}</span>
    <span class="h2-badge">{{ $request->country_required }}</span>
</h2>
<div class="h2-section" style="background:#fff;border-radius:12px;padding:16px;margin-bottom:14px">
    <pre style="white-space:pre-wrap;font-family:inherit;margin:0">{{ $request->brief_text }}</pre>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px">
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px">
        <h4 style="margin-top:0">Products</h4>
        <table class="h2-table">
            <thead><tr><th>Item</th><th>Qty</th><th>Est.</th></tr></thead>
            <tbody>
            @foreach($request->product_details ?? [] as $p)
                <tr><td data-label="Item">{{ $p['name'] }}<br><small style="word-break:break-all">{{ $p['url'] }}</small></td>
                    <td data-label="Qty">{{ $p['quantity'] }}</td>
                    <td data-label="Est.">${{ number_format($p['estimated_value'], 2) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px">
        <h4 style="margin-top:0">Your Quote</h4>
        @if($myQuote)
            <table class="h2-table">
                <tr><td data-label="Amount"><b>${{ number_format($myQuote->quoted_amount, 2) }}</b></td></tr>
                <tr><td data-label="Days">{{ $myQuote->estimated_days }} days</td></tr>
                <tr><td data-label="Status"><span class="h2-badge">{{ $myQuote->status }}</span></td></tr>
            </table>
            @if($myQuote->status === 'pending')
                <form action="{{ route('shipper.quotes.withdraw', $myQuote->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Withdraw quote?')">@csrf @method('DELETE')
                    <button class="h2-btn h2-btn-outline">Withdraw</button></form>
            @endif
        @elseif($canQuote)
            <form action="{{ route('shipper.requests.quote', $request->id) }}" method="POST" class="h2-form" style="max-width:none;padding:0;border:0">
                @csrf
                <label>Your price (what you charge)</label>
                <input type="number" step="0.01" name="quoted_amount" min="1" required>
                <label>Estimated days</label>
                <input type="number" name="estimated_days" min="1" max="120" required>
                <label>Notes for admin (optional)</label>
                <textarea name="notes" rows="3"></textarea>
                <button class="h2-btn h2-btn-primary" style="margin-top:12px">Submit Quote</button>
            </form>
        @else
            <p style="color:var(--h2-muted)">You can't quote right now (capacity limit or KYC pending).</p>
        @endif
        <p style="margin-top:10px"><a href="{{ route('shipper.requests.index') }}">← Back to requests</a></p>
    </div>
</div>
@endsection
