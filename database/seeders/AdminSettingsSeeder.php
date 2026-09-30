<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Agent D — seeds default values for every declared settings key
 * (config/admin_settings.php). Idempotent: existing values are kept.
 */
class AdminSettingsSeeder extends Seeder
{
    public function run()
    {
        $created = 0;
        $kept = 0;

        foreach (config('admin_settings.tabs', []) as $tabKey => $tab) {
            foreach ($tab['fields'] ?? [] as $fieldKey => $field) {
                if (DB::table('settings')->where('key', $fieldKey)->exists()) {
                    $kept++;
                    continue;
                }

                Setting::set(
                    $fieldKey,
                    $field['default'] ?? '',
                    $tabKey,
                    $field['type'] ?? 'string'
                );
                $created++;
            }
        }

        $this->command?->info("AdminSettingsSeeder: {$created} default(s) seeded, {$kept} existing value(s) kept.");
    }
}
