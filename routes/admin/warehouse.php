<?php

/**
 * Admin routes: WAREHOUSE — bins, inbound packages, consolidation,
 * shipments + events, dispatch manifests.
 */

use App\Http\Controllers\Admin\Warehouse\WarehouseController;

/* Bins */
Route::get('admin/warehouse/bins', [WarehouseController::class, 'binsIndex'])->name('admin.warehouse.bins.index');
Route::get('admin/warehouse/bins/data', [WarehouseController::class, 'binsData'])->name('admin.warehouse.bins.data');
Route::get('admin/warehouse/bins/create', [WarehouseController::class, 'binsCreate'])->name('admin.warehouse.bins.create');
Route::post('admin/warehouse/bins', [WarehouseController::class, 'binsStore'])->name('admin.warehouse.bins.store');
Route::get('admin/warehouse/bins/{bin}/edit', [WarehouseController::class, 'binsEdit'])->name('admin.warehouse.bins.edit');
Route::put('admin/warehouse/bins/{bin}', [WarehouseController::class, 'binsUpdate'])->name('admin.warehouse.bins.update');
Route::delete('admin/warehouse/bins/{bin}', [WarehouseController::class, 'binsDestroy'])->name('admin.warehouse.bins.destroy');

/* Packages (inbound) */
Route::get('admin/warehouse/packages', [WarehouseController::class, 'packagesIndex'])->name('admin.warehouse.packages.index');
Route::get('admin/warehouse/packages/data', [WarehouseController::class, 'packagesData'])->name('admin.warehouse.packages.data');
Route::get('admin/warehouse/packages/create', [WarehouseController::class, 'packagesCreate'])->name('admin.warehouse.packages.create');
Route::post('admin/warehouse/packages', [WarehouseController::class, 'packagesStore'])->name('admin.warehouse.packages.store');
Route::get('admin/warehouse/packages/{package}/receive', [WarehouseController::class, 'packagesReceiveForm'])->name('admin.warehouse.packages.receive-form');
Route::put('admin/warehouse/packages/{package}/receive', [WarehouseController::class, 'packagesReceive'])->name('admin.warehouse.packages.receive');

/* Consolidation + shipments */
Route::post('admin/warehouse/consolidate', [WarehouseController::class, 'consolidate'])->name('admin.warehouse.consolidate');
Route::get('admin/warehouse/shipments', [WarehouseController::class, 'shipmentsIndex'])->name('admin.warehouse.shipments.index');
Route::get('admin/warehouse/shipments/data', [WarehouseController::class, 'shipmentsData'])->name('admin.warehouse.shipments.data');
Route::get('admin/warehouse/shipments/{shipment}', [WarehouseController::class, 'shipmentsShow'])->name('admin.warehouse.shipments.show');
Route::put('admin/warehouse/shipments/{shipment}', [WarehouseController::class, 'shipmentsUpdate'])->name('admin.warehouse.shipments.update');
Route::get('admin/warehouse/shipments/{shipment}/manifest', [WarehouseController::class, 'manifest'])->name('admin.warehouse.shipments.manifest');
