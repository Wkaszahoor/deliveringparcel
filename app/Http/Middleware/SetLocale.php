<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Homepage i18n pilot (see resources/views/home.blade.php and
 * lang/{locale}.json). Runs on every request (registered globally in the
 * "web" group in bootstrap/app.php) so App::getLocale() is always correct
 * even on pages that don't yet call __() themselves — a visitor who picked
 * a language on the homepage keeps that choice site-wide via the cookie,
 * they just won't see translated copy outside the homepage until more
 * pages are wrapped in __() too.
 */
class SetLocale
{
    /**
     * en (default) + the 8 languages requested for the pilot.
     *
     * @var array<int, string>
     */
    public const SUPPORTED = ['en', 'es', 'ar', 'zh', 'ja', 'it', 'fr', 'ko', 'de'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('locale');

        if (! $locale || ! in_array($locale, self::SUPPORTED, true)) {
            // No explicit choice yet — read the browser's own Accept-Language
            // header (no GeoIP/external service involved) and fall back to
            // the app default when nothing offered matches.
            $locale = $request->getPreferredLanguage(self::SUPPORTED) ?? config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
