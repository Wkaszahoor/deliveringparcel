<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('AddShipperColumnsToOrders')) {
class AddShipperColumnsToOrders extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'has_shipper_assignment')) {
                $table->boolean('has_shipper_assignment')->default(false);
            }
            if (!Schema::hasColumn('orders', 'shipper_proof_approved')) {
                $table->boolean('shipper_proof_approved')->default(false);
            }
            if (!Schema::hasColumn('orders', 'delivery_address_submitted')) {
                $table->boolean('delivery_address_submitted')->default(false);
            }
            if (!Schema::hasColumn('orders', 'delivery_address_forwarded')) {
                $table->boolean('delivery_address_forwarded')->default(false);
            }
            if (!Schema::hasColumn('orders', 'shipper_tracking_shared')) {
                $table->boolean('shipper_tracking_shared')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['has_shipper_assignment', 'shipper_proof_approved',
                'delivery_address_submitted', 'delivery_address_forwarded',
                'shipper_tracking_shared'];
            $existing = array_filter($cols, fn ($c) => Schema::hasColumn('orders', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
}
} // end class_exists guard
