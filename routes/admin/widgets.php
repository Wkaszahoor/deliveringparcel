<?php

use App\Http\Controllers\Admin\Widgets\WidgetController;
use Illuminate\Support\Facades\Route;

Route::get('admin/widgets', [WidgetController::class, 'index'])->name('admin.widgets.index');
Route::get('admin/widgets/data', [WidgetController::class, 'data'])->name('admin.widgets.data');
Route::get('admin/widgets/create', [WidgetController::class, 'create'])->name('admin.widgets.create');
Route::post('admin/widgets', [WidgetController::class, 'store'])->name('admin.widgets.store');
Route::get('admin/widgets/{widget}/edit', [WidgetController::class, 'edit'])->name('admin.widgets.edit');
Route::put('admin/widgets/{widget}', [WidgetController::class, 'update'])->name('admin.widgets.update');
Route::post('admin/widgets/{widget}/toggle', [WidgetController::class, 'toggle'])->name('admin.widgets.toggle');
Route::delete('admin/widgets/{widget}', [WidgetController::class, 'destroy'])->name('admin.widgets.destroy');
