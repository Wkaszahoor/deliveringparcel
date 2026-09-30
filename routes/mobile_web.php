<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile App Management — web panel routes (isolated module)
|--------------------------------------------------------------------------
| Required by routes/web.php with a single require line. Everything the
| mobile admin panel needs lives here and in app/Mobile + app\Mobile
| views under resources/views/mobile-admin/. No legacy route touched.
|
*/

Route::prefix('admin/mobile-app')
    ->middleware(['auth', 'role:admin'])
    ->namespace('App\Mobile\Controllers\Admin')
    ->name('mobile.admin.')
    ->group(function () {
        // Dashboard
        Route::get('/', 'MobileAppController@index')->name('dashboard');

        // Screens
        Route::get('/screens', 'MobileScreenController@index')->name('screens.index');
        Route::put('/screens/bulk-update', 'MobileScreenController@bulkUpdate')->name('screens.bulk');
        Route::put('/screens/{key}', 'MobileScreenController@update')->name('screens.update');
        Route::put('/screens/{key}/config', 'MobileScreenController@updateConfig')->name('screens.config');

        // Labels
        Route::get('/labels', 'MobileLabelController@index')->name('labels.index');
        Route::put('/labels', 'MobileLabelController@update')->name('labels.update');
        Route::post('/labels/reset', 'MobileLabelController@reset')->name('labels.reset');

        // UI
        Route::get('/ui', 'MobileUiController@index')->name('ui.index');
        Route::put('/ui', 'MobileUiController@update')->name('ui.update');

        // Features
        Route::get('/features', 'MobileFeatureController@index')->name('features.index');
        Route::put('/features', 'MobileFeatureController@update')->name('features.update');
        Route::post('/features/{key}/toggle', 'MobileFeatureController@toggleFlag')->name('features.toggle');

        // Notifications
        Route::get('/notifications', 'MobileNotificationController@index')->name('notifications.index');
        Route::post('/notifications/send', 'MobileNotificationController@send')->name('notifications.send');
        Route::get('/notifications/history', 'MobileNotificationController@history')->name('notifications.history');
        Route::get('/notifications/templates', 'MobileNotificationController@templates')->name('notifications.templates');
        Route::post('/notifications/templates', 'MobileNotificationController@saveTemplate')->name('notifications.templates.save');
        Route::delete('/notifications/templates/{id}', 'MobileNotificationController@deleteTemplate')->name('notifications.templates.delete');

        // UI Controls — 4-level hierarchy (screens → sections → elements → actions)
        Route::get('/ui-controls', 'UIControlsController@index')->name('ui-controls.index');
        Route::put('/ui-controls/screens/{key}', 'UIControlsController@updateScreen')->name('ui-controls.screens.update');
        Route::put('/ui-controls/sections/{key}', 'UIControlsController@updateSection')->name('ui-controls.sections.update');
        Route::put('/ui-controls/elements/{key}', 'UIControlsController@updateElement')->name('ui-controls.elements.update');
        Route::put('/ui-controls/actions/{key}', 'UIControlsController@updateAction')->name('ui-controls.actions.update');
        Route::put('/ui-controls/bulk', 'UIControlsController@bulkUpdate')->name('ui-controls.bulk');
        Route::post('/ui-controls/bump-version', 'UIControlsController@bumpVersion')->name('ui-controls.bump');
    });

/* ============================================================
   Order archive (soft delete) — admin cleanup of dead orders.
   Appended 2026-08-31; module-owned routes, legacy untouched.
   ============================================================ */
Route::prefix('admin/order-archive')
    ->middleware(['auth', 'role:admin'])
    ->namespace('App\Mobile\Controllers\Admin')
    ->group(function () {
        Route::post('/{order}', 'OrderArchiveController@archive')->name('archive.order');
        Route::post('/{order}/restore', 'OrderArchiveController@restore')->name('archive.restore');
        Route::post('/bulk', 'OrderArchiveController@bulk')->name('archive.bulk');
    });
