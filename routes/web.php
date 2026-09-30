<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::group(['middleware' => ['auth', 'role:admin']], function () {
    route::get('admin-dashbord', function () {
        return view('admin.home');
    })->name('admin-dashbord');

    // admin-dashbord (typo) above is the legacy dashboard; the executive
    // KPI dashboard lives at admin-dashboard2 (registered further down
    // next to admin-orders). admin-dashboard below keeps its ORIGINAL
    // behavior: redirect to the legacy dashboard.

	    
    
    // april 16

//

    Route::get('admin-orderss',     [App\Http\Controllers\AdminorderssController::class, 'index'])->name('admin-orderss');
       Route::get('admin-orderss',     [App\Http\Controllers\AdminorderssController::class, 'index2'])->name('admin-orderss');
    route::get('admin-orders',      [App\Http\Controllers\AdminordersController::class, 'index'])->name('admin-orders');
    // Fixed spelling - redirect to legacy dashboard (original behavior restored)
    Route::get('admin-dashboard', function () {
        return redirect()->route('admin-dashbord');
    })->name('admin-dashboard');

    // Executive KPI dashboard — moved here from admin-dashboard so the
    // old admin-dashboard link keeps redirecting to the legacy dashboard.
    route::get('admin-dashboard2', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('admin-dashboard2');
    route::resource ('adminorders', 'App\Http\Controllers\AdminordersController');
 //   route::resource ('adminorderss', 'App\Http\Controllers\AdminorderssController');
    route::get('order/{id}', [App\Http\Controllers\AdminordersController::class, 'showaddress'])->name('order');
    route::get('clients', [App\Http\Controllers\ClientsController::class, 'index'])->name('clients');
    route::get('show_details/{id}', [App\Http\Controllers\ClientsController::class, 'show'])->name('show_details');
    route::post('image_upload/{id}', [App\Http\Controllers\OrdersController::class, 'image_upload'])->name('image_upload');
    route::post('purchase_image/{id}', [App\Http\Controllers\OrderOfferController::class, 'purchase_image'])->name('purchase_image');
    route::resource('orderoffer', 'App\Http\Controllers\OrderOfferController');
    route::post('tracking_link/{id}', [App\Http\Controllers\OrderOfferController::class, 'tracking_link'])->name('tracking_link');
    route::post('order_tracking/{id}', [App\Http\Controllers\OrdersController::class, 'order_tracking'])->name('order_tracking');
    Route::get('avatar', [App\Http\Controllers\AdminordersController::class, 'Avatar_index'])->name('avatar');
    Route::post('avatar', [App\Http\Controllers\AdminordersController::class, 'Avatar_update']);
    Route::get('Admin_password', [App\Http\Controllers\AdminordersController::class, 'password'])->name('Admin_password');
    Route::put('Admin_password', [App\Http\Controllers\AdminordersController::class, 'changePassword']);
    Route::post('countryaddress', [App\Http\Controllers\AdminordersController::class, 'countryaddress'])->name("countryaddress");
    Route::resource('address', 'App\Http\Controllers\AddressController');
    Route::put('edit_offer', [App\Http\Controllers\AdminordersController::class, 'edit_offer'])->name('edit_offer');
    Route::get('admin_notifications', function () {
        return view('admin.admin_notifications');
    })->name('admin_notifications');

    // Admin inbox — paginated notifications & messages with filters
    Route::get('admin/inbox/notifications', [App\Http\Controllers\Admin\InboxController::class, 'notificationsPage'])->name('admin.inbox.notifications');
    Route::get('admin/inbox/messages', [App\Http\Controllers\Admin\InboxController::class, 'messagesPage'])->name('admin.inbox.messages');
    Route::get('admin/inbox/feed', [App\Http\Controllers\Admin\InboxController::class, 'feed'])->name('admin.inbox.feed');
    Route::post('admin/inbox/messages/reply', [App\Http\Controllers\Admin\InboxController::class, 'reply'])->name('admin.inbox.reply');
    Route::post('admin/inbox/notifications/mark-all-read', [App\Http\Controllers\Admin\InboxController::class, 'markAllRead'])->name('admin.inbox.markAllRead');
    Route::post('admin/inbox/messages/dedupe', [App\Http\Controllers\Admin\InboxController::class, 'dedupe'])->name('admin.inbox.dedupe');
});

Route::group(['middleware' => ['auth', 'role:client']], function () {
    route::get('client-dashbord', function () {
        return view('clients.client_dashbord');
    })->name('client-dashbord');
    Route::get('client_chat', function () {
        return view('clients.client_chat');
    })->name('client_chat');
    route::get('client-place_orders', function () {
        return view('clients.place_orders');
    })->name('client-place_orders');
    route::get('dashboard', [App\Http\Controllers\OrdersController::class, 'index'])->name('dashboard');
    Route::get('dashboard/data', [App\Http\Controllers\OrdersController::class, 'dashboardData'])->name('dashboard.data')->middleware('auth');
    route::post('offer_accept/{id}', [App\Http\Controllers\OrdersController::class, 'offer_accept'])->name('offer_accept');
    route::post('offer_reject/{id}', [App\Http\Controllers\OrdersController::class, 'offer_reject'])->name('offer_reject');
    route::post('rejected/{id}', [App\Http\Controllers\OrdersController::class, 'rejected'])->name('rejected');
    route::post('tracking_id/{id}', [App\Http\Controllers\OrdersController::class, 'tracking_id'])->name('tracking_id');
    route::post('Order_confirmation/{id}', [App\Http\Controllers\OrdersController::class, 'Order_confirmation'])->name('Order_confirmation');
    route::post('Order_complete/{id}', [App\Http\Controllers\OrdersController::class, 'Order_complete'])->name('Order_complete');
    route::post('order-received/{id}', [App\Http\Controllers\OrdersController::class, 'order_received'])->name('order_received');
    Route::get('password', [App\Http\Controllers\HomeController::class, 'password'])->name('password');
    Route::put('password', [App\Http\Controllers\HomeController::class, 'changePassword']);
    Route::post('client_chat', [App\Http\Controllers\ChatneedaddressController::class, 'chat']);
    Route::post('client_chat', [App\Http\Controllers\ChatneedaddressController::class, 'store_new']);
    Route::post('stripe', [App\Http\Controllers\OrdersController::class, 'stripePost'])->name('stripe');
    Route::get('client_avatar', [App\Http\Controllers\UserController::class, 'index'])->name('client_avatar');
    Route::post('client_avatar', [App\Http\Controllers\UserController::class, 'update']);
    Route::get('notifications', function () {
        return view('clients.notifications');
    })->name('notifications');

    // Client inbox — own notifications & messages with reply
    Route::get('client/inbox/notifications', [App\Http\Controllers\Clients\InboxController::class, 'notificationsPage'])->name('client.inbox.notifications');
    Route::get('client/inbox/messages', [App\Http\Controllers\Clients\InboxController::class, 'messagesPage'])->name('client.inbox.messages');
    Route::get('client/inbox/feed', [App\Http\Controllers\Clients\InboxController::class, 'feed'])->name('client.inbox.feed');
    Route::post('client/inbox/messages/reply', [App\Http\Controllers\Clients\InboxController::class, 'reply'])->name('client.inbox.reply');
    Route::post('client/inbox/notifications/mark-all-read', [App\Http\Controllers\Clients\InboxController::class, 'markAllRead'])->name('client.inbox.markAllRead');
});
Route::get('country', [App\Http\Controllers\OrdersController::class, 'country'])->name('country');

// Route::get('all_notification', [App\Http\Controllers\AdminordersController::class, 'all_notification'])->name('all_notification');
Route::get('chat_count', [App\Http\Controllers\OrdersController::class, 'chat_count'])->name('chat_count');
Route::get('read_message', [App\Http\Controllers\OrdersController::class, 'read_message'])->name('read_message');
Route::get('chat_messages', [App\Http\Controllers\OrdersController::class, 'chat_messages'])->name('chat_messages');
Route::post('chat', [App\Http\Controllers\OrdersController::class, 'chat'])->name('chat');
Route::get('check_notification', [App\Http\Controllers\OrdersController::class, 'check_notification'])->name('check_notification');
route::resource('orders', 'App\Http\Controllers\OrdersController');

Route::get('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);
Auth::routes();

Route::get('auth/google/redirect', [App\Http\Controllers\Auth\GoogleController::class, 'redirect'])->name('auth.google.redirect');
Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'callback'])->name('auth.google.callback');
Route::get('auth/facebook/redirect', [App\Http\Controllers\Auth\FacebookController::class, 'redirect'])->name('auth.facebook.redirect');
Route::get('auth/facebook/callback', [App\Http\Controllers\Auth\FacebookController::class, 'callback'])->name('auth.facebook.callback');

