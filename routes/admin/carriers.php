<?php

/**
 * Admin routes: CARRIERS — adapter registry cards, tracking-number parser,
 * aggregated tracking, lookup history.
 */

use App\Http\Controllers\Admin\Carriers\CarrierController;

Route::get('admin/carriers', [CarrierController::class, 'index'])->name('admin.carriers.index');
Route::get('admin/carriers/ping/{code}', [CarrierController::class, 'ping'])->name('admin.carriers.ping')->where('code', '[a-z0-9]+');
Route::post('admin/carriers/toggle/{code}', [CarrierController::class, 'toggle'])->name('admin.carriers.toggle')->where('code', '[a-z0-9]+');
Route::get('admin/carriers/parse', [CarrierController::class, 'parseForm'])->name('admin.carriers.parse');
Route::post('admin/carriers/parse', [CarrierController::class, 'parse'])->name('admin.carriers.parse.post');
Route::get('admin/carriers/track', [CarrierController::class, 'trackForm'])->name('admin.carriers.track');
Route::get('admin/carriers/history', [CarrierController::class, 'history'])->name('admin.carriers.history');
