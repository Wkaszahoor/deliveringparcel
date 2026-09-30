<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Deploy-safe helper loader: guarantees app/Support/helpers.php
        // (dp_per_page etc.) is defined even when prod's vendor/composer
        // autoload_files.php predates the file (no composer on shared host).
        require_once app_path('Support/helpers.php');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Ported from RouteServiceProvider::configureRateLimiting (Laravel 12 upgrade).
        RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        // Audit logging observers (mask sensitive fields via AuditLogger).
        $observer = \App\Observers\AuditObserver::class;
        foreach ([
            \App\Models\Blog::class,
            \App\Models\BlogCategory::class,
            \App\Models\Service::class,
            \App\Models\ServiceCategory::class,
            \App\Models\HeroSlide::class,
            \App\Models\Country::class,
            \App\Models\WeightUnit::class,
            \App\Models\RateZone::class,
            \App\Models\RateRule::class,
            \App\Models\Setting::class,
        ] as $model) {
            if (class_exists($model)) {
                $model::observe($observer);
            }
        }
    }
}
