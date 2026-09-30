<?php

return [
    'package_statuses' => [
        'pending'  => ['label' => 'Pending receipt', 'color' => 'bg-warning'],
        'received' => ['label' => 'Received',        'color' => 'bg-success'],
        'damaged'  => ['label' => 'Damaged',         'color' => 'bg-danger'],
        'returned' => ['label' => 'Returned to sender', 'color' => 'bg-secondary'],
    ],
    'shipment_statuses' => [
        'preparing'  => ['label' => 'Preparing',   'color' => 'bg-secondary', 'next' => ['dispatched']],
        'dispatched' => ['label' => 'Dispatched',  'color' => 'bg-info',      'next' => ['in_transit', 'delivered', 'exception']],
        'in_transit' => ['label' => 'In transit',  'color' => 'bg-primary',   'next' => ['delivered', 'exception']],
        'delivered'  => ['label' => 'Delivered',   'color' => 'bg-success',   'next' => []],
        'exception'  => ['label' => 'Exception',   'color' => 'bg-danger',    'next' => ['in_transit', 'delivered']],
    ],
    'per_page' => 15,
];