/* Legacy (AdminLTE-style) password reset pages, kept reachable on their
   own URLs. /password/reset + /password/reset/{token} now serve the
   NEW home2-design pages; these serve the previous design. */
Route::get('password/resetlegacy', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestFormLegacy'])->name('password.requestlegacy');
Route::get('password/resetlegacy/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetFormLegacy'])->name('password.resetlegacy');

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('/');
Route::get('/lang/{locale}', [App\Http\Controllers\LocaleController::class, 'switch'])->name('lang.switch');
Route::get('testimonials', [App\Http\Controllers\TestimonialController::class, 'index'])->name('testimonials');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/best-parcel-forwarding-service', [App\Http\Controllers\HomeController::class, 'index'])->name('best-parcel-forwarding-service');
// Route::get('/roles', [App\Http\Controllers\PermissionController::class, 'Permission']);
Route::get('login.php', function () {
    return view('auth.login');
})->name('login.php');
Route::get('become-a-shopper', function () {
    return view('ebecomeshopper');
})->name('become-a-shopper');
// Route::get('become-a-shoppers-purchase-assistance', function () {
//     return view('becomeshopper');
// })->name('become-a-shoppers-purchase-assistance');
// Route::get('become-a-shipper', function () {
//     return view('becomeshipper');
// })->name('become-a-shipper');
// Route::get('become-a-shipper-for-delivering-parcel', function () {
//     return view('becomeshipper');
// })->name('become-a-shipper-for-delivering-parcel');
Route::get('aboutus', function () {
    return view('eaboutus');
})->name('aboutus');
// Route::get('how_it_works_for_shipper.php', function () {
//     return view('becomeshipper');
// })->name('how_it_works_for_shipper.php');
Route::get('contact-details', function () {
    return view('contactus');
})->name('contact-details');
// Route::get('pick-and-pack-fulfillment-services', function () {
//     return view('pickandpack');
// })->name('pick-and-pack-fulfillment-services');
// Route::get('fulfillment-service', function () {
//     return view('fullfillment');
// })->name('fulfillment-service');
Route::get('special-request', function () {
    return view('freequote');
})->name('special-request');
/*
 * /request + /country keep serving the LEGACY working page (user request
 * 2026-08-20: "follow legacy request page"). The redesigned form lives at
 * /home2/request2 (home2 theme).
 */
