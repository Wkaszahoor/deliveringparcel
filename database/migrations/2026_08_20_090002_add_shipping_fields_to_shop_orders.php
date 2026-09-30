<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingFieldsToShopOrders extends Migration
{
    public function up()
    {
        Schema::table('shop_orders', function (Blueprint $t) {
            $t->string('ship_name', 120)->nullable()->after('status');
            $t->string('ship_address', 500)->nullable()->after('ship_name');
            $t->string('ship_phone', 40)->nullable()->after('ship_address');
        });
    }

    public function down()
    {
        Schema::table('shop_orders', function (Blueprint $t) {
            $t->dropColumn(['ship_name', 'ship_address', 'ship_phone']);
        });
    }
}
