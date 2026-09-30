<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Server-side price verification (2026-09-12): OrdersController@store recomputes
 * every product row total from quantity × price. When a submitted value does not
 * match, the computed (authoritative) value is stored and the mismatch details
 * land in orders.price_flags — shown as a red banner on the admin order page and
 * pushed to the admin as a notification.
 */
class AddPriceFlagsToOrdersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'price_flags')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->text('price_flags')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'price_flags')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('price_flags');
            });
        }
    }
}
