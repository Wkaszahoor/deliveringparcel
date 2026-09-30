<?php

namespace App\Services;

use App\Models\ThemeSetting;

/**
 * Home2 Content Settings (2026-09-05).
 *
 * Single source of truth for the admin-editable texts shown on the home2
 * design (resources/views/home2/*) — hero copy, section headings, contact
 * info, footer text and social links.
 *
 * STORAGE: the `theme_settings` table via App\Models\ThemeSetting
 * (ThemeSetting::get()/ThemeSetting::put()), stored with group = 'home2'.
 * Keys are flat strings with the `home2_` prefix (underscores, no dots),
 * so they survive HTML form field name mangling unchanged.
 *
 * Usage in views/controllers:
 *   \App\Services\Home2Settings::get('home2_hero_title')   // default fallback
 *   \App\Services\Home2Settings::all()                     // [key => value]
 */
class Home2Settings
{
    /** Storage group used inside theme_settings. */
    public const GROUP = 'home2';

    /**
     * Every editable key with its default value, label and input type.
     * type: text | textarea | url | email | phone (drives admin form + validation).
     */
    public const KEYS = [
        // ---- Hero ----
        'home2_hero_title' => [
            'default' => 'Ship Anything, Anywhere',
            'label'   => 'Hero title',
            'type'    => 'text',
            'group'   => 'hero',
        ],
        'home2_hero_subtitle' => [
            'default' => 'Shop worldwide, ship to your door.',
            'label'   => 'Hero subtitle',
            'type'    => 'textarea',
            'group'   => 'hero',
        ],
        'home2_hero_cta_label' => [
            'default' => 'Get a Quote',
            'label'   => 'Hero CTA label',
            'type'    => 'text',
            'group'   => 'hero',
        ],
        'home2_hero_cta_url' => [
            'default' => '/get-quote',
            'label'   => 'Hero CTA URL',
            'type'    => 'url',
            'group'   => 'hero',
        ],

        // ---- Section headings ----
        'home2_section_services_title' => [
            'default' => 'Our Services',
            'label'   => 'Services section title',
            'type'    => 'text',
            'group'   => 'sections',
        ],
        'home2_section_services_subtitle' => [
            'default' => 'Fast, reliable international shipping.',
            'label'   => 'Services section subtitle',
            'type'    => 'text',
            'group'   => 'sections',
        ],
        'home2_section_blog_title' => [
            'default' => 'From the Blog',
            'label'   => 'Blog section title',
            'type'    => 'text',
            'group'   => 'sections',
        ],

        // ---- Contact ----
        'home2_contact_email' => [
            'default' => 'support@deliveringparcel.com',
            'label'   => 'Contact email',
            'type'    => 'email',
            'group'   => 'contact',
        ],
        'home2_contact_phone' => [
            'default' => '+1 (555) 000-0000',
            'label'   => 'Contact phone',
            'type'    => 'phone',
            'group'   => 'contact',
        ],
        'home2_contact_address' => [
            'default' => '',
            'label'   => 'Contact address',
            'type'    => 'textarea',
            'group'   => 'contact',
        ],

        // ---- Footer ----
        'home2_footer_about' => [
            'default' => 'DeliveringParcel — parcel forwarding and purchase assistance.',
            'label'   => 'Footer about text',
            'type'    => 'textarea',
            'group'   => 'footer',
        ],
        'home2_footer_copyright' => [
            'default' => '© 2018–2026 DeliveringParcel. All rights reserved.',
            'label'   => 'Footer copyright line',
            'type'    => 'text',
            'group'   => 'footer',
        ],

        // ---- Social links ----
        'home2_social_facebook' => [
            'default' => '',
            'label'   => 'Facebook URL',
            'type'    => 'url',
            'group'   => 'social',
        ],
        'home2_social_instagram' => [
            'default' => '',
            'label'   => 'Instagram URL',
            'type'    => 'url',
            'group'   => 'social',
        ],
        'home2_social_twitter' => [
            'default' => '',
            'label'   => 'Twitter / X URL',
            'type'    => 'url',
            'group'   => 'social',
        ],
        'home2_social_linkedin' => [
            'default' => '',
            'label'   => 'LinkedIn URL',
            'type'    => 'url',
            'group'   => 'social',
        ],
    ];

    /** Admin form card groups: group key => [title, icon]. */
    public const GROUPS = [
        'hero'     => ['title' => 'Hero', 'icon' => 'fas fa-image'],
        'sections' => ['title' => 'Section Headings', 'icon' => 'fas fa-heading'],
        'contact'  => ['title' => 'Contact', 'icon' => 'fas fa-address-book'],
        'footer'   => ['title' => 'Footer', 'icon' => 'fas fa-shoe-prints'],
        'social'   => ['title' => 'Social Links', 'icon' => 'fas fa-share-alt'],
    ];

    /**
     * Get one setting with default fallback.
     * Unknown keys fall back to '' (never null) so Blade can echo safely.
     */
    public static function get(string $key): string
    {
        $meta = self::KEYS[$key] ?? null;
        $default = $meta['default'] ?? '';

        // Guard (2026-09-18): public pages call this directly from views — a
        // missing theme_settings table (pre-migration server) must render the
        // default text, never a 500.
        try {
            return (string) ThemeSetting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /** Persist one setting via ThemeSetting's own storage (group 'home2'). */
    public static function set(string $key, string $value): void
    {
        ThemeSetting::put($key, $value, self::GROUP);
    }

    /** All known keys resolved to their current values: [key => string]. */
    public static function all(): array
    {
        $values = [];
        foreach (self::KEYS as $key => $meta) {
            $values[$key] = self::get($key);
        }

        return $values;
    }

    /** Keys belonging to one form group, preserving definition order. */
    public static function keysInGroup(string $group): array
    {
        return array_keys(array_filter(self::KEYS, fn (array $m) => ($m['group'] ?? '') === $group));
    }
}
