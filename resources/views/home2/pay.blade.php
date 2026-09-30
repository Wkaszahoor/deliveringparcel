@extends('home2.layouts.app')
@section('title', 'Pay Order #' . $order->order_id)
@section('meta_description', 'Secure checkout')

@section('content')
<section class="h2-section alt">
    <div class="h2-container">
        <h2>Checkout — order #{{ $order->order_id }}</h2>

        @if (!empty($blocked))
            <div class="h2-alert h2-alert-error">{{ $blocked }}</div>
            <p><a class="h2-btn h2-btn-outline" href="{{ route('home2.payments') }}">Back to my orders</a></p>
        @else
            <div class="h2-form" style="max-width:640px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
                    <span class="muted">Amount due (server-authoritative)</span>
                    <strong style="font-size:1.4rem">{{ strtoupper($currency ?: 'USD') }} {{ number_format((float) $amount, 2) }}</strong>
                </div>

                {{-- Flash + validation feedback: proof uploads POST as a plain
                     form, so every outcome arrives here as a redirect. --}}
                @if (session('success'))
                    <div class="h2-alert h2-alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="h2-alert h2-alert-error">{{ session('error') }}</div>
                @endif
                @foreach ($errors->all() as $err)
                    <div class="h2-alert h2-alert-error">{{ $err }}</div>
                @endforeach

                @if ($payment)
                    @php $stateLabel = $statusLabels[$payment->status]['label'] ?? $payment->status; @endphp
                    <div class="h2-alert {{ in_array($payment->status, ['paid','processing']) ? 'h2-alert-success' : 'h2-alert-error' }}" style="margin-top:0">
                        Payment status: <strong>{{ $stateLabel }}</strong>
                        @if ($payment->status === 'awaiting_payment') — complete the transfer below @endif
                    </div>
                @endif

                {{-- Bank transfer instructions --}}
                @if (!empty($instructions))
                    <h4>Bank transfer instructions</h4>

                    {{-- Bank account selector if multiple accounts available --}}
                    @if (!empty($allBankAccounts) && count($allBankAccounts) > 1)
                        <div style="margin-bottom: 1rem;">
                            <label for="bank-account-select" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Select Bank Account:</label>
                            <select id="bank-account-select" style="width: 100%; padding: 0.5rem; border: 1px solid var(--h2-border); border-radius: 4px;">
                                @foreach ($allBankAccounts as $idx => $acc)
                                    <option value="{{ $idx }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $acc['bank_name'] }} - {{ $acc['account_title'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div id="bank-details-container">
                        <table class="h2-table" style="margin-bottom:1rem">
                            <tbody>
                                <tr><td data-label="recipient"><strong>Recipient</strong></td><td data-label="value" style="text-align:right">{{ $instructions['account_title'] ?? '' }}</td></tr>
                                @if (!empty($instructions['recipient_address']))
                                    <tr><td data-label="recipient_address"><strong>Recipient Address</strong></td><td data-label="value" style="text-align:right">{{ $instructions['recipient_address'] }}</td></tr>
                                @endif
                                <tr><td data-label="bank_name"><strong>Bank Name</strong></td><td data-label="value" style="text-align:right">{{ $instructions['bank_name'] }}</td></tr>

                                @if (!empty($instructions['account_number']))
                                    <tr><td data-label="account_number"><strong>Account Number</strong></td><td data-label="value" style="text-align:right">{{ $instructions['account_number'] }}</td></tr>
                                @endif

                                @if (!empty($instructions['iban']))
                                    <tr><td data-label="iban"><strong>IBAN</strong></td><td data-label="value" style="text-align:right">{{ $instructions['iban'] }}</td></tr>
                                @endif

                                @if (!empty($instructions['swift_code']))
                                    <tr><td data-label="swift_code"><strong>SWIFT/BIC Code</strong></td><td data-label="value" style="text-align:right">{{ $instructions['swift_code'] }}</td></tr>
                                @endif

                                @if (!empty($instructions['intermediary_bic']))
                                    <tr><td data-label="intermediary_bic"><strong>Intermediary BIC</strong></td><td data-label="value" style="text-align:right">{{ $instructions['intermediary_bic'] }}</td></tr>
                                @endif

                                @if (!empty($instructions['uk_account_number']))
                                    <tr><td data-label="uk_account_number"><strong>UK Account Number</strong></td><td data-label="value" style="text-align:right">{{ $instructions['uk_account_number'] }}</td></tr>
                                @endif

                                @if (!empty($instructions['uk_sort_code']))
                                    <tr><td data-label="uk_sort_code"><strong>UK Sort Code</strong></td><td data-label="value" style="text-align:right">{{ $instructions['uk_sort_code'] }}</td></tr>
                                @endif

                                @if (!empty($instructions['transfer_reference']))
                                    <tr><td><strong>Transfer Reference</strong></td><td style="text-align:right">{{ $instructions['transfer_reference'] }}</td></tr>
                                @endif
                            </tbody>
                        </table>

                        {{-- Additional Instructions Section --}}
                        @if (!empty($instructions['additional_instructions']))
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 3px solid var(--h2-primary);">
                                <h5 style="margin: 0 0 0.5rem 0; color: var(--h2-primary);">Additional Instructions:</h5>
                                <div style="white-space: pre-wrap; font-size: 0.9rem;">{{ $instructions['additional_instructions'] }}</div>
                            </div>
                        @endif
                    </div>

                    <form method="POST" enctype="multipart/form-data" action="{{ route('home2.pay.proof', $payment->id) }}">
                        @csrf
                        <label for="pf-proof">Upload payment proof (jpg/png/pdf, 5MB)</label>
                        <input id="pf-proof" type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required>
                        <button class="h2-btn h2-btn-primary" type="submit" style="margin-top:1rem;width:100%">Submit proof for verification</button>
                    </form>
                    <p class="muted small" style="margin-top:.75rem">Bank transfers are marked paid only after our team verifies the transfer — uploading a screenshot alone never auto-completes payment.</p>
                @endif

                {{-- Stripe (simulated/processing) --}}
                @if (!empty($gatewayPayload) && ($gatewayPayload['kind'] ?? '') === 'processing')
                    <div class="h2-alert h2-alert-success">Payment is being processed{{ !empty($gatewayPayload['simulated']) ? ' (sandbox mode — no live charge)' : '' }}.
                        <a href="javascript:window.location.reload()">Refresh status</a></div>
                @endif

                {{-- Method selector --}}
                @if ($methods->isNotEmpty() && (!$payment || in_array($payment->status, ['pending', 'method_selected'])))
                    <h4 style="margin-top:1.25rem">Choose payment method</h4>
                    <form method="POST" action="{{ route('home2.pay.pay', $order->id) }}" id="pay-form">
                        @csrf
                        @foreach ($methods as $m)
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

                        {{-- Stripe Checkout info banner (shown when Stripe is selected) --}}
                        <div id="stripe-info-container" style="display:none;margin-top:1rem;padding:1rem;border:1px solid var(--h2-border);border-radius:8px;background:#f8f9fa">
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                                <span style="font-weight:600">Secure payment powered by Stripe</span>
                            </div>
                            <p class="muted small" style="margin:.5rem 0 0">You'll be redirected to Stripe's secure checkout page to complete your payment. After payment, you'll return here.</p>
                        </div>

                        <button class="h2-btn h2-btn-primary" type="submit" id="pay-go" style="width:100%;margin-top:.75rem">Pay {{ strtoupper($currency ?: 'USD') }} {{ number_format((float) $amount, 2) }}</button>
                    </form>
                    <p class="muted small" style="margin-top:.5rem">You can switch methods any time before payment is finalized.</p>
                @elseif (!$payment)
                    <div class="h2-alert h2-alert-error">No payment methods are currently available for this order.</div>
                @endif
            </div>
        @endif
    </div>

    @if ($payment && !in_array($payment->status, ['paid', 'failed', 'cancelled', 'refunded', 'partially_refunded']))
    <meta name="dp-pay-status-url" content="{{ route('home2.pay.status', $payment->id) }}">
    <div id="dp-pay-live" class="muted small" style="margin-top:.75rem">Checking payment status automatically…</div>
    @endif
