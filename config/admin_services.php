<?php

/*
|--------------------------------------------------------------------------
| Admin Services module configuration
|--------------------------------------------------------------------------
|
| TYPE options map for the `services.type` column plus availability
| labels/colors used by lists and badges.
|
*/

return [

    /*
     | Shipping service types available in the admin.
     | key => human label (also used for the badge color hint below).
     */
    'types' => [
        'standard'      => 'Standard',
        'express'       => 'Express',
        'freight'       => 'Freight',
        'international' => 'International',
        'warehousing'   => 'Warehousing',
        'packing'       => 'Packing',
    ],

    /*
     | Badge color per type (Bootstrap 4 contextual names) for lists.
     */
    'type_colors' => [
        'standard'      => 'secondary',
        'express'       => 'warning',
        'freight'       => 'info',
        'international' => 'primary',
        'warehousing'   => 'success',
        'packing'       => 'dark',
    ],

    /*
     | Availability labels + badge colors (value => [label, color]).
     */
    'availability' => [
        1 => ['label' => 'Available',   'color' => 'success'],
        0 => ['label' => 'Unavailable', 'color' => 'secondary'],
    ],

    /*
     | Records per page on the JSON data endpoint.
     */
    'per_page' => 10,
];
