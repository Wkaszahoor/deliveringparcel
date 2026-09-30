<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent D — Settings module.
 * New key/value settings table (guarded — never re-created / dropped).
 */
class CreateSettingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->string('group', 64)->default('general');
            $table->string('type', 32)->default('string');
            $table->timestamps();
        });
    }

    public function down()
    {
        // Intentionally NOT dropping — live production copy.
    }
}
