<?php

namespace App\Providers;

use App\Models\RouteManagerSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class RouteManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /*
         * Share $routeLinks with EVERY Blade view so headers/footers
         * can use it without controller changes. Cached 5 minutes.
         */
        View::composer('*', function ($view) {
            if (!$view->offsetExists('routeLinks')) {
                $links = cache()->remember('route_mgr_all_links', 300, function () {
                    try {
                        return RouteManagerSetting::all()
                            ->keyBy('route_key')
                            ->map(fn ($r) => [
                                'label'            => $r->label,
                                'url'              => $r->current_version === 'new'
                                                        ? $r->new_url
                                                        : $r->legacy_url,
                                'version'          => $r->current_version,
                                'show_in_header'   => $r->show_in_header,
                                'show_in_footer'   => $r->show_in_footer,
                                'show_in_main_nav' => $r->show_in_main_nav,
                            ])
                            ->toArray();
                    } catch (\Throwable $e) {
                        // Table not migrated yet — return empty so
                        // views don't crash during deployment.
                        return [];
                    }
                });

                $view->with('routeLinks', $links);
            }
        });
    }
}
