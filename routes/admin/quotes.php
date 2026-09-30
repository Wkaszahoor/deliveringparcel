<?php

/**
 * Admin routes: QUOTES + OFFERS + RETURNS & CLAIMS.
 */

use App\Http\Controllers\Admin\Quotes\QuoteController;
use App\Http\Controllers\Admin\Returns\ReturnsController;

/* Quote inbox */
Route::get('admin/quotes', [QuoteController::class, 'quotesIndex'])->name('admin.quotes.index');
Route::get('admin/quotes/data', [QuoteController::class, 'quotesData'])->name('admin.quotes.data');
Route::get('admin/quotes/{id}', [QuoteController::class, 'show'])->name('admin.quotes.show')->where('id', '[0-9]+');
/* PM-015: mark the gateway the quote's customer must pay with. */
Route::post('admin/quotes/{id}/payment-method', [QuoteController::class, 'updatePaymentMethod'])->name('admin.quotes.payment-method')->where('id', '[0-9]+');

/* Offers / negotiation */
Route::get('admin/quotes/offers', [QuoteController::class, 'offersIndex'])->name('admin.quotes.offers');
Route::get('admin/quotes/offers/data', [QuoteController::class, 'offersData'])->name('admin.quotes.offers.data');

/* Returns */
Route::get('admin/returns', [ReturnsController::class, 'returnsIndex'])->name('admin.returns.index');
Route::get('admin/returns/data', [ReturnsController::class, 'returnsData'])->name('admin.returns.data');

/* Claims (must precede the {return} wildcard) */
Route::get('admin/returns/claims', [ReturnsController::class, 'claimsIndex'])->name('admin.returns.claims');
Route::get('admin/returns/claims/data', [ReturnsController::class, 'claimsData'])->name('admin.returns.claims.data');
Route::get('admin/returns/claims/{claim}', [ReturnsController::class, 'claimShow'])->name('admin.returns.claims-show');
Route::put('admin/returns/claims/{claim}', [ReturnsController::class, 'claimUpdate'])->name('admin.returns.claims-update');

Route::get('admin/returns/{return}', [ReturnsController::class, 'show'])->name('admin.returns.show');
Route::put('admin/returns/{return}/status', [ReturnsController::class, 'updateStatus'])->name('admin.returns.status');
