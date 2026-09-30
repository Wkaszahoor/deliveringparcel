<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarrierTables extends Migration
{
    public function up()
    {
        Schema::create('carriers', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->unique();     // dhl|fedex|ups|correos|royalmail|aftership|17track
            $t->string('name', 100);
            $t->boolean('is_enabled')->default(true)->index();
            $t->json('settings')->nullable();
            $t->timestamp('last_checked_at')->nullable();
            $t->timestamps();
        });

        Schema::create('carrier_tracking_lookups', function (Blueprint $t) {
            $t->id();
            $t->string('number', 100)->index();
            $t->string('carrier_code', 20)->nullable()->index();
            $t->string('result_status', 40)->nullable();
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrier_tracking_lookups');
        Schema::dropIfExists('carriers');
    }
}