</section>
@endsection

@push('scripts')
{{-- Bank account selector --}}
<script>
(function () {
    var select = document.getElementById('bank-account-select');
    if (!select) return;

    var allAccounts = @json($allBankAccounts ?? []);
    if (!allAccounts || allAccounts.length === 0) return;

    function updateBankDetails(idx) {
        var account = allAccounts[idx];
        if (!account || !account.instructions) return;

        var container = document.getElementById('bank-details-container');
        if (!container) return;

        var instr = account.instructions;
        var html = '<table class="h2-table" style="margin-bottom:1rem"><tbody>';

        // Recipient
        html += '<tr><td data-label="recipient"><strong>Recipient</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.account_title || '') + '</td></tr>';

        // Recipient Address
        if (instr.recipient_address) {
            html += '<tr><td data-label="recipient_address"><strong>Recipient Address</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.recipient_address) + '</td></tr>';
        }

        // Bank Name
        html += '<tr><td data-label="bank_name"><strong>Bank Name</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.bank_name) + '</td></tr>';

        // Account Number
        if (instr.account_number) {
            html += '<tr><td data-label="account_number"><strong>Account Number</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.account_number) + '</td></tr>';
        }

        // IBAN
        if (instr.iban) {
            html += '<tr><td data-label="iban"><strong>IBAN</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.iban) + '</td></tr>';
        }

        // SWIFT/BIC
        if (instr.swift_code) {
            html += '<tr><td data-label="swift_code"><strong>SWIFT/BIC Code</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.swift_code) + '</td></tr>';
        }

        // Intermediary BIC
        if (instr.intermediary_bic) {
            html += '<tr><td data-label="intermediary_bic"><strong>Intermediary BIC</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.intermediary_bic) + '</td></tr>';
        }

        // UK Account Number
        if (instr.uk_account_number) {
            html += '<tr><td data-label="uk_account_number"><strong>UK Account Number</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.uk_account_number) + '</td></tr>';
        }

        // UK Sort Code
        if (instr.uk_sort_code) {
            html += '<tr><td data-label="uk_sort_code"><strong>UK Sort Code</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.uk_sort_code) + '</td></tr>';
        }

        // Transfer Reference
        if (instr.transfer_reference) {
            html += '<tr><td><strong>Transfer Reference</strong></td><td style="text-align:right">' + escapeHtml(instr.transfer_reference) + '</td></tr>';
        }

        html += '</tbody></table>';

        // Additional Instructions
        if (instr.additional_instructions) {
            html += '<div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 3px solid var(--h2-primary);">';
            html += '<h5 style="margin: 0 0 0.5rem 0; color: var(--h2-primary);">Additional Instructions:</h5>';
            html += '<div style="white-space: pre-wrap; font-size: 0.9rem;">' + escapeHtml(instr.additional_instructions) + '</div>';
            html += '</div>';
        }

        container.innerHTML = html;
    }

    select.addEventListener('change', function () {
        updateBankDetails(parseInt(this.value));
    });

    // Initialize with first bank account on page load
    updateBankDetails(0);

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>
        }

        // IBAN
        if (instr.iban) {
            html += '<tr><td data-label="iban"><strong>IBAN</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.iban) + '</td></tr>';
        }

        // SWIFT/BIC
        if (instr.swift_code) {
            html += '<tr><td data-label="swift_code"><strong>SWIFT/BIC Code</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.swift_code) + '</td></tr>';
        }

        // Intermediary BIC
        if (instr.intermediary_bic) {
            html += '<tr><td data-label="intermediary_bic"><strong>Intermediary BIC</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.intermediary_bic) + '</td></tr>';
        }

        // UK Account Number
        if (instr.uk_account_number) {
            html += '<tr><td data-label="uk_account_number"><strong>UK Account Number</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.uk_account_number) + '</td></tr>';
        }

        // UK Sort Code
        if (instr.uk_sort_code) {
            html += '<tr><td data-label="uk_sort_code"><strong>UK Sort Code</strong></td><td data-label="value" style="text-align:right">' + escapeHtml(instr.uk_sort_code) + '</td></tr>';
        }

        // Transfer Reference
        if (instr.transfer_reference) {
            html += '<tr><td><strong>Transfer Reference</strong></td><td style="text-align:right">' + escapeHtml(instr.transfer_reference) + '</td></tr>';
        }

        html += '</tbody></table>';

        // Additional Instructions
        if (instr.additional_instructions) {
            html += '<div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 3px solid var(--h2-primary);">';
            html += '<h5 style="margin: 0 0 0.5rem 0; color: var(--h2-primary);">Additional Instructions:</h5>';
            html += '<div style="white-space: pre-wrap; font-size: 0.9rem;">' + escapeHtml(instr.additional_instructions) + '</div>';
            html += '</div>';
        }

        container.innerHTML = html;
    });

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>

