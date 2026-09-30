<?php

/**
 * Admin routes: DASHBOARD + ANALYTICS module.
 * Controllers were salvaged from the first agent pass; views wired in-place.
 */

use App\Http\Controllers\Admin\Analytics\DashboardController;
use App\Http\Controllers\Admin\Analytics\RevenueController;
use App\Http\Controllers\Admin\Analytics\DemandController;
use App\Http\Controllers\Admin\Analytics\RfmController;

Route::get('admin/dashbord2', [DashboardController::class, 'index'])->name('admin.dashboard2');
Route::get('admin/analytics/dashboard', [DashboardController::class, 'index'])->name('admin.analytics.dashboard');
Route::get('admin/analytics/data', [DashboardController::class, 'data'])->name('admin.analytics.data');

Route::get('admin/analytics/revenue', [RevenueController::class, 'index'])->name('admin.analytics.revenue');
Route::get('admin/analytics/revenue/data', [RevenueController::class, 'data'])->name('admin.analytics.revenue.data');

Route::get('admin/analytics/demand', [DemandController::class, 'index'])->name('admin.analytics.demand');
Route::get('admin/analytics/demand/data', [DemandController::class, 'data'])->name('admin.analytics.demand.data');

Route::get('admin/analytics/rfm', [RfmController::class, 'index'])->name('admin.analytics.rfm');
Route::get('admin/analytics/rfm/data', [RfmController::class, 'data'])->name('admin.analytics.rfm.data');
