<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;

/**
 * Agent PM — gateway adapter contract (PM-002).
 *
 * Every gateway (Stripe today; PayPal/JazzCash/EasyPaisa/COD later)
 * implements exactly this surface. Controllers and Blade never talk to
 * gateways directly — they go through PaymentService.
 */
interface PaymentGatewayContract
{
    /** Adapter key as used in payment_methods.gateway + config('admin_payments_engine.gateways'). */
    public function code(): string;

    /** Human label. */
    public function name(): string;

    /**
     * Whether the gateway is usable right now (keys present, settings set).
     * Unconfigured gateways are offered as DISABLED, never removed silently.
     */
    public function isConfigured(): bool;

    /**
     * Start a gateway-side payment intent for the ledger row.
     *
     * Returns a driver payload, e.g.:
     *   ['kind' => 'redirect', 'redirect_url' => ...]            — hosted checkout
     *   ['kind' => 'intent',  'client_secret' => ..., 'publishable_key' => ...]
     *   ['kind' => 'instructions', 'instructions' => [...]]      — bank transfer
     *   ['kind' => 'mock', ...]                                   — simulated (no keys)
     *
     * @param  array  $context  extra input (return_url, etc.) — NEVER amounts:
     *                          amounts are read from $payment (server-authoritative, PM-009).
     */
    public function initiate(Payment $payment, ?User $customer = null, array $context = []): array;

    /**
     * Ask the gateway for the current truth of a payment.
     * Returns ['status' => paid|failed|pending|processing, 'raw' => [...]].
     */
    public function verify(Payment $payment): array;

    /**
     * Refund (full or partial). Returns
     * ['ok' => bool, 'refunded' => float, 'gateway_ref' => ?string, 'error' => ?string].
     */
    public function refund(Payment $payment, ?float $amount = null): array;
}
