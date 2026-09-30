<?php

return [
    'order_statuses' => [
        'pending'   => ['label' => 'Pending',   'color' => 'bg-secondary'],
        'paid'      => ['label' => 'Paid',      'color' => 'bg-info'],
        'fulfilled' => ['label' => 'Fulfilled', 'color' => 'bg-primary'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'bg-danger'],
        'refunded'  => ['label' => 'Refunded',  'color' => 'bg-warning'],
    ],
    'per_page' => 15,
];
