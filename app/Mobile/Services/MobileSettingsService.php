<?php

namespace App\Mobile\Services;

use App\Mobile\Models\AppScreenSetting;
use App\Mobile\Models\AppUiSetting;
use App\Mobile\Models\MobileFeatureFlag;
use Illuminate\Support\Facades\Cache;

/**
 * Single place that assembles the payload served by
 * GET /api/mobile/v1/settings, plus the one cache-flusher every
 * admin save calls.
 */
class MobileSettingsService
{
    /** Full settings payload (cached). */
    public function getFullSettings(): array
    {
        return Cache::remember('app_settings', config('mobile.cache_ttl.settings', 300), function () {
            $system = AppUiSetting::grouped()['system'] ?? [];

            return [
                'screens'  => AppScreenSetting::allAsApiFormat(),
                'ui'       => AppUiSetting::allAsApiFormat(),
                'features' => MobileFeatureFlag::allAsApiFormat(),

                'maintenance' => [
                    'is_active'     => ($system['maintenance_active'] ?? '0') === '1',
                    'message'       => $system['maintenance_message'] ?? '',
                    'expected_back' => $system['maintenance_expected_back'] ?? '',
                ],
                'force_update' => [
                    'required'    => ($system['force_update_required'] ?? '0') === '1',
                    'min_ios'     => $system['force_update_min_ios']     ?? '1.0.0',
                    'min_android' => $system['force_update_min_android'] ?? '1.0.0',
                    'message'     => $system['force_update_message'] ?? '',
                ],
            ];
        });
    }

    public function clearAllCaches(): void
    {
        Cache::forget('mobile_labels');
        Cache::forget('app_screen_settings');
        Cache::forget('app_ui_settings');
        Cache::forget('mobile_feature_flags');
        Cache::forget('app_settings');
    }
}
