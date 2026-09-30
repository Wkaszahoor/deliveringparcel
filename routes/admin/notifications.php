<?php

/**
 * Admin routes: NOTIFICATIONS + AUDIT LOG.
 */

use App\Http\Controllers\Admin\System\NotificationController;
use App\Http\Controllers\Admin\System\AuditController;

Route::prefix('admin/notifications')->name('admin.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('data', [NotificationController::class, 'data'])->name('data');
    Route::get('unread', [NotificationController::class, 'unreadCount'])->name('unread');
    Route::post('{id}/read', [NotificationController::class, 'markRead'])->where('id', '[0-9a-f-]+')->name('read');
    Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::get('preferences', [NotificationController::class, 'preferences'])->name('preferences');
    Route::post('preference', [NotificationController::class, 'updatePreference'])->name('pref');
});

Route::prefix('admin/audit')->name('admin.audit.')->group(function () {
    Route::get('/', [AuditController::class, 'index'])->name('index');
    Route::get('data', [AuditController::class, 'data'])->name('data');
});
