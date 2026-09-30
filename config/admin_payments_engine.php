<?php

/**
 * Agent PM — dynamic payment engine configuration (PM-001/PM-005/PM-004).
 *
 * Single authoritative payment state machine + routing/tuning knobs.
 * Method CATALOGUE and per-service rules live in the DB
 * (payment_methods / payment_method_rules) — this file only holds the
 * machine definition and engine behaviour.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Payment states (single authoritative machine — PM-005)
    |--------------------------------------------------------------------------
    | key          => internal status (payments.status)
    | label/color  => admin display
    | terminal     => no outbound transitions allowed
    | admin_only   => reachable exclusively through admin actions
    |
    | Happy path:   pending → method_selected → awaiting_payment
    |                        → (bank) awaiting_verification → paid
    |                        → (card) processing → paid|failed
    | Any pre-processing state → failed|cancelled.
    | refunded / partially_refunded only ever from paid (admin — PM-004).
    */
    'states' => [

        'pending' => [
            'label' => 'Pending', 'color' => 'secondary', 'terminal' => false, 'admin_only' => false,
        ],

        'method_selected' => [
            'label' => 'Method selected', 'color' => 'info', 'terminal' => false, 'admin_only' => false,
        ],

        'awaiting_payment' => [
            'label' => 'Awaiting payment', 'color' => 'warning', 'terminal' => false, 'admin_only' => false,
        ],

        // Bank transfer with proof uploaded — waits for ADMIN verification (PM-008).
        'awaiting_verification' => [
            'label' => 'Awaiting verification', 'color' => 'warning', 'terminal' => false, 'admin_only' => false,
        ],

        'processing' => [
            'label' => 'Processing', 'color' => 'primary', 'terminal' => false, 'admin_only' => false,
        ],

        'paid' => [
            'label' => 'Paid', 'color' => 'success', 'terminal' => false, 'admin_only' => false,
        ],

        'failed' => [
            'label' => 'Failed', 'color' => 'danger', 'terminal' => true, 'admin_only' => false,
        ],

        'cancelled' => [
            'label' => 'Cancelled', 'color' => 'dark', 'terminal' => true, 'admin_only' => false,
        ],

        'refunded' => [
            'label' => 'Refunded', 'color' => 'info', 'terminal' => true, 'admin_only' => true,
        ],

        'partially_refunded' => [
            'label' => 'Partially refunded', 'color' => 'info', 'terminal' => false, 'admin_only' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed transitions (from => [to, to, ...])
    |--------------------------------------------------------------------------
    | Anything not listed throws — illegal transitions are impossible.
    */
    'transitions' => [
        'pending'               => ['method_selected', 'failed', 'cancelled'],
        'method_selected'       => ['awaiting_payment', 'processing', 'failed', 'cancelled'],
        'awaiting_payment'      => ['awaiting_verification', 'processing', 'paid', 'failed', 'cancelled'],
        'awaiting_verification' => ['paid', 'failed', 'cancelled'],
        'processing'            => ['paid', 'failed', 'cancelled'],
        'paid'                  => ['refunded', 'partially_refunded'],
        'partially_refunded'    => ['refunded', 'partially_refunded'],
        'failed'                => [],
        'cancelled'             => [],
        'refunded'              => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Method change locking (PM-004)
    |--------------------------------------------------------------------------
    | Customer may switch method while the payment is in these states.
    | After that (processing+) and forever after paid, the method is immutable.
    */
    'method_change_allowed_states' => [
        'pending', 'method_selected', 'awaiting_payment',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reference / idempotency (PM-012)
    |--------------------------------------------------------------------------
    */
    'reference_prefix_setting' => 'bank_reference_prefix', // Setting key
    'reference_fallback_prefix' => 'DP',
    'reference_rand_length' => 6,

    /*
    |--------------------------------------------------------------------------
    | Bank transfer instructions (PM-008) — Setting store keys
    |--------------------------------------------------------------------------
    */
    'bank_settings' => [
        'bank_name'           => ['label' => 'Bank name', 'default' => ''],
        'bank_account_title'  => ['label' => 'Account title', 'default' => ''],
        'bank_iban'           => ['label' => 'IBAN', 'default' => ''],
        'bank_account_number' => ['label' => 'Account number', 'default' => ''],
        'bank_reference_prefix' => ['label' => 'Transfer reference prefix', 'default' => 'DP'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proof upload validation (PM-008)
    |--------------------------------------------------------------------------
    */
    'proof' => [
        'mimes'  => 'jpg,jpeg,png,pdf',
        'max_kb' => 5120,
        'disk_path' => 'uploads/payments/proofs', // under public/
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateway bindings — adapter key => contract implementation
    |--------------------------------------------------------------------------
    | New gateway = new adapter class + one line here.
    */
    'gateways' => [
        'stripe'       => \App\Services\Payments\StripeGateway::class,
        'bank_transfer'=> \App\Services\Payments\BankTransferGateway::class,
        'wallet'       => \App\Services\Payments\WalletGateway::class,
        'cod'          => \App\Services\Payments\CodGateway::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Service-context detection (PM-001 routing resolver input)
    |--------------------------------------------------------------------------
    | order columns inspected, in order, to classify an order.
    */
    'service_detection' => [
        'custom_marker_column'  => 'custom_category',
        'purchase_marker_column'=> 'product_purchase',
    ],

    /*
    |--------------------------------------------------------------------------
    | Friendly error copy (PM-011) — gateway internals NEVER reach customers
    |--------------------------------------------------------------------------
    */
    'friendly_errors' => [
        'no_accepted_offer' => 'We could not find an accepted offer for this order yet. Please accept the latest offer before paying.',
        'no_methods'        => 'No payment method is currently available for this order. Please contact support.',
        'method_not_allowed'=> 'That payment method is not available for this order.',
        'not_found'         => 'Payment not found.',
        'generic'           => 'Something went wrong while processing your payment. Please try again in a moment. Our team has been notified.',
        'wallet_disabled'   => 'The wallet is currently unavailable. Please choose another payment method.',
    ],
];
