<?php

/**
 * Admin routes: SHIPPING RATE ENGINE + GEO (zones, rules, surcharges,
 * insurance, calculator, states/cities).
 */

use App\Http\Controllers\Admin\Rates\RateZoneController;
use App\Http\Controllers\Admin\Rates\RateRuleController;
use App\Http\Controllers\Admin\Rates\RateExtraController;
use App\Http\Controllers\Admin\Rates\GeoController;
use App\Http\Controllers\Admin\Rates\RateCalculatorController;

/* ---------------- Calculator ---------------- */
Route::get('admin/rates/calculator', [RateCalculatorController::class, 'index'])->name('admin.rates.calculator');
Route::post('admin/rates/calculator/compute', [RateCalculatorController::class, 'compute'])->name('admin.rates.compute');

/* ---------------- Zones ---------------- */
Route::get('admin/rates/zones', [RateZoneController::class, 'index'])->name('admin.rates.zones.index');
Route::get('admin/rates/zones/data', [RateZoneController::class, 'data'])->name('admin.rates.zones.data');
Route::get('admin/rates/zones/create', [RateZoneController::class, 'create'])->name('admin.rates.zones.create');
Route::post('admin/rates/zones', [RateZoneController::class, 'store'])->name('admin.rates.zones.store');
Route::get('admin/rates/zones/{zone}/edit', [RateZoneController::class, 'edit'])->name('admin.rates.zones.edit');
Route::put('admin/rates/zones/{zone}', [RateZoneController::class, 'update'])->name('admin.rates.zones.update');
Route::delete('admin/rates/zones/{zone}', [RateZoneController::class, 'destroy'])->name('admin.rates.zones.destroy');

/* ---------------- Rules ---------------- */
Route::get('admin/rates/rules', [RateRuleController::class, 'index'])->name('admin.rates.rules.index');
Route::get('admin/rates/rules/data', [RateRuleController::class, 'data'])->name('admin.rates.rules.data');
Route::get('admin/rates/rules/create', [RateRuleController::class, 'create'])->name('admin.rates.rules.create');
Route::post('admin/rates/rules', [RateRuleController::class, 'store'])->name('admin.rates.rules.store');
Route::get('admin/rates/rules/{rule}/edit', [RateRuleController::class, 'edit'])->name('admin.rates.rules.edit');
Route::put('admin/rates/rules/{rule}', [RateRuleController::class, 'update'])->name('admin.rates.rules.update');
Route::delete('admin/rates/rules/{rule}', [RateRuleController::class, 'destroy'])->name('admin.rates.rules.destroy');

/* ---------------- Surcharges ---------------- */
Route::get('admin/rates/surcharges', [RateExtraController::class, 'surchargesIndex'])->name('admin.rates.surcharges.index');
Route::get('admin/rates/surcharges/data', [RateExtraController::class, 'surchargesData'])->name('admin.rates.surcharges.data');
Route::get('admin/rates/surcharges/create', [RateExtraController::class, 'surchargesCreate'])->name('admin.rates.surcharges.create');
Route::post('admin/rates/surcharges', [RateExtraController::class, 'surchargesStore'])->name('admin.rates.surcharges.store');
Route::get('admin/rates/surcharges/{surcharge}/edit', [RateExtraController::class, 'surchargesEdit'])->name('admin.rates.surcharges.edit');
Route::put('admin/rates/surcharges/{surcharge}', [RateExtraController::class, 'surchargesUpdate'])->name('admin.rates.surcharges.update');
Route::delete('admin/rates/surcharges/{surcharge}', [RateExtraController::class, 'surchargesDestroy'])->name('admin.rates.surcharges.destroy');

/* ---------------- Insurance ---------------- */
Route::get('admin/rates/insurances', [RateExtraController::class, 'insurancesIndex'])->name('admin.rates.insurances.index');
Route::get('admin/rates/insurances/data', [RateExtraController::class, 'insurancesData'])->name('admin.rates.insurances.data');
Route::get('admin/rates/insurances/create', [RateExtraController::class, 'insurancesCreate'])->name('admin.rates.insurances.create');
Route::post('admin/rates/insurances', [RateExtraController::class, 'insurancesStore'])->name('admin.rates.insurances.store');
Route::get('admin/rates/insurances/{insurance}/edit', [RateExtraController::class, 'insurancesEdit'])->name('admin.rates.insurances.edit');
Route::put('admin/rates/insurances/{insurance}', [RateExtraController::class, 'insurancesUpdate'])->name('admin.rates.insurances.update');
Route::delete('admin/rates/insurances/{insurance}', [RateExtraController::class, 'insurancesDestroy'])->name('admin.rates.insurances.destroy');

/* ---------------- Geo: states & cities ---------------- */
Route::get('admin/geo/states', [GeoController::class, 'statesIndex'])->name('admin.geo.states.index');
Route::get('admin/geo/states/data', [GeoController::class, 'statesData'])->name('admin.geo.states.data');
Route::get('admin/geo/states/create', [GeoController::class, 'statesCreate'])->name('admin.geo.states.create');
Route::post('admin/geo/states', [GeoController::class, 'statesStore'])->name('admin.geo.states.store');
Route::get('admin/geo/states/{state}/edit', [GeoController::class, 'statesEdit'])->name('admin.geo.states.edit');
Route::put('admin/geo/states/{state}', [GeoController::class, 'statesUpdate'])->name('admin.geo.states.update');
Route::delete('admin/geo/states/{state}', [GeoController::class, 'statesDestroy'])->name('admin.geo.states.destroy');

Route::get('admin/geo/cities', [GeoController::class, 'citiesIndex'])->name('admin.geo.cities.index');
Route::get('admin/geo/cities/data', [GeoController::class, 'citiesData'])->name('admin.geo.cities.data');
Route::get('admin/geo/cities/create', [GeoController::class, 'citiesCreate'])->name('admin.geo.cities.create');
Route::post('admin/geo/cities', [GeoController::class, 'citiesStore'])->name('admin.geo.cities.store');
Route::get('admin/geo/cities/{city}/edit', [GeoController::class, 'citiesEdit'])->name('admin.geo.cities.edit');
Route::put('admin/geo/cities/{city}', [GeoController::class, 'citiesUpdate'])->name('admin.geo.cities.update');
Route::delete('admin/geo/cities/{city}', [GeoController::class, 'citiesDestroy'])->name('admin.geo.cities.destroy');

/* Dependent dropdown feeds */
Route::get('admin/geo/states-by-country', [GeoController::class, 'statesByCountry'])->name('admin.geo.states-by-country');
Route::get('admin/geo/cities-by-state', [GeoController::class, 'citiesByState'])->name('admin.geo.cities-by-state');
