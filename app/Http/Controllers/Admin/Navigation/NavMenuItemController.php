<?php

namespace App\Http\Controllers\Admin\Navigation;

use App\Http\Controllers\Controller;
use App\Models\NavMenuItem;
use App\Services\NavRenderer;
use Illuminate\Http\Request;

/**
 * FE-004 / FE-005 — Navigation manager (admin).
 */
class NavMenuItemController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.navigation.index', [
            'filters' => [
                'q'          => (string) $request->input('q', ''),
                'visibility' => (string) $request->input('visibility', ''),
                'active'     => (string) $request->input('active', ''),
            ],
        ]);
    }

    /** Paginated JSON for the lazy-table. */
    public function data(Request $request)
    {
        $query = NavMenuItem::query();

        if ($q = trim((string) $request->input('q', ''))) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('label', 'like', "%{$q}%")
                    ->orWhere('url', 'like', "%{$q}%")
                    ->orWhere('route_name', 'like', "%{$q}%");
            });
        }
        if (in_array($request->input('visibility'), NavMenuItem::VISIBILITIES, true)) {
            $query->where('visibility', $request->input('visibility'));
        }
        if ($request->input('active') === '1') {
            $query->where('is_active', true);
        } elseif ($request->input('active') === '0') {
            $query->where('is_active', false);
        }

        $perPage = (int) config('admin_settings.per_page', 20) ?: 20;

        return response()->json(
            $query->orderBy('parent_id')->orderBy('sort')->orderBy('id')
                ->paginate($perPage)
                ->through(fn (NavMenuItem $item) => $this->row($item))
        );
    }

    public function create()
    {
        return view('admin.navigation.form', [
            'item'    => new NavMenuItem(['is_active' => true, 'visibility' => 'everyone', 'sort' => 0]),
            'parents' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['is_active'] = $request->has('is_active');
        $data['new_tab']   = $request->has('new_tab');
        $data['parent_id'] = $this->safeParentId($request);

        NavMenuItem::create($data);
        NavRenderer::flushCache();

        return redirect()->route('admin.navigation.index')->with('success', 'Menu item created successfully.');
    }

    public function edit(NavMenuItem $navMenuItem)
    {
        return view('admin.navigation.form', [
            'item'    => $navMenuItem,
            'parents' => $this->parentOptions($navMenuItem->id),
        ]);
    }

    public function update(Request $request, NavMenuItem $navMenuItem)
    {
        $data = $this->validated($request);

        $data['is_active'] = $request->has('is_active');
        $data['new_tab']   = $request->has('new_tab');
        $data['parent_id'] = $this->safeParentId($request, $navMenuItem->id);

        $navMenuItem->update($data);
        NavRenderer::flushCache();

        return redirect()->route('admin.navigation.index')->with('success', 'Menu item updated successfully.');
    }

    public function destroy(NavMenuItem $navMenuItem)
    {
        // children are deleted by FK nullOnDelete -> they become top-level items
        $navMenuItem->delete();
        NavRenderer::flushCache();

        return redirect()->route('admin.navigation.index')->with('success', 'Menu item deleted successfully.');
    }

    /** Toggle is_active (AJAX). */
    public function toggle(Request $request, NavMenuItem $navMenuItem)
    {
        $navMenuItem->update(['is_active' => !$navMenuItem->is_active]);
        NavRenderer::flushCache();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'        => true,
                'is_active' => $navMenuItem->is_active,
                'label'     => $navMenuItem->is_active ? 'Active' : 'Inactive',
                'color'     => $navMenuItem->is_active ? 'success' : 'secondary',
            ]);
        }

        return redirect()->route('admin.navigation.index')->with('success', 'Menu item status updated.');
    }

    /** Move an item up/down among its siblings (AJAX). */
    public function move(Request $request, NavMenuItem $navMenuItem, string $direction)
    {
        $direction = $direction === 'up' ? 'up' : 'down';

        $siblings = NavMenuItem::where('parent_id', $navMenuItem->parent_id)
            ->orderBy('sort')->orderBy('id')->get();

        $pos = $siblings->search(fn ($s) => $s->id === $navMenuItem->id);
        $swapWith = $direction === 'up' ? $pos - 1 : $pos + 1;

        if ($pos === false || $swapWith < 0 || $swapWith >= $siblings->count()) {
            return response()->json(['ok' => false, 'message' => 'Item is already at the ' . ($direction === 'up' ? 'top' : 'bottom') . '.'], 422);
        }

        $neighbour = $siblings[$swapWith];

        $mySort = $navMenuItem->sort;
        $navMenuItem->update(['sort' => $neighbour->sort]);
        $neighbour->update(['sort' => $mySort]);

        // normalise sort values on this level (guards against legacy gaps)
        $order = 0;
        NavMenuItem::where('parent_id', $navMenuItem->parent_id)
            ->orderBy('sort')->orderBy('id')->get()
            ->each(function ($s) use (&$order) {
                if ((int) $s->sort !== $order) {
                    $s->forceFill(['sort' => $order])->saveQuietly();
                }
                $order++;
            });

        NavRenderer::flushCache();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Menu item moved ' . $direction . '.']);
        }

        return redirect()->route('admin.navigation.index')->with('success', 'Menu item moved ' . $direction . '.');
    }

    /* ------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------ */

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'      => 'required|string|max:100',
            'label'      => 'nullable|string|max:100',
            'url'        => 'nullable|string|max:500',
            'route_name' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9._\-]+$/',
            'icon_class' => 'nullable|string|max:100',
            'sort'       => 'nullable|integer|min:0|max:999999',
            'visibility' => 'required|in:everyone,guest,auth',
        ]);
    }

    /** Parent must exist, be top-level itself (max 2 levels) and not the item. */
    private function safeParentId(Request $request, $ignoreId = null)
    {
        $parentId = (int) $request->input('parent_id', 0);

        if ($parentId <= 0 || ($ignoreId && $parentId === (int) $ignoreId)) {
            return null;
        }

        $parent = NavMenuItem::find($parentId);
        if (!$parent || $parent->parent_id) {
            return null; // no grand-children: nesting depth capped at 2
        }

        return $parent->id;
    }

    /** Top-level items eligible as parents. */
    private function parentOptions($ignoreId = null)
    {
        return NavMenuItem::whereNull('parent_id')->orderBy('sort')->orderBy('id')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get();
    }

    /** Row shape for the lazy-table JSON. */
    private function row(NavMenuItem $item): array
    {
        return [
            'id'         => $item->id,
            'title'      => $item->parent_id ? '↳ ' . $item->title : $item->title,
            'label'      => $item->label ?: $item->title,
            'target'     => $item->route_name ?: ($item->url ?: '—'),
            'visibility' => ucfirst($item->visibility),
            'vis_badge'  => $item->visibility === 'auth' ? 'badge-info' : ($item->visibility === 'guest' ? 'badge-warning' : 'badge-secondary'),
            'sort'       => $item->sort,
            'is_active'  => $item->is_active,
            'urls'       => [
                'edit'   => route('admin.navigation.edit', $item->id),
                'delete' => route('admin.navigation.destroy', $item->id),
                'toggle' => route('admin.navigation.toggle', $item->id),
                'up'     => route('admin.navigation.move', ['nav_menu_item' => $item->id, 'direction' => 'up']),
                'down'   => route('admin.navigation.move', ['nav_menu_item' => $item->id, 'direction' => 'down']),
            ],
        ];
    }
}
