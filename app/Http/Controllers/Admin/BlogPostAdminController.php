<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Blog\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;

/**
 * Blog Import System (2026-09-02) — CRUD for imported/new-design blog
 * posts (blog_posts table). Separate from the legacy Admin\Blog\BlogController.
 */
class BlogPostAdminController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::with('categories', 'tags')
            ->when($request->search, fn ($q, $s) =>
                $q->where('title', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) =>
                $q->where('status', $s))
            ->when($request->batch, fn ($q, $b) =>
                $q->where('import_batch_id', $b))
            ->latest('published_at')
            ->paginate(30)
            ->withQueryString();

        $categories = BlogCategory::orderBy('name')->get();
        $stats = [
            'total'     => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft'     => BlogPost::where('status', 'draft')->count(),
            'archived'  => BlogPost::where('status', 'archived')->count(),
        ];

        return view('admin.blog.posts.index', compact('posts', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags       = BlogTag::orderBy('name')->get();
        return view('admin.blog.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:500',
            'content'            => 'nullable|string',
            'excerpt'            => 'nullable|string|max:1000',
            'status'             => 'required|in:draft,published,archived',
            'published_at'       => 'nullable|date',
            'seo_title'          => 'nullable|string|max:500',
            'seo_description'    => 'nullable|string|max:500',
            'seo_keywords'       => 'nullable|string|max:500',
            'categories'         => 'nullable|array',
            'categories.*'       => 'exists:blog_categories,id',
            'tags_input'         => 'nullable|string',
            'featured_image_url' => 'nullable|url',
        ]);

        $data['slug']         = BlogPost::generateSlug($data['title']);
        $data['created_by']   = auth()->id();
        $data['published_at'] = $data['published_at'] ?? now();

        $post = BlogPost::create($data);

        if (!empty($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }

        if (!empty($data['tags_input'])) {
            $tagIds = collect(explode(',', $data['tags_input']))
                ->map(fn ($t) => BlogTag::findOrCreateByName(trim($t))->id)
                ->toArray();
            $post->tags()->sync($tagIds);
        }

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags       = BlogTag::orderBy('name')->get();
        $post->load('categories', 'tags');
        return view('admin.blog.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:500',
            'content'            => 'nullable|string',
            'excerpt'            => 'nullable|string|max:1000',
            'status'             => 'required|in:draft,published,archived',
            'published_at'       => 'nullable|date',
            'seo_title'          => 'nullable|string|max:500',
            'seo_description'    => 'nullable|string|max:500',
            'seo_keywords'       => 'nullable|string|max:500',
            'categories'         => 'nullable|array',
            'categories.*'       => 'exists:blog_categories,id',
            'tags_input'         => 'nullable|string',
            'featured_image_url' => 'nullable|url',
        ]);

        $data['updated_by'] = auth()->id();
        if ($post->title !== $data['title']) {
            $data['slug'] = BlogPost::generateSlug($data['title']);
        }

        $post->update($data);
        $post->categories()->sync($data['categories'] ?? []);

        if (isset($data['tags_input'])) {
            $tagIds = collect(explode(',', $data['tags_input']))
                ->filter()
                ->map(fn ($t) => BlogTag::findOrCreateByName(trim($t))->id)
                ->toArray();
            $post->tags()->sync($tagIds);
        }

        return back()->with('success', 'Post updated.');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();
        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'Post moved to trash.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action'     => 'required|in:publish,draft,archive,delete',
            'post_ids'   => 'required|array',
            'post_ids.*' => 'exists:blog_posts,id',
        ]);

        $posts = BlogPost::whereIn('id', $request->post_ids);

        match ($request->action) {
            'publish' => $posts->update(['status' => 'published']),
            'draft'   => $posts->update(['status' => 'draft']),
            'archive' => $posts->update(['status' => 'archived']),
            'delete'  => $posts->each->delete(),
        };

        return back()->with('success', count($request->post_ids) . ' posts updated.');
    }
}
