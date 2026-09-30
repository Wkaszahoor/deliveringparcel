<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsTaxonomyAdminController extends Controller
{
    private array $taxonomies = [
        'category'     => 'Category',
        'tag'          => 'Tag',
        'service_area' => 'Service Area',
    ];

    private array $postTypes = [
        'page'      => 'Static Page',
        'blog_post' => 'Blog Post',
        'product'   => 'Product',
        'service'   => 'Service',
    ];

    // ── INDEX ──────────────────────────────────────────────────────────────
    public function index()
    {
        $taxonomies = CmsTaxonomy::withCount('posts')
            ->orderBy('taxonomy')->orderBy('post_type')->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($t) => $t->taxonomy . '_' . $t->post_type);

        return view('admin.cms.taxonomies.index', compact('taxonomies'));
    }

    // ── CREATE ─────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $taxonomyOptions = $this->taxonomies;
        $postTypeOptions = $this->postTypes;

        return view('admin.cms.taxonomies.create', [
            'taxonomyOptions' => $taxonomyOptions,
            'postTypeOptions' => $postTypeOptions,
            'parents'         => $this->parentOptions($request->get('taxonomy', 'category'), $request->get('post_type', 'blog_post')),
            'taxonomy'        => null,
        ]);
    }

    // ── STORE ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $this->validateTerm($request);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        CmsTaxonomy::create($data);

        return redirect()->route('admin.cms.taxonomies.index')
                         ->with('success', "Term \"{$data['name']}\" created.");
    }

    // ── EDIT ───────────────────────────────────────────────────────────────
    public function edit(CmsTaxonomy $cmsTaxonomy)
    {
        $taxonomyOptions = $this->taxonomies;
        $postTypeOptions = $this->postTypes;

        return view('admin.cms.taxonomies.edit', [
            'taxonomy'        => $cmsTaxonomy,
            'taxonomyOptions' => $taxonomyOptions,
            'postTypeOptions' => $postTypeOptions,
            'parents'         => $this->parentOptions($cmsTaxonomy->taxonomy, $cmsTaxonomy->post_type, $cmsTaxonomy->id),
        ]);
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────
    public function update(Request $request, CmsTaxonomy $cmsTaxonomy)
    {
        $data = $this->validateTerm($request, $cmsTaxonomy->id);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $cmsTaxonomy->update($data);

        return redirect()->route('admin.cms.taxonomies.index')
                         ->with('success', 'Term updated.');
    }

    // ── DELETE ─────────────────────────────────────────────────────────────
    public function destroy(CmsTaxonomy $cmsTaxonomy)
    {
        $cmsTaxonomy->posts()->detach();
        // Re-parent children before removing
        CmsTaxonomy::where('parent_id', $cmsTaxonomy->id)->update(['parent_id' => null]);
        $cmsTaxonomy->delete();

        return back()->with('success', 'Term deleted.');
    }

    // ── PRIVATE HELPERS ────────────────────────────────────────────────────
    private function validateTerm(Request $request, $ignoreId = null): array
    {
        $slugRule = Rule::unique('cms_taxonomies', 'slug')
            ->where(fn ($q) => $q->where('taxonomy', $request->input('taxonomy'))
                                 ->where('post_type', $request->input('post_type')));
        if ($ignoreId) {
            $slugRule->ignore($ignoreId);
        }

        return $request->validate([
            'taxonomy'     => 'required|in:category,tag,service_area',
            'post_type'    => 'required|in:page,blog_post,product,service',
            'name'         => 'required|string|max:200',
            'slug'         => ['nullable', 'string', 'max:200', $slugRule],
            'description'  => 'nullable|string',
            'parent_id'    => 'nullable|integer',
            'sort_order'   => 'nullable|integer|min:0',
        ]);
    }

    private function parentOptions(string $taxonomy, string $postType, $ignoreId = null)
    {
        return CmsTaxonomy::where('taxonomy', $taxonomy)
            ->where('post_type', $postType)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->orderBy('name')
            ->pluck('name', 'id');
    }
}
