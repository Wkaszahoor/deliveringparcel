<?php

namespace App\Services;

use App\Models\NavMenuItem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

/**
 * FE-004 / FE-005 — renders the DB-driven /home2 header menu.
 *
 * Output is assembled from trusted admin-maintained DB fields; every
 * user-controlled string (title/label/url/icon) is escaped with e().
 */
class NavRenderer
{
    public const CACHE_KEY = 'dp_nav_menu';

    /** Render the full <ul> for the current visitor. */
    public static function render(): string
    {
        $items = self::items();

        if ($items->isEmpty()) {
            return '';
        }

        return self->renderLevel($items, null);
    }

    /** Clear the menu cache (called after admin mutations). */
    public static function flushCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            // cache backend unavailable — ignore
        }
    }

    /**
     * Active items, visibility-filtered for the current user.
     *
     * @return \Illuminate\Support\Collection
     */
    protected static function items()
    {
        try {
            $all = Cache::rememberForever(self::CACHE_KEY, function () {
                return NavMenuItem::where('is_active', true)
                    ->orderBy('sort')->orderBy('id')
                    ->get(['id', 'parent_id', 'title', 'label', 'url', 'route_name', 'icon_class', 'visibility', 'new_tab']);
            });
        } catch (\Throwable $e) {
            return collect();
        }

        // A child whose parent is hidden/inactive must not render either.
        $activeIds = $all->pluck('id')->all();

        return $all->filter(function (NavMenuItem $item) use ($activeIds) {
            if ($item->parent_id && !in_array($item->parent_id, $activeIds, true)) {
                return false;
            }

            return $item->visibleToCurrentUser();
        })->values();
    }

    /** Recursively render one level as a nested <ul>. */
    protected static function renderLevel($items, $parentId, int $depth = 0): string
    {
        $level = $items->filter(fn ($i) => (int) $i->parent_id === (int) $parentId);
        if ($level->isEmpty()) {
            return '';
        }

        $html = $depth === 0
            ? '<ul class="h2-menu" id="h2Menu">'
            : '<ul class="h2-submenu">';

        foreach ($level as $item) {
            $children = $items->filter(fn ($i) => (int) $i->parent_id === (int) $item->id);
            $hasSub   = $children->isNotEmpty();

            $classes = [];
            if ($hasSub) {
                $classes[] = 'h2-has-sub';
            }
            if (self::isActive($item)) {
                $classes[] = 'active';
            }

            $html .= '<li' . ($classes ? ' class="' . e(implode(' ', $classes)) . '"' : '') . '>';
            $html .= self::link($item, $hasSub);
            if ($hasSub) {
                $html .= self::renderLevel($items, $item->id, $depth + 1);
            }
            $html .= '</li>';
        }

        return $html . '</ul>';
    }

    /** The <a> tag for one item. */
    protected static function link(NavMenuItem $item, bool $hasSub): string
    {
        $href = self::resolveUrl($item);
        $attr = [];

        if ($href !== null) {
            $attr[] = 'href="' . e($href) . '"';
        } else {
            $attr[] = 'href="#" role="button"'; // parent without own destination
        }

        if ($item->new_tab && $href !== null) {
            $attr[] = 'target="_blank" rel="noopener"';
        }

        $label = e($item->displayLabel());
        $icon  = trim((string) $item->icon_class);
        $inner = $icon !== '' ? '<span class="h2-nav-icon" aria-hidden="true">' . e($icon) . '</span>' : '';
        $inner .= '<span class="h2-nav-text">' . $label . '</span>';

        if ($hasSub) {
            $inner .= '<span class="h2-caret" aria-hidden="true">&#9662;</span>';
        }

        return '<a ' . implode(' ', $attr) . '>' . $inner . '</a>';
    }

    /** Named route wins; falls back to the raw url field. Null when neither resolves. */
    protected static function resolveUrl(NavMenuItem $item): ?string
    {
        $routeName = trim((string) $item->route_name);
        if ($routeName !== '' && Route::has($routeName)) {
            try {
                return route($routeName);
            } catch (\Throwable $e) {
                // route requires parameters we do not have — fall through
            }
        }

        $url = trim((string) $item->url);
        if ($url !== '') {
            return $url;
        }

        return null;
    }

    /** Active-state: current request path matches the item's path. */
    protected static function isActive(NavMenuItem $item): bool
    {
        $href = self::resolveUrl($item);
        if (!$href) {
            return false;
        }

        $path = trim(parse_url($href, PHP_URL_PATH) ?: '', '/');
        if ($path === '' || $path === 'home2') {
            // exact match only for the homepage
            return request()->is('/') || request()->is('home2') || request()->is('home2/');
        }

        return request()->is($path) || request()->is($path . '/*');
    }
}
