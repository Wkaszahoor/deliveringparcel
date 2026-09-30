<?php

/*
 * Application service providers (Laravel 12 style).
 * Route manipulation moved into bootstrap/app.php ->withRouting /
 * ->withMiddleware; rate limiting lives in AppServiceProvider::boot().
 */
return [
    App\Providers\PermissionsServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteManagerServiceProvider::class,
    App\Providers\CmsServiceProvider::class,
];
