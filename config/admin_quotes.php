<?php

return [
    /*
    | Returns & claims status maps with labels and badge colors.
    */
    'return_statuses' => [
        'requested' => ['label' => 'Requested',  'color' => 'bg-secondary', 'next' => ['approved', 'rejected']],
        'approved'  => ['label' => 'Approved',   'color' => 'bg-info',      'next' => ['received']],
        'received'  => ['label' => 'Received',   'color' => 'bg-primary',   'next' => ['refunded']],
        'refunded'  => ['label' => 'Refunded',   'color' => 'bg-success',   'next' => []],
        'rejected'  => ['label' => 'Rejected',   'color' => 'bg-danger',    'next' => []],
    ],

    'claim_statuses' => [
        'open'          => ['label' => 'Open',          'color' => 'bg-warning',    'next' => ['investigation', 'approved', 'denied']],
        'investigation' => ['label' => 'Investigation', 'color' => 'bg-info',      'next' => ['approved', 'denied']],
        'approved'      => ['label' => 'Approved',      'color' => 'bg-primary',   'next' => ['settled']],
        'denied'        => ['label' => 'Denied',        'color' => 'bg-danger',    'next' => []],
        'settled'       => ['label' => 'Settled',       'color' => 'bg-success',   'next' => []],
    ],

    'claim_types' => [
        'damage' => 'Damage',
        'loss'   => 'Loss',
        'late'   => 'Late delivery',
    ],
];
