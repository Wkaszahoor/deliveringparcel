<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Blog\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;

/**
 * Public frontend for the Blog Import System (2026-09-02).
 * Owns /blog outright since 2026-09-02 (route names blog.*) — the
 * previous Home2 blog routes were removed and /new/blog retired.
 */
class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::query()
            ->published()
            ->whereNotNull('published_at')
            ->with('categories', 'tags')
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = $request->get('q');
                $q->where(function ($w) use ($s) {
                    $w->where('title', 'like', "%{$s}%")
                      ->orWhere('excerpt', 'like', "%{$s}%")
                      ->orWhere('content', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('category'), fn ($q) =>
                $q->whereHas('categories',
                    fn ($c) => $c->where('slug', $request->category)))
            ->when($request->filled('tag'), fn ($q) =>
                $q->whereHas('tags',
                    fn ($t) => $t->where('slug', $request->tag)))
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = BlogCategory::withCount([
                'posts' => fn ($q) => $q->where('status', 'published'),
            ])
            ->having('posts_count', '>', 0)
            ->orderBy('name')
            ->get();

        return view('blog.index', compact('posts', 'categories'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()
            ->with('categories', 'tags')
            ->where('slug', $slug)
            ->firstOrFail();

        $post->recordView();

        $related = BlogPost::published()
            ->with('categories')
            ->whereHas('categories', fn ($q) =>
                $q->whereIn('id', $post->categories->pluck('id')))
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        $prev = BlogPost::published()
            ->where('published_at', '<', $post->published_at)
            ->latest('published_at')->first();

        $next = BlogPost::published()
            ->where('published_at', '>', $post->published_at)
            ->oldest('published_at')->first();

        return view('blog.show', compact('post', 'related', 'prev', 'next'));
    }

    public function category(string $slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = BlogPost::published()
            ->whereHas('categories', fn ($q) => $q->where('slug', $slug))
            ->with('categories', 'tags')
            ->latest('published_at')
            ->paginate(12);

        $categories = BlogCategory::withCount('posts')
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->get();

        return view('blog.category', compact('category', 'posts', 'categories'));
    }

    public function tag(string $slug)
    {
        $tag   = BlogTag::where('slug', $slug)->firstOrFail();
        $posts = BlogPost::published()
            ->whereHas('tags', fn ($q) => $q->where('slug', $slug))
            ->with('categories', 'tags')
            ->latest('published_at')
            ->paginate(12);

        return view('blog.tag', compact('tag', 'posts'));
    }
}
