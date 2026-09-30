<?php

use App\Http\Controllers\Admin\Cms\{
    CmsPostAdminController,
    CmsTaxonomyAdminController,
    CmsMenuAdminController,
    CmsMediaAdminController
};
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => 'admin/cms',
    // 'as' (not 'name'): Laravel 8 only honours 'as' as a group name prefix
    'as'         => 'admin.cms.',
    'middleware' => ['auth', 'role:admin'],
], function () {

    // ── Posts ──────────────────────────────────────────────────────────
    Route::get('/posts',                [CmsPostAdminController::class, 'index'])       ->name('posts.index');
    Route::get('/posts/create',         [CmsPostAdminController::class, 'create'])      ->name('posts.create');
    Route::post('/posts',               [CmsPostAdminController::class, 'store'])       ->name('posts.store');
    Route::get('/posts/{cmsPost}/edit', [CmsPostAdminController::class, 'edit'])        ->name('posts.edit');
    Route::put('/posts/{cmsPost}',      [CmsPostAdminController::class, 'update'])      ->name('posts.update');
    Route::get('/posts/{cmsPost}/seo',  [CmsPostAdminController::class, 'seo'])          ->name('posts.seo');
    Route::get('/posts/{cmsPost}/autofill', [CmsPostAdminController::class, 'autofill'])     ->name('posts.autofill');
    Route::post('/posts/{cmsPost}/autofix', [CmsPostAdminController::class, 'autofixApply'])  ->name('posts.autofix');
    Route::post('/posts/{cmsPost}/toggle-status',
                                        [CmsPostAdminController::class, 'toggleStatus'])->name('posts.toggle');
    Route::delete('/posts/{cmsPost}',   [CmsPostAdminController::class, 'destroy'])     ->name('posts.destroy');
    Route::post('/posts/restore/{id}',  [CmsPostAdminController::class, 'restore'])     ->name('posts.restore');
    Route::delete('/posts/force/{id}',  [CmsPostAdminController::class, 'forceDelete']) ->name('posts.force-delete');
    Route::post('/posts/bulk',          [CmsPostAdminController::class, 'bulk'])        ->name('posts.bulk');

    // ── Taxonomies ─────────────────────────────────────────────────────
    Route::get('/taxonomies',           [CmsTaxonomyAdminController::class, 'index'])   ->name('taxonomies.index');
    Route::get('/taxonomies/create',    [CmsTaxonomyAdminController::class, 'create'])  ->name('taxonomies.create');
    Route::post('/taxonomies',          [CmsTaxonomyAdminController::class, 'store'])   ->name('taxonomies.store');
    Route::get('/taxonomies/{cmsTaxonomy}/edit',
                                        [CmsTaxonomyAdminController::class, 'edit'])    ->name('taxonomies.edit');
    Route::put('/taxonomies/{cmsTaxonomy}',
                                        [CmsTaxonomyAdminController::class, 'update'])  ->name('taxonomies.update');
    Route::delete('/taxonomies/{cmsTaxonomy}',
                                        [CmsTaxonomyAdminController::class, 'destroy']) ->name('taxonomies.destroy');

    // ── Menus ──────────────────────────────────────────────────────────
    Route::get('/menus',                        [CmsMenuAdminController::class, 'index'])     ->name('menus.index');
    Route::get('/menus/{menu}',                 [CmsMenuAdminController::class, 'show'])      ->name('menus.show');
    Route::post('/menus/{menu}/items',          [CmsMenuAdminController::class, 'addItem'])   ->name('menus.add-item');
    Route::post('/menus/{menu}/reorder',        [CmsMenuAdminController::class, 'reorder'])   ->name('menus.reorder');
    Route::put('/menus/{menu}/items/{item}',    [CmsMenuAdminController::class, 'editItem'])  ->name('menus.edit-item');
    Route::delete('/menus/{menu}/items/{item}', [CmsMenuAdminController::class, 'removeItem'])->name('menus.remove-item');

    // ── Media ──────────────────────────────────────────────────────────
    Route::get('/media',            [CmsMediaAdminController::class, 'index'])  ->name('media.index');
    Route::post('/media/upload',    [CmsMediaAdminController::class, 'upload']) ->name('media.upload');
    Route::get('/media/browse',     [CmsMediaAdminController::class, 'browse']) ->name('media.browse');
    Route::put('/media/{media}',    [CmsMediaAdminController::class, 'update']) ->name('media.update');
    Route::delete('/media/{media}', [CmsMediaAdminController::class, 'destroy'])->name('media.destroy');
});
