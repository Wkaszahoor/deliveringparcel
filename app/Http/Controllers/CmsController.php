<?php
namespace App\Http\Controllers;

use App\Models\{CmsPost, CmsTaxonomy};
use Illuminate\Http\Request;

class CmsController extends Controller
{
    // ── BLOG ──────────────────────────────────────────────────────────────
    public function blogIndex(Request $request)
    {
        $query = CmsPost::with(['categories','tags'])
                        ->ofType('blog_post')
                        ->published()
                        ->orderByDesc('published_at');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($qb) =>
                $qb->where('title','like',"%{$q}%")
                   ->orWhere('excerpt','like',"%{$q}%")
            );
        }

        $posts      = $query->paginate(9)->withQueryString();
        $categories = CmsTaxonomy::where('taxonomy','category')
                                 ->where('post_type','blog_post')
                                 ->withCount(['posts' => fn($q) => $q->where('status','published')])
                                 ->having('posts_count','>',0)
                                 ->orderBy('name')
                                 ->get();

        return view('cms.blog.index', compact('posts','categories'));
    }

    public function blogShow(string $slug)
    {
        $post = CmsPost::ofType('blog_post')->published()
                       ->where('slug',$slug)->with(['categories','tags','author'])
                       ->firstOrFail();
        $post->incrementViewCount();

        $related = CmsPost::ofType('blog_post')->published()
                          ->where('id','!=',$post->id)
                          ->whereHas('categories', fn($q) =>
                              $q->whereIn('cms_taxonomies.id',
                                  $post->categories->pluck('id')))
                          ->orderByDesc('published_at')
                          ->take(3)->get();

        $prevPost = CmsPost::ofType('blog_post')->published()
                           ->where('published_at','<',$post->published_at)
                           ->orderByDesc('published_at')->first();
        $nextPost = CmsPost::ofType('blog_post')->published()
                           ->where('published_at','>',$post->published_at)
                           ->orderBy('published_at')->first();

        // Responsive images: lazy-load + WebP srcset from the optimizer.
        try {
            $post->content = app(\App\Services\Media\ImageOptimizerService::class)->enhanceContent((string) $post->content);
        } catch (\Throwable $e) { /* fail open: serve unenhanced content */ }

        return view('cms.blog.show',
                    compact('post','related','prevPost','nextPost'));
    }

    public function blogCategory(string $slug)
    {
        $category = CmsTaxonomy::where('taxonomy','category')
                               ->where('post_type','blog_post')
                               ->where('slug',$slug)->firstOrFail();
        $posts = CmsPost::ofType('blog_post')->published()
                        ->whereHas('taxonomies', fn($q) => $q->where('cms_taxonomies.id',$category->id))
                        ->orderByDesc('published_at')->paginate(9);
        return view('cms.blog.category', compact('category','posts'));
    }

    public function blogTag(string $slug)
    {
        $tag = CmsTaxonomy::where('taxonomy','tag')
                          ->where('post_type','blog_post')
                          ->where('slug',$slug)->firstOrFail();
        $posts = CmsPost::ofType('blog_post')->published()
                        ->whereHas('taxonomies', fn($q) => $q->where('cms_taxonomies.id',$tag->id))
                        ->orderByDesc('published_at')->paginate(9);
        return view('cms.blog.tag', compact('tag','posts'));
    }

    // ── SERVICES ──────────────────────────────────────────────────────────
    public function servicesIndex()
    {
        $services = CmsPost::ofType('service')->published()
                           ->topLevel()->with('children')
                           ->orderBy('menu_order')->get();
        return view('cms.services.index', compact('services'));
    }

    public function serviceShow(string $slug)
    {
        $service = CmsPost::ofType('service')->published()
                          ->where('slug',$slug)->with(['children','parent'])
                          ->firstOrFail();
        $service->incrementViewCount();
        try {
            $service->content = app(\App\Services\Media\ImageOptimizerService::class)->enhanceContent((string) $service->content);
        } catch (\Throwable $e) { /* fail open */ }
        return view('cms.services.show', compact('service'));
    }

    // ── SHOP ──────────────────────────────────────────────────────────────
    public function shopIndex(Request $request)
    {
        $query = CmsPost::ofType('product')->published()
                        ->with('categories')->orderByDesc('published_at');
        if ($request->filled('category')) {
            $query->whereHas('categories',
                fn($q) => $q->where('slug',$request->category));
        }
        $products   = $query->paginate(12)->withQueryString();
        $categories = CmsTaxonomy::where('taxonomy','category')
                                 ->where('post_type','product')
                                 ->withCount(['posts'=>fn($q)=>$q->where('status','published')])
                                 ->having('posts_count','>',0)
                                 ->orderBy('name')->get();
        return view('cms.shop.index', compact('products','categories'));
    }

    public function productShow(string $slug)
    {
        $product = CmsPost::ofType('product')->published()
                          ->where('slug',$slug)->firstOrFail();
        $product->incrementViewCount();
        return view('cms.shop.show', compact('product'));
    }

    // ── STATIC PAGES ──────────────────────────────────────────────────────
    public function pageShow(string $slug)
    {
        $page = CmsPost::ofType('page')->published()
                       ->where('slug',$slug)->firstOrFail();
        $page->incrementViewCount();
        try {
            $page->content = app(\App\Services\Media\ImageOptimizerService::class)->enhanceContent((string) $page->content);
        } catch (\Throwable $e) { /* fail open */ }
        $layout = $page->layout ?: 'home2.layouts.app';
        return view('cms.page.show', compact('page','layout'));
    }
}
