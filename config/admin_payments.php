<?php

/**
 * Agent D — Payments module configuration.
 *
 * READ-ONLY analysis over existing `orders` (+ `offerorders`) payment-ish
 * columns. No new payments table. The map below derives a normalized
 * payment status from the values actually present in the
 * `orders.order_status` column (varchar/text statuses).
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Normalized payment statuses
    |--------------------------------------------------------------------------
    | key    => internal status
    | label  => display label
    | color  => AdminLTE/Bootstrap badge color
    | order_statuses => raw orders.order_status values mapped to this status
    | offer_statuses => raw offerorders.offer_status values mapped (context only)
    */
    'statuses' => [

        'paid' => [
            'label' => 'Paid',
            'color' => 'success',
            'kpi_label' => 'Collected this month',
            'order_statuses' => ['completed'],
            'offer_statuses' => [1],
        ],

        'pending' => [
            'label' => 'Pending',
            'color' => 'warning',
            'kpi_label' => 'Pending payments',
            'order_statuses' => [
                null, 'offer placed', 'order placed', 'offer updated', 'offer accepted',
                'in process', 'order processing', 'ready to ship', 'confirm shipment',
            ],
            'offer_statuses' => [0],
        ],

        'refunded' => [
            'label' => 'Refunded',
            'color' => 'info',
            'kpi_label' => 'Refunded',
            // no refund column exists on orders — stays empty until schema evolves
            'order_statuses' => ['refunded'],
            'offer_statuses' => [],
        ],

        'failed' => [
            'label' => 'Failed / Rejected',
            'color' => 'danger',
            'kpi_label' => 'Failed / Rejected',
            'order_statuses' => ['offer rejected'],
            'offer_statuses' => [2],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Amount extraction
    |--------------------------------------------------------------------------
    | orders.total is a VARCHAR containing numeric values (sometimes empty /
    | non-numeric). The query sanitizes it with REGEXP_REPLACE + CAST.
    | product_totalprice (int) is used as a fallback for display.
    */
    'amount_column' => 'total',
    'amount_fallback_column' => 'product_totalprice',
    'currency_setting' => 'business_currency',

    /*
    |--------------------------------------------------------------------------
    | Method labels (no dedicated method column exists — derived)
    |--------------------------------------------------------------------------
    */
    'methods' => [
        'offer' => 'Offer / Invoice',
        'direct' => 'Direct order',
    ],

    /*
    |--------------------------------------------------------------------------
    | Refund capability (feature-detected)
    |--------------------------------------------------------------------------
    | A refund is only offered when ALL of the following hold:
    |  - a refund-ish status column exists on `orders` (candidates checked
    |    dynamically with Schema::hasColumn)
    |  - a Stripe charge reference column exists (candidates below)
    |  - services.stripe.secret is configured via .env (no hardcoded keys)
    |  - the api_stripe_enabled setting toggle is ON
    | Otherwise the UI shows a feature-disabled chip instead of the action.
    */
    'refund' => [
        'status_column_candidates' => ['refund_status', 'refunded_at', 'payment_refunded'],
        'charge_column_candidates' => ['stripe_charge', 'charge_id', 'stripe_ref', 'payment_intent'],
        'stripe_secret_config' => 'services.stripe.secret',
    ],
];
