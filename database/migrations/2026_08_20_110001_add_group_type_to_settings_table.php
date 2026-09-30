<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026-08-20 (laptop baseline) — bring the legacy 5-column `settings`
 * table up to the schema the Settings module (Setting::set) expects:
 * adds `group` (default 'general') and `type` (default 'string').
 * Additive-only; existing key/value rows are preserved.
 */
class AddGroupTypeToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'group')) {
                $table->string('group', 64)->default('general')->after('value');
            }
            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type', 32)->default('string')->after('group');
            }
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'group')) {
                $table->dropColumn('group');
            }
            if (Schema::hasColumn('settings', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
}
