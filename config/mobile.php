<?php

return [
    'site_root'   => env('APP_URL', 'https://deliveringparcel.com'),
    'image_path'  => 'uploads/productsimages',
    'api_version' => 'v1',

    'cache_ttl' => [
        'labels'   => 600,
        'settings' => 300,
        'features' => 600,
    ],

    'order_statuses' => [
        ['key' => 'pending',    'label' => 'Pending',    'color' => '#FFA726'],
        ['key' => 'processing', 'label' => 'Processing', 'color' => '#42A5F5'],
        ['key' => 'shipped',    'label' => 'Shipped',    'color' => '#26C6DA'],
        ['key' => 'completed',  'label' => 'Completed',  'color' => '#66BB6A'],
        ['key' => 'received',   'label' => 'Received',   'color' => '#26A69A'],
        ['key' => 'cancelled',  'label' => 'Cancelled',  'color' => '#EF5350'],
        ['key' => 'rejected',   'label' => 'Rejected',   'color' => '#EC407A'],
    ],

    /* Raw legacy orders.order_status values → display labels (theme parity). */
    'order_status_labels' => [
        ''                 => 'Request Placed',
        'Offer Placed'     => 'Offer Placed',
        'In process'       => 'In Process',
        'Offer Updated'    => 'Offer Updated',
        'Offer Accepted'   => 'Offer Accepted',
        'Offer Rejected'   => 'Offer Rejected',
        'Order placed'     => 'Order Placed',
        'Confirm Shipment' => 'Confirm Shipment',
        'Ready To Ship'    => 'Ready To Ship',
        'Order processing' => 'Processing',
        'Shipped'          => 'Shipped',
        'completed'        => 'Completed',
        'received'         => 'Parcel Received',
    ],

    'service_fields' => [
        'product_customs'       => 'Custom Declaration',
        'product_check'         => 'Content Check',
        'product_prohibited'    => 'Prohibited Items Removal',
        'product_disinfection'  => 'Product Disinfection',
        'product_consolidation' => 'Package Consolidation',
        'product_purchase'      => 'Purchase Assistance',
    ],
];
