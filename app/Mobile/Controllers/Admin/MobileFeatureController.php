<?php

namespace App\Mobile\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mobile\Models\AppUiSetting;
use App\Mobile\Models\MobileFeatureFlag;
use App\Mobile\Requests\UpdateFeaturesRequest;
use App\Mobile\Services\MobileSettingsService;

class MobileFeatureController extends Controller
{
    public function index()
    {
        $flags = MobileFeatureFlag::orderBy('feature_key')->get();
        $system = AppUiSetting::grouped()['system'] ?? [];

        $maintenance = (object) [
            'active'       => ($system['maintenance_active'] ?? '0') === '1',
            'message'      => $system['maintenance_message'] ?? '',
            'expected_back'=> $system['maintenance_expected_back'] ?? '',
        ];

        $forceUpdate = (object) [
            'required'    => ($system['force_update_required'] ?? '0') === '1',
            'min_ios'     => $system['force_update_min_ios'] ?? '1.0.0',
            'min_android' => $system['force_update_min_android'] ?? '1.0.0',
            'message'     => $system['force_update_message'] ?? '',
        ];

        return view('mobile-admin.features.index', compact('flags', 'maintenance', 'forceUpdate'));
    }

    public function update(UpdateFeaturesRequest $request)
    {
        foreach ($request->input('flags', []) as $key => $row) {
            MobileFeatureFlag::where('feature_key', $key)->update([
                'is_enabled'    => !empty($row['is_enabled']),
                'allowed_roles' => array_values($row['allowed_roles'] ?? []),
            ]);
        }

        $system = [];

        $maintenance = $request->input('maintenance', []);
        $system['maintenance_active']        = !empty($maintenance['active']) ? '1' : '0';
        $system['maintenance_message']       = (string) ($maintenance['message'] ?? '');
        $system['maintenance_expected_back'] = (string) ($maintenance['expected_back'] ?? '');

        $force = $request->input('force_update', []);
        $system['force_update_required']    = !empty($force['required']) ? '1' : '0';
        $system['force_update_min_ios']     = (string) ($force['min_ios'] ?? '1.0.0');
        $system['force_update_min_android'] = (string) ($force['min_android'] ?? '1.0.0');
        $system['force_update_message']     = (string) ($force['message'] ?? '');

        foreach ($system as $key => $value) {
            AppUiSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value, 'setting_group' => 'system']
            );
        }

        app(MobileSettingsService::class)->clearAllCaches();

        return back()->with('success', 'Feature flags & app modes saved.');
    }

    /** AJAX single-flag toggle. */
    public function toggleFlag(\Illuminate\Http\Request $request, string $key)
    {
        $flag = MobileFeatureFlag::where('feature_key', $key)->firstOrFail();
        $flag->update(['is_enabled' => !$flag->is_enabled]);

        app(MobileSettingsService::class)->clearAllCaches();

        return response()->json(['ok' => true, 'is_enabled' => $flag->is_enabled]);
    }
}
