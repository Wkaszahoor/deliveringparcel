<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNotificationTimerSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add notification timer setting with default 30 seconds (30000ms)
        DB::table('settings')->insertOrIgnore([
            'key' => 'preferences_notification_timer',
            'value' => '30000',
            'group' => 'preferences',
            'type' => 'number'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('key', 'preferences_notification_timer')->delete();
    }
}
