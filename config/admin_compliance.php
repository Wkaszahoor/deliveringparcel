<?php

return [
    'screening_threshold' => 70, // metaphone+levenshtein similarity %

    'kyc_statuses' => [
        'pending'  => ['label' => 'Pending',  'color' => 'bg-warning'],
        'approved' => ['label' => 'Approved', 'color' => 'bg-success'],
        'rejected' => ['label' => 'Rejected', 'color' => 'bg-danger'],
        'expired'  => ['label' => 'Expired',  'color' => 'bg-secondary'],
    ],

    'doc_types' => [
        'passport'       => 'Passport',
        'national_id'    => 'National ID',
        'driver_license' => 'Driver license',
    ],

    'vat_schemes' => [
        'eu_ioss'  => 'EU IOSS',
        'uk_vat'   => 'UK VAT',
        'eu_b2b'   => 'Intra-EU B2B',
        'standard' => 'Standard',
    ],

    'consent_triggers' => [
        'always'     => 'Always required',
        'country'    => 'Specific country',
        'route'      => 'Specific route',
        'cargo_type' => 'Specific cargo type',
    ],
];
