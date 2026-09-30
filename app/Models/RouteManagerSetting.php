<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteManagerSetting extends Model
{
    protected $table = 'route_manager_settings';

    protected $fillable = [
        'route_key', 'label', 'group', 'current_version',
        'legacy_url', 'new_url', 'show_in_header', 'show_in_footer',
        'show_in_main_nav', 'is_locked', 'notes',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'show_in_header'   => 'boolean',
        'show_in_footer'   => 'boolean',
        'show_in_main_nav' => 'boolean',
        'is_locked'        => 'boolean',
    ];

    /**
     * Return the active URL for this route.
     * Used in Blade: RouteManagerSetting::activeUrl('blog')
     */
    public static function activeUrl(string $key, string $fallback = '#'): string
    {
        $row = static::where('route_key', $key)->first();
        if (!$row) {
            return $fallback;
        }

        return $row->current_version === 'new'
            ? ($row->new_url ?? $fallback)
            : ($row->legacy_url ?? $fallback);
    }

    /**
     * Return all rows where show_in_header=true as key→url map.
     * Used in header Blade partial.
     */
    public static function headerLinks(): array
    {
        return static::where('show_in_header', true)
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->route_key => [
                    'label'   => $r->label,
                    'url'     => $r->current_version === 'new' ? $r->new_url : $r->legacy_url,
                    'version' => $r->current_version,
                ],
            ])
            ->toArray();
    }

    /**
     * Return all rows where show_in_footer=true as key→url map.
     */
    public static function footerLinks(): array
    {
        return static::where('show_in_footer', true)
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->route_key => [
                    'label'   => $r->label,
                    'url'     => $r->current_version === 'new' ? $r->new_url : $r->legacy_url,
                    'version' => $r->current_version,
                ],
            ])
            ->toArray();
    }

    /**
     * Cache-friendly version — wraps activeUrl with 5-minute cache.
     */
    public static function activeUrlCached(string $key, string $fallback = '#'): string
    {
        return cache()->remember(
            "route_mgr_{$key}",
            300,
            fn () => static::activeUrl($key, $fallback)
        );
    }

    /**
     * Bust ALL route manager caches after any update:
     * the per-key route_mgr_{key} entries AND the aggregate
     * route_mgr_all_links (ViewComposer) + route_mgr_mobile (API) keys.
     */
    public static function bustCache(): void
    {
        static::all()->each(fn ($r) => cache()->forget("route_mgr_{$r->route_key}"));

        cache()->forget('route_mgr_all_links');
        cache()->forget('route_mgr_mobile');
    }
}
