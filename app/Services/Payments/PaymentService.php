<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodRule;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\TaskNotification;
use App\Services\AuditLogger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Agent PM — payment engine core (PM-002/PM-004/PM-005/PM-009/PM-010/PM-012).
 *
 * The ONLY writer of payments.status. Responsibilities:
 *  - routing resolver: which methods are offered for a service context
 *  - amount resolver: server-authoritative totals from the accepted offerorder
 *  - idempotent initiate (unique reference + unique(order_id, attempt) + state guard)
 *  - state machine enforcement (config/admin_payments_engine.php)
 *  - method change + locking, audited via the existing AuditLogger
 *  - lifecycle notifications via the existing TaskNotification system
 */
class PaymentService
{
    /* ================================================================
     * Logging (PM-011): technical detail goes to logs/payments.log,
     * customers only ever see the friendly copy.
     * ================================================================ */

    public function logger(): \Psr\Log\LoggerInterface
    {
        try {
            return Log::build(['driver' => 'single', 'path' => storage_path('logs/payments.log')]);
        } catch (Throwable $e) {
            return Log::getLogger();
        }
    }

    public function logTech(string $level, string $message, array $context = []): void
    {
        $context = $this->scrub($context);
        try {
            $this->logger()->{$level}($message, $context);
        } catch (Throwable $e) {
            // never let logging break a payment
        }
    }

    /** Defensive scrub: secrets must never reach logs (PM-007). */
    protected function scrub(array $context): array
    {
        foreach ($context as $k => $v) {
            if (is_string($k) && Str::contains(strtolower($k), ['secret', 'password', 'token', 'card', 'cvv'])) {
                $context[$k] = '***';
            }
        }

        return $context;
    }

    /* ================================================================
     * Gateway resolution (PM-002)
     * ================================================================ */

    public function gateway(string $key): PaymentGatewayContract
    {
        $map = config('admin_payments_engine.gateways', []);
        if (!isset($map[$key])) {
            throw new PaymentException(
                config('admin_payments_engine.friendly_errors.method_not_allowed'),
                'unknown gateway key: ' . $key
            );
        }

        return app($map[$key]);
    }

    public function gatewayFor(Payment $payment): ?PaymentGatewayContract
    {
        if (!$payment->gateway) {
            return null;
        }

        try {
            return $this->gateway($payment->gateway);
        } catch (PaymentException $e) {
            return null;
        }
    }

    /* ================================================================
     * Payment mode (PM-015) — single legacy/advanced switch.
     * 'payment_mode' = legacy|advanced (admin Settings). Falls back to
     * the older payments_engine_enabled bool when the mode is unset.
     * ================================================================ */

    public static function engineEnabled(): bool
    {
        $mode = strtolower(trim((string) Setting::get('payment_mode', '')));
        if ($mode === 'legacy') {
            return false;
        }
        if ($mode === 'advanced') {
            return true;
        }

        return (bool) Setting::get('payments_engine_enabled', true);
    }

    /* ================================================================
     * Routing resolver (PM-001/PM-003)
     * ================================================================ */

    /**
     * Service context for an orders row (object or array-access row).
     * ship_for_me | shop_for_me | custom — the rules table maps these to methods.
     */
    public function serviceContextForOrder($order): string
    {
        $customCol = config('admin_payments_engine.service_detection.custom_marker_column', 'custom_category');
        $purchaseCol = config('admin_payments_engine.service_detection.purchase_marker_column', 'product_purchase');

        $custom = trim((string) ($order->{$customCol} ?? ''));
        if ($custom !== '') {
            return PaymentMethodRule::CONTEXT_CUSTOM;
        }

        if (!empty($order->{$purchaseCol})) {
            return PaymentMethodRule::CONTEXT_SHOP_FOR_ME;
        }

        return PaymentMethodRule::CONTEXT_SHIP_FOR_ME;
    }

    /**
     * Methods offered for a context, ordered primary → secondary (PM-003).
     *
     * @return array<int, array{method: PaymentMethod, priority: int, available: bool, reason: ?string, gateway_configured: bool}>
     */
    public function allowedMethods(string $context, ?float $amount = null): array
    {
        $rows = PaymentMethod::query()
            ->join('payment_method_rules as r', function ($j) use ($context) {
                $j->on('r.payment_method_id', '=', 'payment_methods.id')
                    ->where('r.service_context', $context)
                    ->where('r.is_allowed', true);
            })
            ->where('payment_methods.is_enabled', true)
            ->select('payment_methods.*', 'r.priority as rule_priority')
            ->orderByRaw('COALESCE(r.priority, payment_methods.priority) ASC')
            ->get();

        $out = [];
        foreach ($rows as $method) {
            $gatewayOk = true;
            try {
                $gatewayOk = $this->gateway($method->gateway)->isConfigured();
            } catch (PaymentException $e) {
                $gatewayOk = false;
            }

            $reason = $method->unavailableReason($amount);
            if ($reason === null && !$gatewayOk) {
                $reason = 'This payment method is temporarily unavailable.';
            }

            $out[] = [
                'method'             => $method,
                'priority'           => (int) ($method->rule_priority ?? $method->priority),
                'available'          => $reason === null,
                'reason'             => $reason,
                'gateway_configured' => $gatewayOk,
            ];
        }

        return $out;
    }

    /** Validate a method selection against the routing rules (throws PaymentException). */
    public function assertMethodSelectable(string $context, string $methodCode, ?float $amount): PaymentMethod
    {
        foreach ($this->allowedMethods($context, $amount) as $entry) {
            if ($entry['method']->code === $methodCode) {
                if (!$entry['available']) {
                    throw new PaymentException($entry['reason'] ?? config('admin_payments_engine.friendly_errors.method_not_allowed'));
                }

                return $entry['method'];
            }
        }

        throw new PaymentException(
            config('admin_payments_engine.friendly_errors.method_not_allowed'),
            'method not allowed for context ' . $context . ': ' . $methodCode
        );
    }

    /* ================================================================
     * Amount resolver (PM-009 — P0)
     * ================================================================ */

