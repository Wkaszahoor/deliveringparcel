<?php

use App\Http\Controllers\Admin\PayoneerController as AdminPayoneerController;
use App\Http\Controllers\PayoneerController;
use Illuminate\Support\Facades\Route;

/*
 * Payoneer link-payment flow (2026-09-12) — fully additive.
 * Client flow lives on its own page (/payoneer/{order}) so the legacy order
 * payment tab, renderPaymentMethods JS and payment_methods table are untouched.
 */

Route::middleware(['auth'])->prefix('payoneer')->group(function () {
    Route::get('{order}', [PayoneerController::class, 'show'])->name('payoneer.show');
    Route::post('{order}/request', [PayoneerController::class, 'requestLink'])->middleware('throttle:10,1')->name('payoneer.request');
    Route::post('{order}/proof', [PayoneerController::class, 'uploadProof'])->middleware('throttle:10,1')->name('payoneer.proof');
    Route::post('{order}/mark-paid', [PayoneerController::class, 'markPaid'])->middleware('throttle:10,1')->name('payoneer.markPaid');
    Route::post('{order}/cancel', [PayoneerController::class, 'cancel'])->middleware('throttle:10,1')->name('payoneer.cancel');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin/payoneer')->group(function () {
    Route::get('/', [AdminPayoneerController::class, 'index'])->name('admin.payoneer.index');
    Route::post('settings', [AdminPayoneerController::class, 'settings'])->name('admin.payoneer.settings');
    Route::post('{payoneerRequest}/link', [AdminPayoneerController::class, 'sendLink'])->name('admin.payoneer.link');
    Route::post('{payoneerRequest}/verify', [AdminPayoneerController::class, 'verify'])->name('admin.payoneer.verify');
    Route::post('{payoneerRequest}/cancel', [AdminPayoneerController::class, 'cancel'])->name('admin.payoneer.cancel');
});