Route::get('request', function () {
    return view('specialrequest');
})->name('request');


//Route::get('orderss', function () {     return view('privacy'); })->name('privacy-policy');


// Route::view('special-request-page1', 'specialrequest1'); 
// Route::get('special-request-page', function () {
//     return view('specialrequest');
// })->name('special-request-page');
/* Dynamic XML sitemap — public pages only (static, services, published blog).
   NOTE: any STATIC sitemap.xml in the docroot shadows this route — remove it
   at deploy time so the dynamic one serves. */
Route::get('sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

Route::get('terms-and-conditions', function () {
    // Admin-editable Terms (Settings, Legal). Empty/unset = legacy page.
    $content = '';
    try {
        $content = trim((string) \App\Models\Setting::get('terms_content', ''));
    } catch (\Throwable $e) {
    }

    /* FIX (parity port): fall back to the legacy page instead of a 500. */
    try {
        return $content !== ''
            ? view('terms-content', ['content' => $content])
            : view('term-condition');
    } catch (\Throwable $e) {
        report($e);
        return view('term-condition');
    }
})->name('terms-and-conditions');
Route::get('privacy-policy', function () {
    return view('privacy');
})->name('privacy-policy');
Route::get('refund-policy', function () {
    return view('refund-policy');
})->name('refund-policy');

/* Destinations — country/region landing pages built from the shop-and-ship
   guide PDFs, one page per region, linked from the header's Destinations
   dropdown (resources/views/layouts/fmaster.blade.php). */
Route::prefix('destinations')->name('destinations.')->group(function () {
    Route::get('asia', fn () => view('destinations.asia'))->name('asia');
    Route::get('australia', fn () => view('destinations.australia'))->name('australia');
    Route::get('europe', fn () => view('destinations.europe'))->name('europe');
    Route::get('middle-east', fn () => view('destinations.middle-east'))->name('middle-east');
    Route::get('south-america', fn () => view('destinations.south-america'))->name('south-america');
    Route::get('usa', fn () => view('destinations.usa'))->name('usa');
});

Route::get('markThisAsRead', function (Request $request) {
    // Expired session while the notification bell polls, user() is null (parity port).
    $user = auth()->user();
    if ($user) {
        $user->unreadNotifications->where('id', $request->id)->markAsRead();
    }
    return response()->json(['ok' => (bool) $user]);
})->name('markThisAsRead');
// route::resource('chats', 'App\Http\Controllers\chatcontroller'); // controller class does not exist (unused)
/* Quote SUBMISSION stays public (special-request form posts freequote.store);
   viewing/managing quotes is admin-only — the admin layout assumes a logged-in user. */
/* /freequote UX (route-slot fix: a resource GET on the same URI overwrites any
   earlier route, so the admin inbox lives at /freequote-inbox and the public
   path /freequote routes guests/clients to the quote form). */
Route::post('freequote', [\App\Http\Controllers\FreeQuoteController::class, 'store'])->name('freequote.store');
Route::get('freequote-inbox', [\App\Http\Controllers\FreeQuoteController::class, 'index'])
    ->name('freequote.index')->middleware(['auth', 'role:admin']);
Route::get('freequote-inbox/orders-data', [\App\Http\Controllers\FreeQuoteController::class, 'ordersData'])
    ->name('freequote.orders.data')->middleware(['auth', 'role:admin']);
Route::get('freequote/{freequote}', [\App\Http\Controllers\FreeQuoteController::class, 'show'])
    ->name('freequote.show')->middleware(['auth', 'role:admin']);
Route::delete('freequote/{freequote}', [\App\Http\Controllers\FreeQuoteController::class, 'destroy'])
    ->name('freequote.destroy')->middleware(['auth', 'role:admin']);
Route::get('freequote', function () {
    if (auth()->check() && auth()->user()->hasRole('admin')) {
        return redirect()->route('freequote.index');
    }
    return redirect()->to('/special-request');
})->name('freequote.landing');
Route::get('ReadNotification', function (Request $request) {
    $user = auth()->user();
    if ($user) {
        $user->unreadNotifications->where('id', $request->id)->markAsRead();
    }
    return response()->json(['ok' => (bool) $user]);
})->name('ReadNotification');
// route::post('contactus', [App\Http\Controllers\ClientsController::class, 'contactus'])->name('contactus');
/* Contact SUBMISSION stays public (the public form posts contactus.store);
   viewing/managing submissions is admin-only — mirrors the freequote split above. */
route::resource('contactus', 'App\Http\Controllers\ContactedusController')->only(['store']);
route::resource('contactus', 'App\Http\Controllers\ContactedusController')
    ->only(['index', 'show', 'destroy'])
    ->middleware(['auth', 'role:admin']);
Route::get('404', function () {
    return view('errors.404');
})->name('404');


/* ============================================================
   Cache maintenance — ADMIN ONLY.
   Uses optimize:clear (wipes route/config/view caches). The old
   helpers here called route:cache/config:cache — i.e. they CREATED
   caches — which froze stale routes on prod (2026-08-25 outage:
   Route [pay.pay]/[testimonials] not defined).
   ============================================================ */
Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/clear-cache', function () {
        Artisan::call('optimize:clear');
        return 'All caches cleared.';
    });
});

