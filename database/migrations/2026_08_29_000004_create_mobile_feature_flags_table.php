<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileFeatureFlagsTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key', 50)->unique();
            $table->string('feature_name', 100);
            $table->boolean('is_enabled')->default(true);
            $table->json('allowed_roles')->nullable(); // MySQL strict: no JSON defaults; seeder always populates
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_feature_flags');
    }
}
