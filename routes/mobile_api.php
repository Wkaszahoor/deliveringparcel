<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile module API v1 (isolated)
|--------------------------------------------------------------------------
| Required by routes/api.php with a single require line. Laravel already
| prefixes /api here, so these URLs are /api/mobile/v1/*.
|
| settings/labels : consumed by both apps (Sanctum token).
| admin/*, chat/* : mobile admin app; OrderDetail/Chat controllers are
| thin delegates to the LIVE controllers so the offer two-column state
| machine, PaymentService and UploadGuard invariants stay in one place.
|
*/

Route::prefix('mobile/v1')
    ->middleware('auth:sanctum')
    ->namespace('App\Mobile\Controllers\Api')
    ->name('mobile.api.')
    ->group(function () {
        // App settings + labels
        Route::get('/settings', 'AppSettingsController@settings')->name('settings');
        Route::get('/labels', 'AppSettingsController@labels')->name('labels');

        // Conversations inbox — ROLE-AWARE (shared by both apps): admin sees
        // every order, client only their own. Kept out of the admin group so
        // the client app gets v2 parity (filters + search + unread counts).
        Route::get('/messages/conversations', 'MessagesController@conversations')->name('messages.conversations');

        // Order detail (admin app)
        Route::prefix('admin')
            ->middleware('api.admin')
            ->group(function () {
                Route::get('/orders/{order}', 'OrderDetailController@show')->name('orders.show');
                Route::put('/orders/{order}/status', 'OrderDetailController@updateStatus')->name('orders.status');
                Route::put('/orders/{order}/details', 'OrderDetailController@updateDetails')->name('orders.details');
                Route::post('/orders/{order}/offer', 'OrderDetailController@storeOffer')->name('orders.offer');
                Route::post('/orders/{order}/fix-offer-status', 'OrderDetailController@fixOfferStatus')->name('orders.fix-offer');
                Route::post('/orders/{order}/package-received', 'OrderDetailController@packageReceived')->name('orders.package-received');
                Route::put('/orders/{order}/tracking', 'OrderDetailController@updateTracking')->name('orders.tracking');
                Route::post('/payments/{payment}/verify', 'OrderDetailController@verifyPayment')->name('payments.verify');
            });

        // Chat (admin app)
        Route::prefix('chat')
            ->middleware('api.admin')
            ->group(function () {
                Route::get('/admin/{order}', 'ChatController@index')->name('chat.index');
                Route::post('/admin/{order}', 'ChatController@send')->name('chat.send');
            });
    });

/* ============================================================
   Admin app v2 — enhanced dashboard, conversations inbox,
   notifications, payment verification queue. Appended 2026-08-29.
   ============================================================ */
Route::prefix('mobile/v1')
    ->middleware(['auth:sanctum', 'api.admin'])
    ->namespace('App\Mobile\Controllers\Api')
    ->name('mobile.api.')
    ->group(function () {
        // Dashboard v2 (urgent alerts, KPIs, action required, metrics)
        Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

        // Notifications (delegates to the live notification store)
        Route::get('/notifications', 'NotificationsController@index')->name('notifications.index');
        Route::post('/notifications/mark-all-read', 'NotificationsController@markAllRead')->name('notifications.mark-all');
        Route::post('/notifications/{id}/read', 'NotificationsController@markRead')->name('notifications.read');
        Route::put('/notifications/{id}/delete', 'NotificationsController@delete')->name('notifications.delete');

        // Payment verification queue (+ secure receipt streaming)
        Route::get('/payments/queue', 'PaymentQueueController@index')->name('payments.queue');
        Route::post('/payments/{payment}/verify', 'PaymentQueueController@verify')->name('payments.verify.mobile');
    });

/* Proof streaming needs CLIENT access too (owning client views their own
   receipt in the attachments panel) — so it lives outside the api.admin
   group; the controller enforces admin-or-owner itself. */
Route::prefix('mobile/v1')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/payments/{payment}/proof', [\App\Mobile\Controllers\Api\PaymentQueueController::class, 'streamProof'])
            ->name('mobile.api.payments.proof');
    });

/* ============================================================
   UI Controls hierarchy — consumed by BOTH apps (any Sanctum
   user): full 4-level config + cheap version polling.
   ============================================================ */
Route::prefix('mobile/v1')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/ui/controls', [\App\Mobile\Controllers\Api\UIControlsApiController::class, 'index'])->name('mobile.api.ui.controls');
        Route::get('/ui/version', [\App\Mobile\Controllers\Api\UIControlsApiController::class, 'version'])->name('mobile.api.ui.version');
    });

/* ============================================================
   Client order actions (client app): tracking submission +
   shipping/customs confirmation. Mirrors the frozen web client
   flow as plain column writes.
   ============================================================ */
Route::prefix('mobile/v1')
    ->middleware(['auth:sanctum', 'api.client'])
    ->group(function () {
        Route::post('/client/orders/{order}/tracking', [\App\Mobile\Controllers\Api\ClientOrderController::class, 'submitTracking'])->name('mobile.api.client.tracking');
        Route::post('/client/orders/{order}/confirmation', [\App\Mobile\Controllers\Api\ClientOrderController::class, 'submitConfirmation'])->name('mobile.api.client.confirmation');
    });

/* ============================================================
   Route Manager — public config (no auth). Tells both mobile
   apps which URL version of each page/link is currently live
   so WebView/external links follow the admin's choices without
   an app update.
   ============================================================ */
Route::prefix('mobile/v1')->group(function () {
    Route::get('/route-config', [\App\Http\Controllers\Api\RouteConfigController::class, 'index'])->name('mobile.api.route-config');
});
