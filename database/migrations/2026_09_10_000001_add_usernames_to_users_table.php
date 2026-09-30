<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!class_exists('AddUsernamesToUsersTable')) {
class AddUsernamesToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'customer_username')) {
                // Customer tracking username — CUS-A7K2P1
                $table->string('customer_username', 20)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'shipper_username')) {
                // Shipper tracking username — SHP-UK-4821
                $table->string('shipper_username', 20)->nullable()->unique()->after('customer_username');
            }
            if (!Schema::hasColumn('users', 'username_generated_at')) {
                $table->timestamp('username_generated_at')->nullable()->after('shipper_username');
            }
        });

        // Backfill: every existing user gets a customer username immediately.
        $users = DB::table('users')->whereNull('customer_username')->get();
        foreach ($users as $user) {
            $attempts = 0;
            do {
                $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                $rand = '';
                for ($i = 0; $i < 6; $i++) {
                    $rand .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $username = 'CUS-' . $rand;
                $attempts++;
            } while (DB::table('users')->where('customer_username', $username)->exists()
                && $attempts < 50);

            DB::table('users')->where('id', $user->id)->update([
                'customer_username'     => $username,
                'username_generated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'customer_username',
                'shipper_username',
                'username_generated_at',
            ]);
        });
    }
}
} // end class_exists guard
