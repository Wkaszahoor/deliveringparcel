<?php

/**
 * Admin routes: TOOLS — global search, system health, DB viewer,
 * PDF exports, testimonials.
 */

use App\Http\Controllers\Admin\Tools\ToolsController;

Route::get('admin/tools/search', [ToolsController::class, 'search'])->name('admin.tools.search');
Route::get('admin/tools/search.json', [ToolsController::class, 'searchJson'])->name('admin.tools.search-json');
Route::get('admin/tools/health', [ToolsController::class, 'health'])->name('admin.tools.health');
Route::get('admin/tools/database', [ToolsController::class, 'database'])->name('admin.tools.database');
Route::get('admin/tools/database/{table}', [ToolsController::class, 'browse'])->name('admin.tools.database-browse')->where('table', '[a-zA-Z0-9_]+');

Route::get('admin/tools/pdf', [ToolsController::class, 'pdfIndex'])->name('admin.tools.pdf');
Route::get('admin/tools/pdf/invoice/{offerOrderId}', [ToolsController::class, 'pdfInvoice'])->name('admin.tools.pdf-invoice');
Route::get('admin/tools/pdf/order/{orderId}', [ToolsController::class, 'pdfOrder'])->name('admin.tools.pdf-order');
Route::get('admin/tools/pdf/financial-report', [ToolsController::class, 'pdfFinancial'])->name('admin.tools.pdf-financial');

Route::get('admin/testimonials', [ToolsController::class, 'testimonialsIndex'])->name('admin.testimonials.index');
Route::get('admin/testimonials/data', [ToolsController::class, 'testimonialsData'])->name('admin.testimonials.data');
Route::get('admin/testimonials/create', [ToolsController::class, 'testimonialsCreate'])->name('admin.testimonials.create');
Route::post('admin/testimonials', [ToolsController::class, 'testimonialsStore'])->name('admin.testimonials.store');
Route::get('admin/testimonials/{testimonial}/edit', [ToolsController::class, 'testimonialsEdit'])->name('admin.testimonials.edit');
Route::put('admin/testimonials/{testimonial}', [ToolsController::class, 'testimonialsUpdate'])->name('admin.testimonials.update');
Route::delete('admin/testimonials/{testimonial}', [ToolsController::class, 'testimonialsDestroy'])->name('admin.testimonials.destroy');
Route::post('admin/testimonials/{testimonial}/move/{direction}', [ToolsController::class, 'testimonialsMove'])->name('admin.testimonials.move');
Route::post('admin/testimonials/{testimonial}/toggle-publish', [ToolsController::class, 'testimonialsTogglePublish'])->name('admin.testimonials.toggle-publish');
Route::post('admin/testimonials/platforms', [ToolsController::class, 'testimonialsUpdatePlatforms'])->name('admin.testimonials.platforms');
Route::post('admin/testimonials/sync-google', [ToolsController::class, 'testimonialsSyncGoogle'])->name('admin.testimonials.sync-google');
