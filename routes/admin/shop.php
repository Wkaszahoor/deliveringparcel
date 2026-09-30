<?php

/**
 * Admin routes: SHOP module — products, categories, reviews,
 * coupons, orders, analytics.
 */

use App\Http\Controllers\Admin\Shop\ProductController;
use App\Http\Controllers\Admin\Shop\ShopExtraController;

/* Products */
/* CMS ENDGAME 2026-09-19: products UI retired into CMS (cart still reads shop_products). */
Route::redirect('admin/shop/products', '/admin/cms/posts?type=product', 301)->name('admin.shop.products.index');
Route::redirect('admin/shop/products/{any}', '/admin/cms/posts?type=product', 301)->where('any', '.*');
/* CMS ENDGAME 2026-09-19: products UI retired into CMS (cart still reads shop_products). */
Route::redirect('admin/shop/products', '/admin/cms/posts?type=product', 301)->name('admin.shop.products.index');
Route::redirect('admin/shop/products/{any}', '/admin/cms/posts?type=product', 301)->where('any', '.*');
/* CMS ENDGAME 2026-09-19: products UI retired into CMS (cart still reads shop_products). */
Route::redirect('admin/shop/products', '/admin/cms/posts?type=product', 301)->name('admin.shop.products.index');
Route::redirect('admin/shop/products/{any}', '/admin/cms/posts?type=product', 301)->where('any', '.*');
/* CMS ENDGAME 2026-09-19: products UI retired into CMS (cart still reads shop_products). */
Route::redirect('admin/shop/products', '/admin/cms/posts?type=product', 301)->name('admin.shop.products.index');
Route::redirect('admin/shop/products/{any}', '/admin/cms/posts?type=product', 301)->where('any', '.*');
/* CMS ENDGAME 2026-09-19: products UI retired into CMS (cart still reads shop_products). */
Route::redirect('admin/shop/products', '/admin/cms/posts?type=product', 301)->name('admin.shop.products.index');
Route::redirect('admin/shop/products/{any}', '/admin/cms/posts?type=product', 301)->where('any', '.*');
/* CMS ENDGAME 2026-09-19: products UI retired into CMS (cart still reads shop_products). */
Route::redirect('admin/shop/products', '/admin/cms/posts?type=product', 301)->name('admin.shop.products.index');
Route::redirect('admin/shop/products/{any}', '/admin/cms/posts?type=product', 301)->where('any', '.*');
/* CMS ENDGAME 2026-09-19: products UI retired into CMS (cart still reads shop_products). */
Route::redirect('admin/shop/products', '/admin/cms/posts?type=product', 301)->name('admin.shop.products.index');
Route::redirect('admin/shop/products/{any}', '/admin/cms/posts?type=product', 301)->where('any', '.*');

/* Categories */
Route::get('admin/shop/categories', [ShopExtraController::class, 'categoriesIndex'])->name('admin.shop.categories.index');
Route::get('admin/shop/categories/data', [ShopExtraController::class, 'categoriesData'])->name('admin.shop.categories.data');
Route::get('admin/shop/categories/create', [ShopExtraController::class, 'categoriesCreate'])->name('admin.shop.categories.create');
Route::post('admin/shop/categories', [ShopExtraController::class, 'categoriesStore'])->name('admin.shop.categories.store');
Route::get('admin/shop/categories/{category}/edit', [ShopExtraController::class, 'categoriesEdit'])->name('admin.shop.categories.edit');
Route::put('admin/shop/categories/{category}', [ShopExtraController::class, 'categoriesUpdate'])->name('admin.shop.categories.update');
Route::delete('admin/shop/categories/{category}', [ShopExtraController::class, 'categoriesDestroy'])->name('admin.shop.categories.destroy');

/* Reviews */
Route::get('admin/shop/reviews', [ShopExtraController::class, 'reviewsIndex'])->name('admin.shop.reviews.index');
Route::get('admin/shop/reviews/data', [ShopExtraController::class, 'reviewsData'])->name('admin.shop.reviews.data');
Route::put('admin/shop/reviews/{review}/approve', [ShopExtraController::class, 'reviewApprove'])->name('admin.shop.reviews.approve');
Route::put('admin/shop/reviews/{review}/reject', [ShopExtraController::class, 'reviewReject'])->name('admin.shop.reviews.reject');
Route::delete('admin/shop/reviews/{review}', [ShopExtraController::class, 'reviewDestroy'])->name('admin.shop.reviews.destroy');

/* Coupons */
Route::get('admin/shop/coupons', [ShopExtraController::class, 'couponsIndex'])->name('admin.shop.coupons.index');
Route::get('admin/shop/coupons/data', [ShopExtraController::class, 'couponsData'])->name('admin.shop.coupons.data');
Route::get('admin/shop/coupons/create', [ShopExtraController::class, 'couponsCreate'])->name('admin.shop.coupons.create');
Route::post('admin/shop/coupons', [ShopExtraController::class, 'couponsStore'])->name('admin.shop.coupons.store');
Route::get('admin/shop/coupons/{coupon}/edit', [ShopExtraController::class, 'couponsEdit'])->name('admin.shop.coupons.edit');
Route::put('admin/shop/coupons/{coupon}', [ShopExtraController::class, 'couponsUpdate'])->name('admin.shop.coupons.update');
Route::delete('admin/shop/coupons/{coupon}', [ShopExtraController::class, 'couponsDestroy'])->name('admin.shop.coupons.destroy');

/* Orders */
Route::get('admin/shop/orders', [ShopExtraController::class, 'ordersIndex'])->name('admin.shop.orders.index');
Route::get('admin/shop/orders/data', [ShopExtraController::class, 'ordersData'])->name('admin.shop.orders.data');
// PM-015: per-shop-order forced payment gateway (must precede the {order} show route).
Route::post('admin/shop/orders/{order}/payment-method', [ShopExtraController::class, 'updatePaymentMethod'])->name('admin.shop.orders.payment-method');
Route::get('admin/shop/orders/{order}', [ShopExtraController::class, 'ordersShow'])->name('admin.shop.orders.show');

/* Analytics */
Route::get('admin/shop/analytics', [ShopExtraController::class, 'analytics'])->name('admin.shop.analytics');
