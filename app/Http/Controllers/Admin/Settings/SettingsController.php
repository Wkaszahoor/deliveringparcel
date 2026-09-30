<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Agent D — Settings admin module (config/admin_settings.php driven).
 *
 * edit     : tabbed form (Business / SEO / Theme / API / Preferences)
 * update   : validate + persist every key via Setting::set (transaction + cache flush)
 * apiStatus: JSON chip data — integration CONFIG PRESENCE only, never values.
 */
class SettingsController extends Controller
{
    public function edit()
    {
        $tabs = config('admin_settings.tabs', []);

        $values = [];
        foreach ($tabs as $tabKey => $tab) {
            foreach ($tab['fields'] ?? [] as $fieldKey => $field) {
                $value = Setting::get($fieldKey, $field['default'] ?? null);
                if (($field['type'] ?? '') === 'bool') {
                    $value = in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
                }
                $values[$fieldKey] = old($fieldKey, $value);
            }
        }

        return view('admin.settings.edit', [
            'tabs' => $tabs,
            'values' => $values,
            'apiStatus' => $this->integrationStatus(),
        ]);
    }

    public function update(Request $request)
    {
        $fields = $this->flatFields();

        $request->validate($this->buildRules($fields));

        DB::transaction(function () use ($request, $fields) {
            foreach ($fields as $key => $field) {
                $type = $field['type'] ?? 'string';

                if ($type === 'bool') {
                    // unchecked switches are absent from the POST body
                    $value = $request->has($key) ? '1' : '0';
                } else {
                    $value = $request->input($key);
                    if ($value === null || $value === '') {
                        $value = (string) ($field['default'] ?? '');
                    }
                }

                Setting::set($key, $value, $field['group'], $type);
            }
        });

        $tab = (string) $request->input('_tab', 'business');
        $tab = array_key_exists($tab, config('admin_settings.tabs', [])) ? $tab : 'business';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->to(route('admin.settings.edit') . '#tab-' . $tab)
            ->with('success', 'Settings saved successfully.');
    }

    /** GET /admin/settings/api-status — presence-only integration chips. */
    public function apiStatus()
    {
        return response()->json(['integrations' => array_values($this->integrationStatus())]);
    }

    /* ------------------------------------------------------------------
     * helpers
     * ------------------------------------------------------------------ */

    /** @return array<string, array> flattened field definitions with group */
    protected function flatFields(): array
    {
        $flat = [];
        foreach (config('admin_settings.tabs', []) as $tabKey => $tab) {
            foreach ($tab['fields'] ?? [] as $fieldKey => $field) {
                $flat[$fieldKey] = array_merge($field, ['group' => $tabKey]);
            }
        }

        return $flat;
    }

    /** Validation rules derived from field types + explicit 'validate' hints. */
    protected function buildRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $key => $field) {
            $type = $field['type'] ?? 'string';

            switch ($type) {
                case 'bool':
                    $base = ['nullable', 'boolean'];
                    break;
                case 'int':
                    $base = ['nullable', 'integer'];
                    break;
                case 'select':
                    $base = ['nullable', Rule::in(array_keys($field['options'] ?? []))];
                    break;
                case 'color':
                    $base = ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'];
                    break;
                case 'text':
                    $base = ['nullable', 'string', 'max:6000'];
                    break;
                default: // string
                    $base = ['nullable', 'string', 'max:191'];
                    break;
            }

            foreach (array_filter(explode('|', (string) ($field['validate'] ?? ''))) as $extra) {
                if (!in_array($extra, $base, true)) {
                    $base[] = $extra;
                }
            }

            $rules[$key] = $base;
        }

        return $rules;
    }

    /**
     * Presence-only status of the integrations declared in config
     * (config values are NEVER returned — only a boolean).
     */
    protected function integrationStatus(): array
    {
        $status = [];

        foreach (config('admin_settings.tabs', []) as $tabKey => $tab) {
            foreach ($tab['fields'] ?? [] as $fieldKey => $field) {
                if (empty($field['integration'])) {
                    continue;
                }

                $configured = true;
                foreach ((array) ($field['integration']['config_paths'] ?? []) as $configPath) {
                    if (trim((string) config($configPath, '')) === '') {
                        $configured = false;
                        break;
                    }
                }

                $status[$fieldKey] = [
                    'setting_key' => $fieldKey,
                    'name' => $field['integration']['name'] ?? ucfirst($fieldKey),
                    'env_vars' => $field['integration']['env_vars'] ?? [],
                    'configured' => $configured,
                    'enabled' => Setting::getBool($fieldKey),
                ];
            }
        }

        return $status;
    }
}
