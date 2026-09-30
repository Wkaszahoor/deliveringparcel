<?php

/**
 * SEO Shield — analyzer defaults + internal linking topic map.
 * Consumed by App\Services\Seo\SeoAnalyzerService and the admin tool.
 */
return [
    'brand' => 'DeliveringParcel',
    'domain' => 'deliveringparcel.com',
    'analyzer_version' => '2.0.0',
    'cache_minutes' => 360,
    'fetch' => ['timeout' => 8, 'max_redirects' => 5, 'max_bytes' => 5242880],
    'brand_suffix' => 'DeliveringParcel',
    'filler_phrases' => [
        "in today's digital world", 'in the fast-paced world', 'in the modern world',
        'look no further', 'unlock the', 'in this article, we will', 'it is important to note',
        'when it comes to', 'at the end of the day', 'in conclusion,',
        'plays a vital role', 'plays a crucial role', 'ever-evolving world',
    ],
    'internal_topic_map' => [
        '/shipper-program' => ['shipper', 'ship for me', 'become a shipper', 'earn'],
        '/services' => ['service', 'shipping assistance', 'assisted shopping'],
        '/track-order' => ['track', 'tracking', 'where is my parcel'],
        '/freequote' => ['quote', 'cost', 'price', 'how much'],
        '/contact-details' => ['contact', 'support', 'help'],
        '/testimonials' => ['review', 'testimonial', 'trust'],
        '/blog' => ['guide', 'blog', 'read more'],
    ],
];
