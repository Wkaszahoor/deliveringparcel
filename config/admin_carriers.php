<?php

return [
    /*
    | Carrier registry. env_key names document WHERE credentials live —
    | secret VALUES are never stored in DB or shown in UI (status chips only).
    */
    'carriers' => [
        'dhl'       => ['name' => 'DHL',        'icon' => 'fa-truck',   'color' => 'warning',  'env_keys' => ['CARRIER_DHL_KEY', 'CARRIER_DHL_SECRET']],
        'fedex'     => ['name' => 'FedEx',      'icon' => 'fa-shipping-fast', 'color' => 'info', 'env_keys' => ['CARRIER_FEDEX_KEY', 'CARRIER_FEDEX_SECRET']],
        'ups'       => ['name' => 'UPS',        'icon' => 'fa-box',     'color' => 'primary',  'env_keys' => ['CARRIER_UPS_KEY', 'CARRIER_UPS_SECRET']],
        'correos'   => ['name' => 'Correos',    'icon' => 'fa-envelope-open-text', 'color' => 'secondary', 'env_keys' => ['CARRIER_CORREOS_KEY']],
        'royalmail' => ['name' => 'Royal Mail', 'icon' => 'fa-crown',   'color' => 'danger',   'env_keys' => ['CARRIER_ROYALMAIL_KEY']],
        'aftership' => ['name' => 'Aftership',  'icon' => 'fa-globe',   'color' => 'success',  'env_keys' => ['CARRIER_AFTERSHIP_KEY']],
        '17track'   => ['name' => '17TRACK',    'icon' => 'fa-search-location', 'color' => 'dark', 'env_keys' => ['CARRIER_17TRACK_KEY']],
    ],

    /* Global mock switch: when true (or keys missing) adapters return deterministic fake events. */
    'mock' => env('CARRIER_MOCK', true),

    'cache_minutes' => 10,
];
