<?php

/*
|--------------------------------------------------------------------------
| Admin Orders Module — Status State Machine (config/admin_orders.php)
|--------------------------------------------------------------------------
|
| Keys are the EXACT `order_status` strings found in the LIVE orders table
| (`SELECT DISTINCT order_status FROM orders`) — 11 statuses including
| NULL (represented here by the empty-string key ''). Do NOT rename keys;
| the module writes these strings back to the database verbatim.
|
| Each status defines:
|   label       — human friendly name shown in the admin UI
|   badge       — AdminLTE 3 / Bootstrap 4 background class for .dp-badge
|   next        — allowed forward transitions (state machine edges)
|   final       — true when no further transitions are allowed
|   archivable  — true when the order may be archived by an operator
|   step        — position on the main progress timeline (-1 = side state)
|   description — short explanation of what this status means
|
| Derived from real transitions in OrdersController / OrderOfferController /
| AdminordersController (offer_accept, offer_reject, order_tracking,
| purchase_image, Order_confirmation, Order_complete, edit_offer...).
*/

return [

    'statuses' => [

        '' => [
            'label'       => 'Request Placed',
            'badge'       => 'bg-secondary',
            'next'        => ['Offer Placed', 'Offer Rejected'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 0,
            'description' => 'Client submitted the order request; admin has not placed an offer yet.',
        ],

        'Offer Placed' => [
            'label'       => 'Offer Placed',
            'badge'       => 'bg-primary',
            'next'        => ['In process', 'Offer Updated', 'Offer Accepted', 'Offer Rejected'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 1,
            'description' => 'Admin placed an offer; waiting for the client to accept or reject.',
        ],

        'In process' => [
            'label'       => 'In process',
            'badge'       => 'bg-info',
            'next'        => ['Offer Updated', 'Offer Placed'],
            'final'       => false,
            'archivable'  => false,
            'step'        => -1,
            'description' => 'Admin is editing the offer (edit_offer flag raised).',
        ],

        'Offer Updated' => [
            'label'       => 'Offer Updated',
            'badge'       => 'bg-teal',
            'next'        => ['In process', 'Offer Accepted', 'Offer Rejected'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 1,
            'description' => 'Admin updated and re-sent the offer; waiting for client response.',
        ],

        'Offer Accepted' => [
            'label'       => 'Offer Accepted',
            'badge'       => 'bg-success',
            'next'        => ['Order placed', 'Ready To Ship', 'In process'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 2,
            'description' => 'Client accepted the offer; waiting for order / tracking details.',
        ],

        'Offer Rejected' => [
            'label'       => 'Offer Rejected',
            'badge'       => 'bg-danger',
            'next'        => ['Offer Placed'],
            'final'       => false,
            'archivable'  => true,
            'step'        => -1,
            'description' => 'Client rejected the offer. Admin may place a new offer or archive.',
        ],

        'Order placed' => [
            'label'       => 'Order Placed',
            'badge'       => 'bg-indigo',
            'next'        => ['Confirm Shipment', 'Ready To Ship'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 3,
            'description' => 'Client placed the order and provided tracking ids; admin to confirm shipment.',
        ],

        'Confirm Shipment' => [
            'label'       => 'Confirm Shipment',
            'badge'       => 'bg-orange',
            'next'        => ['Order processing', 'completed'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 4,
            'description' => 'Admin requested shipment confirmation from the client.',
        ],

        'Ready To Ship' => [
            'label'       => 'Ready To Ship',
            'badge'       => 'bg-purple',
            'next'        => ['Order processing', 'completed'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 4,
            'description' => 'Purchase assistance items received and photographed; ready to ship.',
        ],

        'Order processing' => [
            'label'       => 'Order Processing',
            'badge'       => 'bg-warning',
            'next'        => ['completed'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 5,
            'description' => 'Tracking added and the parcel is shipped / in transit.',
        ],

        'completed' => [
            'label'       => 'Completed',
            'badge'       => 'bg-success',
            'next'        => ['received'],
            'final'       => false,
            'archivable'  => true,
            'step'        => 6,
            'description' => 'Order delivered and closed. Client may still confirm receipt.',
        ],

        'Shipped' => [
            'label'       => 'Shipped',
            'badge'       => 'bg-primary',
            'next'        => ['completed', 'received'],
            'final'       => false,
            'archivable'  => false,
            'step'        => 5,
            'description' => 'Parcel handed to the carrier and on its way (legacy tracking write).',
        ],

        'received' => [
            'label'       => 'Parcel Received',
            'badge'       => 'bg-success',
            'next'        => [],
            'final'       => true,
            'archivable'  => true,
            'step'        => 7,
            'description' => 'Client confirmed the parcel arrived at the warehouse/destination.',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Main progress path rendered on the order detail timeline.
    |----------------------------------------------------------------------
    */
    'timeline' => [
        '',
        'Offer Placed',
        'Offer Accepted',
        'Order placed',
        'Confirm Shipment',
        'Order processing',
        'completed',
    ],

    /*
    |----------------------------------------------------------------------
    | Misc module settings
    |----------------------------------------------------------------------
    */
    'per_page'   => 20,        // rows per page on JSON data endpoints
    'archive_on' => ['completed', 'Offer Rejected'], // statuses where the
                  // archive toggle is offered in the UI (is_archivable=true
                  // statuses above stay the single source of truth).
];
