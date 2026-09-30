<?php

/*
|--------------------------------------------------------------------------
| Shipping Rate Engine configuration (Agent H - rates + geo module)
|--------------------------------------------------------------------------
| Service types offered by the rate matrix, default calculation values and
| CSV import/export settings. Kept in config so admins can change service
| catalogue centrally (config:clear needed after edits).
*/

return [

    /*
    | Service types available on rate_rules.service_type + calculator UI.
    | key => label. Keys are stored in DB (keep them stable / lowercase).
    */
    'service_types' => [
        'standard' => 'Standard',
        'express'  => 'Express',
        'economy'  => 'Economy',
        'priority' => 'Priority',
    ],

    /*
    | Surcharge "applies_to" options (matches rate_surcharges.applies_to).
    */
    'surcharge_applies' => [
        'all'       => 'All shipments',
        'fuel'      => 'Fuel (opt-in per calculation)',
        'insurance' => 'Insurance (opt-in per calculation)',
        'remote'    => 'Remote areas (opt-in per calculation)',
    ],

    'defaults' => [
        'currency'            => 'USD',
        'volumetric_divisor'  => 5000,   // l x w x h (cm) / divisor = volumetric kg
        'max_decimals'        => 2,
        'per_page'            => 15,     // server-side pagination for lists
        'apply_fuel'          => true,   // calculator default checkbox
        'apply_insurance'     => false,  // calculator default checkbox
        'remote'              => false,
    ],

    /*
    | CSV import / export of rate_rules.
    */
    'csv' => [
        'delimiter'   => ',',
        'enclosure'   => '"',
        'max_upload_kb' => 5120,
        // Header row expected by importer / produced by exporter + sample file.
        'columns' => [
            'origin_zone_code',
            'destination_zone_code',
            'service_type',
            'weight_min',
            'weight_max',
            'base_price',
            'per_kg_price',
            'transit_days_min',
            'transit_days_max',
            'priority',
            'is_active',
            'valid_from',
            'valid_to',
        ],
    ],
];
