<?php

return [
    /*
    | Email event types for per-user toggles (in_app + email channels).
    */
    'types' => [
        'order_placed'      => 'Order placed',
        'offer_received'    => 'Offer received',
        'offer_accepted'    => 'Offer accepted',
        'order_shipped'     => 'Order shipped',
        'order_delivered'   => 'Order delivered',
        'payment_received'  => 'Payment received',
        'contact_reply'     => 'Contact reply sent',
        'account_security'  => 'Account security',
    ],

    'per_page' => 15,
];