    /**
     * FINAL payable, recomputed from the accepted offerorder.
     * Client-sent amounts are NEVER accepted anywhere in this class.
     *
     * Units: offerorders.total is INT MAJOR units (whole USD; verified against
     * live data + legacy OrdersController::stripePost which multiplies by 100
     * only when talking to Stripe). The ledger stores DECIMAL(12,2) major units.
     */
    public function resolveAmount($orderId): array
    {
        // LEGACY COMPATIBILITY: Accept latest offer regardless of status (>= 0)
        // Legacy flow leaves offer_status at 0 until payment completes.
        // Amount remains server-authoritative from offerorders.total.
        $offer = DB::table('offerorders')
            ->where('order_id', $orderId)
            ->where('offer_status', '>=', 0) // accept latest offer (legacy: 0, new: 1)
            ->orderByDesc('id')
            ->first();

        if (!$offer) {
            throw new PaymentException(
                config('admin_payments_engine.friendly_errors.no_accepted_offer'),
                'no offerorder found for order ' . $orderId
            );
        }

        return [
            'amount'   => round(max(0.0, (float) $offer->total), 2),
            'currency' => strtoupper((string) Setting::get('business_currency', 'USD')),
            'offer_id' => (int) $offer->id,
        ];
    }

    /* ================================================================
     * Idempotent initiate (PM-012 — P0)
     * ================================================================ */

