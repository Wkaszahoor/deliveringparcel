<?php

/*
|--------------------------------------------------------------------------
| Admin Reviews Module — config/admin_reviews.php  (RV-001..RV-008)
|--------------------------------------------------------------------------
|
| Central configuration for the customer review system:
|   - statuses        moderation state machine (label/color/next)  [RV-003]
|   - types           review_type taxonomy                          [RV-002]
|   - eligible_order_statuses
|                     orders with one of these `orders.order_status`
|                     values may be reviewed. Keys are the EXACT
|                     strings from `SELECT DISTINCT order_status FROM orders`
|                     (see also config/admin_orders.php).            [RV-001]
|   - rating_min/max  allowed rating range (tinyint)                [RV-002]
|   - date_presets    short-hand ranges for the admin filters       [RV-005]
|   - sort_options    ordering modes for admin data + widget        [RV-005/006]
|   - presets         default saved filter presets (seeded into
|                     review_filter_presets)                        [RV-005]
|   - widget          defaults for the review-widget component      [RV-006]
|   - validation      title/body length limits                      [RV-007]
*/

return [

    /*
    | Moderation lifecycle: pending → approved/rejected (+hidden/spam).
    | `next` lists the statuses an admin may move a review INTO from here.
    */
    'statuses' => [
        'pending' => [
            'label' => 'Pending',
            'color' => 'bg-warning',
            'next'  => ['approved', 'rejected', 'spam'],
        ],
        'approved' => [
            'label' => 'Approved',
            'color' => 'bg-success',
            'next'  => ['hidden'],
        ],
        'rejected' => [
            'label' => 'Rejected',
            'color' => 'bg-danger',
            'next'  => ['pending', 'approved'],
        ],
        'hidden' => [
            'label' => 'Hidden',
            'color' => 'bg-secondary',
            'next'  => ['approved', 'pending'],
        ],
        'spam' => [
            'label' => 'Spam',
            'color' => 'bg-dark',
            'next'  => ['rejected', 'pending'],
        ],
    ],

    /*
    | Review taxonomy. Keys are stored in reviews.review_type.
    */
    'types' => [
        'order'      => 'Order',
        'product'    => 'Product',
        'service'    => 'Service',
        'delivery'   => 'Delivery',
        'experience' => 'Overall experience',
    ],

    /*
    | RV-001: only orders whose `order_status` is listed here can be reviewed.
    | 'completed' is the terminal delivery state in config/admin_orders.php
    | (status machine: Order processing → completed).
    */
    'eligible_order_statuses' => ['completed'],

    /*
    | RV-002: rating bounds (stored as tinyint, app- AND db-constrained).
    */
    'rating_min' => 1,
    'rating_max' => 5,

    /*
    | RV-005: named date ranges for the admin filter toolbar (AND-combinable
    | with every other filter). `custom` uses date_from/date_to inputs.
    */
    'date_presets' => ['today', '7d', '30d', '90d', 'ytd', 'custom'],

    /*
    | Sort modes shared by the admin table and the public widget.
    */
    'sort_options' => [
        'newest'      => 'Newest first',
        'oldest'      => 'Oldest first',
        'rating_high' => 'Highest rating',
        'rating_low'  => 'Lowest rating',
        'random'      => 'Random',
        'featured'    => 'Featured first',
    ],

    /*
    | RV-005: default saved presets seeded into review_filter_presets.
    | `filters` is the exact combined-filter payload accepted by
    | ReviewController::data() / ReviewFilters::apply().
    */
    'presets' => [
        [
            'name'    => 'homepage_reviews',
            'filters' => [
                'status'     => 'approved',
                'rating_min' => 4,
                'verified'   => 1,
                'limit'      => 6,
                'sort'       => 'featured',
                'layout'     => 'cards',
            ],
        ],
        [
            'name'    => 'product_page',
            'filters' => [
                'status' => 'approved',
                'type'   => 'product',
                'sort'   => 'newest',
                'limit'  => 10,
                'layout' => 'list',
            ],
        ],
        [
            'name'    => 'testimonials',
            'filters' => [
                'status'   => 'approved',
                'featured' => 1,
                'rating_min' => 5,
                'limit'    => 3,
                'sort'     => 'random',
                'layout'   => 'testimonial',
            ],
        ],
    ],

    /*
    | RV-006: review-widget component defaults (per-instance overridable
    | through the $widget config array / review_filter_presets row).
    | show_* toggles control which fields render.
    */
    'widget' => [
        'limit'       => 6,
        'sort'        => 'newest',
        'layout'      => 'cards',
        'show_summary'   => true,
        'show_rating'    => true,
        'show_title'     => true,
        'show_body'      => true,
        'show_date'      => true,
        'show_user'      => true,
        'show_verified'  => true,
        'show_type'      => false,
    ],

    /*
    | RV-007: customer submission validation limits.
    */
    'validation' => [
        'title_max' => 120,
        'body_max'  => 2000,
        'body_min'  => 10,
    ],

    /*
    | RV-008: how many months the admin stats trend chart covers.
    */
    'stats_months' => 6,
];
