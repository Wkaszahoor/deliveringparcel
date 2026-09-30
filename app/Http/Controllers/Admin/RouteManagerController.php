<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RouteManagerSetting;
use Illuminate\Http\Request;

class RouteManagerController extends Controller
{
    /**
     * Overview: all route settings grouped by group name.
     */
    public function index()
    {
        $groups = RouteManagerSetting::orderBy('group')->orderBy('id')
            ->get()
            ->groupBy('group');

        return view('admin.route-manager.index', compact('groups'));
    }

    /**
     * Update a single route setting. Locked routes require
     * the confirmation phrase "i confirm" (case-insensitive, trimmed).
     */
    public function update(Request $request, RouteManagerSetting $setting)
    {
        // Locked routes require a confirmation phrase.
        if ($setting->is_locked) {
            $phrase = strtolower(trim($request->input('confirm_phrase', '')));
            if ($phrase !== 'i confirm') {
                return back()->withErrors([
                    'confirm_phrase' => 'Type exactly "I CONFIRM" to change a locked route.',
                ])->withInput();
            }
        }

        $validated = $request->validate([
            'current_version'  => 'required|in:legacy,new',
            'legacy_url'       => 'nullable|string|max:500',
            'new_url'          => 'nullable|string|max:500',
            'show_in_header'   => 'boolean',
            'show_in_footer'   => 'boolean',
            'show_in_main_nav' => 'boolean',
            'notes'            => 'nullable|string|max:1000',
            'label'            => 'required|string|max:150',
        ]);

        $validated['updated_by'] = auth()->id();
        $setting->update($validated);

        RouteManagerSetting::bustCache();

        return back()->with('success', "Route \"{$setting->label}\" updated successfully.");
    }

    /**
     * Switch every unlocked route in a group to legacy/new at once.
     */
    public function bulkSwitch(Request $request)
    {
        $request->validate([
            'group'           => 'required|string',
            'current_version' => 'required|in:legacy,new',
        ]);

        RouteManagerSetting::where('group', $request->group)
            ->where('is_locked', false)
            ->update([
                'current_version' => $request->current_version,
                'updated_by'      => auth()->id(),
            ]);

        RouteManagerSetting::bustCache();

        return back()->with('success', "All unlocked \"{$request->group}\" routes switched to {$request->current_version}.");
    }

    /**
     * Sync every DynamicPage into route_manager_settings as its own row
     * (route_key = "page_{slug}"). Upsert keeps existing manual edits to
     * other columns; our values win for label/urls/group/flags/lock.
     *
     * NOTE: the REAL route_manager_settings columns are route_key, label,
     * current_version, legacy_url, new_url, group, show_in_header,
     * show_in_footer, show_in_main_nav, is_locked, notes.
     */
    public function syncDynamicPages()
    {
        $pages  = \App\Models\DynamicPage::withTrashed()->get();
        $synced = 0;

        foreach ($pages as $page) {
            \DB::table('route_manager_settings')->updateOrInsert(
                ['route_key' => 'page_' . $page->slug],
                [
                    'label'            => $page->nav_label ?: $page->title,
                    'legacy_url'       => '/page/' . $page->slug,
                    'new_url'          => '/page/' . $page->slug,
                    'current_version'  => 'new',
                    'group'            => $page->section,
                    // (bool) coerce — these columns are NOT NULL.
                    'show_in_header'   => (bool) $page->show_in_header,
                    'show_in_footer'   => (bool) $page->show_in_footer,
                    'show_in_main_nav' => (bool) $page->show_in_main_nav,
                    'is_locked'        => (bool) $page->is_locked,
                    'updated_at'       => now(),
                    'created_at'       => now(),
                ]
            );
            $synced++;
        }

        // Targeted cache clears (per-key + aggregate). Never Cache::flush().
        foreach ($pages as $page) {
            \Cache::forget('route_mgr_page_' . $page->slug);
        }
        \Cache::forget('route_mgr_all_links');
        \Cache::forget('route_mgr_mobile');

        return back()->with('success', "Synced {$synced} dynamic pages to Route Manager.");
    }

    /**
     * JSON endpoint — returns the active URL for a given key.
     * Used by Blade partials via fetch() if needed.
     */
    public function activeUrl(string $key)
    {
        return response()->json([
            'url' => RouteManagerSetting::activeUrlCached($key),
        ]);
    }

    /**
     * Toggle a single boolean flag (show_in_header etc.) via AJAX.
     */
    public function toggleFlag(Request $request, RouteManagerSetting $setting)
    {
        $field = $request->validate([
            'field' => 'required|in:show_in_header,show_in_footer,show_in_main_nav',
        ])['field'];

        $setting->update([
            $field       => !$setting->{$field},
            'updated_by' => auth()->id(),
        ]);

        RouteManagerSetting::bustCache();

        return response()->json(['value' => $setting->fresh()->{$field}]);
    }
}
