<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agent WL — PM-015: per-entity forced payment gateway + shop payment rails.
 *
 * Additive only:
 *   shop_products.forced_payment_method_code  — force gateway for orders containing this product
 *   shop_orders.forced_payment_method_code    — per-shop-order override (admin or checkout capture)
 *   request_quotes.forced_payment_method_code — admin marks the gateway a quote's client must use
 *   orders.request_quote_id                   — optional quote → order linkage so a quote's forced
 *                                                method can carry into the created order
 *   payment_method_rules                      — 'shop' service context (shop checkout joins the
 *                                                multi-gateway engine: stripe / wallet / bank transfer)
 */
class AddForcedPaymentMethodToEntities extends Migration
{
    public function up()
    {
        foreach (['shop_products' => null, 'shop_orders' => null, 'request_quotes' => null] as $table => $_) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'forced_payment_method_code')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('forced_payment_method_code', 30)->nullable();
                });
            }
        }

        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'request_quote_id')) {
            Schema::table('orders', function (Blueprint $t) {
                $t->unsignedBigInteger('request_quote_id')->nullable()->index();
            });
        }

        // Shop context rules: every enabled method rides the shop checkout.
        if (Schema::hasTable('payment_methods') && Schema::hasTable('payment_method_rules')) {
            $methodIds = DB::table('payment_methods')->where('is_enabled', true)->pluck('id');
            foreach ($methodIds as $methodId) {
                $exists = DB::table('payment_method_rules')
                    ->where('service_context', 'shop')
                    ->where('payment_method_id', $methodId)
                    ->exists();
                if (!$exists) {
                    DB::table('payment_method_rules')->insert([
                        'service_context'   => 'shop',
                        'payment_method_id' => $methodId,
                        'is_allowed'        => true,
                        'priority'          => 10,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Additive-only policy on the prod copy: no-op.
    }
}
