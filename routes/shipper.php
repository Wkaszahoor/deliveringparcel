<?php
/* ============================================================
   SHIPPER SYSTEM — Shipper-facing web routes (2026-09-10 series)
   + customer delivery-address submission. Additive only.
   ============================================================ */
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shipper\{
    ShipperDashboardController,
    ShipperRequestController,
    ShipperAssignmentController,
    ShipperRegistrationController
};

// Public: shipper registration
// Public program page + shipper directory (2026-09-19)
Route::get('/shipper-program', [ShipperRegistrationController::class, 'program'])->name('shipper.program');
Route::get('/shippers', [ShipperRegistrationController::class, 'directory'])->name('shippers.directory');

// Public: shipper guide — the long-form "how it works" companion to
// /shipper-program (the application/perks page). Kept separate rather than
// merged into shipper.program so that page's existing escrow/payout/KYC
// product facts (sourced from the live shipper system) are never touched;
// this page covers the broader onboarding + FAQ content supplied separately.
Route::get('/become-a-shipper-guide', function () {
    return view('eshipperguide');
})->name('shipper.guide');
Route::get('/become-a-shipper', [ShipperRegistrationController::class, 'showForm'])
    ->name('shipper.register.form');
Route::post('/become-a-shipper', [ShipperRegistrationController::class, 'register'])
    ->name('shipper.register');

// Customer submits delivery address (after proof approval)
Route::post('/delivery-address/{orderId}', [ShipperAssignmentController::class, 'submitDeliveryAddress'])
    ->name('shipper.delivery-address.submit')->middleware('auth');

// Customer rates the shipper after delivery
Route::post('/rate-shipper/{orderId}', [ShipperAssignmentController::class, 'rateShipper'])
    ->name('shipper.rate')->middleware('auth');

// Customer views approved shipper proof photos (private disk, streamed)
Route::get('/order-proofs/{orderId}/{proofId}', [\App\Http\Controllers\Api\Client\ShipperClientApiController::class, 'proofFile'])
    ->name('shipper.proofs.customer')->middleware('auth');

// Authenticated shipper area
Route::middleware(['auth', 'role:shipper,shipper_pending', 'shipper.profile'])
    ->prefix('shipper')
    ->name('shipper.')
    ->group(function () {

        Route::get('/dashboard', [ShipperDashboardController::class, 'index'])->name('dashboard');

        Route::post('/kyc/upload', [ShipperRegistrationController::class, 'uploadKyc'])
            ->name('kyc.upload');

        // Countries change requests — allowed for pending shippers too (admin approves later).
        Route::get('/countries', [ShipperAssignmentController::class, 'countries'])->name('countries');
        Route::post('/countries', [ShipperAssignmentController::class, 'submitCountryRequest'])->name('countries.request');

        // Profile — pending shippers may manage it too (whitelisted in RequireShipperProfile).
        Route::get('/profile', [ShipperAssignmentController::class, 'myProfile'])->name('profile');
        Route::put('/profile', [ShipperAssignmentController::class, 'updateProfile'])->name('profile.update');

        Route::middleware('role:shipper')->group(function () {
            Route::get('/requests/data',  [ShipperRequestController::class, 'data'])->name('requests.data');            Route::get('/requests',       [ShipperRequestController::class, 'index'])->name('requests.index');
            Route::get('/requests/{id}',  [ShipperRequestController::class, 'show'])->name('requests.show');
            Route::post('/requests/{id}/quote', [ShipperRequestController::class, 'submitQuote'])->name('requests.quote');
            Route::delete('/quotes/{id}', [ShipperRequestController::class, 'withdrawQuote'])->name('quotes.withdraw');
            Route::get('/my-quotes',      [ShipperRequestController::class, 'myQuotes'])->name('quotes.index');

            Route::get('/assignments/data', [ShipperAssignmentController::class, 'data'])->name('assignments.data');
            Route::get('/wallet/data',      [ShipperAssignmentController::class, 'walletData'])->name('wallet.data');            Route::get('/assignments',      [ShipperAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('/assignments/{id}', [ShipperAssignmentController::class, 'show'])->name('assignments.show');
            Route::post('/assignments/{id}/proof',    [ShipperAssignmentController::class, 'uploadProof'])->name('assignments.proof');
            Route::post('/assignments/{id}/tracking', [ShipperAssignmentController::class, 'submitTracking'])->name('assignments.tracking');
            Route::post('/assignments/{id}/purchased', [ShipperAssignmentController::class, 'markPurchased'])->name('assignments.purchased');
            Route::post('/assignments/{id}/package-received', [ShipperAssignmentController::class, 'markPackageReceived'])->name('assignments.package-received');

            Route::get('/wallet',         [ShipperAssignmentController::class, 'walletHistory'])->name('wallet');
            Route::post('/wallet/payout', [ShipperAssignmentController::class, 'requestPayout'])->name('wallet.payout');

        });
    });
