@extends('shipper.layouts.app')
@section('title', 'Wallet')

@section('content')
<h2 style="margin:8px 0">Wallet</h2>
<div class="shp-grid">
    <div class="shp-tile" style="background:linear-gradient(135deg,#198754,#3fb27f)">
        <div class="v">${{ number_format($profile->wallet_balance, 2) }}</div><div class="l">Available</div></div>
    <div class="shp-tile" style="background:linear-gradient(135deg,#f0a800,#ffc43d)">
        <div class="v">${{ number_format($profile->wallet_pending, 2) }}</div><div class="l">Held (7-day buffer)</div></div>
    <div class="shp-tile" style="background:linear-gradient(135deg,#6f42c1,#9d6bff)">
        <div class="v">${{ number_format($profile->total_earned, 2) }}</div><div class="l">Total Earned</div></div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;margin-top:14px">
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px">
        <h4 style="margin-top:0">Request Payout</h4>
        @php($available = $profile->wallet_balance - $profile->wallet_pending)
        <p style="color:var(--h2-muted)">Withdrawable: <b>${{ number_format(max(0, $available), 2) }}</b></p>
        <form action="{{ route('shipper.wallet.payout') }}" method="POST" class="h2-form" style="max-width:none;padding:0;border:0">
            @csrf
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" min="1" max="{{ max(0, $available) }}" required>
            <label>Method</label>
            <select name="method" required>
                <option value="bank">Bank transfer</option>
                <option value="paypal">PayPal</option>
                <option value="wise">Wise</option>
            </select>
            <label>Payout details (account/email)</label>
            <textarea name="details" rows="2" required></textarea>
            <button class="h2-btn h2-btn-primary" style="margin-top:12px">Request Payout</button>
        </form>
        @if($payouts->count())
            <h4 style="margin-top:16px">Recent Payouts</h4>
            <table class="h2-table">
                @foreach($payouts as $p)
                    <tr><td data-label="Amount"><b>${{ number_format($p->amount, 2) }}</b></td>
                        <td data-label="Method">{{ ucfirst($p->method) }}</td>
                        <td data-label="Status">{{ $p->status }}</td>
                        <td data-label="When">{{ $p->created_at->diffForHumans() }}</td></tr>
                @endforeach
            </table>
        @endif
    </div>
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px">
        <h4 style="margin-top:0">Transactions</h4>
        <div class="h2-table-wrap">
            <table class="h2-table">
                <thead><tr><th>When</th><th>Type</th><th>Amount</th><th>Balance</th><th>Note</th></tr></thead>
                <tbody>
                @forelse($transactions as $t)
                    <tr><td data-label="When">{{ $t->created_at->diffForHumans() }}</td>
                        <td data-label="Type">{{ str_replace('_', ' ', $t->type) }}</td>
                        <td data-label="Amount" style="color:{{ in_array($t->type, ['credit','hold_release','bonus']) ? '#137333' : '#b3261e' }}">{{ $t->type === 'debit' || $t->type === 'payout_paid' ? '-' : '+' }}${{ number_format($t->amount, 2) }}</td>
                        <td data-label="Balance">${{ number_format($t->balance_after, 2) }}</td>
                        <td data-label="Note">{{ $t->note }}</td></tr>
                @empty<tr><td colspan="5">No transactions yet.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links('home2.partials.pagination') }}
    </div>
</div>
@endsection
