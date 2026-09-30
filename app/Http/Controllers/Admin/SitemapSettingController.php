<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Admin → Sitemap Settings (2026-09-01).
 * Controls the dynamic /sitemap.xml (see SitemapController): enable/disable,
 * per-section inclusion, base URL, defaults, manual extra/excluded URLs,
 * cache minutes + Regenerate Now / Ping Google actions.
 */
class SitemapSettingController extends Controller
{
    private const KEYS = [
        'sitemap.enabled'          => ['default' => '1',   'type' => 'bool'],
        'sitemap.base_url'         => ['default' => '',    'type' => 'string'],
        'sitemap.include_blog'     => ['default' => '1',   'type' => 'bool'],
        'sitemap.include_services' => ['default' => '1',   'type' => 'bool'],
        'sitemap.include_shop'     => ['default' => '1',   'type' => 'bool'],
        'sitemap.include_static'   => ['default' => '1',   'type' => 'bool'],
        'sitemap.default_priority' => ['default' => '0.8', 'type' => 'string'],
        'sitemap.default_changefreq' => ['default' => 'weekly', 'type' => 'string'],
        'sitemap.cache_minutes'    => ['default' => '60',  'type' => 'string'],
        'sitemap.extra_urls'       => ['default' => '',    'type' => 'text'],
        'sitemap.exclude_urls'     => ['default' => '',    'type' => 'text'],
    ];

    public function index()
    {
        $values = [];
        foreach (self::KEYS as $key => $meta) {
            $values[$key] = Setting::get($key, $meta['default']);
        }

        return view('admin.settings.sitemap', ['values' => $values]);
    }

    public function update(Request $request)
    {
        /* IMPORTANT: browsers/PHP mangle dots in form field names to
           underscores ("sitemap.enabled" arrives as "sitemap_enabled"), and
           Laravel also treats dots as ARRAY nesting in has()/input(). So the
           FORM uses underscore names and the STORAGE uses the dotted keys. */
        $form = fn (string $storageKey): string => str_replace('.', '_', $storageKey);

        $validated = $request->validate([
            'sitemap_base_url'         => ['nullable', 'string', 'max:255', 'regex:/^https?:\/\//i'],
            'sitemap_default_priority' => ['nullable', 'numeric', 'between:0.1,1'],
            'sitemap_default_changefreq' => ['nullable', 'in:always,hourly,daily,weekly,monthly,yearly,never'],
            'sitemap_cache_minutes'    => ['nullable', 'integer', 'between:0,1440'],
            'sitemap_extra_urls'       => ['nullable', 'string', 'max:5000'],
            'sitemap_exclude_urls'     => ['nullable', 'string', 'max:5000'],
            // checkboxes: absent when unticked — validated loosely, read via has()
            'sitemap_enabled'          => ['nullable', 'in:1'],
            'sitemap_include_blog'     => ['nullable', 'in:1'],
            'sitemap_include_services' => ['nullable', 'in:1'],
            'sitemap_include_shop'     => ['nullable', 'in:1'],
            'sitemap_include_static'   => ['nullable', 'in:1'],
        ]);

        foreach (self::KEYS as $key => $meta) {
            $field = $form($key);
            if ($meta['type'] === 'bool') {
                Setting::set($key, $request->has($field) ? '1' : '0');
            } else {
                $value = trim((string) $request->input($field, ''));
                Setting::set($key, $value !== '' ? $value : $meta['default']);
            }
        }

        $this->bustCache();

        return redirect()->route('admin.sitemap.settings')
            ->with('success', 'Sitemap settings saved — /sitemap.xml regenerated on next request.');
    }

    /** "Regenerate Now" — clears the cached XML so the next hit rebuilds it. */
    public function regenerate(Request $request)
    {
        $this->bustCache();

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'message' => 'Sitemap cache cleared.'])
            : redirect()->route('admin.sitemap.settings')->with('success', 'Sitemap cache cleared — it rebuilds on the next /sitemap.xml request.');
    }

    /** "Ping Google" — best-effort, 10s timeout, never fatal. */
    public function ping(Request $request)
    {
        $base = rtrim(Setting::get('sitemap.base_url', config('app.url') ?: url('/')), '/');
        $target = 'https://www.google.com/ping?sitemap=' . urlencode($base . '/sitemap.xml');

        try {
            $response = Http::timeout(10)->get($target);
            $ok = $response->successful();
            $message = $ok
                ? 'Google pinged successfully (HTTP ' . $response->status() . ').'
                : 'Google responded HTTP ' . $response->status() . ' — check the sitemap URL.';
        } catch (\Throwable $e) {
            $ok = false;
            $message = 'Ping failed: ' . $e->getMessage();
        }

        return $request->wantsJson()
            ? response()->json(['ok' => $ok, 'message' => $message], $ok ? 200 : 200)
            : redirect()->route('admin.sitemap.settings')
                ->with($ok ? 'success' : 'error', $message);
    }

    private function bustCache(): void
    {
        Cache::forget(\App\Http\Controllers\SitemapController::CACHE_KEY);
    }
}
