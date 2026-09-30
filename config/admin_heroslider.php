<?php

/*
|--------------------------------------------------------------------------
| Admin Hero Slider module configuration
|--------------------------------------------------------------------------
|
| The `hero_slides.options` JSON column holds per-slide UI settings.
| Every key below declares: label, group (Layout / Buttons / Colors /
| Advanced), input type (text|number|range|select|toggle|color),
| options (for select), min/max/step (number/range) and default.
|
*/

return [

    'per_page' => 10,

    /*
     | Per-slide option fields. Rendered grouped by `group` in the form.
     */
    'option_fields' => [

        /* ---------------- Layout ---------------- */
        'overlay_opacity' => [
            'label' => 'Overlay opacity', 'group' => 'Layout',
            'type' => 'range', 'min' => 0, 'max' => 0.9, 'step' => 0.05,
            'default' => 0.35, 'hint' => 'Dark overlay drawn over the slide image (0 - 0.9).',
        ],
        'text_align' => [
            'label' => 'Text align', 'group' => 'Layout',
            'type' => 'select', 'options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'],
            'default' => 'left',
        ],
        'theme' => [
            'label' => 'Text theme', 'group' => 'Layout',
            'type' => 'select', 'options' => ['light' => 'Light (dark text)', 'dark' => 'Dark (white text)'],
            'default' => 'dark',
        ],
        'min_height' => [
            'label' => 'Min height', 'group' => 'Layout',
            'type' => 'text', 'default' => '480px', 'hint' => 'CSS length, e.g. 480px, 60vh.',
        ],
        'title_size' => [
            'label' => 'Title size', 'group' => 'Layout',
            'type' => 'text', 'default' => '3.2rem', 'hint' => 'CSS font-size for the title.',
        ],
        'subtitle_size' => [
            'label' => 'Subtitle size', 'group' => 'Layout',
            'type' => 'text', 'default' => '1.25rem',
        ],
        'mobile_title_size' => [
            'label' => 'Mobile title size', 'group' => 'Layout',
            'type' => 'text', 'default' => '2rem',
        ],
        'show_on_mobile' => [
            'label' => 'Show on mobile', 'group' => 'Layout',
            'type' => 'toggle', 'default' => true,
        ],
        'bg_position' => [
            'label' => 'Background position', 'group' => 'Layout',
            'type' => 'select',
            'options' => [
                'center' => 'Center', 'top' => 'Top', 'bottom' => 'Bottom',
                'left' => 'Left', 'right' => 'Right', 'top center' => 'Top center',
            ],
            'default' => 'center',
        ],

        /* ---------------- Buttons ---------------- */
        'btn2_text' => [
            'label' => 'Second button text', 'group' => 'Buttons',
            'type' => 'text', 'default' => '', 'hint' => 'Leave empty to hide the second button.',
        ],
        'btn2_link' => [
            'label' => 'Second button link', 'group' => 'Buttons',
            'type' => 'text', 'default' => '',
        ],
        'btn2_style' => [
            'label' => 'Second button style', 'group' => 'Buttons',
            'type' => 'select',
            'options' => [
                'outline-light' => 'Outline light', 'outline-dark' => 'Outline dark',
                'primary' => 'Solid primary', 'secondary' => 'Solid secondary',
            ],
            'default' => 'outline-light',
        ],
        'animation' => [
            'label' => 'Content animation', 'group' => 'Buttons',
            'type' => 'select',
            'options' => ['fade' => 'Fade', 'slide' => 'Slide up', 'zoom' => 'Zoom', 'none' => 'None'],
            'default' => 'fade',
        ],
        'animation_delay' => [
            'label' => 'Animation delay (ms)', 'group' => 'Buttons',
            'type' => 'number', 'min' => 0, 'max' => 3000, 'step' => 50, 'default' => 0,
        ],
        'ken_burns' => [
            'label' => 'Ken Burns effect', 'group' => 'Buttons',
            'type' => 'toggle', 'default' => false, 'hint' => 'Slow background zoom on the slide image.',
        ],

        /* ---------------- Colors ---------------- */
        'badge_text' => [
            'label' => 'Badge text', 'group' => 'Colors',
            'type' => 'text', 'default' => '', 'hint' => 'Small label above the title. Leave empty to hide.',
        ],
        'badge_color' => [
            'label' => 'Badge color', 'group' => 'Colors',
            'type' => 'select',
            'options' => [
                'primary' => 'Primary', 'success' => 'Success', 'danger' => 'Danger',
                'warning' => 'Warning', 'info' => 'Info', 'dark' => 'Dark',
            ],
            'default' => 'primary',
        ],
        'gradient_from' => [
            'label' => 'Gradient from', 'group' => 'Colors',
            'type' => 'color', 'default' => '#000000',
        ],
        'gradient_to' => [
            'label' => 'Gradient to', 'group' => 'Colors',
            'type' => 'color', 'default' => '#000000', 'hint' => 'Gradient overlay is applied left-to-right when the two colors differ.',
        ],

        /* ---------------- Advanced ---------------- */
        'custom_css_class' => [
            'label' => 'Custom CSS class', 'group' => 'Advanced',
            'type' => 'text', 'default' => '', 'hint' => 'Extra classes added to the slide element.',
        ],
    ],
];
