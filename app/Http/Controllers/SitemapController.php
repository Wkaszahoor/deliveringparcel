<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Dynamic XML sitemap for the whole public website (2026-09-01):
 *   static marketing/legal pages, root /services + /blog (+ their slugs),
 *   active shop products, track-order. Admin / auth / customer pages are
 *   NEVER included. Fully controlled from Admin → Sitemap Settings
 *   (enable/disable per section, extra/excluded URLs, cache minutes) —
 *   see SitemapSettingController. Optional ?type=blog|services|shop|pages
 *   filters the output. Cache is busted on every settings save.
 */
class SitemapController extends Controller
{
    public const CACHE_KEY = 'sitemap.index.xml';

    public function index(Request $request)
    {
        if (Setting::get('sitemap.enabled', '1') !== '1') {
            abort(404);
        }

        $minutes = max(0, (int) Setting::get('sitemap.cache_minutes', '60'));
        $type = strtolower((string) $request->query('type', ''));
        $builder = fn () => $this->build($request);

        // Cache key includes the type filter so ?type=blog never serves the
        // cached full sitemap.
        $key = self::CACHE_KEY . ($type !== '' ? '.' . $type : '');
        $xml = $minutes > 0
            ? Cache::remember($key, $minutes * 60, $builder)
            : $builder();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    protected function build(Request $request): string
    {
        $base = rtrim(Setting::get('sitemap.base_url', config('app.url') ?: 'https://deliveringparcel.com'), '/');
        $type = strtolower((string) $request->query('type', ''));
        $defPri = Setting::get('sitemap.default_priority', '0.8');
        $defFreq = Setting::get('sitemap.default_changefreq', 'weekly');

        $urls = [];

        /* ---- Static pages ---- */
        if ($this->want($type, 'pages') && Setting::get('sitemap.include_static', '1') === '1') {
            $static = [
                ['/', 'daily', '1.0'],
                ['/track-order', 'monthly', '0.6'],
                ['/testimonials', 'daily', '0.8'],
                ['/contact-details', 'yearly', '0.7'],
                ['/request', 'monthly', '0.9'],
                ['/special-request', 'monthly', '0.9'],
                ['/terms-and-conditions', 'yearly', '0.3'],
                ['/privacy-policy', 'yearly', '0.3'],
                ['/services', 'weekly', '0.8'],
                ['/blog', 'daily', '0.8'],
            ];
            foreach ($static as [$path, $freq, $pri]) {
                $urls[] = ['loc' => $base . $path, 'lastmod' => null, 'freq' => $freq, 'pri' => $pri];
            }
        }

        /* ---- Services (root URLs post-migration) ---- */
        if ($this->want($type, 'services') && Setting::get('sitemap.include_services', '1') === '1') {
            try {
                foreach (Service::query()->where('is_available', true)->orderBy('sort')->get(['slug', 'updated_at']) as $s) {
                    $urls[] = [
                        'loc'     => $base . '/services/' . rawurlencode($s->slug),
                        'lastmod' => optional($s->updated_at)->toAtomString(),
                        'freq'    => $defFreq,
                        'pri'     => $defPri,
                    ];
                }
            } catch (\Throwable $e) {
                // table missing pre-migration — skip silently
            }
        }

        /* ---- Blog posts ---- */
        if ($this->want($type, 'blog') && Setting::get('sitemap.include_blog', '1') === '1') {
            try {
                foreach (Blog::query()->where('status', 'published')->orderByDesc('published_at')->get(['slug', 'published_at', 'updated_at']) as $b) {
                    $urls[] = [
                        'loc'     => $base . '/blog/' . rawurlencode($b->slug),
                        'lastmod' => optional($b->updated_at ?: $b->published_at)->toAtomString(),
                        'freq'    => $defFreq,
                        'pri'     => $defPri,
                    ];
                }
            } catch (\Throwable $e) {
            }
        }

        /* ---- Shop products (public catalog: /shop/{slug}) ---- */
        if ($this->want($type, 'shop') && Setting::get('sitemap.include_shop', '1') === '1') {
            try {
                foreach (\App\Models\ShopProduct::query()->where('is_active', true)->get(['slug', 'updated_at']) as $p) {
                    $urls[] = [
                        'loc'     => $base . '/shop/' . rawurlencode($p->slug),
                        'lastmod' => optional($p->updated_at)->toAtomString(),
                        'freq'    => $defFreq,
                        'pri'     => $defPri,
                    ];
                }
            } catch (\Throwable $e) {
                // shop tables unavailable — section skipped, sitemap stays valid
            }
        }

        /* ---- Manually added extra URLs (one per line) ---- */
        foreach (preg_split('/\r\n|\r|\n/', (string) Setting::get('sitemap.extra_urls', '')) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $urls[] = ['loc' => $line, 'lastmod' => null, 'freq' => $defFreq, 'pri' => $defPri];
            }
        }

        /* ---- Manual exclusions (one per line, prefix or full-URL match) ---- */
        $excluded = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) Setting::get('sitemap.exclude_urls', '')) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $excluded[] = $line;
            }
        }
        $urls = array_values(array_filter($urls, function ($u) use ($base, $excluded) {
            foreach ($excluded as $x) {
                if ($x !== '' && (str_starts_with($u['loc'], $base . $x) || str_starts_with($u['loc'], $x))) {
                    return false;
                }
            }
            return true;
        }));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n"
                . '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($u['lastmod'], ENT_XML1) . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . htmlspecialchars($u['freq'], ENT_XML1) . "</changefreq>\n"
                . '    <priority>' . htmlspecialchars($u['pri'], ENT_XML1) . "</priority>\n"
                . "  </url>\n";
        }
        $xml .= '</urlset>';

        return $xml;
    }

    /** Type filter: empty type = all sections. */
    private function want(string $type, string $section): bool
    {
        return $type === '' || $type === $section;
    }
}
