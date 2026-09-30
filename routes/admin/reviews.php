<?php

/**
 * Admin routes: REVIEWS module (RV-001..RV-008).
 * Loaded inside the ['auth', 'role:admin'] group in routes/web.php.
 */

use App\Http\Controllers\Admin\Reviews\ReviewController;

/* List + JSON data endpoint (RV-004 / RV-005) */
Route::get('admin/reviews', [ReviewController::class, 'index'])->name('admin.reviews.index');
Route::get('admin/reviews/data', [ReviewController::class, 'data'])->name('admin.reviews.data');

/* Saved filter presets (RV-005) — static segments BEFORE the {review} wildcard */
Route::post('admin/reviews/presets', [ReviewController::class, 'presetStore'])->name('admin.reviews.presets.store');
Route::get('admin/reviews/presets/{preset}/load', [ReviewController::class, 'presetLoad'])->name('admin.reviews.presets.load')->where('preset', '[0-9]+');
Route::delete('admin/reviews/presets/{preset}', [ReviewController::class, 'presetDestroy'])->name('admin.reviews.presets.destroy')->where('preset', '[0-9]+');

/* Single-request bulk moderation (RV-004) */
Route::post('admin/reviews/bulk', [ReviewController::class, 'bulk'])->name('admin.reviews.bulk');

/* Detail */
Route::get('admin/reviews/{review}', [ReviewController::class, 'show'])->name('admin.reviews.show')->where('review', '[0-9]+');

/* Moderation lifecycle (RV-003) */
Route::put('admin/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('admin.reviews.approve')->where('review', '[0-9]+');
Route::put('admin/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('admin.reviews.reject')->where('review', '[0-9]+');
Route::put('admin/reviews/{review}/hide', [ReviewController::class, 'hide'])->name('admin.reviews.hide')->where('review', '[0-9]+');
Route::put('admin/reviews/{review}/restore', [ReviewController::class, 'restore'])->name('admin.reviews.restore')->where('review', '[0-9]+');
Route::put('admin/reviews/{review}/spam', [ReviewController::class, 'spam'])->name('admin.reviews.spam')->where('review', '[0-9]+');

/* Featured toggle + delete (RV-004) */
Route::put('admin/reviews/{review}/feature', [ReviewController::class, 'feature'])->name('admin.reviews.feature')->where('review', '[0-9]+');
Route::put('admin/reviews/{review}/unfeature', [ReviewController::class, 'unfeature'])->name('admin.reviews.unfeature')->where('review', '[0-9]+');
Route::delete('admin/reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy')->where('review', '[0-9]+');
