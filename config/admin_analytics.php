<?php

/*
 * Admin Dashboard + Analytics defaults (Agent A module).
 *
 * Revenue definition used across this module:
 *   `offerorders.total` of offers ACCEPTED by the client (offer_status = 1),
 *   deduplicated to one accepted offer per order (MAX per order).
 */
return [

    /* Cache lifetime for expensive aggregates (minutes). */
    'cache_minutes' => 10,

    /* offerorders.offer_status value meaning "accepted by client". */
    'accepted_offer_status' => 1,

    /*
     * Order statuses considered "won" (client accepted an offer /
     * order moving through fulfilment). Used for dashboard widgets.
     */
    'won_order_statuses' => [
        'Offer Accepted',
        'Order placed',
        'Order processing',
        'Ready To Ship',
        'Confirm Shipment',
        'completed',
    ],

    /*
     * Order statuses that still need someone's action (admin to place an
     * offer, client to respond, or order being processed).
     * NULL order_status (brand new order, no offer yet) is always included.
     */
    'awaiting_action_statuses' => [
        'Offer Placed',
        'Offer Updated',
        'In process',
    ],

    /* Chart palette (hex) cycled for doughnut / bar datasets. */
    'chart_colors' => [
        '#0d6efd', '#20c997', '#fd7e14', '#6f42c1',
        '#dc3545', '#198754', '#0dcaf0', '#d63384',
        '#ffc107', '#6c757d',
    ],

    /* Rows per page for server-side paginated JSON lists. */
    'per_page' => 10,
    'products_per_page' => 15,
    'rfm_per_page' => 15,

    /* Revenue analytics page: default range (months back from today). */
    'revenue_default_months' => 12,

    /* Demand analytics: max product-name length used for grouping. */
    'demand_name_length' => 80,

    /*
     * RFM segmentation defaults. All of them can be overridden per request
     * via query params: champion_r, champion_f, loyal_f, recent_r,
     * repeat_f, risk_f, attention_r (1..5).
     *
     * Scores are quintile based (1..5): R = 5 most recent, F = 5 most
     * frequent, M = 5 highest monetary. Segments (first match wins):
     *   Champions          R >= champion_r && F >= champion_f
     *   Loyal              R >= recent_r     && F >= loyal_f
     *   Potential Loyalist R >= recent_r     && F >= repeat_f
     *   New                R >= champion_r   && F <  repeat_f
     *   Promising          R >= recent_r     && F <  repeat_f
     *   Need Attention     R >= attention_r  && F >= repeat_f
     *   At Risk            F >= risk_f
     *   Hibernating        everything else
     */
    'rfm' => [
        'champion_r' => 4,
        'champion_f' => 4,
        'loyal_f'    => 4,
        'recent_r'   => 3,
        'repeat_f'   => 2,
        'risk_f'     => 3,
        'attention_r' => 2,
    ],
];
