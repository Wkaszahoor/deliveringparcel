<?php

namespace App\Mobile\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mobile\Models\MobileUiAction;
use App\Mobile\Models\MobileUiElement;
use App\Mobile\Models\MobileUiScreen;
use App\Mobile\Models\MobileUiSection;
use App\Mobile\Services\UIControlsService;
use Illuminate\Http\Request;

/**
 * Web panel for the 4-level UI control system:
 * /admin/mobile-app/ui-controls — screens → sections → elements → actions,
 * each with admin/client Visible+Enabled toggles and a global status.
 * Every save clears the cache and bumps the version the apps poll.
 */
class UIControlsController extends Controller
{
    public function index()
    {
        $screens = MobileUiScreen::orderBy('screen_order')->get();
        $sections = MobileUiSection::orderBy('section_order')->get()->groupBy('screen_key');
        $elements = MobileUiElement::orderBy('element_order')->get()->groupBy('section_key');
        $actions = MobileUiAction::orderBy('action_order')->get()->groupBy('section_key');

        $version = app(UIControlsService::class)->getVersion();

        return view('mobile-admin.ui-controls.index', compact('screens', 'sections', 'elements', 'actions', 'version'));
    }

    public function updateScreen(Request $request, string $key)
    {
        return $this->updateRow(MobileUiScreen::where('screen_key', $key), $request, 'screen');
    }

    public function updateSection(Request $request, string $key)
    {
        return $this->updateRow(MobileUiSection::where('section_key', $key), $request, 'section');
    }

    public function updateElement(Request $request, string $key)
    {
        return $this->updateRow(MobileUiElement::where('element_key', $key), $request, 'element');
    }

    public function updateAction(Request $request, string $key)
    {
        $data = $request->validate($this->rules() + [
            'allowed_order_statuses'   => 'nullable|array',
            'allowed_order_statuses.*' => 'string|max:100',
            'confirmation_message'     => 'nullable|string|max:500',
            'confirmation_required'    => 'nullable|boolean',
            'action_label'             => 'nullable|string|max:100',
        ]);

        $action = MobileUiAction::where('action_key', $key)->firstOrFail();

        $payload = $this->boolPayload($data, $action);
        if (array_key_exists('allowed_order_statuses', $data)) {
            $payload['allowed_order_statuses'] = array_values($data['allowed_order_statuses'] ?: []);
        }
        foreach (['confirmation_message', 'confirmation_required', 'action_label'] as $f) {
            if (array_key_exists($f, $data)) {
                $payload[$f] = $f === 'confirmation_required' ? !empty($data[$f]) : $data[$f];
            }
        }

        $action->update($payload);

        return $this->saved($request, "Action \"{$action->action_name}\" saved.");
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'screens'               => 'nullable|array',
            'sections'              => 'nullable|array',
            'elements'              => 'nullable|array',
            'actions'               => 'nullable|array',
            'screens.*'             => 'array',
            'sections.*'            => 'array',
            'elements.*'            => 'array',
            'actions.*'             => 'array',
        ]);

        $updated = 0;
        foreach (['screens' => MobileUiScreen::class, 'sections' => MobileUiSection::class,
                  'elements' => MobileUiElement::class, 'actions' => MobileUiAction::class] as $group => $model) {
            $keyCol = $group === 'screens' ? 'screen_key' : ($group === 'sections' ? 'section_key'
                : ($group === 'elements' ? 'element_key' : 'action_key'));

            foreach (($data[$group] ?? []) as $key => $fields) {
                $row = $model::where($keyCol, $key)->first();
                if (!$row) continue;
                $row->update($this->boolPayload($fields, $row));
                $updated++;
            }
        }

        return response()->json(['ok' => true, 'updated_count' => $updated]);
    }

    /** Manual version bump — forces every app to re-sync on next poll. */
    public function bumpVersion(Request $request)
    {
        app(UIControlsService::class)->clearCache();
        $version = app(UIControlsService::class)->getVersion();

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'version' => $version])
            : back()->with('success', "Version bumped to {$version} — apps sync on next check.");
    }

    /* ── helpers ── */

    private function rules(): array
    {
        return [
            'global_status' => 'nullable|in:active,disabled,maintenance',
            'admin_visible' => 'nullable|boolean',
            'admin_enabled' => 'nullable|boolean',
            'client_visible'=> 'nullable|boolean',
            'client_enabled'=> 'nullable|boolean',
        ];
    }

    private function boolPayload(array $data, $row): array
    {
        $payload = [];
        foreach (['admin_visible', 'admin_enabled', 'client_visible', 'client_enabled'] as $f) {
            if (array_key_exists($f, $data)) $payload[$f] = !empty($data[$f]);
        }
        if (array_key_exists('global_status', $data)) $payload['global_status'] = $data['global_status'];

        return $payload;
    }

    private function updateRow($query, Request $request, string $type)
    {
        $row = $query->firstOrFail();
        $data = $request->validate($this->rules());
        $row->update($this->boolPayload($data, $row));

        $name = $row->screen_name ?? $row->section_name ?? $row->element_name ?? $row->action_name ?? $key ?? '';

        return $this->saved($request, ucfirst($type) . " \"{$name}\" saved.");
    }

    private function saved(Request $request, string $message)
    {
        return $request->wantsJson()
            ? response()->json(['ok' => true, 'message' => $message])
            : back()->with('success', $message);
    }
}
