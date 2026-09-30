<?php

namespace App\Mobile\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mobile\Models\AppScreenSetting;
use App\Mobile\Models\AppUiSetting;
use App\Mobile\Requests\UpdateScreenRequest;
use App\Mobile\Services\MobileSettingsService;
use Illuminate\Http\Request;

class MobileScreenController extends Controller
{
    public const ELEMENT_OPTIONS = [
        'order_number', 'order_status', 'order_price', 'order_notes', 'action_buttons',
    ];

    public const BUTTON_OPTIONS = [
        'view_detail', 'track_order', 'cancel_order', 'reorder',
    ];

    public function index()
    {
        $main = AppScreenSetting::main()->get();
        $ui = AppUiSetting::allKeyed();

        return view('mobile-admin.screens.index', compact('main', 'ui'));
    }

    public function update(UpdateScreenRequest $request, string $key)
    {
        $screen = AppScreenSetting::where('screen_key', $key)->firstOrFail();

        $payload = [];
        if ($request->has('is_visible')) $payload['is_visible'] = (bool) $request->boolean('is_visible');
        if ($request->has('is_enabled')) $payload['is_enabled'] = (bool) $request->boolean('is_enabled');
        if ($request->has('visible_to')) $payload['visible_to'] = array_values($request->input('visible_to', []));

        if ($request->has('config')) {
            $config = $screen->config ?? [];
            foreach (['visible_elements', 'visible_buttons'] as $block) {
                if ($request->has("config.$block")) {
                    $allowed = $block === 'visible_elements' ? self::ELEMENT_OPTIONS : self::BUTTON_OPTIONS;
                    $map = [];
                    foreach ($allowed as $opt) {
                        $map[$opt] = in_array($opt, (array) $request->input("config.$block", []));
                    }
                    $config[$block] = $map;
                }
            }
            $payload['config'] = $config;
        }

        $screen->update($payload);
        app(MobileSettingsService::class)->clearAllCaches();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Screen "' . $screen->screen_name . '" saved.']);
        }

        return back()->with('success', 'Screen updated');
    }

    public function bulkUpdate(Request $request)
    {
        $rows = $request->validate([
            'screens'                => 'required|array',
            'screens.*.screen_key'   => 'required|string',
            'screens.*.is_visible'   => 'sometimes|boolean',
            'screens.*.is_enabled'   => 'sometimes|boolean',
            'screens.*.visible_to'   => 'sometimes|array',
            'screens.*.visible_to.*' => 'in:admin,client',
        ])['screens'];

        foreach ($rows as $row) {
            $screen = AppScreenSetting::where('screen_key', $row['screen_key'])->first();
            if (!$screen) continue;

            $payload = [];
            if (array_key_exists('is_visible', $row)) $payload['is_visible'] = (bool) $row['is_visible'];
            if (array_key_exists('is_enabled', $row)) $payload['is_enabled'] = (bool) $row['is_enabled'];
            if (array_key_exists('visible_to', $row)) $payload['visible_to'] = array_values($row['visible_to'] ?: []);

            $screen->update($payload);
        }

        app(MobileSettingsService::class)->clearAllCaches();

        return response()->json(['ok' => true, 'message' => count($rows) . ' screens saved.']);
    }

    /**
     * Screen config panel save. For "messages" the panel edits the
     * canonical bubble colors stored in app_ui_settings
     * (group message_colors) — kept under ui[...] in the request.
     */
    public function updateConfig(UpdateScreenRequest $request, string $key)
    {
        if ($key === 'messages' && $request->has('ui')) {
            $ui = $request->validate([
                'ui.admin_bubble_color'  => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'ui.admin_text_color'    => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'ui.admin_position'      => 'nullable|in:left,right,center',
                'ui.client_bubble_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'ui.client_text_color'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'ui.client_position'     => 'nullable|in:left,right,center',
                'ui.system_bubble_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'ui.system_text_color'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'ui.system_position'     => 'nullable|in:left,right,center',
            ])['ui'];

            $groups = AppUiSetting::query()->pluck('setting_group', 'setting_key')->toArray();
            foreach ($ui as $k => $v) {
                AppUiSetting::updateOrCreate(
                    ['setting_key' => $k],
                    ['setting_value' => $v, 'setting_group' => $groups[$k] ?? 'message_colors']
                );
            }
        }

        app(MobileSettingsService::class)->clearAllCaches();

        return response()->json(['ok' => true, 'message' => 'Screen config saved.']);
    }
}
