<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\Cms\CmsNavService;

class CmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CmsNavService::class);
    }

    public function boot()
    {
        // Inject $cmsHeaderNav and $cmsFooterMenus into EVERY view
        View::composer('*', function ($view) {
            try {
                $navService = app(CmsNavService::class);
                $view->with([
                    'cmsHeaderNav'   => $navService->getMenu('header_main'),
                    'cmsFooterMenus' => $navService->getFooterMenus(),
                ]);
            } catch (\Throwable $e) {
                $view->with([
                    'cmsHeaderNav'   => collect([]),
                    'cmsFooterMenus' => [],
                ]);
            }
        });
    }
}
