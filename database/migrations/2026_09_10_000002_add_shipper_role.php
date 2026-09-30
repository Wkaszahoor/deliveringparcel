<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

if (!class_exists('AddShipperRole')) {
class AddShipperRole extends Migration
{
    public function up()
    {
        // Real table verified: roles(name, slug) — pivot with users is users_roles.
        foreach (['shipper', 'shipper_pending'] as $slug) {
            if (!DB::table('roles')->where('slug', $slug)->exists()) {
                DB::table('roles')->insert([
                    'name'       => ucfirst(str_replace('_', ' ', $slug)),
                    'slug'       => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('roles')->whereIn('slug', ['shipper', 'shipper_pending'])->delete();
    }
}
} // end class_exists guard
