<?php

/*
 * TW-001..TW-005 — Theme/Widget manager registry.
 * Types define WHAT can be placed; widget_instances define WHERE/WHEN/HOW.
 */
return [

    'areas' => [
        'home_top'    => 'Home — above content',
        'home_bottom' => 'Home — below content',
        'sidebar'     => 'Sidebar (pages with sidebar)',
        'footer'      => 'Footer promo strip',
    ],

    /* scope precedence for resolution (TW-004): higher wins exclusively. */
    'precedence' => ['page' => 4, 'template' => 3, 'theme' => 2, 'global' => 1],

    'scopes' => [
        'page'     => 'Specific page (exact URL path, e.g. home2/services — highest priority)',
        'template' => 'Template context (matches widget context name)',
        'theme'    => 'Active theme (matches theme name from Setting active_theme)',
        'global'   => 'All pages (fallback)',
    ],

    'types' => [

        'review_widget' => [
            'label'       => 'Reviews widget',
            'description' => 'Customer reviews list driven by filters (status is always forced to approved).',
            'view'        => 'widgets.render.review-widget',
            'fields'      => [
                'limit' => 'int (1-24)', 'sort' => 'newest|oldest|rating_high|rating_low|random|featured',
                'rating_min' => 'int 1-5 (optional)', 'verified' => 'bool (only verified purchases)',
                'layout' => 'grid|list|cards', 'title' => 'string',
            ],
        ],

        'blade' => [
            'label'       => 'Pre-built block',
            'description' => 'One of the whitelisted widget views in resources/views/widgets/render/.',
            'view'        => null, // resolved from config.view, validated against whitelist
            'fields'      => ['view' => 'key from admin_widgets.blocks', 'title' => 'string'],
        ],

        'html' => [
            'label'       => 'Custom HTML (sanitized)',
            'description' => 'Free HTML block — sanitized through HtmlSanitizer on save (scripts/iframes stripped).',
            'view'        => 'widgets.render.html',
            'fields'      => ['html' => 'sanitized html', 'title' => 'string (optional heading)'],
        ],
    ],

    /* Whitelisted pre-built blocks for the "blade" type (TW-002). */
    'blocks' => [
        'cta-strip'   => ['label' => 'CTA strip',    'view' => 'widgets.render.cta-strip'],
        'stats-row'   => ['label' => 'Stats row',    'view' => 'widgets.render.stats-row'],
        'banner'      => ['label' => 'Simple banner','view' => 'widgets.render.banner'],
    ],

    'default_cache_minutes' => 10,
];