/* ============================================================
   Admin module routes (split files — one owner agent each).
   Loaded inside the auth + role:admin group.
   ============================================================ */
Route::group(['middleware' => ['auth', 'role:admin']], function () {
    require __DIR__ . '/admin/dashboard.php';
    require __DIR__ . '/admin/orders.php';
    require __DIR__ . '/admin/content.php';
    require __DIR__ . '/admin/callouts.php';
    require __DIR__ . '/admin/communications.php';
    require __DIR__ . '/admin/settings.php';
    require __DIR__ . '/admin/wallets.php';
    require __DIR__ . '/admin/shop.php';
    require __DIR__ . '/admin/warehouse.php';
    require __DIR__ . '/admin/carriers.php';
    require __DIR__ . '/admin/rates.php';
    require __DIR__ . '/admin/compliance.php';
    require __DIR__ . '/admin/notifications.php';
    require __DIR__ . '/admin/quotes.php';
    require __DIR__ . '/admin/tools.php';
    require __DIR__ . '/admin/navigation.php';
    require __DIR__ . '/admin/reviews.php';
    require __DIR__ . '/admin/widgets.php';
    require __DIR__ . '/admin/route-manager.php';
    require __DIR__ . '/admin/blog.php'; // 2026-09-02 — Blog Import System (admin.blog-posts.*, admin.blog-import.*)
    require __DIR__ . '/admin/dynamic-pages.php'; // 2026-09-02 — Dynamic Pages + Section Manager
    require __DIR__ . '/admin/cms.php'; // 2026-09-03 — CMS (posts/taxonomies/menus/media — admin.cms.*)
    require __DIR__ . '/admin/seo-shield.php'; // 2026-09-21 — SEO Shield (admin.seo-shield.*)
});

