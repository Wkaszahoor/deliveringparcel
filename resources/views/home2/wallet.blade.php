@extends('home2.layouts.app')
@section('title', 'My Wallet')
@section('meta_description', 'Wallet balance, top-ups and transaction history')

@section('content')
<section class="h2-section alt">
    <div class="h2-container">
        <h2>My Wallet</h2>

        @if (!$enabled)
            <div class="h2-alert h2-alert-error">The wallet is currently unavailable. Please contact support.</div>
        @elseif ($wallet->is_locked)
            <div class="h2-alert h2-alert-error">Your wallet is currently locked. Please contact support.</div>
        @endif

        <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:stretch;margin-bottom:1.25rem">
            {{-- Balance card --}}
            <div style="flex:1;min-width:260px;padding:1.25rem;border:1px solid var(--h2-border);border-radius:10px">
                <span class="muted">Available balance</span>
                <div style="font-size:2rem;font-weight:700" id="wallet-balance">
                    {{ $wallet->currency }} {{ number_format((float) $wallet->balance, 2) }}
                </div>
                <p class="muted small" style="margin-top:.5rem">
                    Pay orders instantly from this balance at checkout — no card needed.
                    <a href="{{ route('home2.payments') }}">View my orders</a>
                </p>
            </div>

            {{-- Top-up card --}}
            <div style="flex:1;min-width:260px;padding:1.25rem;border:1px solid var(--h2-border);border-radius:10px">
                <label for="wt-amount" style="font-weight:600">Top up by card</label>
                <div style="display:flex;gap:.5rem;margin:.5rem 0">
                    <span style="align-self:center">{{ $wallet->currency }}</span>
                    <input id="wt-amount" type="number" min="{{ $min }}" max="{{ $max }}" step="0.01" value="{{ $min }}"
                           style="width:100%;padding:.5rem;border:1px solid var(--h2-border);border-radius:6px">
                </div>
                <p class="muted small" style="margin:.25rem 0 .5rem">Minimum {{ number_format($min, 2) }} — maximum {{ number_format($max, 2) }} per top-up.</p>

                @if ($enabled && !$wallet->is_locked && !empty($publishableKey))
                    <button class="h2-btn h2-btn-primary" type="button" id="wt-go" style="width:100%">Top up now</button>
                    <p class="muted small" style="margin-top:.5rem">You'll be taken to Stripe's secure payment page to enter card details — nothing card-related is collected on this site.</p>
                    <div class="h2-alert h2-alert-success" id="wt-status" style="display:none;margin-top:.75rem"></div>
                @elseif (empty($publishableKey))
                    <div class="h2-alert h2-alert-error">Card top-up is not configured on this environment yet. Please try again later.</div>
                @endif
            </div>
        </div>

        {{-- In-flight top-up (settles via webhook within seconds of paying) --}}
        @if (!empty($pendingTopup))
            @php $ptLabel = $statusLabels[$pendingTopup->status]['label'] ?? $pendingTopup->status; @endphp
            <div class="h2-alert h2-alert-success" id="wt-pending" data-reference="{{ $pendingTopup->reference }}">
                Top-up <strong>{{ $pendingTopup->reference }}</strong> is <strong>{{ strtolower($ptLabel) }}</strong> — your balance updates automatically once the card payment is confirmed. This page checks every few seconds.
            </div>
        @endif

        {{-- History --}}
        <h3 style="margin-top:1.5rem">Transaction history</h3>
        @if ($transactions->isEmpty())
            <p class="muted">No wallet activity yet.</p>
        @else
            <table class="h2-table">
                <thead>
                    <tr>
                        <th>Date</th><th>Type</th><th>Description</th>
                        <th style="text-align:right">Amount</th><th style="text-align:right">Balance after</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $t)
                        <tr>
                            <td data-label="Date">{{ optional($t->created_at)->format('M d, Y H:i') }}</td>
                            <td data-label="Type">{{ $t->label() }}</td>
                            <td data-label="Description">{{ $t->description ?: ($t->reference ?: '—') }}</td>
                            <td data-label="Amount" style="text-align:right;color:{{ $t->direction === 'credit' ? '#198754' : '#dc3545' }}">
                                {{ $t->direction === 'credit' ? '+' : '−' }} {{ number_format((float) $t->amount, 2) }}
                            </td>
                            <td data-label="Balance after" style="text-align:right">{{ number_format((float) $t->balance_after, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $transactions->links() }}
        @endif
    </div>
</section>
@endsection

@push('scripts')
@if ($enabled && !$wallet->is_locked && !empty($publishableKey))
<script>
(function () {
    var go = document.getElementById('wt-go');
    if (!go) return;

    function setStatus(msg, isError) {
        var s = document.getElementById('wt-status');
        if (!s) return;
        s.style.display = 'block';
        s.textContent = msg;
        s.className = 'h2-alert ' + (isError ? 'h2-alert-error' : 'h2-alert-success');
    }

    go.addEventListener('click', function () {
        var amount = parseFloat(document.getElementById('wt-amount').value);
        if (!(amount > 0)) { setStatus('Please enter a valid amount.', true); return; }

        go.disabled = true;
        go.innerHTML = 'Processing… do not click again';

        // Step 1 — create the top-up payment server-side (amount validated there).
        fetch(@json(route('home2.wallet.topup')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': @json(csrf_token())
            },
            body: JSON.stringify({ amount: amount })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.ok) throw new Error(data.message || 'Top-up could not be started.');

            // Step 2 — Stripe Checkout: redirect to the hosted payment page.
            if (data.payload && data.payload.kind === 'checkout' && data.payload.checkout_url) {
                setStatus('Redirecting to secure Stripe payment page…', false);
                window.location.href = data.payload.checkout_url;
                return;
            }
            // Mock mode (no Stripe secrets here) — settle via webhook path and poll.
            setStatus('Top-up started — waiting for confirmation…', false);
            pollBalance(60);
        })
        .catch(function (err) {
            setStatus(err.message, true);
            go.disabled = false;
            go.innerHTML = 'Top up now';
        });
    });

    // Poll the lightweight refresh endpoint until the webhook lands.
    function pollBalance(tries) {
        if (tries <= 0) {
            setStatus('Still processing — please refresh in a moment.', false);
            window.location.reload();
            return;
        }
        fetch(@json(route('home2.wallet.refresh')), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            var b = document.getElementById('wallet-balance');
            if (b && typeof d.balance === 'number') {
                b.textContent = d.currency + ' ' + Number(d.balance).toFixed(2);
            }
            if (d.last_topup && d.last_topup.is_paid) {
                setStatus('Top-up complete — your balance has been updated.', false);
                setTimeout(function () { window.location.reload(); }, 1200);
            } else {
                setTimeout(function () { pollBalance(tries - 1); }, 3000);
            }
        })
        .catch(function () { setTimeout(function () { pollBalance(tries - 1); }, 3000); });
    }

    // Existing in-flight top-up from a previous visit — keep polling for it too.
    var pending = document.getElementById('wt-pending');
    if (pending) pollBalance(120);
})();
</script>
@endif
@endpush
