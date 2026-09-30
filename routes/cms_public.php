<?php

/* ═══════════════════════════════════════════════════════════════════════════
   CMS Public Frontend — 2026-09-03
   ═══════════════════════════════════════════════════════════════════════════
   This file is appended from routes/web.php context WITHOUT the /api prefix,
   so all routes here are plain ROOT-LEVEL routes.

   ONLY /page/{slug} is mounted now (it supersedes the undeployed
   DynamicPageController route).

   The /blog, /services and /shop CMS groups below are PRE-WIRED BUT
   COMMENTED. They currently collide with live legacy routes:
       /blog, /blog/{slug}            → BlogController (BlogPost import system)
       /services, /services/{slug}    → Home2Controller
       /shop, /shop/{slug}            → Home2 ShopController
   ▶ Uncomment each group ONLY after migrating that content into cms_posts
     (see README). Do NOT mount a group while its legacy routes are live.
   ═══════════════════════════════════════════════════════════════════════════ */

use Illuminate\Support\Facades\Route;

// ─── Static Pages (Contact, Terms, Privacy, FAQ, Careers, …) — MOUNTED ─────
/* /page/{slug} serves CMS-first with DynamicPage fallback: a slug present in
   cms_posts renders the CMS page; otherwise an active dynamic_pages row
   renders the Dynamic Pages system; otherwise 404. */
Route::get('/page/{slug}', function (string $slug) {
    $cms = \App\Models\CmsPost::ofType('page')->published()->where('slug', $slug)->first();
    if ($cms) {
        return app(\App\Http\Controllers\CmsController::class)->pageShow($slug);
    }

    $dp = \App\Models\DynamicPage::where('slug', $slug)->where('is_active', true)->first();
    if ($dp) {
        return app(\App\Http\Controllers\DynamicPageController::class)->show(request(), $slug);
    }

    abort(404);
})->name('cms.page.show');

// ─── Blog — PRE-WIRED, COMMENTED ─────────────────────────────────────────────
// ACTIVATE: migrate legacy BlogPost/BlogCategory content into cms_posts
// (post_type=blog_post) and point nav links at the CMS routes, then uncomment.
Route::prefix('blog')->name('cms.blog.')->group(function () {
    Route::get('/',                 [\App\Http\Controllers\CmsController::class, 'blogIndex'])   ->name('index');
    Route::get('/category/{slug}',  [\App\Http\Controllers\CmsController::class, 'blogCategory'])->name('category');
    Route::get('/tag/{slug}',       [\App\Http\Controllers\CmsController::class, 'blogTag'])     ->name('tag');
    Route::get('/{slug}',           [\App\Http\Controllers\CmsController::class, 'blogShow'])    ->name('show');
});

// ─── Services — PRE-WIRED, COMMENTED ─────────────────────────────────────────
// ACTIVATE: migrate services into cms_posts (post_type=service), then uncomment.
Route::prefix('services')->name('cms.services.')->group(function () {
    Route::get('/',         [\App\Http\Controllers\CmsController::class, 'servicesIndex'])->name('index');
    Route::get('/{slug}',   [\App\Http\Controllers\CmsController::class, 'serviceShow'])  ->name('show');
});

// ─── Shop — PRE-WIRED, COMMENTED ─────────────────────────────────────────────
// ACTIVATE: migrate products into cms_posts (post_type=product), then uncomment.
// Route::prefix('shop')->name('cms.shop.')->group(function () {
//     Route::get('/',         [\App\Http\Controllers\CmsController::class, 'shopIndex'])   ->name('index');
//     Route::get('/{slug}',   [\App\Http\Controllers\CmsController::class, 'productShow']) ->name('product');
// });
