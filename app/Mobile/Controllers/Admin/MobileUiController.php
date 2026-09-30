<?php

namespace App\Mobile\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mobile\Models\AppUiSetting;
use App\Mobile\Requests\UpdateUiRequest;
use App\Mobile\Services\MobileSettingsService;

class MobileUiController extends Controller
{
    public function index()
    {
        $grouped = AppUiSetting::grouped();

        return view('mobile-admin.ui.index', compact('grouped'));
    }

    public function update(UpdateUiRequest $request)
    {
        // colors[primary_color] etc. → key primary_color in group app_colors
        foreach (($request->input('colors', [])) as $key => $value) {
            if ($value !== null && $value !== '') {
                AppUiSetting::updateOrCreate(
                    ['setting_key' => $key],
                    ['setting_value' => $value, 'setting_group' => 'app_colors']
                );
            }
        }

        // message_colors[admin][bubble] → key admin_bubble_color in group message_colors
        foreach (($request->input('message_colors', [])) as $who => $parts) {
            foreach (['bubble' => 'bubble_color', 'text' => 'text_color', 'position' => 'position'] as $part => $suffix) {
                $value = $parts[$part] ?? null;
                if ($value !== null && $value !== '') {
                    AppUiSetting::updateOrCreate(
                        ['setting_key' => $who . '_' . $suffix],
                        ['setting_value' => $value, 'setting_group' => 'message_colors']
                    );
                }
            }
        }

        // typography[font_size_base] → key font_size_base in group typography
        foreach (($request->input('typography', [])) as $key => $value) {
            if ($value !== null && $value !== '') {
                AppUiSetting::updateOrCreate(
                    ['setting_key' => $key],
                    ['setting_value' => (string) $value, 'setting_group' => 'typography']
                );
            }
        }

        // layout[screen_bottom_padding] → px of space under scroll content
        // so nothing hides behind the tab bar (admin-tunable).
        $padding = $request->input('layout.screen_bottom_padding');
        if ($padding !== null && $padding !== '') {
            AppUiSetting::updateOrCreate(
                ['setting_key' => 'screen_bottom_padding'],
                ['setting_value' => (string) max(0, min(200, (int) $padding)), 'setting_group' => 'layout']
            );
        }

        app(MobileSettingsService::class)->clearAllCaches();

        return back()->with('success', 'App UI saved — colors and fonts apply on next app refresh.');
    }
}