    public function findOpenPayment(int $orderId): ?Payment
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->whereIn('status', Payment::OPEN_STATUSES)
            ->orderByDesc('attempt')
            ->first();
    }

    /**
     * Create (or idempotently return) the active payment attempt for an order.
     * Double POSTing this method yields exactly ONE ledger row.
     *
     * @return array{payment: Payment, payload: array, created: bool}
     */
    public function initiatePayment($order, string $methodCode, User $customer, array $context = []): array
    {
        $orderId = (int) (is_object($order) ? $order->id : $order);

        // 0a) Checkout sessions expire (30 min); an abandoned Stripe page
        //     must not wedge the order in 'processing' forever. Fail dead
        //     sessions BEFORE the double-pay guard below judges the order.
        $this->releaseExpiredCheckoutAttempts($orderId);

        // 0b) Checkout resume (webhook-independent): an abandoned Stripe
        //     page leaves the attempt in 'processing' with a session that
        //     is still OPEN for up to 30 minutes. The double-pay guard
        //     below treats 'processing' as blocked, so ask Stripe for the
        //     live session state FIRST: open → re-offer the hosted page,
        //     complete+paid → settle now (webhook may not be linked),
        //     expired → fail it so a fresh attempt can start.
        $checkout = Payment::query()
            ->where('order_id', $orderId)
            ->where('status', Payment::STATUS_PROCESSING)
            ->where('gateway', 'stripe')
            ->orderByDesc('attempt')
            ->first();

        if ($checkout
            && ($checkout->metadata['gateway_kind'] ?? null) === 'checkout'
            && !empty($checkout->metadata['session_id'])
        ) {
            $gateway = $this->gatewayFor($checkout);
            $state = $gateway ? $gateway->checkoutSessionState($checkout) : null;

            if (($state['status'] ?? null) === 'open' && !empty($state['url'])) {
                if (!empty($context['origin'])) {
                    $checkout->mergeMetadata(['origin' => $context['origin']]);
                    $checkout->save();
                }

                $this->logTech('info', 'resuming open stripe checkout session', [
                    'payment' => $checkout->reference, 'order_id' => $orderId,
                ]);

                return [
                    'payment' => $checkout,
                    'payload' => [
                        'kind'         => 'checkout',
                        'checkout_url' => $state['url'],
                        'session_id'   => $checkout->metadata['session_id'],
                        'resumed'      => true,
                        'simulated'    => false,
                    ],
                    'created' => false,
                ];
            }

            if (($state['status'] ?? null) === 'expired') {
                // Race guard — releaseExpiredCheckoutAttempts above normally
                // already caught this; never let it wedge the guard.
                try {
                    $this->transition($checkout, Payment::STATUS_FAILED, 'system', [
                        'failure_reason' => 'stripe checkout session expired without payment',
                    ]);
                } catch (PaymentException $e) {
                    // a racing webhook/0a pass already moved it
                }
            }

            if (($state['status'] ?? null) === 'complete' && !empty($state['paid'])) {
                // The customer actually paid on the "abandoned" trip but the
                // webhook never arrived (endpoint not linked). Settle the
                // ledger now; the guard below then correctly reports the
                // order as already paid instead of blocking a new attempt.
                try {
                    $fresh = Payment::find($checkout->id);
                    if ($fresh && !$fresh->isPaid()) {
                        $this->transition($fresh, Payment::STATUS_PAID, 'system', [
                            'verified_via' => 'initiate_recovery',
                        ]);
                    }
                } catch (PaymentException $e) {
                    // raced the webhook into a terminal state — nothing to do
                }
            }
        }

        // 0) Double-payment guard — mirrors the client "payable" logic in
        //    Home2\Home2Controller::orderShow() / PaymentController: once any
        //    attempt for this order sits in a terminal-ish state (paid /
        //    awaiting verification / processing / refunded), NO new attempt
        //    may be opened. The UI only HIDES the pay button; this enforces
        //    it server-side so a direct POST cannot double-charge.
        $blocked = Payment::query()
            ->where('order_id', $orderId)
            ->whereIn('status', [
                Payment::STATUS_PAID,
                Payment::STATUS_AWAITING_VERIFICATION,
                Payment::STATUS_PROCESSING,
                Payment::STATUS_REFUNDED,
                Payment::STATUS_PARTIALLY_REFUNDED,
            ])
            ->exists();

        if ($blocked) {
            throw new PaymentException(
                'This order has already been paid or is awaiting verification.',
                'initiate blocked: order ' . $orderId . ' already has a payment in paid/awaiting_verification/processing/refunded state'
            );
        }

        // 1) Server-authoritative amount (PM-009) — ignore any client amount in $context.
        unset($context['amount'], $context['total']);
        $resolved = $this->resolveAmount($orderId);

        // 2) Rule check (PM-001) — the method must be allowed for THIS order's context.
        $contextKey = is_object($order) ? $this->serviceContextForOrder($order) : ($context['service_context'] ?? PaymentMethodRule::CONTEXT_SHIP_FOR_ME);
        $method = $this->assertMethodSelectable($contextKey, $methodCode, $resolved['amount']);

        // 2b) Per-order forced method (admin negotiation override): pay() can be
        //     reached directly via POST (bypassing selectMethod), so enforce the
        //     override HERE too. selectMethod runs first in the normal flow and
        //     would already have thrown; throwing again here is harmless.
        $this->assertNoOrderOverride($order, $methodCode);

        try {
            return DB::transaction(function () use ($orderId, $method, $customer, $resolved, $context, $order) {
                // 3) State guard (PM-012): an open attempt exists → reuse it.
                $existing = Payment::query()
                    ->where('order_id', $orderId)
                    ->whereIn('status', Payment::OPEN_STATUSES)
                    ->lockForUpdate()
                    ->orderByDesc('attempt')
                    ->first();

                if ($existing) {
                    // Reuse; switch method only while changeable (PM-004).
                    if ($existing->payment_method_code !== $method->code) {
                        if (!$existing->methodChangeable()) {
                            throw new PaymentException(
                                'Your payment is already in progress with another method, so the method can no longer be changed.',
                                'method change blocked in state ' . $existing->status
                            );
                        }
                        $existing = $this->changeMethod($existing, $method->code, $customer, 'customer re-selection before payment');
                    }

                    // Remember which layout (legacy/home2) is actively paying so
                    // the Stripe return lands on the page the customer came from.
                    if (!empty($context['origin'])) {
                        $existing->mergeMetadata(['origin' => $context['origin']]);
                        $existing->save();
                    }

                    $payload = $this->driveGateway($existing, $customer, $context);

                    return ['payment' => $existing->refresh(), 'payload' => $payload, 'created' => false];
                }

                // 4) New attempt — unique reference + unique(order_id, attempt).
                $attempt = ((int) Payment::where('order_id', $orderId)->max('attempt')) + 1;
                $reference = $this->buildReference($orderId, $attempt);

                $payment = Payment::create([
                    'reference'           => $reference,
                    'order_id'            => $orderId,
                    'request_id'          => $context['request_id'] ?? null,
                    'user_id'             => $customer->id,
                    'attempt'             => $attempt,
                    'amount'              => $resolved['amount'],
                    'currency'            => $resolved['currency'],
                    'payment_method_id'   => $method->id,
                    'payment_method_code' => $method->code,
                    'gateway'             => $method->gateway,
                    'status'              => Payment::STATUS_PENDING,
                    'status_set_by'       => 'customer',
                    'initiated_at'        => now(),
                    'metadata'            => [
                        'offer_id'          => $resolved['offer_id'],
                        'service_context'   => is_object($order) ? $this->serviceContextForOrder($order) : ($context['service_context'] ?? null),
                        'amount_source'     => 'offerorders:' . $resolved['offer_id'],
                        'origin'            => $context['origin'] ?? 'home2',
                    ],
                ]);

                AuditLogger::log($payment, 'created');

                $payment = $this->transition($payment, Payment::STATUS_METHOD_SELECTED, 'customer');

                $payload = $this->driveGateway($payment, $customer, $context);

                return ['payment' => $payment->refresh(), 'payload' => $payload, 'created' => true];
            });
        } catch (QueryException $e) {
            // Lost the unique(order_id, attempt) race (double POST): return the winner row.
            if ($this->isDuplicateKeyError($e)) {
                $this->logTech('warning', 'duplicate initiate raced — reusing existing row', ['order_id' => $orderId]);
                $existing = $this->findOpenPayment($orderId);
                if ($existing) {
                    $payload = $this->driveGateway($existing, $customer, $context);

                    return ['payment' => $existing->refresh(), 'payload' => $payload, 'created' => false];
                }
            }
            throw $e;
        }
    }

    /* ================================================================
     * Wallet top-ups (PM-014) — order-less payments that reuse the same
     * ledger, state machine and webhook truth path as order payments.
     * ================================================================ */

    /**
     * Create (or idempotently return) the open top-up payment for a user.
     * There is no offerorder behind a top-up, so the amount comes from
     * validated client input — clamped here against the wallet min/max
     * settings before anything is written.
     *
     * @return array{payment: Payment, payload: array, created: bool}
     */
    public function initiateTopup(User $customer, float $amount, string $methodCode = 'stripe', array $context = []): array
    {
        if (!app(WalletService::class)->enabled()) {
            throw new PaymentException('The wallet is currently disabled.', 'wallet disabled by setting');
        }

        $amount = round($amount, 2);
        $min = (float) Setting::get('wallet_topup_min', 10);
        $max = (float) Setting::get('wallet_topup_max', 5000);
        if ($amount < $min || $amount > $max) {
            throw new PaymentException(
                sprintf('Top-up amount must be between %s and %s.', number_format($min, 2), number_format($max, 2)),
                sprintf('topup amount %.2f outside window %.2f-%.2f', $amount, $min, $max)
            );
        }

        // Rule check (PM-001) — methods allowed for the wallet_topup context.
        $method = $this->assertMethodSelectable('wallet_topup', $methodCode, $amount);

        try {
            return DB::transaction(function () use ($customer, $amount, $method, $context) {
                // State guard (PM-012): reuse the user's open top-up attempt.
                $existing = Payment::query()
                    ->whereNull('order_id')
                    ->where('user_id', $customer->id)
                    ->where('metadata->purpose', 'wallet_topup')
                    ->whereIn('status', Payment::OPEN_STATUSES)
                    ->lockForUpdate()
                    ->orderByDesc('id')
                    ->first();

                if ($existing) {
                    if ($existing->payment_method_code !== $method->code && $existing->methodChangeable()) {
                        $existing = $this->changeMethod($existing, $method->code, $customer, 'customer re-selection before top-up');
                    }

                    $payload = $this->driveGateway($existing, $customer, $context);

                    return ['payment' => $existing->refresh(), 'payload' => $payload, 'created' => false];
                }

                $payment = Payment::create([
                    'reference'           => $this->buildTopupReference($customer->id),
                    'order_id'            => null, // order-less: wallet top-up
                    'user_id'             => $customer->id,
                    'attempt'             => 1,
                    'amount'              => $amount,
                    'currency'            => strtoupper((string) Setting::get('business_currency', 'USD')),
                    'payment_method_id'   => $method->id,
                    'payment_method_code' => $method->code,
                    'gateway'             => $method->gateway,
                    'status'              => Payment::STATUS_PENDING,
                    'status_set_by'       => 'customer',
                    'initiated_at'        => now(),
                    'metadata'            => ['purpose' => 'wallet_topup'],
                ]);

                AuditLogger::log($payment, 'created');

                $payment = $this->transition($payment, Payment::STATUS_METHOD_SELECTED, 'customer');

                $payload = $this->driveGateway($payment, $customer, $context);

                return ['payment' => $payment->refresh(), 'payload' => $payload, 'created' => true];
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyError($e)) {
                $existing = Payment::query()
                    ->whereNull('order_id')
                    ->where('user_id', $customer->id)
                    ->where('metadata->purpose', 'wallet_topup')
                    ->whereIn('status', Payment::OPEN_STATUSES)
                    ->orderByDesc('id')
                    ->first();
                if ($existing) {
                    return ['payment' => $existing, 'payload' => $this->driveGateway($existing, $customer, $context), 'created' => false];
                }
            }
            throw $e;
        }
    }

    /** Top-up references are user-scoped: WT-<userId>-<rand> (unique index guards). */
    protected function buildTopupReference(int $userId): string
    {
        $rand = strtoupper(Str::random((int) config('admin_payments_engine.reference_rand_length', 6)));

        return sprintf('WT-%d-%s', $userId, $rand);
    }

    /* ================================================================
     * Shop orders (PM-015) — shop checkout rides the same ledger +
     * state machine as order payments, with the forced-gateway chain:
     *   shop-order override → forced product in the cart → normal rules.
     * ================================================================ */

    /** The method forced for a shop order: order-level first, then its products. */
    public function forcedMethodForShopOrder($shopOrder): ?PaymentMethod
    {
        if (!is_object($shopOrder)) {
            $shopOrder = DB::table('shop_orders')->where('id', (int) $shopOrder)->first();
            if (!$shopOrder) {
                return null;
            }
        }

        $codes = [];
        $orderLevel = trim((string) ($shopOrder->forced_payment_method_code ?? ''));
        if ($orderLevel !== '') {
            $codes[] = $orderLevel;
        }

        // Product-level forcing: any item whose catalog row forces a method
        // (most constrained cart member wins — first forced product found).
        $productCodes = DB::table('shop_order_items as si')
            ->join('shop_products as p', 'p.id', '=', 'si.shop_product_id')
            ->where('si.shop_order_id', $shopOrder->id)
            ->whereNotNull('p.forced_payment_method_code')
            ->where('p.forced_payment_method_code', '!=', '')
            ->orderBy('si.id')
            ->pluck('p.forced_payment_method_code')
            ->all();
        $codes = array_merge($codes, $productCodes);

        foreach ($codes as $code) {
            $method = PaymentMethod::query()->where('code', $code)->where('is_enabled', true)->first();
            if ($method) {
                return $method;
            }
        }

        return null;
    }

    /** Throw when the customer picks anything other than the forced shop-order method. */
    public function assertNoShopOrderOverride($shopOrder, string $methodCode): void
    {
        $forced = $this->forcedMethodForShopOrder($shopOrder);
        if ($forced && $forced->code !== $methodCode) {
            throw new PaymentException(
                'Our team has set ' . $forced->name . ' as the payment method for this purchase. Please use that method or contact support.',
                'shop-order forced method ' . $forced->code . ' overrides customer selection ' . $methodCode
            );
        }
    }

    /**
     * Create (or idempotently return) the payment for a shop order.
     * Amount is the server-side shop_orders.total — never client input.
     *
     * @return array{payment: Payment, payload: array, created: bool}
     */
    public function initiateShopOrderPayment($shopOrder, string $methodCode, User $customer, array $context = []): array
    {
        $shopOrderId = (int) (is_object($shopOrder) ? $shopOrder->id : $shopOrder);
        $row = DB::table('shop_orders')->where('id', $shopOrderId)->first();
        if (!$row) {
            throw new PaymentException('Shop order not found.', 'shop order ' . $shopOrderId . ' missing');
        }

        $amount = round(max(0.0, (float) $row->total), 2);
        $method = $this->assertMethodSelectable('shop', $methodCode, $amount);
        $this->assertNoShopOrderOverride($row, $methodCode);

        try {
            return DB::transaction(function () use ($row, $amount, $method, $customer, $context) {
                // State guard: reuse the open attempt for THIS shop order.
                $existing = Payment::query()
                    ->whereNull('order_id')
                    ->where('user_id', $customer->id)
                    ->where('metadata->purpose', 'shop_order')
                    ->where('metadata->shop_order_id', (string) $row->id)
                    ->whereIn('status', Payment::OPEN_STATUSES)
                    ->lockForUpdate()
                    ->orderByDesc('id')
                    ->first();

                if ($existing) {
                    if ($existing->payment_method_code !== $method->code && $existing->methodChangeable()) {
                        $existing = $this->changeMethod($existing, $method->code, $customer, 'customer re-selection before shop payment');
                    }

                    $payload = $this->driveGateway($existing, $customer, $context);

                    return ['payment' => $existing->refresh(), 'payload' => $payload, 'created' => false];
                }

                $rand = strtoupper(Str::random((int) config('admin_payments_engine.reference_rand_length', 6)));
                $payment = Payment::create([
                    'reference'           => sprintf('SH-%d-%s', $row->id, $rand),
                    'order_id'            => null, // order-less: shop order payment
                    'user_id'             => $customer->id,
                    'attempt'             => 1,
                    'amount'              => $amount,
                    'currency'            => strtoupper((string) Setting::get('business_currency', 'USD')),
                    'payment_method_id'   => $method->id,
                    'payment_method_code' => $method->code,
                    'gateway'             => $method->gateway,
                    'status'              => Payment::STATUS_PENDING,
                    'status_set_by'       => 'customer',
                    'initiated_at'        => now(),
                    'metadata'            => ['purpose' => 'shop_order', 'shop_order_id' => (string) $row->id, 'shop_order_code' => $row->code],
                ]);

                AuditLogger::log($payment, 'created');

                $payment = $this->transition($payment, Payment::STATUS_METHOD_SELECTED, 'customer');

                $payload = $this->driveGateway($payment, $customer, $context);

                return ['payment' => $payment->refresh(), 'payload' => $payload, 'created' => true];
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyError($e)) {
                $existing = Payment::query()
                    ->whereNull('order_id')
                    ->where('user_id', $customer->id)
                    ->where('metadata->purpose', 'shop_order')
                    ->where('metadata->shop_order_id', (string) $row->id)
                    ->whereIn('status', Payment::OPEN_STATUSES)
                    ->orderByDesc('id')
                    ->first();
                if ($existing) {
                    return ['payment' => $existing, 'payload' => $this->driveGateway($existing, $customer, $context), 'created' => false];
                }
            }
            throw $e;
        }
    }

    /** Mark a shop order paid when its engine payment settles (all gateways). */
    protected function syncShopOrderOnPaid(Payment $payment): void
    {
        try {
            $shopOrderId = (int) ($payment->metadata['shop_order_id'] ?? 0);
            if ($shopOrderId > 0) {
                DB::table('shop_orders')->where('id', $shopOrderId)->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);
            }

            $this->logTech('info', 'shop order synced on payment paid', [
                'payment' => $payment->reference, 'shop_order_id' => $shopOrderId,
            ]);
        } catch (\Throwable $e) {
            $this->logTech('error', 'shop order sync failed on payment paid', [
                'payment' => $payment->reference, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Push an open payment through its gateway adapter as far as its state allows.
     * Safe to call repeatedly (idempotent per gateway).
     */
    protected function driveGateway(Payment $payment, ?User $customer, array $context): array
    {
        $gateway = $this->gatewayFor($payment);
        if (!$gateway) {
            throw new PaymentException(
                config('admin_payments_engine.friendly_errors.generic'),
                'no gateway adapter for payment ' . $payment->reference
            );
        }

        // pending rows that skipped method_selected (defensive) still need the hop.
        if ($payment->status === Payment::STATUS_PENDING) {
            $payment = $this->transition($payment, Payment::STATUS_METHOD_SELECTED, 'system');
        }

        if ($payment->status === Payment::STATUS_METHOD_SELECTED) {
            $payment = $this->transition($payment, Payment::STATUS_AWAITING_PAYMENT, 'system');
        }

        $payload = [];

        if ($payment->status === Payment::STATUS_AWAITING_PAYMENT) {
            $payload = $gateway->initiate($payment, $customer, $context);

            // Persist gateway references (client secrets/ids — never secrets of ours).
            $meta = $payload['kind'] ?? null;
            if (($meta === 'intent' || $meta === 'mock') && !empty($payload['client_secret'])) {
                $payment->gateway_transaction_id = $payment->gateway_transaction_id
                    ?: $this->intentIdFromSecret((string) $payload['client_secret']);
                $payment->mergeMetadata(['gateway_kind' => $meta]);
                $payment->save();
            }

            // Stripe Checkout Sessions - store session_id in metadata for webhook resolution
            if ($meta === 'checkout' && !empty($payload['session_id'])) {
                $payment->mergeMetadata([
                    'gateway_kind' => 'checkout',
                    'session_id' => $payload['session_id'],
                ]);
                $payment->save();
            }

            // Card gateways move to processing once an intent exists (truth arrives by webhook).
            if (($meta === 'intent' || $meta === 'mock') && $gateway->code() === 'stripe') {
                $payment = $this->transition($payment, Payment::STATUS_PROCESSING, 'system');
            }

            // Checkout sessions also move to processing state
            if ($meta === 'checkout' && $gateway->code() === 'stripe') {
                $payment = $this->transition($payment, Payment::STATUS_PROCESSING, 'system');
            }

            // Wallet rails (PM-014): settle instantly from balance — debit +
            // paid transition happen atomically inside WalletService. The
            // browser never decides success; insufficient funds throw and
            // roll the whole attempt back.
            if (($payload['kind'] ?? null) === 'wallet') {
                $payment = app(WalletService::class)->payFromWallet($payment, $customer);
                $payload['status'] = $payment->status;
            }
        }

        // Checkout resume: a 'processing' Stripe Checkout attempt whose
        // session is still open re-offers the hosted-page URL so the
        // customer can finish paying instead of staring at a dead status.
        if ($payment->status === Payment::STATUS_PROCESSING
            && $gateway->code() === 'stripe'
            && ($payment->metadata['gateway_kind'] ?? null) === 'checkout'
            && !empty($payment->metadata['session_id'])
        ) {
            $state = $gateway->checkoutSessionState($payment);
            if (($state['status'] ?? null) === 'open' && !empty($state['url'])) {
                return [
                    'kind'         => 'checkout',
                    'checkout_url' => $state['url'],
                    'session_id'   => $payment->metadata['session_id'],
                    'resumed'      => true,
                    'simulated'    => false,
                ];
            }
        }

        // Bank flow: include instructions until a proof is uploaded.
        if ($gateway instanceof BankTransferGateway && method_exists($gateway, 'instructions')) {
            $payload['instructions'] = $gateway->instructions($payment);
        }

        return $payload;
    }

    /** PaymentIntent id from a client secret (pi_xxx_secret_yyy → pi_xxx). */
    protected function intentIdFromSecret(string $secret): ?string
    {
        $parts = explode('_secret', $secret, 2);

        return $parts[0] ?: null;
    }

    /**
     * Self-healing for stuck Stripe Checkout payments: if a payment sits in
     * an open state with a Checkout Session on file, ask Stripe for the live
     * session state and finalize immediately when it was actually paid
     * (covers missing return trips and undelivered webhooks). Cooldown-
     * guarded (20s) so page polling never hammers the Stripe API.
     */
    public function recoverStuckCheckout(Payment $payment): Payment
    {
        if ($payment->gateway !== 'stripe'
            || !in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_PROCESSING], true)) {
            return $payment;
        }

        $sessionId = trim((string) ($payment->metadata['session_id'] ?? ''));
        if ($sessionId === '') {
            return $payment;
        }

        $lastCheck = (int) ($payment->metadata['session_checked_at'] ?? 0);
        if (time() - $lastCheck < 20) {
            return $payment;
        }

        $payment->mergeMetadata(['session_checked_at' => time()]);
        $payment->save();

        try {
            $state = $this->gatewayFor($payment)?->checkoutSessionState($payment);
        } catch (\Throwable $e) {
            $this->logTech('warning', 'session recovery check failed: ' . $e->getMessage(), ['payment' => $payment->reference]);
            return $payment->refresh();
        }

        if ($state && ($state['status'] ?? '') === 'complete' && !empty($state['paid'])) {
            try {
                $fresh = Payment::find($payment->id);
                if ($fresh && !$fresh->isPaid()) {
                    return $this->transition($fresh, Payment::STATUS_PAID, 'system', [
                        'verified_via' => 'session_recovery',
                    ]);
                }
            } catch (\Throwable $e) {
                // Illegal-transition race with the webhook is fine.
                $this->logTech('warning', 'session recovery finalize skipped: ' . $e->getMessage(), [
                    'payment' => $payment->reference,
                ]);
            }
        }

        return $payment->refresh();
    }

    /**
     * Fail 'processing' Stripe Checkout attempts whose session has expired
     * so the customer can start a fresh attempt. Truth about SUCCESS still
     * arrives only via the verified webhook (PM-013) — a 'complete' session
     * is deliberately left for the webhook to settle.
     */
    protected function releaseExpiredCheckoutAttempts(int $orderId): void
    {
        $stale = Payment::query()
            ->where('order_id', $orderId)
            ->where('status', Payment::STATUS_PROCESSING)
            ->where('gateway', 'stripe')
            ->whereNotNull('metadata')
            ->get();

        foreach ($stale as $payment) {
            if (($payment->metadata['gateway_kind'] ?? null) !== 'checkout') {
                continue;
            }
            if (empty($payment->metadata['session_id'])) {
                continue;
            }

            $gateway = $this->gatewayFor($payment);
            $state = $gateway ? $gateway->checkoutSessionState($payment) : null;

            if (($state['status'] ?? null) === 'expired') {
                $this->transition($payment, Payment::STATUS_FAILED, 'system', [
                    'failure_reason' => 'stripe checkout session expired without payment',
                ]);
                $this->logTech('info', 'released expired stripe checkout session', ['payment' => $payment->reference]);
            }
        }
    }

    protected function buildReference(int $orderId, int $attempt): string
    {
        $prefix = strtoupper(trim((string) Setting::get(
            config('admin_payments_engine.reference_prefix_setting'),
            config('admin_payments_engine.reference_fallback_prefix')
        )));
        $prefix = $prefix !== '' ? $prefix : 'DP';
        $rand = strtoupper(Str::random((int) config('admin_payments_engine.reference_rand_length', 6)));

        return sprintf('%s-%d-%d-%s', $prefix, $orderId, $attempt, $rand);
    }

    protected function isDuplicateKeyError(QueryException $e): bool
    {
        $msg = strtolower($e->getMessage());

        return str_contains($msg, 'duplicate entry') || str_contains($msg, 'unique constraint');
    }

    /* ================================================================
     * Method change + locking (PM-004)
     * ================================================================ */

    public function changeMethod(Payment $payment, string $newMethodCode, User $actor, ?string $reason = null): Payment
    {
        if (!$payment->methodChangeable()) {
            throw new PaymentException(
                'The payment method can no longer be changed for this payment.',
                'method change blocked in state ' . $payment->status . ' (ref ' . $payment->reference . ')'
            );
        }

        // Rule check for the new method (PM-001): same rules as initial selection.
        $context = $payment->metadata['service_context'] ?? $this->contextForOrderId($payment->order_id);
        $method = $this->assertMethodSelectable($context, $newMethodCode, (float) $payment->amount);

        $oldCode = (string) $payment->getOriginal('payment_method_code');

        if ($oldCode === $newMethodCode) {
            return $payment; // no-op
        }

        $payment->fill([
            'payment_method_id'   => $method->id,
            'payment_method_code' => $method->code,
            'gateway'             => $method->gateway,
            'gateway_transaction_id' => null, // previous gateway intent is abandoned
        ]);
        $payment->mergeMetadata([
            'last_method_change' => [
                'from' => $oldCode,
                'to'   => $method->code,
                'by'   => $actor->id,
                'at'   => now()->toDateTimeString(),
                'reason' => $reason,
            ],
        ]);

        // Audit old → new via the existing AuditLogger (PM-010): snapshots
        // capture old/new method ids; action names the event. Called BEFORE
        // save so getOriginal()/getAttributes() hold true before/after values.
        AuditLogger::log($payment, 'payment_method_changed');
        $payment->save();

        $this->notifyAdmins('Payment method changed', 'Order payment method updated',
            'Payment ' . $payment->reference . ' changed from ' . $oldCode . ' to ' . $method->code . ' by user #' . $actor->id . ($reason ? ' — ' . $reason : ''));

        return $payment->refresh();
    }

    protected function contextForOrderId(int $orderId): string
    {
        $order = DB::table('orders')->where('id', $orderId)->first();

        return $order ? $this->serviceContextForOrder($order) : PaymentMethodRule::CONTEXT_SHIP_FOR_ME;
    }

    /* ================================================================
     * Per-order forced method (admin negotiation override)
     * ================================================================ */

    /**
     * The method an admin has forced for THIS order during negotiation,
     * or null when the order follows the normal service-context rules.
     * Returns null unless the stored code maps to an enabled method.
     */
    public function forcedMethodForOrder($order): ?PaymentMethod
    {
        $codes = [];
        $orderLevel = trim((string) ($order->forced_payment_method_code ?? ''));
        if ($orderLevel !== '') {
            $codes[] = $orderLevel;
        }

        // PM-015: a quote's forced method carries onto the order created
        // from it (orders.request_quote_id → request_quotes).
        $quoteId = (int) ($order->request_quote_id ?? 0);
        if ($quoteId > 0) {
            $quoteCode = trim((string) DB::table('request_quotes')->where('id', $quoteId)->value('forced_payment_method_code'));
            if ($quoteCode !== '') {
                $codes[] = $quoteCode;
            }
        }

        foreach ($codes as $code) {
            $method = PaymentMethod::query()
                ->where('code', $code)
                ->where('is_enabled', true)
                ->first();
            if ($method) {
                return $method;
            }
        }

        return null;
    }

    /**
     * Throw when the customer tries to pick a method other than the one
     * forced by an admin for this order (throws only when an override is active).
     */
    public function assertNoOrderOverride($order, string $methodCode): void
    {
        $forced = $this->forcedMethodForOrder($order);
        if ($forced && $forced->code !== $methodCode) {
            throw new PaymentException(
                'Our team has set ' . $forced->name . ' as the payment method for this order. Please use that method or contact support.',
                'order-level forced method ' . $forced->code . ' overrides customer selection ' . $methodCode
            );
        }
    }

    /* ================================================================
     * State machine (PM-005)
     * ================================================================ */

    public function assertTransition(string $from, string $to): void
    {
        $map = (array) config('admin_payments_engine.transitions.' . $from, null);

        if ($map === null || !in_array($to, $map, true)) {
            throw new PaymentException(
                'This payment action is not allowed in the current state.',
                'illegal transition ' . $from . ' → ' . $to
            );
        }
    }

    /**
     * The single status writer. Illegal transitions throw (PM-005).
     * $setBy: customer|admin|webhook|system. Admin-only targets
     * (refunded / partially_refunded) require $setBy = admin.
     */
    /**
     * Sync order status when payment becomes paid (LEGACY COMPATIBILITY).
     * Mirrors legacy OrdersController::stripePost behavior by setting offer_status = 1
     * so the legacy client payment tab advances to the tracking screen.
     */
    protected function syncOrderOnPaid(Payment $payment): void
    {
        try {
            DB::table('offerorders')
                ->where('order_id', $payment->order_id)
                ->update(['offer_status' => 1]);

            $this->logTech('info', 'order synced on payment paid', [
                'payment' => $payment->reference,
                'order_id' => $payment->order_id,
            ]);
        } catch (\Throwable $e) {
            $this->logTech('error', 'order sync failed on payment paid', [
                'payment' => $payment->reference,
                'order_id' => $payment->order_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function transition(Payment $payment, string $to, string $setBy, array $extraMeta = []): Payment
    {
        $from = $payment->status;
        $this->assertTransition($from, $to);

        if (config('admin_payments_engine.states.' . $to . '.admin_only') && $setBy !== 'admin') {
            throw new PaymentException(
                'This payment action is restricted to administrators.',
                'admin-only transition ' . $from . ' → ' . $to . ' attempted by ' . $setBy
            );
        }

        // Immutable after settlement (PM-004): guard paid rows against method mutation happens
        // in changeMethod; here we guard that settled rows only move to refund states.
        if (in_array($from, [Payment::STATUS_PAID, Payment::STATUS_PARTIALLY_REFUNDED], true)
            && !in_array($to, [Payment::STATUS_REFUNDED, Payment::STATUS_PARTIALLY_REFUNDED], true)) {
            throw new PaymentException(
                'This payment is already settled and cannot be modified.',
                'settled payment ' . $payment->reference . ' cannot move to ' . $to
            );
        }

        if ($extraMeta) {
            $payment->mergeMetadata($extraMeta);
        }

        $payment->status = $to;
        $payment->status_set_by = $setBy;

        switch ($to) {
            case Payment::STATUS_PAID:
                $payment->paid_at = $payment->paid_at ?: now();
                $payment->failure_reason = null;
                if (($payment->metadata['purpose'] ?? null) === 'wallet_topup') {
                    // Wallet top-up (PM-014): credit the balance BEFORE the
                    // row is saved as paid — a credit failure aborts the
                    // paid transition so a Stripe retry can settle cleanly.
                    app(WalletService::class)->creditTopup($payment);
                    break;
                }
                if (($payment->metadata['purpose'] ?? null) === 'shop_order') {
                    // Shop order (PM-015): flip the shop order to paid.
                    $this->syncShopOrderOnPaid($payment);
                    break;
                }
                // LEGACY SYNC: Update offer status to align legacy views with payment reality
                $this->syncOrderOnPaid($payment);
                break;
            case Payment::STATUS_FAILED:
                $payment->failed_at = now();
                break;
            case Payment::STATUS_CANCELLED:
                $payment->cancelled_at = now();
                break;
            case Payment::STATUS_REFUNDED:
                $payment->refunded_at = now();
                break;
        }

        // Audited lifecycle change (PM-010) — logged BEFORE save so the
        // AuditLogger snapshots record the real old → new pair.
        AuditLogger::log($payment, 'payment_status_' . $from . '_to_' . $to);

        $payment->save();

        $this->logTech('info', 'payment transition', [
            'payment' => $payment->reference, 'from' => $from, 'to' => $to, 'by' => $setBy,
        ]);

        $this->fireLifecycleNotifications($payment, $from, $to);

        return $payment->refresh();
    }

    /* ================================================================
     * Bank proof + admin verification (PM-008/PM-013)
     * ================================================================ */

    /** Called by the controller AFTER the validated proof file is stored. */
    public function attachProof(Payment $payment, string $storedRelativePath, ?User $customer = null): Payment
    {
        if (!in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_AWAITING_VERIFICATION], true)) {
            throw new PaymentException(
                'A transfer receipt cannot be uploaded for this payment right now.',
                'proof upload blocked in state ' . $payment->status
            );
        }

        $payment->proof_path = $storedRelativePath;
        $payment->proof_uploaded_at = now();

        if ($payment->status === Payment::STATUS_AWAITING_PAYMENT) {
            $payment = $this->transition($payment, Payment::STATUS_AWAITING_VERIFICATION, 'customer');
        } else {
            AuditLogger::log($payment, 'payment_proof_reuploaded');
            $payment->save();
        }

        $this->notifyAdmins(
            'Bank transfer receipt uploaded',
            'Payment verification needed',
            'Customer uploaded a bank receipt for payment ' . $payment->reference . ' (order #' . $payment->order_id . '). Verification is required before it is marked paid.'
        );

        return $payment->refresh();
    }

    /** ADMIN verify action — the only path from awaiting_verification to paid. */
    public function verifyBankPayment(Payment $payment, User $admin, bool $approve, ?string $note = null): Payment
    {
        if ($payment->status !== Payment::STATUS_AWAITING_VERIFICATION) {
            throw new PaymentException(
                'This payment is not awaiting verification.',
                'verify attempted in state ' . $payment->status
            );
        }

        $payment->verified_by = $admin->id;
        $payment->verified_at = now();
        $payment->mergeMetadata(['verification_note' => $note, 'verified_by' => $admin->id]);
        $payment->save();

        return $this->transition(
            $payment,
            $approve ? Payment::STATUS_PAID : Payment::STATUS_FAILED,
            'admin',
            ['verification' => ['approved' => $approve, 'note' => $note, 'admin' => $admin->id, 'at' => now()->toDateTimeString()]]
        );
    }

    /**
     * ADMIN manual mark-paid (PM-016): confirm the money arrived OUTSIDE the
     * app (bank statement reconciliation) — works with or without an
     * uploaded receipt. This is the "mark payment as received" option.
     */
    public function markBankPaymentReceived(Payment $payment, User $admin, ?string $note = null): Payment
    {
        if (!in_array($payment->status, [Payment::STATUS_AWAITING_PAYMENT, Payment::STATUS_AWAITING_VERIFICATION], true)) {
            throw new PaymentException(
                'Only bank transfers awaiting payment or verification can be marked as received.',
                'manual mark-received attempted in state ' . $payment->status . ' (ref ' . $payment->reference . ')'
            );
        }

        $payment->verified_by = $admin->id;
        $payment->verified_at = now();
        $payment->mergeMetadata([
            'verified_by'        => $admin->id,
            'verification_note'  => $note,
            'manual_receipt'     => ['by' => $admin->id, 'note' => $note, 'at' => now()->toDateTimeString()],
        ]);
        $payment->save();

        return $this->transition($payment, Payment::STATUS_PAID, 'admin', [
            'verification' => ['approved' => true, 'manual' => true, 'note' => $note, 'admin' => $admin->id, 'at' => now()->toDateTimeString()],
        ]);
    }

    /* ================================================================
     * Refunds (PM-004/PM-002) — admin only
     * ================================================================ */

    /**
     * Refund a payment. Destination (PM-017):
     *   'original' — back through the payment's own rails (Stripe → card,
     *                wallet → wallet, bank/COD → manual bookkeeping)
     *   'wallet'   — credit the customer's wallet instead (chosen by admin)
     */
    public function refundPayment(Payment $payment, User $admin, ?float $amount = null, ?string $note = null, string $destination = 'original'): Payment
    {
        if (!in_array($payment->status, [Payment::STATUS_PAID, Payment::STATUS_PARTIALLY_REFUNDED], true)) {
            throw new PaymentException(
                'Only paid payments can be refunded.',
                'refund attempted in state ' . $payment->status
            );
        }

        $total = (float) $payment->amount;
        $already = (float) $payment->amount_refunded;
        $amount = $amount !== null ? round(min($amount, $total - $already), 2) : ($total - $already);

        if ($amount <= 0) {
            throw new PaymentException('Nothing left to refund on this payment.', 'refund amount <= 0');
        }

        if ($destination === 'wallet') {
            $result = app(WalletService::class)->refundToWallet($payment, $amount);
        } else {
            $gateway = $this->gatewayFor($payment);
            $result = $gateway
                ? $gateway->refund($payment, $amount)
                : ['ok' => true, 'refunded' => $amount, 'gateway_ref' => null, 'error' => null];
        }

        if (!$result['ok']) {
            $this->logTech('error', 'gateway refund failed', [
                'payment' => $payment->reference, 'error' => $result['error'] ?? null,
            ]);
            throw new PaymentException(
                'The refund could not be processed by the payment provider. No changes were made.',
                'gateway refund failed: ' . ($result['error'] ?? '?')
            );
        }

        $payment->amount_refunded = round($already + (float) $result['refunded'], 2);
        $payment->mergeMetadata([
            'last_refund_destination' => $destination,
            'refunds' => array_merge($payment->metadata['refunds'] ?? [], [[
                'amount' => (float) $result['refunded'],
                'by'     => $admin->id,
                'at'     => now()->toDateTimeString(),
                'note'   => $note,
                'gateway_ref' => $result['gateway_ref'] ?? null,
            ]]),
        ]);

        AuditLogger::log($payment, 'payment_refund_recorded');
        $payment->save();

        // Top-up refunds (PM-014): the money went back to the original
        // payment rails (card), so the credited wallet balance must be
        // reversed — capped at the current balance so it never goes negative.
        if (($payment->metadata['purpose'] ?? null) === 'wallet_topup') {
            app(WalletService::class)->reverseTopup($payment, (float) $result['refunded']);
        }

        $target = ((float) $payment->amount_refunded >= $total - 0.001)
            ? Payment::STATUS_REFUNDED
            : Payment::STATUS_PARTIALLY_REFUNDED;

        return $this->transition($payment, $target, 'admin');
    }

    /* ================================================================
     * Notifications (PM-010) — existing TaskNotification system only
     * ================================================================ */

    protected function fireLifecycleNotifications(Payment $payment, string $from, string $to): void
    {
        try {
            // Wallet top-ups (PM-014) have no order — wallet-specific copy.
            if (($payment->metadata['purpose'] ?? null) === 'wallet_topup') {
                if ($to === Payment::STATUS_PAID) {
                    $this->notifyCustomer($payment, 'Wallet topped up', 'Your wallet balance was updated',
                        'Top-up ' . $payment->reference . ' (' . $payment->currency . ' ' . number_format((float) $payment->amount, 2) . ') was confirmed and added to your wallet.');
                    $this->notifyAdmins('Wallet top-up received', 'A wallet top-up was confirmed',
                        'Top-up ' . $payment->reference . ' — ' . $payment->currency . ' ' . number_format((float) $payment->amount, 2) . ' via ' . $payment->payment_method_code . '.');
                }

                return;
            }

            // Shop orders (PM-015) — reference is the shop order code.
            if (($payment->metadata['purpose'] ?? null) === 'shop_order') {
                if ($to === Payment::STATUS_PAID) {
                    $shopCode = (string) ($payment->metadata['shop_order_code'] ?? $payment->reference);
                    $this->notifyCustomer($payment, 'Shop order paid', 'Your shop order payment was received',
                        'Payment ' . $payment->reference . ' (' . $payment->currency . ' ' . number_format((float) $payment->amount, 2) . ') for shop order ' . $shopCode . ' was confirmed.');
                    $this->notifyAdmins('Shop order paid', 'A shop order payment was confirmed',
                        'Payment ' . $payment->reference . ' — shop order ' . $shopCode . ' — ' . $payment->currency . ' ' . number_format((float) $payment->amount, 2) . ' via ' . $payment->payment_method_code . '.');
                }

                return;
            }

            $orderRef = DB::table('orders')->where('id', $payment->order_id)->value('order_id') ?: ('#' . $payment->order_id);

            if ($to === Payment::STATUS_PAID) {
                $this->notifyCustomer($payment, 'Payment received', 'Your payment was successful',
                    'Payment ' . $payment->reference . ' for order ' . $orderRef . ' (' . $payment->currency . ' ' . number_format((float) $payment->amount, 2) . ') has been confirmed.');
                $this->notifyAdmins('Payment received', 'A payment was marked as paid',
                    'Payment ' . $payment->reference . ' for order ' . $orderRef . ' — ' . $payment->currency . ' ' . number_format((float) $payment->amount, 2) . ' via ' . $payment->payment_method_code . '.');
            } elseif ($to === Payment::STATUS_FAILED && $from !== Payment::STATUS_AWAITING_VERIFICATION) {
                $this->notifyCustomer($payment, 'Payment failed', 'Your payment could not be completed',
                    'Payment ' . $payment->reference . ' for order ' . $orderRef . ' failed. Nothing was charged — please try again or choose another method.');
            } elseif ($to === Payment::STATUS_FAILED) {
                $this->notifyCustomer($payment, 'Payment receipt declined', 'Your transfer receipt could not be verified',
                    'We could not verify the receipt for payment ' . $payment->reference . '. Please contact support or start a new payment attempt.');
            } elseif (in_array($to, [Payment::STATUS_REFUNDED, Payment::STATUS_PARTIALLY_REFUNDED], true)) {
                $this->notifyCustomer($payment, 'Payment refunded', 'A refund has been issued',
                    'Payment ' . $payment->reference . ' for order ' . $orderRef . ' — refunded ' . $payment->currency . ' ' . number_format((float) $payment->amount_refunded, 2) . ' of ' . $payment->currency . ' ' . number_format((float) $payment->amount, 2) . '.');
            }
        } catch (Throwable $e) {
            $this->logTech('error', 'payment notification failed: ' . $e->getMessage(), ['payment' => $payment->reference]);
        }
    }

    protected function notifyCustomer(Payment $payment, string $title, string $greeting, string $description): void
    {
        $user = User::find($payment->user_id);
        if (!$user) {
            return;
        }

        $user->notify(new TaskNotification([
            'title'       => $title,
            'greeting'    => $greeting,
            'description' => $description,
            'order_id'    => $payment->order_id,
            'reference'   => $payment->reference,
        ]));
    }

    protected function notifyAdmins(string $title, string $greeting, string $description): void
    {
        foreach (User::where('type', 'admin')->limit(5)->get() as $admin) {
            $admin->notify(new TaskNotification([
                'title'       => $title,
                'greeting'    => $greeting,
                'description' => $description,
            ]));
        }
    }
}
