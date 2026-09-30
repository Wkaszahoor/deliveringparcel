<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Blog posts list page.
     */
    public function index(Request $request)
    {
        return view('admin.blog.index', [
            'categories' => BlogCategory::orderBy('name')->get(),
            'filters'    => [
                'q'           => (string) $request->input('q', ''),
                'category_id' => (string) $request->input('category_id', ''),
                'status'      => (string) $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Paginated JSON feed for DP.infiniteScroll.
     */
    public function data(Request $request)
    {
        $query = Blog::query()->with('category');

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%");
            });
        }
        if ($categoryId = (int) $request->input('category_id')) {
            $query->where('blog_category_id', $categoryId);
        }
        if (in_array($request->input('status'), ['draft', 'published'])) {
            $query->where('status', $request->input('status'));
        }

        $pages = $query->latest('id')->paginate(10)->through(function (Blog $post) {
            return [
                'id'           => $post->id,
                'title'        => $post->title,
                'slug'         => $post->slug,
                'category'     => optional($post->category)->name,
                'status'       => $post->status,
                'published_at' => $post->published_at ? $post->published_at->format('M d, Y') : null,
                'cover'        => $post->coverUrl(),
                'excerpt'      => Str::limit(strip_tags((string) $post->excerpt), 90),
                'urls'         => [
                    'show'   => route('admin.blogs.show', $post->id),
                    'edit'   => route('admin.blogs.edit', $post->id),
                    'delete' => route('admin.blogs.destroy', $post->id),
                ],
            ];
        });

        return response()->json($pages);
    }

    public function create()
    {
        return view('admin.blog.create', [
            'blog'       => new Blog(['status' => 'draft']),
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeUpload($request->file('cover_image'));
        }
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        Blog::create($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post created successfully.');
    }

    public function show(Blog $blog)
    {
        return view('admin.blog.show', ['blog' => $blog]);
    }

    public function edit(Blog $blog)
    {
        return view('admin.blog.edit', [
            'blog'       => $blog,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $this->validated($request, $blog->id);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title'], $blog->id);

        if ($request->hasFile('cover_image')) {
            $newFile = $this->storeUpload($request->file('cover_image'));
            $this->removeUpload($blog->cover_image);
            $data['cover_image'] = $newFile;
        } elseif ($request->input('remove_cover') === '1') {
            $this->removeUpload($blog->cover_image);
            $data['cover_image'] = null;
        }
        if ($data['status'] === 'published' && empty($data['published_at']) && !$blog->published_at) {
            $data['published_at'] = now();
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy(Blog $blog)
    {
        $this->removeUpload($blog->cover_image);
        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post deleted successfully.');
    }

    /* ------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------ */

    private function validated(Request $request, $ignoreId = null)
    {
        $slugUnique = $ignoreId
            ? 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:blogs,slug,' . $ignoreId
            : 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:blogs,slug';

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => $slugUnique,
            'excerpt'          => 'nullable|string|max:2000',
            'body'             => 'nullable|string|max:4294900000',
            'cover_image'      => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:2048', // svg removed: scriptable content (SE-006)
            'blog_category_id' => 'nullable|integer|exists:blog_categories,id',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'status'           => 'required|in:draft,published',
            'published_at'     => 'nullable|date',
        ]);

        $data['blog_category_id'] = !empty($data['blog_category_id']) ? (int) $data['blog_category_id'] : null;
        $data['published_at']     = !empty($data['published_at']) ? \Illuminate\Support\Carbon::parse($data['published_at']) : null;

        return $data;
    }

    private function uniqueSlug($slug, $title, $ignoreId = null)
    {
        $slug = trim((string) $slug) !== '' ? trim((string) $slug) : Str::slug($title);
        $base = $slug !== '' ? $slug : 'post';

        $i   = 1;
        $dup = true;
        while ($dup) {
            $query = Blog::withTrashed()->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            $dup = (bool) $query->exists();
            if ($dup) {
                $slug = $base . '-' . ++$i;
            }
        }

        return $slug;
    }

    /**
     * Upload destination: C:\laragon\www\dplive\uploads\blog
     * (web docroot uploads folder, served at /uploads/blog/...).
     */
    private function uploadDir()
    {
        $dir = dirname(public_path(), 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'blog';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private function storeUpload($file)
    {
        // UploadGuard: real-MIME allow-list (jpg/png/webp/gif), size cap,
        // random name. SVG intentionally rejected — it can carry scripts (SE-006).
        // Returns the bare filename: Blog::coverUrl() prepends uploads/blog/.
        $path = \App\Support\UploadGuard::store($file, 'blog');

        return $path !== null ? basename($path) : null;
    }

    private function removeUpload($filename)
    {
        if (!is_string($filename) || $filename === '' || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return;
        }
        $path = $this->uploadDir() . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
