<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| DeliveringParcel mobile API (Sanctum token auth).
| Existing Stripe webhook is preserved unchanged below.
|
*/

/* ============================================================
   Auth (public)
   ============================================================ */
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::get('/user/profile', [App\Http\Controllers\Api\AuthController::class, 'profile'])
    ->middleware('auth:sanctum');

/* ============================================================
   Profile (shared — both roles) — mobile-api-v5
   ============================================================ */
Route::put('/profile', [App\Http\Controllers\Api\ProfileController::class, 'update'])
    ->middleware('auth:sanctum');
Route::put('/profile/password', [App\Http\Controllers\Api\ProfileController::class, 'changePassword'])
    ->middleware('auth:sanctum');

/* ============================================================
   Mobile section labels (admin-renamable via Settings → Mobile App)
   ============================================================ */
Route::get('/mobile/labels', function () {
    $out = [];
    foreach (config('admin_settings.tabs.mobile.fields', []) as $key => $field) {
        $out[$key] = \App\Models\Setting::get($key, $field['default'] ?? '');
    }
    return response()->json(['labels' => $out]);
})->middleware('auth:sanctum');

/* ============================================================
   Conversations inbox (shared — both roles) — mobile-api-v5
   ============================================================ */
Route::get('/conversations', [App\Http\Controllers\Api\ConversationsController::class, 'index'])
    ->middleware('auth:sanctum');

/* ============================================================
   FCM Token Registration (shared — both roles)
   ============================================================ */
Route::post('/fcm/token', function (Request $request) {
    $request->validate([
        'token' => 'required|string',
        'device' => 'nullable|string',
    ]);

    $user = $request->user();
    $fcmTokens = $user->fcm_tokens ?? [];
    $newToken = [
        'token' => $request->token,
        'device' => $request->device ?? 'android',
        'registered_at' => now()->toIso8601String(),
    ];

    // Dedupe by token value
    $fcmTokens = array_filter($fcmTokens, fn($t) => $t['token'] !== $request->token);
    $fcmTokens[] = $newToken;
    $user->fcm_tokens = array_values($fcmTokens);
    $user->save();

    return response()->json(['ok' => true]);
})->middleware('auth:sanctum');

/* ============================================================
   Unread notification badge (shared — both roles)
   ============================================================ */
Route::get('/notifications/unread-count', function (Request $request) {
    return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
})->middleware(['auth:sanctum']);

/* ============================================================
   Admin API
   ============================================================ */
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'api.admin'])
    ->namespace('App\Http\Controllers\Api\Admin')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', 'DashboardController@index');

        // Conversations inbox (real — last message + unread per order)
        Route::get('/conversations', [\App\Http\Controllers\Api\ConversationsController::class, 'index']);

        // Orders
        Route::get('/orders', 'OrderController@index');
        Route::get('/orders/{order}', 'OrderController@show');
        Route::put('/orders/{order}/status', 'OrderController@updateStatus');
        Route::put('/orders/{order}/details', 'OrderController@updateDetails');
        Route::post('/orders/{order}/offer', 'OrderController@storeOffer');

        // Manual bank-payment verification (PM-016): action = approve|reject|mark_received
        Route::post('/payments/{payment}/verify', 'OrderController@verifyPayment');

        // One-click fix for stuck offer_status (out of sync with order_status)
        Route::post('/orders/{order}/fix-offer-status', 'OrderController@fixOfferStatus');

        // Chat
        Route::get('/orders/{order}/chat', [\App\Http\Controllers\Api\ChatController::class, 'index']);
        Route::post('/orders/{order}/chat', [\App\Http\Controllers\Api\ChatController::class, 'store']);

        // Notifications
        Route::get('/notifications', 'NotificationController@index');
        Route::get('/notifications/unread-count', 'NotificationController@unreadCount');
        Route::post('/notifications/{id}/read', 'NotificationController@markRead');
        Route::post('/notifications/read-all', 'NotificationController@markAllRead');
        Route::delete('/notifications/{id}', 'NotificationController@destroy');
    });

/* ============================================================
   Client API
   ============================================================ */
Route::prefix('client')
    ->middleware(['auth:sanctum', 'api.client'])
    ->namespace('App\Http\Controllers\Api\Client')
    ->group(function () {

        // Dashboard (client home) — mobile-api-v5
        Route::get('/dashboard', 'DashboardController@index');

        // Conversations inbox (real — last message + unread per order)
        Route::get('/conversations', [\App\Http\Controllers\Api\ConversationsController::class, 'index']);

        // Orders
        Route::get('/orders', 'OrderController@index');
        Route::post('/orders', 'OrderController@store');
        Route::get('/orders/{order}', 'OrderController@show');
        Route::post('/orders/{order}/received', 'OrderController@markReceived');

        // Chat
        Route::get('/orders/{order}/chat', [\App\Http\Controllers\Api\ChatController::class, 'index']);
        Route::post('/orders/{order}/chat', [\App\Http\Controllers\Api\ChatController::class, 'store']);

        // Offers
        Route::post('/offers/{offer}/accept', 'OfferController@accept');
        Route::post('/offers/{offer}/reject', 'OfferController@reject');

        // Payment
        Route::get('/payment-methods', 'PaymentController@methods');
        Route::post('/orders/{order}/pay', 'PaymentController@initiate');
        Route::post('/orders/{order}/payment-proof', 'PaymentController@uploadProof');
        Route::get('/orders/{order}/payment-status', 'PaymentController@status');
        Route::post('/orders/{order}/review', 'OrderController@submitReview');

        // Notifications
        Route::get('/notifications', 'NotificationController@index');
        Route::get('/notifications/unread-count', 'NotificationController@unreadCount');
        Route::post('/notifications/{id}/read', 'NotificationController@markRead');
        Route::post('/notifications/read-all', 'NotificationController@markAllRead');
        Route::delete('/notifications/{id}', 'NotificationController@destroy');
    });

/* ============================================================
   Existing Stripe webhook — preserved unchanged
   ============================================================ */
Route::post('/payments/stripe/webhook', App\Http\Controllers\Payments\StripeWebhookController::class)
    ->name('payments.stripe.webhook');

/* ============================================================
   Legacy stub — kept for backward compat
   ============================================================ */
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ── Mobile App API Routes ──────────────────────────────────
// Added: 2026-08-29. Do not modify above this line.
// All mobile-module API routes live in routes/mobile_api.php.
require __DIR__.'/mobile_api.php';

/* ============================================================
   SHIPPER SYSTEM mobile API (2026-09-10 series) — additive.
   ============================================================ */
require __DIR__ . "/api/shipper_api.php";
