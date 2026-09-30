<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DynamicPageAdminController extends Controller
{
    public function index()
    {
        $pages = DynamicPage::withTrashed()
                            ->orderBy('section')
                            ->orderBy('sort_order')
                            ->get()
                            ->groupBy('section');

        return view('admin.dynamic-pages.index', compact('pages'));
    }

    public function create()
    {
        $sections = ['pages', 'blog', 'shop', 'services', 'careers', 'custom'];
        $layouts  = [
            'home2.layouts.app' => 'Home2 (Default)',
            'layouts.app'       => 'Legacy',
        ];

        return view('admin.dynamic-pages.create', compact('sections', 'layouts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:dynamic_pages,slug',
            'route_path'       => 'nullable|string|max:255|unique:dynamic_pages,route_path',
            'nav_label'        => 'nullable|string|max:100',
            'section'          => 'required|string|max:50',
            'icon'             => 'nullable|string|max:100',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'content'          => 'nullable|string',
            'layout'           => 'required|string|max:100',
            'show_in_header'   => 'boolean',
            'show_in_footer'   => 'boolean',
            'show_in_main_nav' => 'boolean',
            'is_active'        => 'boolean',
            'sort_order'       => 'integer|min:0',
            'controller_override' => 'nullable|string|max:255',
        ]);

        $data['slug']       = ($data['slug'] ?? '') ?: Str::slug($data['title']);
        $data['route_path'] = ($data['route_path'] ?? '') ?: '/' . $data['slug'];

        // Booleans from checkboxes
        foreach (['show_in_header', 'show_in_footer', 'show_in_main_nav', 'is_active'] as $f) {
            $data[$f] = $request->has($f);
        }

        $page = DynamicPage::create($data);

        // Auto-add to RouteManager
        $this->syncToRouteManager($page);

        return redirect()->route('admin.dynamic-pages.index')
                         ->with('success', "Page \"{$page->title}\" created. Route: {$page->route_path}");
    }

    public function edit(DynamicPage $dynamicPage)
    {
        $sections = ['pages', 'blog', 'shop', 'services', 'careers', 'custom'];
        $layouts  = [
            'home2.layouts.app' => 'Home2 (Default)',
            'layouts.app'       => 'Legacy',
        ];

        return view('admin.dynamic-pages.edit', compact('dynamicPage', 'sections', 'layouts'));
    }

    public function update(Request $request, DynamicPage $dynamicPage)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'nav_label'        => 'nullable|string|max:100',
            'section'          => 'required|string|max:50',
            'icon'             => 'nullable|string|max:100',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'content'          => 'nullable|string',
            'layout'           => 'required|string|max:100',
            'is_active'        => 'boolean',
            'sort_order'       => 'integer|min:0',
            'show_in_header'   => 'boolean',
            'show_in_footer'   => 'boolean',
            'show_in_main_nav' => 'boolean',
            'controller_override' => 'nullable|string|max:255',
        ]);

        foreach (['show_in_header', 'show_in_footer', 'show_in_main_nav', 'is_active'] as $f) {
            $data[$f] = $request->has($f);
        }

        $dynamicPage->update($data);

        $this->syncToRouteManager($dynamicPage);

        return redirect()->route('admin.dynamic-pages.index')
                         ->with('success', "Page \"{$dynamicPage->title}\" updated.");
    }

    public function toggleActive(DynamicPage $dynamicPage)
    {
        $dynamicPage->update(['is_active' => !$dynamicPage->is_active]);

        return back()->with(
            'success',
            "Page \"{$dynamicPage->title}\" " .
            ($dynamicPage->is_active ? 'activated' : 'deactivated') . '.'
        );
    }

    public function destroy(DynamicPage $dynamicPage)
    {
        if ($dynamicPage->is_locked) {
            return back()->with('error', 'This page is locked and cannot be deleted.');
        }

        $dynamicPage->delete();

        return redirect()->route('admin.dynamic-pages.index')
                         ->with('success', 'Page deleted (soft).');
    }

    public function restore($id)
    {
        $page = DynamicPage::withTrashed()->findOrFail($id);
        $page->restore();

        return back()->with('success', "Page \"{$page->title}\" restored.");
    }

    public function forceDelete($id)
    {
        $page = DynamicPage::withTrashed()->findOrFail($id);

        if ($page->is_locked) {
            return back()->with('error', 'Locked page cannot be permanently deleted.');
        }

        $page->forceDelete();

        return back()->with('success', 'Page permanently deleted.');
    }

    /**
     * When a page is created/updated, upsert a row in route_manager_settings.
     *
     * NOTE: the REAL route_manager_settings columns are route_key, label,
     * current_version, legacy_url, new_url, group, show_in_header,
     * show_in_footer, show_in_main_nav, is_locked, notes — NOT the
     * route_name/active_version/group_name names used in the original spec.
     */
    private function syncToRouteManager(DynamicPage $page): void
    {
        try {
            \DB::table('route_manager_settings')->updateOrInsert(
                ['route_key' => 'page_' . $page->slug],
                [
                    'label'            => $page->nav_label ?: $page->title,
                    'legacy_url'       => '/page/' . $page->slug,
                    'new_url'          => '/page/' . $page->slug,
                    'current_version'  => 'new',
                    'group'            => $page->section,
                    // (bool) coerce: a freshly created model has NULL for
                    // unposted boolean attrs (DB defaults are not in memory),
                    // and these columns are NOT NULL.
                    'show_in_header'   => (bool) $page->show_in_header,
                    'show_in_footer'   => (bool) $page->show_in_footer,
                    'show_in_main_nav' => (bool) $page->show_in_main_nav,
                    'is_locked'        => (bool) $page->is_locked,
                    'updated_at'       => now(),
                    'created_at'       => now(),
                ]
            );

            // Targeted cache clears (never Cache::flush() — it nukes settings etc.)
            \Cache::forget('route_mgr_all_links');
            \Cache::forget('route_mgr_mobile');
            \Cache::forget('route_mgr_page_' . $page->slug);
        } catch (\Throwable $e) {
            // Route manager table may not exist yet — non-fatal
            \Log::warning('syncToRouteManager failed: ' . $e->getMessage());
        }
    }
}
