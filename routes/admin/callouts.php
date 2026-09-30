<?php

/**
 * Admin routes: ORDER STATUS CALLOUTS (customer-facing callout cards on
 * the order pages). Loaded inside the ['auth', 'role:admin'] group in
 * routes/web.php.
 */

use App\Http\Controllers\Admin\Callouts\CalloutsController;

Route::post('admin/callouts/{callout}/toggle', [CalloutsController::class, 'toggle'])->name('admin.callouts.toggle');
Route::resource('admin/callouts', CalloutsController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('admin.callouts');
