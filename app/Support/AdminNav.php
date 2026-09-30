<?php

namespace App\Support;

/**
 * Single source of truth for the new Tailwind admin's top nav + slim
 * sub-sidebar. Phase 1+ batch-migration plans extend $sections as each
 * module gets migrated off AdminLTE — this is a plain array, not a DB
 * table, since the structure only changes when a developer migrates a
 * page, not at runtime.
 */
class AdminNav
{
    /**
     * @return array<int, array{key: string, label: string, icon: string, path_prefix: string, route: string, links: array<int, array{label: string, route: string}>}>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'fa-tachometer-alt',
                'path_prefix' => 'admin-dashbord',
                'route' => 'admin-dashbord',
                'links' => [],
            ],
            [
                'key' => 'orders',
                'label' => 'Orders & Quotes',
                'icon' => 'fa-clipboard-list',
                'path_prefix' => 'freequote-inbox',
                'route' => 'freequote.index',
                'links' => [],
            ],
            [
                'key' => 'addresses',
                'label' => 'Shipping Addresses',
                'icon' => 'fa-map-marker-alt',
                'path_prefix' => 'address',
                'route' => 'address.index',
                'links' => [],
            ],
            [
                'key' => 'contacts',
                'label' => 'Messages',
                'icon' => 'fa-envelope',
                'path_prefix' => 'admin/contacts',
                'route' => 'admin.contacts.index',
                'links' => [],
            ],
        ];
    }

    /**
     * Which section key should be highlighted as active for a given request path.
     * Matches at a path-segment boundary (not a raw substring), so e.g. 'address'
     * won't wrongly match a future 'address-verification' route. Sections are
     * checked in declaration order and the first match wins — if you add a new
     * section whose path_prefix is a broader/shorter prefix of an existing one
     * (e.g. a generic 'admin' section), declare it AFTER the more specific ones
     * or it will steal their matches.
     */
    public static function activeSectionKey(string $requestPath): string
    {
        $requestPath = ltrim($requestPath, '/');

        foreach (self::sections() as $section) {
            if ($section['path_prefix'] === '') {
                continue;
            }
            $prefix = $section['path_prefix'];
            if ($requestPath === $prefix || str_starts_with($requestPath, $prefix . '/') || str_starts_with($requestPath, $prefix . '?')) {
                return $section['key'];
            }
        }

        return 'dashboard';
    }

    /** The full section array for the currently active section, or the dashboard section if none matches. */
    public static function activeSection(string $requestPath): array
    {
        $key = self::activeSectionKey($requestPath);

        return collect(self::sections())->firstWhere('key', $key) ?? self::sections()[0];
    }
}
