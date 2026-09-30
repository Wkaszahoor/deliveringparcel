<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!class_exists('AddMatrixFlagsToCountriesTable')) {
    class AddMatrixFlagsToCountriesTable extends Migration
    {
        public function up()
        {
            Schema::table('countries', function (Blueprint $table) {
                if (!Schema::hasColumn('countries', 'allow_request_from')) {
                    $table->boolean('allow_request_from')->default(false)
                        ->comment('Client/Shopper can pick this country in the request form Ship From list');
                }
                if (!Schema::hasColumn('countries', 'allow_delivery_to')) {
                    $table->boolean('allow_delivery_to')->default(false)
                        ->comment('Country shows in the Ship To list — admin can deliver there');
                }
                if (!Schema::hasColumn('countries', 'allow_shipper')) {
                    $table->boolean('allow_shipper')->default(false)
                        ->comment('Country appears in the shipper service-countries grid');
                }
            });

            // Existing active countries stay available on every surface.
            DB::table('countries')->where('is_active', 1)->update([
                'allow_request_from' => 1,
                'allow_delivery_to'  => 1,
                'allow_shipper'      => 1,
            ]);
        }

        public function down()
        {
            Schema::table('countries', function (Blueprint $table) {
                foreach (['allow_request_from', 'allow_delivery_to', 'allow_shipper'] as $col) {
                    if (Schema::hasColumn('countries', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