/* ============================================================
   /home2 — new customer frontend (coexists with the legacy site)
   ============================================================ */
/* 2026-09-01 — blog + services promoted to ROOT URLs for SEO (group below).
   Same controllers as the former /home2/... routes; 301 redirects preserve
   the old /home2/... links for search engines + bookmarks. */
/* CMS ENDGAME 2026-09-19: services retired into CMS (cms.services.*, mounted
   from routes/cms_public.php). These two lines used to redirect 'services' to
   itself — a leftover from the old /home2/services -> /services migration —
   which, registered before cms_public.php, shadowed the real CMS route and
   made /services an infinite redirect loop. Removed; cms_public.php now owns
   /services and /services/{slug} outright. */
// Home2 blog routes (blog / blog.show) moved to BlogController 2026-09-02.
/* Blog (new design, BlogPost model) — public — 2026-09-02. Category/tag BEFORE {slug}. */
/* CMS ENDGAME 2026-09-19: public blog retired into CMS (routes/cms_public.php
   cms.blog.*). 301 keeps old deep links working; nav links to /blog unchanged. */
Route::redirect('blog', '/blog', 301);
Route::redirect('blog/{slug}', '/blog/{slug}', 301)->where('slug', '.*');

Route::redirect('home2/services', '/services', 301);
Route::redirect('home2/services/{slug}', '/services/{slug}', 301)->where('slug', '.+');
Route::redirect('home2/blog', '/blog', 301);
Route::redirect('home2/blog/{slug}', '/blog/{slug}', 301)->where('slug', '.+');

/* Dynamic Pages (admin-managed: Careers, FAQ, etc.) — 2026-09-02.
   /page/{slug} is now served by the CMS layer with DynamicPage fallback —
   see routes/cms_public.php (CMS-first, dynamic_pages fallback, 404). */


