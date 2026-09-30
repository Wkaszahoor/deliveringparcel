<?php

/**
 * Admin routes: CONTACT/MESSAGES + SETTINGS + PAYMENTS (Agent D owns this file).
 * Loaded inside the ['auth', 'role:admin'] group in routes/web.php.
 */

use App\Http\Controllers\Admin\Contacts\ContactController;
use App\Http\Controllers\Admin\Contacts\TemplateController;
use App\Http\Controllers\Admin\Payments\BankAccountController; // Agent PM Phase 6 (additive)
use App\Http\Controllers\Admin\Payments\PaymentConfigController; // Agent PM (additive)
use App\Http\Controllers\Admin\Payments\PaymentController;
use App\Http\Controllers\Admin\Payments\WebhookController; // Agent WL (PM-017, additive)
use App\Http\Controllers\Admin\Settings\SettingsController;
use App\Http\Controllers\Admin\MenuOrderController;
use Illuminate\Support\Facades\Route;

/* ---------------- Contact / Messages ---------------- */

Route::get('/admin/contacts', [ContactController::class, 'index'])->name('admin.contacts.index');
Route::get('/admin/contacts/data', [ContactController::class, 'data'])->name('admin.contacts.data');
Route::post('/admin/contacts/bulk', [ContactController::class, 'bulk'])->name('admin.contacts.bulk');
Route::get('/admin/contacts/{contact}', [ContactController::class, 'show'])->name('admin.contacts.show');
Route::post('/admin/contacts/{contact}/reply', [ContactController::class, 'reply'])->name('admin.contacts.reply');

/* ---------------- Reply templates (admin-lite CRUD) ---------------- */

Route::get('/admin/contact-templates', [TemplateController::class, 'index'])->name('admin.contacts.templates.index');
Route::get('/admin/contact-templates/create', [TemplateController::class, 'create'])->name('admin.contacts.templates.create');
Route::post('/admin/contact-templates', [TemplateController::class, 'store'])->name('admin.contacts.templates.store');
Route::get('/admin/contact-templates/{template}', [TemplateController::class, 'show'])->name('admin.contacts.templates.show');
Route::get('/admin/contact-templates/{template}/edit', [TemplateController::class, 'edit'])->name('admin.contacts.templates.edit');
Route::put('/admin/contact-templates/{template}', [TemplateController::class, 'update'])->name('admin.contacts.templates.update');
Route::delete('/admin/contact-templates/{template}', [TemplateController::class, 'destroy'])->name('admin.contacts.templates.destroy');

/* ---------------- Settings ---------------- */

Route::get('/admin/settings', [SettingsController::class, 'edit'])->name('admin.settings.edit');
Route::put('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
Route::get('/admin/settings/api-status', [SettingsController::class, 'apiStatus'])->name('admin.settings.apiStatus');

/* ---------------- Payments ---------------- */

Route::get('/admin/payments', [PaymentController::class, 'index'])->name('admin.payments.index');
Route::get('/admin/payments/data', [PaymentController::class, 'data'])->name('admin.payments.data');
Route::put('/admin/payments/{order}/refund', [PaymentController::class, 'refund'])->name('admin.payments.refund');

/* ============================================================
   Agent PM — dynamic payment engine (additive lines only).
   Config (methods + routing rules + bank details) and the NEW
   payments ledger with bank verification + gateway refunds.
   ============================================================ */

// Payment methods + routing rules configuration (PM-001/PM-008)
Route::get('/admin/payments/config', [PaymentConfigController::class, 'index'])->name('admin.payments.config.index');
Route::post('/admin/payments/config/methods', [PaymentConfigController::class, 'storeMethod'])->name('admin.payments.config.methods.store');
Route::post('/admin/payments/config/methods/{method}', [PaymentConfigController::class, 'updateMethod'])->name('admin.payments.config.methods.update');
Route::post('/admin/payments/config/rules', [PaymentConfigController::class, 'updateRules'])->name('admin.payments.config.rules.update');
Route::post('/admin/payments/config/bank', [PaymentConfigController::class, 'updateBank'])->name('admin.payments.config.bank.update');

// NEW payments ledger (PM-005/PM-006/PM-008/PM-013)
Route::get('/admin/payments/ledger', [PaymentController::class, 'indexLedger'])->name('admin.payments.ledger');
Route::get('/admin/payments/ledger/data', [PaymentController::class, 'dataLedger'])->name('admin.payments.ledger.data');
// C3: authorized bank-receipt download from the private disk (never a public URL)
Route::get('/admin/payments/proof/{payment}', [PaymentController::class, 'downloadProof'])->name('admin.payments.proof');
Route::put('/admin/payments/ledger/{payment}/verify', [PaymentController::class, 'verify'])->name('admin.payments.verify');
Route::put('/admin/payments/ledger/{payment}/refund', [PaymentController::class, 'refundLedger'])->name('admin.payments.ledger.refund');

// Stripe webhook history + reconciliation (PM-017)
Route::get('/admin/payments/webhooks/compare', [WebhookController::class, 'compare'])->name('admin.payments.webhooks.compare');
Route::get('/admin/payments/webhooks', [WebhookController::class, 'index'])->name('admin.payments.webhooks');
Route::get('/admin/payments/webhooks/{webhookEvent}', [WebhookController::class, 'show'])->name('admin.payments.webhooks.show');

// Bank accounts management (PM-006 Phase 6)
Route::get('/admin/payments/config/banks', [BankAccountController::class, 'index'])->name('admin.payments.config.banks');
Route::post('/admin/payments/config/banks', [BankAccountController::class, 'store'])->name('admin.payments.config.banks.store');
Route::get('/admin/payments/config/banks/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('admin.payments.config.banks.edit');
Route::put('/admin/payments/config/banks/{bankAccount}', [BankAccountController::class, 'update'])->name('admin.payments.config.banks.update');
Route::delete('/admin/payments/config/banks/{bankAccount}', [BankAccountController::class, 'destroy'])->name('admin.payments.config.banks.destroy');
Route::put('/admin/payments/config/banks/{bankAccount}/toggle', [BankAccountController::class, 'toggle'])->name('admin.payments.config.banks.toggle');

/* ============ end Agent PM payment routes ============ */

/* ---------------- Sitemap Settings (2026-09-01, additive) ---------------- */

use App\Http\Controllers\Admin\SitemapSettingController;

Route::get('/admin/sitemap-settings', [SitemapSettingController::class, 'index'])->name('admin.sitemap.settings');
Route::put('/admin/sitemap-settings', [SitemapSettingController::class, 'update'])->name('admin.sitemap.settings.update');
Route::post('/admin/sitemap-settings/regenerate', [SitemapSettingController::class, 'regenerate'])->name('admin.sitemap.settings.regenerate');
Route::post('/admin/sitemap-settings/ping', [SitemapSettingController::class, 'ping'])->name('admin.sitemap.settings.ping');

/* ---------------- Sidebar menu order (drag-drop) ---------------- */

Route::get('/admin/menu-order', [MenuOrderController::class, 'index'])->name('admin.menu-order.index');
Route::post('/admin/menu-order', [MenuOrderController::class, 'save'])->name('admin.menu-order.save');
