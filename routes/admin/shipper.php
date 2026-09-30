<?php
/* ============================================================
   SHIPPER SYSTEM — Admin routes (2026-09-10 series).
   Additive only: nothing above this file's require changes.
   ============================================================ */
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Shipper\{
    ShipperManagementController,
    ShippingRequestAdminController,
    ShipperAssignmentController,
    ShipperKycController,
    ShipperDashboardController
};

Route::group([
    'prefix'     => 'admin/shippers',
    'as'         => 'admin.shippers.',
    'middleware' => ['auth', 'role:admin'],
], function () {

    Route::get('/overview', [ShipperDashboardController::class, 'index'])->name('overview');

    Route::get('/',                    [ShipperManagementController::class, 'index'])->name('index');
    Route::get('/pending-kyc',         [ShipperManagementController::class, 'pendingKyc'])->name('pending-kyc');
    Route::get('/country-requests',            [ShipperManagementController::class, 'countryRequests'])->name('country-requests');
    Route::post('/country-requests/{id}/approve', [ShipperManagementController::class, 'approveCountryRequest'])->name('country-requests.approve');
    Route::post('/country-requests/{id}/reject',  [ShipperManagementController::class, 'rejectCountryRequest'])->name('country-requests.reject');
    Route::post('/country-requests/edit/{id}',    [ShipperManagementController::class, 'updateCountries'])->name('country-requests.update');
    Route::get('/performance',         [ShipperManagementController::class, 'performance'])->name('performance');
    Route::get('/payout-requests',     [ShipperManagementController::class, 'payoutRequests'])->name('payout-requests');
    Route::get('/{id}',                [ShipperManagementController::class, 'show'])->name('show');
    Route::post('/{id}/approve',       [ShipperManagementController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject',        [ShipperManagementController::class, 'reject'])->name('reject');
    Route::post('/{id}/suspend',       [ShipperManagementController::class, 'suspend'])->name('suspend');
    Route::post('/{id}/reinstate',     [ShipperManagementController::class, 'reinstate'])->name('reinstate');
    Route::post('/{id}/promote',       [ShipperManagementController::class, 'promote'])->name('promote');
    Route::post('/{id}/wallet-adjust', [ShipperManagementController::class, 'walletAdjust'])->name('wallet-adjust');
    Route::post('/payouts/{id}/approve', [ShipperManagementController::class, 'approvePayout'])->name('payouts.approve');
    Route::post('/payouts/{id}/reject',  [ShipperManagementController::class, 'rejectPayout'])->name('payouts.reject');

    Route::get('/kyc/index',              [ShipperKycController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/{shipperId}',        [ShipperKycController::class, 'show'])->name('kyc.show');
    Route::post('/kyc/docs/{docId}/approve', [ShipperKycController::class, 'approveDoc'])->name('kyc.approve-doc');
    Route::post('/kyc/docs/{docId}/reject',  [ShipperKycController::class, 'rejectDoc'])->name('kyc.reject-doc');
    Route::get('/kyc/docs/{docId}/download', [ShipperKycController::class, 'downloadDoc'])->name('kyc.download');

    // Ratings moderation
    Route::post('/ratings/{id}/approve', [ShipperManagementController::class, 'approveRating'])->name('ratings.approve');
    Route::post('/ratings/{id}/publish', [ShipperManagementController::class, 'publishRating'])->name('ratings.publish');
});

Route::group([
    'prefix'     => 'admin/shipping-requests',
    'as'         => 'admin.shipping-requests.',
    'middleware' => ['auth', 'role:admin'],
], function () {
    Route::get('/',              [ShippingRequestAdminController::class, 'index'])->name('index');
    Route::post('/generate',     [ShippingRequestAdminController::class, 'generate'])->name('generate');
    Route::get('/create',        [ShippingRequestAdminController::class, 'create'])->name('create');
    Route::post('/',             [ShippingRequestAdminController::class, 'store'])->name('store');
    Route::get('/{id}',          [ShippingRequestAdminController::class, 'show'])->name('show');
    Route::put('/{id}',          [ShippingRequestAdminController::class, 'update'])->name('update');
    Route::post('/{id}/freeze',  [ShippingRequestAdminController::class, 'freeze'])->name('freeze');
    Route::post('/{id}/unfreeze', [ShippingRequestAdminController::class, 'unfreeze'])->name('unfreeze');
    Route::post('/{id}/publish', [ShippingRequestAdminController::class, 'publish'])->name('publish');
    Route::post('/{id}/select-shipper', [ShippingRequestAdminController::class, 'selectShipper'])->name('select-shipper');
    Route::post('/{id}/message', [ShippingRequestAdminController::class, 'sendMessage'])->name('send-message');
    Route::delete('/{id}',       [ShippingRequestAdminController::class, 'cancel'])->name('cancel');
});

Route::group([
    'prefix'     => 'admin/shipper-assignments',
    'as'         => 'admin.shipper-assignments.',
    'middleware' => ['auth', 'role:admin'],
], function () {
    Route::get('/',            [ShipperAssignmentController::class, 'index'])->name('index');
    Route::get('/{id}',        [ShipperAssignmentController::class, 'show'])->name('show');
    Route::get('/proofs/{proofId}/stream', [ShipperAssignmentController::class, 'streamProof'])->name('proofs.stream');
    Route::post('/{id}/proofs/{proofId}/approve', [ShipperAssignmentController::class, 'approveProof'])->name('approve-proof');
    Route::post('/{id}/proofs/{proofId}/reject',  [ShipperAssignmentController::class, 'rejectProof'])->name('reject-proof');
    Route::post('/{id}/forward-address', [ShipperAssignmentController::class, 'forwardAddress'])->name('forward-address');
    Route::post('/{id}/tracking/{tid}/share', [ShipperAssignmentController::class, 'shareTracking'])->name('share-tracking');
    Route::post('/{id}/release-payment', [ShipperAssignmentController::class, 'releasePayment'])->name('release-payment');
    Route::post('/{id}/note', [ShipperAssignmentController::class, 'addNote'])->name('add-note');
});