/* 2026-09-19: public tracking without the /home2 prefix (legacy-first UX). */
Route::get('track-order', [App\Http\Controllers\Home2\Home2Controller::class, 'trackForm'])->name('track.legacy');
Route::post('track-order', [App\Http\Controllers\Home2\Home2Controller::class, 'track'])->name('track.legacy.post');
Route::redirect('home2', '/', 301);
Route::prefix('home2')->name('home2.')->group(function () {
    Route::get('/', function () {
        return redirect('/', 301);
    })->name('index');
    Route::redirect('contact', '/contact-details', 301);
    Route::redirect('track-order', '/track-order', 301);
    Route::post('track-order', [App\Http\Controllers\Home2\Home2Controller::class, 'track'])->name('track.post');

    /* 2026-08-20 — full consolidation request form in the home2 design
       (legacy /request + /country remain untouched). */
    Route::get('request2', [App\Http\Controllers\Home2\Request2Controller::class, 'create'])->name('request2.create');
    Route::post('request2', [App\Http\Controllers\Home2\Request2Controller::class, 'store'])->middleware('throttle:10,1')->name('request2.store');

    Route::middleware(['auth'])->group(function () {
        /* 2026-09-19: only ADMIN may use the new dashboard; clients/shippers land on the legacy /dashboard. */
    Route::get('dashboard', function () {
        $u = auth()->user();
        if ($u && $u->type === 'admin') {
            return app(App\Http\Controllers\Home2\Home2Controller::class)->dashboard(request());
        }
        return redirect('/dashboard');
    })->name('dashboard')->middleware('auth');
        Route::get('returns', [App\Http\Controllers\Home2\Home2Controller::class, 'myReturns'])->name('returns');
        Route::get('claims', [App\Http\Controllers\Home2\Home2Controller::class, 'myClaims'])->name('claims');
        Route::get('quotes', [App\Http\Controllers\Home2\Home2Controller::class, 'myQuotes'])->name('quotes');
        // Named (unlike Route::redirect()'s default): dashboard.blade.php,
        // pay.blade.php and wallet.blade.php all link out via
        // route('home2.payments') — the dedicated payments list view is
        // gone (myPayments()/home2.payments removed as dead code below),
        // so this now just sends that link to the consolidated /dashboard.
        Route::redirect('payments', '/dashboard', 301)->name('payments');
        Route::get('notifications', [App\Http\Controllers\Home2\Home2Controller::class, 'myNotifications'])->name('notifications');
        Route::post('notifications/read-all', [App\Http\Controllers\Home2\Home2Controller::class, 'markNotificationsRead'])->name('notifications.read-all');

        /* ============================================================
           Agent PM — dynamic payment checkout (additive lines only).
           Home2Controller itself is NOT modified.
           ============================================================ */
        Route::get('pay/{orderId}', [App\Http\Controllers\Home2\PaymentController::class, 'show'])->name('pay.show');
        Route::get('pay/{orderId}/methods', [App\Http\Controllers\Home2\PaymentController::class, 'methods'])->name('pay.methods');
        Route::post('pay/{orderId}/select-method', [App\Http\Controllers\Home2\PaymentController::class, 'selectMethod'])->middleware('throttle:20,1')->name('pay.select-method');
        Route::post('pay/{orderId}/pay', [App\Http\Controllers\Home2\PaymentController::class, 'pay'])->middleware('throttle:10,1')->name('pay.pay');
        Route::post('pay/{payment}/proof', [App\Http\Controllers\Home2\PaymentController::class, 'uploadProof'])->middleware('throttle:10,1')->name('pay.proof');
        // C3: authorized receipt download (owning customer only; name is
        // suffixed because home2.pay.proof is the POST upload route).
        Route::get('pay/proof/{payment}', [App\Http\Controllers\Home2\PaymentController::class, 'downloadProof'])->name('pay.proof.download');
        Route::get('pay/{payment}/status', [App\Http\Controllers\Home2\PaymentController::class, 'status'])->name('pay.status');
        Route::get('pay/{payment}/success', [App\Http\Controllers\Home2\PaymentController::class, 'success'])->name('pay.success');

        /* Authenticated payment JSON endpoints (moved from routes/api.php on
           2026-08-21: they previously sat OUTSIDE any auth, publicly exposing
           bank account numbers/IBAN/SWIFT). Same-origin fetches from the pay
           pages ride the session cookie, so web auth covers them. */
        Route::get('api/bank-account-details', [App\Http\Controllers\Api\PaymentApiController::class, 'bankAccountDetails'])->name('api.bank-account-details');
        Route::get('api/stripe-config', [App\Http\Controllers\Api\PaymentApiController::class, 'stripeConfig'])->name('api.stripe-config');
        /* ============ end Agent PM payment routes ============ */

        /* ============================================================
           Agent WL — customer wallet (additive lines only, PM-014).
           ============================================================ */
        Route::get('wallet', [App\Http\Controllers\Home2\WalletController::class, 'index'])->name('wallet');
        Route::post('wallet/topup', [App\Http\Controllers\Home2\WalletController::class, 'topup'])->middleware('throttle:10,1')->name('wallet.topup');
        Route::get('wallet/topup/return/{paymentId}', [App\Http\Controllers\Home2\WalletController::class, 'topupReturn'])->name('wallet.topup.return');
        Route::get('wallet/refresh', [App\Http\Controllers\Home2\WalletController::class, 'refresh'])->name('wallet.refresh');
        /* ============ end Agent WL wallet routes ============ */

        /* ============================================================
           Agent RV — customer reviews (additive lines only).
           ============================================================ */
        Route::get('reviews', [App\Http\Controllers\Home2\ReviewController::class, 'eligibleOrders'])->name('reviews.index');
        Route::get('reviews/create', [App\Http\Controllers\Home2\ReviewController::class, 'create'])->name('reviews.create');
        Route::post('reviews', [App\Http\Controllers\Home2\ReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');
        Route::get('reviews/mine', [App\Http\Controllers\Home2\ReviewController::class, 'myReviews'])->name('reviews.mine');
        Route::get('reviews/widget-preview', [App\Http\Controllers\Home2\ReviewController::class, 'widgetPreview'])->name('reviews.widget-preview');
        /* ============ end Agent RV review routes ============ */

        /* ============================================================
           TW/FE-002/PD-003/RQ-002/RQ-004 — shop, wizard, offers.
           ============================================================ */
        Route::get('shop', [App\Http\Controllers\Home2\ShopController::class, 'index'])->name('shop.index');
        Route::get('shop/{slug}', [App\Http\Controllers\Home2\ShopController::class, 'show'])->name('shop.show');
        Route::get('cart', [App\Http\Controllers\Home2\ShopController::class, 'cart'])->name('shop.cart');
        Route::post('shop/add/{product}', [App\Http\Controllers\Home2\ShopController::class, 'add'])->middleware('throttle:30,1')->name('shop.add');
        Route::post('cart/update', [App\Http\Controllers\Home2\ShopController::class, 'update'])->name('shop.cart.update');
        Route::post('cart/clear', [App\Http\Controllers\Home2\ShopController::class, 'clear'])->name('shop.cart.clear');
        Route::get('checkout', [App\Http\Controllers\Home2\ShopCheckoutController::class, 'form'])->name('shop.checkout');
        Route::post('checkout', [App\Http\Controllers\Home2\ShopCheckoutController::class, 'place'])->middleware('throttle:10,1')->name('shop.checkout.place');
        Route::get('shop-order/{code}', [App\Http\Controllers\Home2\ShopCheckoutController::class, 'confirmation'])->name('shop.confirmation');
        /* PM-015: shop checkout pays through the payment engine (advanced mode),
           honouring product/shop-order forced methods. */
        Route::post('shop/pay/{code}', [App\Http\Controllers\Home2\ShopCheckoutController::class, 'pay'])->middleware('throttle:10,1')->name('shop.pay');
        Route::get('shop/pay/{code}/status', [App\Http\Controllers\Home2\ShopCheckoutController::class, 'status'])->name('shop.pay.status');

        Route::get('request/new', [App\Http\Controllers\Home2\WizardController::class, 'create'])->name('wizard.create');
        Route::post('request/new', [App\Http\Controllers\Home2\WizardController::class, 'store'])->middleware('throttle:10,1')->name('wizard.store');

        Route::get('offers', [App\Http\Controllers\Home2\OfferController::class, 'index'])->name('offers.index');
        Route::post('offers/{offer}/accept', [App\Http\Controllers\Home2\OfferController::class, 'accept'])->name('offers.accept');
        Route::post('offers/{offer}/reject', [App\Http\Controllers\Home2\OfferController::class, 'reject'])->name('offers.reject');
        /* ============ end shop/wizard/offers routes ============ */

        /* ============================================================
           Client-side parity (M1/M6/M7) — customer order detail page,
           client actions and per-order chat. Ownership is enforced
           server-side in every controller method (user_id scoping).
           ============================================================ */
        Route::get('orders/{id}', [App\Http\Controllers\Home2\Home2Controller::class, 'orderShow'])->name('orders.show')->where('id', '[0-9]+');
        Route::get('orders/{id}/chat', [App\Http\Controllers\Home2\Home2Controller::class, 'orderChat'])->name('orders.chat')->where('id', '[0-9]+');
        Route::post('orders/{id}/chat', [App\Http\Controllers\Home2\Home2Controller::class, 'orderChatSend'])->middleware('throttle:30,1')->name('orders.chat.send')->where('id', '[0-9]+');
        /* ============ end client-side parity routes ============ */
    });
});

// (legacy /clear-config-cache, /clear-app-cache, /clear-view-cache helpers
//  removed 2026-08-25 — anonymous cache manipulation; use admin /clear-cache)

// ── Mobile App Management Routes ──────────────────────────
// Added: 2026-08-29. Do not modify above this line.
// All mobile panel routes live in routes/mobile_web.php (isolated module).
require __DIR__.'/mobile_web.php';


/* ============================================================
   CMS Public Frontend — 2026-09-03 (/page/{slug} mounted;
   /blog /services /shop CMS groups pre-wired but commented inside)
   ============================================================ */
require __DIR__ . '/cms_public.php';

require __DIR__ . '/payoneer.php'; // 2026-09-19 parity port from legacy

/* ============================================================
   SHIPPER SYSTEM (2026-09-10 series) — additive requires only.
   ============================================================ */
require __DIR__ . "/admin/shipper.php"; // admin shipper panel
require __DIR__ . "/shipper.php";       // shipper portal + customer address
