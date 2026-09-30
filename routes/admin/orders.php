<?php

/**
 * Admin routes: ORDERS + USERS/CLIENTS module (Agent B owns this file).
 * Loaded inside the ['auth', 'role:admin'] group in routes/web.php.
 */

use App\Http\Controllers\Admin\OrdersMgmt\OrdersController;
use App\Http\Controllers\Admin\UsersMgmt\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Orders Management — /admin/orders*
|--------------------------------------------------------------------------
*/

Route::prefix('admin/orders')->name('admin.orders.')->group(function () {
    Route::get('/', [OrdersController::class, 'index'])->name('index');
    Route::get('data', [OrdersController::class, 'data'])->name('data');

    // Bulk actions (must be registered before the {id} catch-all).
    Route::post('bulk-status', [OrdersController::class, 'bulkStatus'])->name('bulk-status');
    Route::post('bulk-archive', [OrdersController::class, 'bulkArchive'])->name('bulk-archive');

    Route::get('{id}', [OrdersController::class, 'show'])->name('show')->where('id', '[0-9]+');
    Route::put('{id}/status', [OrdersController::class, 'updateStatus'])->name('status')->where('id', '[0-9]+');
    Route::put('{id}/archive', [OrdersController::class, 'toggleArchive'])->name('archive')->where('id', '[0-9]+');
    Route::put('{id}/tracking', [OrdersController::class, 'updateTracking'])->name('tracking.update')->where('id', '[0-9]+');

    // Per-offer-product tracking links (legacy tracking_link parity).
    Route::post('{id}/tracking', [OrdersController::class, 'storeTracking'])->name('tracking')->where('id', '[0-9]+');

    // Negotiation tool: set/clear the per-order forced payment method.
    Route::post('{id}/payment-method', [OrdersController::class, 'updatePaymentMethod'])->name('payment-method')->where('id', '[0-9]+');

    // PM-016: verify a bank payment / manually mark it as received from the order page.
    Route::post('{id}/payment-verify', [OrdersController::class, 'verifyPayment'])->name('payment-verify')->where('id', '[0-9]+');

    // Make an Offer / revise offer (legacy /order/{id} functional parity).
    Route::post('{id}/offer', [OrdersController::class, 'storeOffer'])->name('offer')->where('id', '[0-9]+');
});

/*
|--------------------------------------------------------------------------
| Users Management — /admin/users*
|--------------------------------------------------------------------------
*/

Route::prefix('admin/users')->name('admin.users.')->group(function () {
    Route::get('/', [UsersController::class, 'index'])->name('index');
    Route::get('data', [UsersController::class, 'data'])->name('data');

    Route::get('{id}', [UsersController::class, 'show'])->name('show')->where('id', '[0-9]+');
    Route::get('{id}/edit', [UsersController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
    Route::get('{id}/orders', [UsersController::class, 'userOrders'])->name('orders')->where('id', '[0-9]+');

    Route::put('{id}', [UsersController::class, 'update'])->name('update')->where('id', '[0-9]+');
    Route::post('{id}/reset-link', [UsersController::class, 'resetLink'])->name('resetLink')->where('id', '[0-9]+');
    Route::post('{id}/set-password', [UsersController::class, 'setPassword'])->name('setPassword')->where('id', '[0-9]+');
});
