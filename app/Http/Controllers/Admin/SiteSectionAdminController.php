<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SiteSectionAdminController extends Controller
{
    public function index()
    {
        $sections = DB::table('site_sections')->orderBy('sort_order')->get();

        return view('admin.site-sections.index', compact('sections'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_enabled'  => 'boolean',
            'show_in_nav' => 'boolean',
            'sort_order'  => 'integer|min:0',
            'index_route' => 'nullable|string|max:255',
        ]);

        $data['is_enabled']  = $request->has('is_enabled');
        $data['show_in_nav'] = $request->has('show_in_nav');
        $data['updated_at']  = now();

        DB::table('site_sections')->where('id', $id)->update($data);

        // Targeted cache clears — NOT Cache::flush(), which would nuke the
        // whole app cache (settings, mobile config, sitemaps, ...).
        Cache::forget('route_mgr_all_links');
        Cache::forget('route_mgr_mobile');
        Cache::forget('dp_settings_all');
        Cache::forget('sitemap.index.xml');

        return back()->with('success', 'Section updated.');
    }
}
