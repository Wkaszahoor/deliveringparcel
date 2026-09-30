<?php

namespace App\Services;

use App\Models\ThemeSetting;
use App\Models\WidgetInstance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

/**
 * TW-003/TW-004 — resolves and renders widget areas.
 *
 * Precedence: page > template > theme > global. When any instance of a higher
 * scope matches the current page, lower scopes are ignored for that area.
 * Every instance render is cached (per instance + config hash) and invalidated
 * on save via WidgetRenderer::flush().
 *
 * SECURITY: 'html' widgets are sanitized on SAVE (HtmlSanitizer) and rendered
 * raw — that is the documented contract. All other types render Blade views
 * that escape their config with {{ }}.
 */
class WidgetRenderer
{
    /** Context a controller may set to enable template-scope matching. */
    public static function context(): array
    {
        $req = request();
        return [
            'path'     => trim($req ? $req->path() : '/', '/'),
            'template' => (string) ($req ? $req->input('tpl', '') : ''),
            'theme'    => (string) (ThemeSetting::get('active_theme', 'default') ?: 'default'),
        ];
    }

    /** Render every matching instance for an area — precedence exclusive. */
    public static function area(string $area): string
    {
        try {
            $ctx = self::context();
            $now = now();

            $instances = WidgetInstance::query()
                ->where('area', $area)
                ->where('is_active', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                })
                ->orderBy('sort')
                ->orderBy('id')
                ->get()
                ->filter(fn (WidgetInstance $w) => self::conditionsPass($w))
                ->filter(fn (WidgetInstance $w) => $w->matchesContext($ctx['path'], $ctx['template'], $ctx['theme']));

            if ($instances->isEmpty()) {
                return '';
            }

            // TW-004: keep only the highest-precedence scope group.
            $top = $instances->max(fn (WidgetInstance $w) => $w->scopeRank());
            $instances = $instances->filter(fn (WidgetInstance $w) => $w->scopeRank() === $top);

            $html = '';
            foreach ($instances as $w) {
                $html .= self::renderInstance($w);
            }
            return $html;
        } catch (\Throwable $e) {
            \Log::warning('widget area failed', ['area' => $area, 'e' => $e->getMessage()]);
            return ''; // never break a page because a widget failed
        }
    }

    public static function renderInstance(WidgetInstance $w): string
    {
        $minutes = (int) ($w->cache_minutes ?? config('admin_widgets.default_cache_minutes'));
        $key     = 'widget.' . $w->id . '.' . md5(json_encode([$w->config, $w->title, $w->type]));

        if ($minutes > 0) {
            $cached = Cache::get($key);
            if ($cached !== null) {
                return $cached;
            }
        }

        $out = self::build($w);
        if ($minutes > 0) {
            Cache::put($key, $out, now()->addMinutes($minutes));
        }
        return $out;
    }

    protected static function build(WidgetInstance $w): string
    {
        $types  = config('admin_widgets.types', []);
        $type   = $types[$w->type] ?? null;
        $config = $w->config ?: [];

        if ($w->type === 'blade') {
            $block = config('admin_widgets.blocks.' . ($config['view'] ?? ''), null);
            $view  = $block['view'] ?? null;
        } else {
            $view = $type['view'] ?? null;
        }
        if (!$view || !View::exists($view)) {
            return '';
        }

        return view($view, ['widget' => $config, 'instance' => $w])->render();
    }

    /** TW-005 conditional display: auth state + optional role membership. */
    protected static function conditionsPass(WidgetInstance $w): bool
    {
        $c = $w->conditions ?: [];
        $mode = $c['auth'] ?? 'any';
        if ($mode === 'guest' && auth()->check()) {
            return false;
        }
        if ($mode === 'user' && !auth()->check()) {
            return false;
        }
        if (!empty($c['roles'])) {
            $user = auth()->user();
            if (!$user || empty($user->role) || !in_array($user->role, (array) $c['roles'], true)) {
                return false;
            }
        }
        return true;
    }

    /** Invalidate cached renders (called on every admin save). */
    public static function flush(): void
    {
        try {
            Cache::flush(); // dev-scale; tags unavailable on file driver
        } catch (\Throwable $e) {
            // ignore — cache driver may reject flush
        }
    }
}
