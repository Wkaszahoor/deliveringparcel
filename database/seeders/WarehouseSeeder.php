<?php

namespace Database\Seeders;

use App\Models\WarehouseBin;
use App\Models\WarehousePackage;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        if (WarehouseBin::exists()) {
            return;
        }

        /* 3 zones × 4 bins */
        $bins = [];
        foreach (['A' => 'Receiving', 'B' => 'Shelving', 'C' => 'Oversize'] as $zoneLetter => $zoneName) {
            foreach (range(1, 4) as $n) {
                $bins[] = WarehouseBin::create([
                    'code' => sprintf('%s-%02d-1', $zoneLetter, $n),
                    'zone' => $zoneName,
                    'capacity' => 100,
                    'is_active' => true,
                ]);
            }
        }

        /* Two pending arrivals + one already received */
        $user = \DB::table('users')->orderBy('id')->first();
        WarehousePackage::create([
            'user_id' => $user->id ?? null,
            'expected_tracking' => '1Z999AA10123456784',
            'status' => 'pending',
            'created_by' => 1,
        ]);
        WarehousePackage::create([
            'user_id' => $user->id ?? null,
            'expected_tracking' => 'JD014600003812345678',
            'status' => 'pending',
            'created_by' => 1,
        ]);
        WarehousePackage::create([
            'user_id' => $user->id ?? null,
            'expected_tracking' => '9400111899223197428490',
            'status' => 'received',
            'received_at' => now()->subDays(2),
            'storage_bin_id' => $bins[0]->id,
            'created_by' => 1,
        ]);
    }
}
