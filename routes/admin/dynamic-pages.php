<?php

/**
 * Admin routes: DYNAMIC PAGES + SITE SECTIONS modules (2026-09-02).
 *
 * Loaded from routes/web.php inside the ['auth', 'role:admin'] group:
 *   require __DIR__ . '/admin/dynamic-pages.php';
 * (the groups below re-apply the same middleware for safety).
 */

use App\Http\Controllers\Admin\DynamicPageAdminController;
use App\Http\Controllers\Admin\SiteSectionAdminController;
use Illuminate\Support\Facades\Route;

/* ============================================================
   Dynamic Pages Manager
   ============================================================ */
Route::prefix('admin/dynamic-pages')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.dynamic-pages.')
    ->group(function () {
        Route::get('/', [DynamicPageAdminController::class, 'index'])
            ->name('index');

        /* Static segments BEFORE the {dynamicPage} wildcard */
        Route::get('/create', [DynamicPageAdminController::class, 'create'])
            ->name('create');

        Route::post('/', [DynamicPageAdminController::class, 'store'])
            ->name('store');

        Route::post('/restore/{id}', [DynamicPageAdminController::class, 'restore'])
            ->name('restore')
            ->where('id', '[0-9]+');

        Route::delete('/force/{id}', [DynamicPageAdminController::class, 'forceDelete'])
            ->name('force-delete')
            ->where('id', '[0-9]+');

        Route::get('/{dynamicPage}/edit', [DynamicPageAdminController::class, 'edit'])
            ->name('edit')
            ->where('dynamicPage', '[0-9]+');

        Route::put('/{dynamicPage}', [DynamicPageAdminController::class, 'update'])
            ->name('update')
            ->where('dynamicPage', '[0-9]+');

        Route::post('/{dynamicPage}/toggle', [DynamicPageAdminController::class, 'toggleActive'])
            ->name('toggle')
            ->where('dynamicPage', '[0-9]+');

        Route::delete('/{dynamicPage}', [DynamicPageAdminController::class, 'destroy'])
            ->name('destroy')
            ->where('dynamicPage', '[0-9]+');
    });

/* ============================================================
   Section Manager
   ============================================================ */
Route::prefix('admin/site-sections')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.site-sections.')
    ->group(function () {
        Route::get('/', [SiteSectionAdminController::class, 'index'])
            ->name('index');

        Route::put('/{id}', [SiteSectionAdminController::class, 'update'])
            ->name('update')
            ->where('id', '[0-9]+');
    });