{{-- Auto-verify payments while the tab is open: fast at first, backs off, and reloads
     the moment the payment completes (webhook or checkout-return finalize). --}}
<script>
(function () {
    var meta = document.querySelector('meta[name="dp-pay-status-url"]');
    if (!meta) return;
    var url = meta.getAttribute('content');
    var live = document.getElementById('dp-pay-live');
    var tries = 0;

    function poll() {
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.ok && d.is_paid) {
                    live.textContent = 'Payment confirmed — reloading…';
                    window.location.reload();
                    return;
                }
                tries++;
                if (tries > 40) { // ~7 minutes — stop hammering, keep manual refresh
                    live.textContent = 'Still pending. This page stopped auto-checking — refresh to try again.';
                    return;
                }
                var delay = tries <= 5 ? 4000 : (tries <= 15 ? 8000 : 12000);
                setTimeout(poll, delay);
            })
            .catch(function () {
                tries++;
                if (tries <= 40) setTimeout(poll, tries <= 5 ? 4000 : 10000);
            });
    }
    setTimeout(poll, 2500);
})();
</script>
{{-- No external Stripe.js needed for Checkout Sessions --}}
<script>
(function () {
    var f = document.getElementById('pay-form'), b = document.getElementById('pay-go');
    if (!f) return;

    // Handle method switching - show/hide Stripe info
    function handleMethodChange() {
        var selectedMethod = document.querySelector('input[name="method"]:checked');
        var stripeContainer = document.getElementById('stripe-info-container');
        if (!selectedMethod || !stripeContainer) return;

        if (selectedMethod.dataset.methodCode === 'stripe') {
            stripeContainer.style.display = 'block';
        } else {
            stripeContainer.style.display = 'none';
        }
    }

    // Attach change listeners to method radios
    var methodRadios = document.querySelectorAll('input[name="method"]');
    methodRadios.forEach(function(radio) {
        radio.addEventListener('change', handleMethodChange);
    });

    // Initial state
    handleMethodChange();

    // Handle form submission
    f.addEventListener('submit', function (e) {
        var selectedMethod = document.querySelector('input[name="method"]:checked');
        var isStripe = selectedMethod && selectedMethod.dataset.methodCode === 'stripe';
        var isWallet = selectedMethod && selectedMethod.dataset.methodCode === 'wallet';

        // Disable button to prevent double submission
        if (b) {
            b.disabled = true;
            b.innerHTML = 'Processing…';
        }

        // Stripe Checkout: redirect to Stripe-hosted page
        if (isStripe) {
            e.preventDefault();

            var formData = new FormData(f);

            fetch(f.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (!data.ok) {
                    throw new Error(data.message || 'Payment initiation failed');
                }

                // Redirect to Stripe Checkout URL
                if (data.payload && data.payload.checkout_url) {
                    window.location.href = data.payload.checkout_url;
                } else if (data.payload && data.payload.simulated) {
                    // Mock mode - redirect to status page
                    window.location.href = data.status_url || window.location.href;
                } else {
                    throw new Error('No checkout URL returned');
                }
            })
            .catch(function(error) {
                console.error('Payment error:', error);
                alert('Payment failed: ' + error.message);
                // Re-enable button
                if (b) { b.disabled = false; b.innerHTML = 'Pay {{ strtoupper($currency ?: 'USD') }} {{ number_format((float) $amount, 2) }}'; }
            });

            return; // Skip normal form submission
        }

        // Wallet payments settle server-side (debit + paid in one atomic
        // transaction) — drive via fetch so the paid/insufficient response
        // is handled without a full page reload.
        if (isWallet) {
            e.preventDefault();

            fetch(f.action, {
                method: 'POST',
                body: new FormData(f),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (!data.ok) throw new Error(data.message || 'Payment failed');
                window.location.href = data.status_url;
            })
            .catch(function(error) {
                alert('Payment failed: ' + error.message);
                if (b) { b.disabled = false; b.innerHTML = 'Pay {{ strtoupper($currency ?: 'USD') }} {{ number_format((float) $amount, 2) }}'; }
            });

            return;
        }

        // For bank transfers and other methods, let the form submit normally
    });
})();
</script>
@endpush
