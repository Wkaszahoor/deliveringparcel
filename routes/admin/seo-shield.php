<?php

/**
 * Admin routes: SEO Shield (content health analyzer, 2026-09-21).
 * Loaded inside the ['auth', 'role:admin'] group in routes/web.php
 * — same mechanism as the other routes/admin/*.php files.
 */

use App\Http\Controllers\Admin\SeoShieldController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/seo-shield', [SeoShieldController::class, 'index'])->name('admin.seo-shield.index');
Route::post('/admin/seo-shield/analyze', [SeoShieldController::class, 'analyze'])->name('admin.seo-shield.analyze');
Route::get('/admin/seo-shield/analysis/{analysis}', [SeoShieldController::class, 'show'])->name('admin.seo-shield.show');
