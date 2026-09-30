<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsPost;
use App\Models\CmsTaxonomy;
use App\Services\Cms\CmsMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsPostAdminController extends Controller
{
    private array $postTypes = [
        'page'      => 'Static Page',
        'blog_post' => 'Blog Post',
        'product'   => 'Product',
        'service'   => 'Service',
    ];

    private array $statuses = [
        'draft', 'published', 'scheduled', 'private',
    ];

    // ── INDEX ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = CmsPost::with(['categories', 'author'])
                        ->withTrashed();

        if ($request->filled('type')) {
            $query->where('post_type', $request->type);
        }
        if ($request->filled('status')) {
            if ($request->status === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                   ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        $query->orderByDesc('updated_at');
        $posts     = $query->paginate(20)->withQueryString();
        $postTypes = $this->postTypes;
        $counts    = [];
        foreach (array_keys($this->postTypes) as $t) {
            $counts[$t] = CmsPost::where('post_type', $t)->count();
        }

        return view('admin.cms.posts.index', compact('posts', 'postTypes', 'counts'));
    }

    // ── CREATE ─────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $postType  = $request->get('type', 'page');
        $postTypes = $this->postTypes;
        $statuses  = $this->statuses;
        $parents   = CmsPost::where('post_type', $postType)
                            ->whereNull('parent_id')
                            ->orderBy('title')
                            ->pluck('title', 'id');
        $categories = CmsTaxonomy::where('taxonomy', 'category')
                                 ->where('post_type', $postType)
                                 ->orderBy('name')
                                 ->get();
        $tags = CmsTaxonomy::where('taxonomy', 'tag')
                           ->where('post_type', $postType)
                           ->orderBy('name')
                           ->get();
        $serviceAreas = CmsTaxonomy::where('taxonomy', 'service_area')
                                   ->orderBy('name')->get();
        $templates = $this->getTemplatesForType($postType);
        $layouts   = $this->getLayouts();

        return view('admin.cms.posts.create',
            compact('postType', 'postTypes', 'statuses', 'parents',
                    'categories', 'tags', 'serviceAreas', 'templates', 'layouts'));
    }

    // ── STORE ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $this->validatePost($request);
        $data['author_id'] = auth()->id();
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        // Handle featured image upload
        if ($request->hasFile('featured_image_file')) {
            $media = app(CmsMediaService::class)
                         ->store($request->file('featured_image_file'), auth()->id());
            $data['featured_image'] = $media->path;
        }

        // Build meta JSON
        $data['meta'] = $this->buildMeta($request, $data['post_type']);

        $post = CmsPost::create($data);

        // Sync taxonomies
        $this->syncTaxonomies($post, $request);

        return redirect()->route('admin.cms.posts.edit', $post)
                         ->with('success', "Post \"{$post->title}\" created successfully.");
    }

    // ── EDIT ───────────────────────────────────────────────────────────────
    /** 1-click auto-fix preview: deterministic proposals, nothing saved. */
    public function autofill(CmsPost $cmsPost, \App\Services\Seo\SeoAutoFixService $fixer)
    {
        return response()->json(['ok' => true, 'data' => $fixer->propose($cmsPost, auth()->id())]);
    }

    /** 1-click auto-fix apply: writes only the ticked choices. */
    public function autofixApply(Request $request, CmsPost $cmsPost, \App\Services\Seo\SeoAutoFixService $fixer, \App\Services\Seo\SeoAnalyzerService $analyzer)
    {
        $choices = $request->validate([
            'meta_title'         => 'nullable|boolean',
            'meta_description'   => 'nullable|boolean',
            'slug'               => 'nullable|boolean',
            'meta_keywords'      => 'nullable|boolean',
            'featured_image'     => 'nullable|boolean',
            'author_id'          => 'nullable|boolean',
            'strip_filler'       => 'nullable|boolean',
            'add_internal_links' => 'nullable|boolean',
        ]);

        $result = $fixer->apply($cmsPost, array_map(fn ($v) => (bool) $v, $choices), auth()->id());

        return response()->json([
            'ok'      => true,
            'applied' => $result['applied'],
            'report'  => $analyzer->analyzePost($cmsPost->fresh()),
        ]);
    }

    /** SEO Content Intelligence — analyze a post (fields + rendered page). */
    public function seo(CmsPost $cmsPost, \App\Services\Seo\SeoAnalyzerService $analyzer)
    {
        return response()->json(['data' => $analyzer->analyze($cmsPost)]);
    }

    public function edit(CmsPost $cmsPost)
    {
        $postType  = $cmsPost->post_type;
        $postTypes = $this->postTypes;
        $statuses  = $this->statuses;
        $parents   = CmsPost::where('post_type', $postType)
                            ->whereNull('parent_id')
                            ->where('id', '!=', $cmsPost->id)
                            ->orderBy('title')
                            ->pluck('title', 'id');
        $categories = CmsTaxonomy::where('taxonomy', 'category')
                                 ->where('post_type', $postType)
                                 ->orderBy('name')->get();
        $tags = CmsTaxonomy::where('taxonomy', 'tag')
                           ->where('post_type', $postType)
                           ->orderBy('name')->get();
        $serviceAreas = CmsTaxonomy::where('taxonomy', 'service_area')
                                   ->orderBy('name')->get();
        $templates      = $this->getTemplatesForType($postType);
        $layouts        = $this->getLayouts();
        $selectedTaxIds = $cmsPost->taxonomies()->pluck('cms_taxonomies.id')->toArray();

        return view('admin.cms.posts.edit',
            compact('cmsPost', 'postType', 'postTypes', 'statuses', 'parents',
                    'categories', 'tags', 'serviceAreas', 'templates', 'layouts',
                    'selectedTaxIds'));
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────
    public function update(Request $request, CmsPost $cmsPost)
    {
        if ($cmsPost->is_locked) {
            return back()->with('error', 'This post is locked.');
        }

        $data = $this->validatePost($request, $cmsPost->id);

        if ($request->hasFile('featured_image_file')) {
            $media = app(CmsMediaService::class)
                         ->store($request->file('featured_image_file'), auth()->id());
            $data['featured_image'] = $media->path;
        } elseif ($request->input('featured_image')) {
            $data['featured_image'] = $request->input('featured_image');
        }

        $data['meta'] = $this->buildMeta($request, $data['post_type']);
        $cmsPost->update($data);
        $this->syncTaxonomies($cmsPost, $request);

        return redirect()->route('admin.cms.posts.edit', $cmsPost)
                         ->with('success', 'Post updated successfully.');
    }

    // ── QUICK TOGGLE STATUS ────────────────────────────────────────────────
    public function toggleStatus(CmsPost $cmsPost)
    {
        $new = $cmsPost->status === 'published' ? 'draft' : 'published';
        $cmsPost->update([
            'status'       => $new,
            'published_at' => $new === 'published' ? now() : $cmsPost->published_at,
        ]);
        return back()->with('success', "Status changed to {$new}.");
    }

    // ── SOFT DELETE ────────────────────────────────────────────────────────
    public function destroy(CmsPost $cmsPost)
    {
        if ($cmsPost->is_locked) {
            return back()->with('error', 'Locked post cannot be deleted.');
        }
        $cmsPost->delete();
        return redirect()->route('admin.cms.posts.index')
                         ->with('success', 'Post moved to trash.');
    }

    public function restore($id)
    {
        $post = CmsPost::withTrashed()->findOrFail($id);
        $post->restore();
        return back()->with('success', 'Post restored.');
    }

    public function forceDelete($id)
    {
        $post = CmsPost::withTrashed()->findOrFail($id);
        if ($post->is_locked) {
            return back()->with('error', 'Locked post cannot be permanently deleted.');
        }
        $post->forceDelete();
        return back()->with('success', 'Post permanently deleted.');
    }

    // ── BULK ACTIONS ───────────────────────────────────────────────────────
    public function bulk(Request $request)
    {
        $ids    = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids)) {
            return back()->with('error', 'No posts selected.');
        }

        switch ($action) {
            case 'publish':
                CmsPost::whereIn('id', $ids)->update(['status' => 'published', 'published_at' => now()]);
                break;
            case 'draft':
                CmsPost::whereIn('id', $ids)->update(['status' => 'draft']);
                break;
            case 'trash':
                CmsPost::whereIn('id', $ids)
                       ->where('is_locked', false)
                       ->each(fn ($p) => $p->delete());
                break;
            default:
                return back()->with('error', 'Unknown action.');
        }

        return back()->with('success', "Bulk {$action} applied to " . count($ids) . " posts.");
    }

    // ── PRIVATE HELPERS ────────────────────────────────────────────────────
    private function validatePost(Request $request, $ignoreId = null): array
    {
        $slugRule = 'nullable|string|max:200|unique:cms_posts,slug' .
                    ($ignoreId ? ",{$ignoreId}" : '');

        return $request->validate([
            'post_type'           => 'required|in:page,blog_post,product,service,portfolio',
            'title'               => 'required|string|max:255',
            'slug'                => $slugRule,
            'status'              => 'required|in:draft,published,scheduled,private',
            'content'             => 'nullable|string',
            'excerpt'             => 'nullable|string|max:500',
            'featured_image'      => 'nullable|string',
            'featured_image_file' => 'nullable|image|max:5120',
            'parent_id'           => 'nullable|integer|exists:cms_posts,id',
            'menu_order'          => 'nullable|integer|min:0',
            'published_at'        => 'nullable|date',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string|max:500',
            'meta_keywords'       => 'nullable|string|max:500',
            // array rule required: values contain commas ("index,follow"), which a
            // pipe-delimited in:... rule would split into separate tokens
            'robots'              => ['nullable', Rule::in(['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])],
            'template'            => 'nullable|string|max:100',
            'layout'              => 'required|string|max:100',
            'show_in_header'      => 'boolean',
            'show_in_footer'      => 'boolean',
            'show_in_main_nav'    => 'boolean',
        ]);
    }

    private function buildMeta(Request $request, string $postType): array
    {
        return match ($postType) {
            'blog_post' => [
                'allow_comments' => $request->boolean('allow_comments'),
                'canonical_url'  => $request->input('canonical_url', ''),
            ],
            'product' => [
                'price'       => (float) $request->input('price', 0),
                'sale_price'  => $request->filled('sale_price')
                                    ? (float) $request->input('sale_price') : null,
                'sku'         => $request->input('sku', ''),
                'stock'       => (int) $request->input('stock', 0),
                'currency'    => $request->input('currency', 'GBP'),
                'is_featured' => $request->boolean('is_featured'),
            ],
            'service' => [
                'icon'        => $request->input('service_icon', 'fa-cog'),
                'cta_label'   => $request->input('cta_label', 'Get Quote'),
                'cta_url'     => $request->input('cta_url', '/get-quote'),
                'is_featured' => $request->boolean('is_featured'),
            ],
            'page' => [
                'show_title'      => $request->boolean('show_title', true),
                'show_breadcrumb' => $request->boolean('show_breadcrumb', true),
            ],
            default => [],
        };
    }

    private function syncTaxonomies(CmsPost $post, Request $request): void
    {
        $ids = array_merge(
            (array) $request->input('category_ids', []),
            (array) $request->input('tag_ids', []),
            (array) $request->input('service_area_ids', [])
        );
        $post->taxonomies()->sync($ids);

        // Update post_count on affected taxonomies
        foreach ($ids as $taxId) {
            $tax = CmsTaxonomy::find($taxId);
            $tax?->syncPostCount();
        }
    }

    private function getTemplatesForType(string $type): array
    {
        return match ($type) {
            'page'      => ['default' => 'Default', 'full-width' => 'Full Width',
                            'landing' => 'Landing Page', 'sidebar-right' => 'Sidebar Right'],
            'blog_post' => ['default' => 'Default', 'full-width' => 'Full Width'],
            'product'   => ['default' => 'Default', 'grid' => 'Grid Card'],
            'service'   => ['default' => 'Default', 'icon-boxes' => 'Icon Boxes'],
            default     => ['default' => 'Default'],
        };
    }

    private function getLayouts(): array
    {
        return [
            'home2.layouts.app' => 'Home2 (Default)',
            'layouts.app'       => 'Legacy Layout',
        ];
    }
}
