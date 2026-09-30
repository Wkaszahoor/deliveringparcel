<?php

/**
 * Admin routes: CONTENT modules — Blog, Services, Hero Slider,
 * Countries, Weight Units (Agent C owns this file).
 * Loaded inside the ['auth', 'role:admin'] group in routes/web.php.
 */

use App\Http\Controllers\Admin\Blog\BlogCategoryController;
use App\Http\Controllers\Admin\Blog\BlogController;
use App\Http\Controllers\Admin\Countries\CountryController;
use App\Http\Controllers\Admin\HeroSlider\HeroSlideController;
use App\Http\Controllers\Admin\Services\ServiceCategoryController;
use App\Http\Controllers\Admin\Services\ServiceController;
use App\Http\Controllers\Admin\WeightUnits\WeightUnitController;

/* ------------------------------------------------------------------
 |  Blog posts
 * ------------------------------------------------------------------ */
/* CMS ENDGAME 2026-09-19: blogs/services UIs retired into CMS (cms:consolidate-content).
   Routes kept as named redirects so old links/shortcuts land on the CMS list. */
Route::redirect('admin/blogs', '/admin/cms/posts?type=blog_post', 301)->name('admin.blogs.index');
Route::redirect('admin/blogs/{any}', '/admin/cms/posts?type=blog_post', 301)->where('any', '.*');
/* retired → admin.cms.posts (was Route::resource admin/blogs) */

/* ------------------------------------------------------------------
 |  Blog categories
 * ------------------------------------------------------------------ */
Route::get('admin/blog-categories/data', [BlogCategoryController::class, 'data'])->name('admin.blog-categories.data');
Route::resource('admin/blog-categories', BlogCategoryController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('admin.blog-categories');

/* ------------------------------------------------------------------
 |  Services
 * ------------------------------------------------------------------ */
Route::redirect('admin/services', '/admin/cms/posts?type=service', 301)->name('admin.services.index');
Route::redirect('admin/services/{any}', '/admin/cms/posts?type=service', 301)->where('any', '.*');
/* retired → admin.cms.posts (was Route::resource admin/services) */

/* ------------------------------------------------------------------
 |  Service categories
 * ------------------------------------------------------------------ */
Route::get('admin/service-categories/data', [ServiceCategoryController::class, 'data'])->name('admin.service-categories.data');
Route::resource('admin/service-categories', ServiceCategoryController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('admin.service-categories');

/* ------------------------------------------------------------------
 |  Hero slider
 * ------------------------------------------------------------------ */
Route::get('admin/hero-slides/data', [HeroSlideController::class, 'data'])->name('admin.hero-slides.data');
Route::post('admin/hero-slides/{hero_slide}/toggle', [HeroSlideController::class, 'toggle'])->name('admin.hero-slides.toggle');
Route::post('admin/hero-slides/{hero_slide}/move/{direction}', [HeroSlideController::class, 'move'])->name('admin.hero-slides.move');
Route::resource('admin/hero-slides', HeroSlideController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('admin.hero-slides');

/* ------------------------------------------------------------------
 |  Countries
 * ------------------------------------------------------------------ */
/* ---------------- Image Optimizer (Smush-style, local) ---------------- */

Route::get('admin/image-optimizer', [\App\Http\Controllers\Admin\Media\ImageOptimizerController::class, 'index'])->name('admin.image-optimizer.index');
Route::get('admin/image-optimizer/data', [\App\Http\Controllers\Admin\Media\ImageOptimizerController::class, 'data'])->name('admin.image-optimizer.data');
Route::post('admin/image-optimizer/optimize', [\App\Http\Controllers\Admin\Media\ImageOptimizerController::class, 'optimize'])->name('admin.image-optimizer.optimize');
Route::post('admin/image-optimizer/bulk', [\App\Http\Controllers\Admin\Media\ImageOptimizerController::class, 'bulk'])->name('admin.image-optimizer.bulk');

Route::get('admin/countries/data', [CountryController::class, 'data'])->name('admin.countries.data');
Route::post('admin/countries/{country}/toggle', [CountryController::class, 'toggle'])->name('admin.countries.toggle');
Route::resource('admin/countries', CountryController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('admin.countries');

/* ------------------------------------------------------------------
 |  Weight units
 * ------------------------------------------------------------------ */
Route::get('admin/weight-units/data', [WeightUnitController::class, 'data'])->name('admin.weight-units.data');
Route::post('admin/weight-units/{weight_unit}/default', [WeightUnitController::class, 'makeDefault'])->name('admin.weight-units.default');
Route::resource('admin/weight-units', WeightUnitController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('admin.weight-units');
