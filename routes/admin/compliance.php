<?php

/**
 * Admin routes: COMPLIANCE — KYC, sanctions screening, restricted items,
 * HS codes, VAT rules, consent templates, GDPR export.
 */

use App\Http\Controllers\Admin\Compliance\ComplianceController;

/* KYC */
Route::get('admin/compliance/kyc', [ComplianceController::class, 'kycIndex'])->name('admin.compliance.kyc.index');
Route::get('admin/compliance/kyc/data', [ComplianceController::class, 'kycData'])->name('admin.compliance.kyc.data');
Route::get('admin/compliance/kyc/{kyc}', [ComplianceController::class, 'kycShow'])->name('admin.compliance.kyc.show');
Route::put('admin/compliance/kyc/{kyc}/approve', [ComplianceController::class, 'kycApprove'])->name('admin.compliance.kyc.approve');
Route::put('admin/compliance/kyc/{kyc}/reject', [ComplianceController::class, 'kycReject'])->name('admin.compliance.kyc.reject');

/* Sanctions */
Route::get('admin/compliance/sanctions', [ComplianceController::class, 'sanctionsIndex'])->name('admin.compliance.sanctions');
Route::post('admin/compliance/sanctions/screen', [ComplianceController::class, 'sanctionsScreen'])->name('admin.compliance.sanctions.screen');

/* Restricted items */
Route::get('admin/compliance/restricted', [ComplianceController::class, 'restrictedIndex'])->name('admin.compliance.restricted.index');
Route::get('admin/compliance/restricted/data', [ComplianceController::class, 'restrictedData'])->name('admin.compliance.restricted.data');
Route::get('admin/compliance/restricted/create', [ComplianceController::class, 'restrictedCreate'])->name('admin.compliance.restricted.create');
Route::post('admin/compliance/restricted', [ComplianceController::class, 'restrictedStore'])->name('admin.compliance.restricted.store');
Route::get('admin/compliance/restricted/{item}/edit', [ComplianceController::class, 'restrictedEdit'])->name('admin.compliance.restricted.edit');
Route::put('admin/compliance/restricted/{item}', [ComplianceController::class, 'restrictedUpdate'])->name('admin.compliance.restricted.update');
Route::delete('admin/compliance/restricted/{item}', [ComplianceController::class, 'restrictedDestroy'])->name('admin.compliance.restricted.destroy');

/* HS codes */
Route::get('admin/compliance/hs-codes', [ComplianceController::class, 'hsIndex'])->name('admin.compliance.hs.index');
Route::get('admin/compliance/hs-codes/data', [ComplianceController::class, 'hsData'])->name('admin.compliance.hs.data');
Route::get('admin/compliance/hs-codes/create', [ComplianceController::class, 'hsCreate'])->name('admin.compliance.hs.create');
Route::post('admin/compliance/hs-codes', [ComplianceController::class, 'hsStore'])->name('admin.compliance.hs.store');
Route::get('admin/compliance/hs-codes/{hs}/edit', [ComplianceController::class, 'hsEdit'])->name('admin.compliance.hs.edit');
Route::put('admin/compliance/hs-codes/{hs}', [ComplianceController::class, 'hsUpdate'])->name('admin.compliance.hs.update');
Route::delete('admin/compliance/hs-codes/{hs}', [ComplianceController::class, 'hsDestroy'])->name('admin.compliance.hs.destroy');

/* VAT rules */
Route::get('admin/compliance/vat', [ComplianceController::class, 'vatIndex'])->name('admin.compliance.vat.index');
Route::get('admin/compliance/vat/data', [ComplianceController::class, 'vatData'])->name('admin.compliance.vat.data');
Route::get('admin/compliance/vat/create', [ComplianceController::class, 'vatCreate'])->name('admin.compliance.vat.create');
Route::post('admin/compliance/vat', [ComplianceController::class, 'vatStore'])->name('admin.compliance.vat.store');
Route::get('admin/compliance/vat/{rule}/edit', [ComplianceController::class, 'vatEdit'])->name('admin.compliance.vat.edit');
Route::put('admin/compliance/vat/{rule}', [ComplianceController::class, 'vatUpdate'])->name('admin.compliance.vat.update');
Route::delete('admin/compliance/vat/{rule}', [ComplianceController::class, 'vatDestroy'])->name('admin.compliance.vat.destroy');

/* Consent templates */
Route::get('admin/compliance/consent', [ComplianceController::class, 'consentIndex'])->name('admin.compliance.consent.index');
Route::get('admin/compliance/consent/data', [ComplianceController::class, 'consentData'])->name('admin.compliance.consent.data');
Route::get('admin/compliance/consent/create', [ComplianceController::class, 'consentCreate'])->name('admin.compliance.consent.create');
Route::post('admin/compliance/consent', [ComplianceController::class, 'consentStore'])->name('admin.compliance.consent.store');
Route::get('admin/compliance/consent/{template}/edit', [ComplianceController::class, 'consentEdit'])->name('admin.compliance.consent.edit');
Route::put('admin/compliance/consent/{template}', [ComplianceController::class, 'consentUpdate'])->name('admin.compliance.consent.update');
Route::delete('admin/compliance/consent/{template}', [ComplianceController::class, 'consentDestroy'])->name('admin.compliance.consent.destroy');

/* GDPR */
Route::get('admin/compliance/gdpr', [ComplianceController::class, 'gdprIndex'])->name('admin.compliance.gdpr');
Route::get('admin/compliance/gdpr/export', [ComplianceController::class, 'gdprExport'])->name('admin.compliance.gdpr.export');
