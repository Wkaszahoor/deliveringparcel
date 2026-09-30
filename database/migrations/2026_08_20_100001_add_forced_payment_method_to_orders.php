<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-08-20 — Admin negotiation feature: per-order forced payment method.
 *
 * When set (e.g. "bank_transfer"), the customer-facing checkout for that
 * single order offers ONLY this method, regardless of the global
 * payment_method_rules for the service context. Admins set/clear it from
 * the admin order detail page while negotiating the order.
 *
 * Additive-only (nullable column) — safe on the live prod copy.
 */
class AddForcedPaymentMethodToOrders extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('forced_payment_method_code', 30)->nullable()->after('archived_at');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('forced_payment_method_code');
        });
    }
}
