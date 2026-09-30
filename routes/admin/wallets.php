<?php

/* ============================================================
   Agent WL — admin wallet management routes (PM-014, additive).
   Loaded inside the auth + role:admin group (routes/web.php).
   ============================================================ */

Route::prefix('admin/wallets')->name('admin.wallets.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\Wallets\WalletController::class, 'index'])->name('index');
    Route::get('/{user}', [App\Http\Controllers\Admin\Wallets\WalletController::class, 'show'])->name('show');
    Route::post('/{user}/adjust', [App\Http\Controllers\Admin\Wallets\WalletController::class, 'adjust'])->name('adjust');
    Route::post('/{user}/lock', [App\Http\Controllers\Admin\Wallets\WalletController::class, 'lock'])->name('lock');
    Route::post('/{user}/unlock', [App\Http\Controllers\Admin\Wallets\WalletController::class, 'unlock'])->name('unlock');
});
