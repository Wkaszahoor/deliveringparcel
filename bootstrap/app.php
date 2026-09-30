<?php

use App\Http\Middleware\Api\ForceJsonResponse;
use App\Http\Middleware\Api\RequireAdmin;
use App\Http\Middleware\Api\RequireClient;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequireShipperProfile;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
 * Laravel 12 application bootstrap (upgraded from Laravel 8, 2026-09-19).
 * Replaces the old app/Http/Kernel.php + app/Console/Kernel.php +
 * app/Exceptions/Handler.php + RouteServiceProvider — all ported here.
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders(require __DIR__ . '/providers.php')
    ->withMiddleware(function (Middleware $middleware) {
        // Was Kernel::$middlewareGroups['api'] (RouteServiceProvider loaded routes/api.php).
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Homepage i18n pilot — reads the locale cookie / Accept-Language
        // header on every web request (see App\Http\Middleware\SetLocale).
        $middleware->web(append: [SetLocale::class]);

        // Was Kernel::$routeMiddleware (app aliases only — framework ones are built in).
        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'role' => RoleMiddleware::class,
            'shipper.profile' => RequireShipperProfile::class,
            'api.json' => ForceJsonResponse::class,
            'api.admin' => RequireAdmin::class,
            'api.client' => RequireClient::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Ported from app/Console/Kernel.php@schedule.
        $schedule->command('queue:work --stop-when-empty')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('shipper:release-holds')->dailyAt('03:10');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Ported from app/Exceptions/Handler.php (reportable was an empty stub).
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);
    })
    ->create();
