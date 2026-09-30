<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsMenuItem;
use App\Models\CmsPost;
use App\Models\CmsTaxonomy;
use App\Models\NavMenu;
use App\Services\Cms\CmsNavService;
use Illuminate\Http\Request;

class CmsMenuAdminController extends Controller
{
    public function index()
    {
        $menus = NavMenu::with(['allItems'])->orderBy('id')->get();
        return view('admin.cms.menus.index', compact('menus'));
    }

    public function show(NavMenu $menu)
    {
        // Top-level items WITH nested children for visual builder
        $items = CmsMenuItem::where('menu_id', $menu->id)
                            ->whereNull('parent_id')
                            ->with('allChildren.allChildren')
                            ->orderBy('sort_order')
                            ->get();

        // Data for the "Add Item" panel
        $pages      = CmsPost::ofType('page')->published()->orderBy('title')->get();
        $blogs      = CmsPost::ofType('blog_post')->published()->orderBy('title')->take(50)->get();
        $products   = CmsPost::ofType('product')->published()->orderBy('title')->take(50)->get();
        $services   = CmsPost::ofType('service')->published()->orderBy('title')->get();
        $categories = CmsTaxonomy::where('taxonomy', 'category')->orderBy('name')->get();
        $tags       = CmsTaxonomy::where('taxonomy', 'tag')->orderBy('name')->get();

        return view('admin.cms.menus.show',
            compact('menu', 'items', 'pages', 'blogs', 'products', 'services',
                    'categories', 'tags'));
    }

    // Add a new item to a menu
    public function addItem(Request $request, NavMenu $menu)
    {
        $data = $request->validate([
            'label'      => 'required|string|max:200',
            'item_type'  => 'required|in:custom,cms_post,taxonomy,section,route',
            'url'        => 'nullable|string|max:500',
            'object_id'  => 'nullable|integer',
            'parent_id'  => 'nullable|integer',
            'target'     => 'in:_self,_blank',
            'icon'       => 'nullable|string|max:100',
            'show_badge' => 'boolean',
            'badge_text' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:30',
        ]);

        // Auto-resolve URL from object
        if (!$data['url'] && $data['item_type'] === 'cms_post' && $data['object_id']) {
            $post = CmsPost::find($data['object_id']);
            $data['url'] = $post?->url;
        }
        if (!$data['url'] && $data['item_type'] === 'taxonomy' && $data['object_id']) {
            $tax = CmsTaxonomy::find($data['object_id']);
            $data['url'] = $tax?->url;
        }

        // Sort order = last + 1
        $maxOrder = CmsMenuItem::where('menu_id', $menu->id)
                               ->where('parent_id', $data['parent_id'] ?? null)
                               ->max('sort_order') ?? 0;
        $data['sort_order'] = $maxOrder + 1;
        $data['menu_id']    = $menu->id;
        $data['is_active']  = true;

        CmsMenuItem::create($data);
        app(CmsNavService::class)->bustAll();

        return back()->with('success', 'Menu item added.');
    }

    // Reorder via AJAX — accepts JSON array of {id, sort_order, parent_id}
    public function reorder(Request $request, NavMenu $menu)
    {
        $items = $request->validate(['items' => 'required|array']);
        foreach ($items['items'] as $item) {
            CmsMenuItem::where('id', $item['id'])
                       ->where('menu_id', $menu->id)
                       ->update([
                           'sort_order' => (int) $item['sort_order'],
                           'parent_id'  => $item['parent_id'] ?: null,
                       ]);
        }
        app(CmsNavService::class)->bustAll();
        return response()->json(['ok' => true]);
    }

    // Edit a single item
    public function editItem(Request $request, NavMenu $menu, CmsMenuItem $item)
    {
        $data = $request->validate([
            'label'      => 'required|string|max:200',
            'url'        => 'nullable|string|max:500',
            'target'     => 'in:_self,_blank',
            'icon'       => 'nullable|string|max:100',
            'is_active'  => 'boolean',
            'show_badge' => 'boolean',
            'badge_text' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:30',
        ]);
        $data['is_active']  = $request->boolean('is_active');
        $data['show_badge'] = $request->boolean('show_badge');
        $item->update($data);
        app(CmsNavService::class)->bustAll();
        return back()->with('success', 'Item updated.');
    }

    public function removeItem(NavMenu $menu, CmsMenuItem $item)
    {
        // Also remove children
        CmsMenuItem::where('parent_id', $item->id)->delete();
        $item->delete();
        app(CmsNavService::class)->bustAll();
        return back()->with('success', 'Item removed.');
    }
}
