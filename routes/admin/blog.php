<?php

/**
 * Admin routes: Blog Import System (2026-09-02) — imported/new-design
 * blog posts + WordPress import. Loaded inside the ['auth', 'role:admin']
 * group in routes/web.php (middleware repeated here for safety).
 *
 * PREFIX NOTE: /admin/blog* was already taken by the LEGACY blog module
 * (admin/blogs, admin/blog-categories — routes/admin/content.php), so
 * this system uses /admin/blog-posts + /admin/blog-import with the
 * matching name prefixes admin.blog-posts.* / admin.blog-import.*.
 */

use App\Http\Controllers\Admin\BlogImportController;
use App\Http\Controllers\Admin\BlogPostAdminController;

Route::prefix('admin/blog-import')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.blog-import.')
    ->group(function () {
        Route::get('/', [BlogImportController::class, 'index'])->name('index');
        Route::post('/upload', [BlogImportController::class, 'upload'])->name('upload');
        Route::get('/log/{log}', [BlogImportController::class, 'showLog'])->name('log');
        Route::delete('/log/{log}', [BlogImportController::class, 'deleteLog'])->name('log.delete');
    });

/* CMS ENDGAME 2026-09-19: blog-posts CRUD retired into CMS. */
Route::redirect('admin/blog-posts', '/admin/cms/posts?type=blog_post', 301)->name('admin.blog-posts.index');
Route::redirect('admin/blog-posts/{any}', '/admin/cms/posts?type=blog_post', 301)->where('any', '.*');
