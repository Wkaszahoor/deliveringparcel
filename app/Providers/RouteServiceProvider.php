<?php

namespace App\Providers;

/**
 * Laravel 12 note: the old RouteServiceProvider was retired during the
 * 8 → 12 upgrade (routing moved to bootstrap/app.php, rate limiting to
 * AppServiceProvider). This shell only keeps the HOME constant that the
 * Auth controllers and RedirectIfAuthenticated still reference.
 */
final class RouteServiceProvider
{
    public const HOME = '/home';
}
