<?php

/**
 * Admin routes: ROUTE MANAGER module.
 * Loaded inside the ['auth', 'role:admin'] group in routes/web.php
 * (the group below re-applies the same middleware for safety).
 */

use App\Http\Controllers\Admin\RouteManagerController;

Route::prefix('admin/route-manager')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.route-manager.')
    ->group(function () {
        Route::get('/', [RouteManagerController::class, 'index'])
            ->name('index');

        /* Static segments BEFORE the {setting} wildcard */
        Route::get('/active-url/{key}', [RouteManagerController::class, 'activeUrl'])
            ->name('active-url');

        Route::post('/bulk-switch', [RouteManagerController::class, 'bulkSwitch'])
            ->name('bulk-switch');

        /* 2026-09-02 — pull all dynamic_pages into route_manager_settings */
        Route::post('/sync-dynamic-pages', [RouteManagerController::class, 'syncDynamicPages'])
            ->name('sync-dynamic');

        Route::put('/{setting}', [RouteManagerController::class, 'update'])
            ->name('update')
            ->where('setting', '[0-9]+');

        Route::post('/{setting}/toggle-flag', [RouteManagerController::class, 'toggleFlag'])
            ->name('toggle-flag')
            ->where('setting', '[0-9]+');
    });
