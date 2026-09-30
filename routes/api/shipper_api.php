<?php
/* ============================================================
   SHIPPER SYSTEM — Mobile API (2026-09-10 series). Additive.
   Shipper app endpoints + admin shipper management + client
   proof/address/tracking endpoints.
   ============================================================ */
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Shipper\{
    ShipperApiDashboardController,
    ShipperApiRequestController,
    ShipperApiAssignmentController,
    ShipperApiWalletController,
    ShipperApiProfileController,
    ShipperApiChatController
};
use App\Http\Controllers\Api\Admin\ShipperAdminApiController;
use App\Http\Controllers\Api\Client\ShipperClientApiController;

/* ── Shipper app ─────────────────────────────────────────── */
Route::middleware(['auth:sanctum', 'api.shipper'])
    ->prefix('mobile/v1/shipper')
    ->name('api.shipper.')
    ->group(function () {

        Route::get('/dashboard', [ShipperApiDashboardController::class, 'index'])->name('dashboard');

        Route::get('/requests',           [ShipperApiRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{id}',      [ShipperApiRequestController::class, 'show'])->name('requests.show');
        Route::post('/requests/{id}/quote', [ShipperApiRequestController::class, 'submitQuote'])->name('requests.quote');
        Route::delete('/quotes/{id}',     [ShipperApiRequestController::class, 'withdrawQuote'])->name('quotes.withdraw');
        Route::get('/my-quotes',          [ShipperApiRequestController::class, 'myQuotes'])->name('quotes.index');

        Route::get('/assignments',        [ShipperApiAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/{id}',   [ShipperApiAssignmentController::class, 'show'])->name('assignments.show');
        Route::post('/assignments/{id}/proof',    [ShipperApiAssignmentController::class, 'uploadProof'])->name('assignments.proof');
        Route::post('/assignments/{id}/tracking', [ShipperApiAssignmentController::class, 'submitTracking'])->name('assignments.tracking');
        Route::post('/assignments/{id}/purchased', [ShipperApiAssignmentController::class, 'markPurchased'])->name('assignments.purchased');
        Route::post('/assignments/{id}/package-received', [ShipperApiAssignmentController::class, 'markPackageReceived'])->name('assignments.package-received');

        Route::get('/wallet',          [ShipperApiWalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/payout',  [ShipperApiWalletController::class, 'requestPayout'])->name('wallet.payout');

        Route::get('/profile',  [ShipperApiProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile',  [ShipperApiProfileController::class, 'update'])->name('profile.update');

        Route::get('/chat/{assignmentId}',  [ShipperApiChatController::class, 'index'])->name('chat.index');
        Route::post('/chat/{assignmentId}', [ShipperApiChatController::class, 'send'])->name('chat.send');
    });

/* ── Admin app: shipper management ───────────────────────── */
Route::middleware(['auth:sanctum', 'api.admin'])
    ->prefix('mobile/v1/admin/shippers')
    ->name('api.admin.shippers.')
    ->group(function () {
        Route::get('/',             [ShipperAdminApiController::class, 'index'])->name('index');
        Route::get('/overview',     [ShipperAdminApiController::class, 'overview'])->name('overview');
        Route::get('/requests',     [ShipperAdminApiController::class, 'requests'])->name('requests');
        Route::get('/assignments',  [ShipperAdminApiController::class, 'assignments'])->name('assignments');
        Route::post('/requests/{id}/freeze', [ShipperAdminApiController::class, 'freeze'])->name('requests.freeze');
        Route::post('/requests/{id}/select-shipper', [ShipperAdminApiController::class, 'selectShipper'])->name('requests.select');
        Route::post('/assignments/{id}/approve-proof',   [ShipperAdminApiController::class, 'approveProof'])->name('assignments.approve-proof');
        Route::post('/assignments/{id}/forward-address', [ShipperAdminApiController::class, 'forwardAddress'])->name('assignments.forward-address');
        Route::post('/assignments/{id}/share-tracking',  [ShipperAdminApiController::class, 'shareTracking'])->name('assignments.share-tracking');
        Route::post('/assignments/{id}/release-payment', [ShipperAdminApiController::class, 'releasePayment'])->name('assignments.release-payment');
        Route::get('/payout-requests', [ShipperAdminApiController::class, 'payoutRequests'])->name('payout-requests');
        Route::post('/payouts/{id}/approve', [ShipperAdminApiController::class, 'approvePayout'])->name('payouts.approve');
    });

/* ── Client app: proofs / address / tracking ─────────────── */
Route::middleware(['auth:sanctum', 'api.client'])
    ->prefix('client/orders')
    ->group(function () {
        Route::get('/{orderId}/shipper-proofs', [ShipperClientApiController::class, 'proofs']);
        Route::get('/{orderId}/shipper-proofs/{proofId}/file', [ShipperClientApiController::class, 'proofFile']);
        Route::post('/{orderId}/delivery-address', [ShipperClientApiController::class, 'submitAddress']);
        Route::post('/{orderId}/rate-shipper', [ShipperClientApiController::class, 'rate']);
        Route::get('/{orderId}/shipper-tracking', [ShipperClientApiController::class, 'tracking']);
    });
