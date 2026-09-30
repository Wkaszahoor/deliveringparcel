<?php

/**
 * Admin routes: COMMUNICATIONS — unified email system (templates, logs,
 * queue monitor). Loaded inside the ['auth', 'role:admin'] group.
 */

use App\Http\Controllers\Admin\Communications\EmailLogsController;
use App\Http\Controllers\Admin\Communications\EmailTemplatesController;
use App\Http\Controllers\Admin\Communications\QueueMonitorController;
use Illuminate\Support\Facades\Route;

/* ---------------- Email templates ---------------- */

Route::get('/admin/emails', [EmailTemplatesController::class, 'index'])->name('admin.emails.index');
Route::get('/admin/emails/create', [EmailTemplatesController::class, 'create'])->name('admin.emails.create');
Route::post('/admin/emails', [EmailTemplatesController::class, 'store'])->name('admin.emails.store');
Route::get('/admin/emails/{template}/edit', [EmailTemplatesController::class, 'edit'])->name('admin.emails.edit');
Route::put('/admin/emails/{template}', [EmailTemplatesController::class, 'update'])->name('admin.emails.update');
Route::delete('/admin/emails/{template}', [EmailTemplatesController::class, 'destroy'])->name('admin.emails.destroy');
Route::post('/admin/emails/{template}/toggle', [EmailTemplatesController::class, 'toggle'])->name('admin.emails.toggle');
Route::post('/admin/emails/{template}/test', [EmailTemplatesController::class, 'test'])->name('admin.emails.test');

/* ---------------- Email logs ---------------- */

Route::get('/admin/email-logs', [EmailLogsController::class, 'index'])->name('admin.email-logs.index');
Route::get('/admin/email-logs/{log}', [EmailLogsController::class, 'show'])->name('admin.email-logs.show');
Route::post('/admin/email-logs/{log}/retry', [EmailLogsController::class, 'retry'])->name('admin.email-logs.retry');

/* ---------------- Queue monitor ---------------- */

Route::get('/admin/queue-monitor', [QueueMonitorController::class, 'index'])->name('admin.queue.monitor');
