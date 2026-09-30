<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.blog.categories.index', [
            'filters' => ['q' => (string) $request->input('q', '')],
        ]);
    }

    public function data(Request $request)
    {
        $query = BlogCategory::query()->withCount('posts');

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%");
            });
        }

        return response()->json(
            $query->orderBy('name')->paginate(15)->through(function (BlogCategory $cat) {
                return [
                    'id'     => $cat->id,
                    'name'   => $cat->name,
                    'slug'   => $cat->slug,
                    'posts'  => $cat->posts_count,
                    'urls'   => [
                        'edit'   => route('admin.blog-categories.edit', $cat->id),
                        'delete' => route('admin.blog-categories.destroy', $cat->id),
                    ],
                ];
            })
        );
    }

    public function create()
    {
        return view('admin.blog.categories.create', ['category' => new BlogCategory()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);

        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category created successfully.');
    }

    public function edit(BlogCategory $blogCategory)
    {
        return view('admin.blog.categories.edit', ['category' => $blogCategory]);
    }

    public function update(Request $request, BlogCategory $blogCategory)
    {
        $data = $this->validated($request, $blogCategory->id);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name'], $blogCategory->id);

        $blogCategory->update($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category updated successfully.');
    }

    public function destroy(BlogCategory $blogCategory)
    {
        $blogCategory->delete();

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category deleted successfully.');
    }

    private function validated(Request $request, $ignoreId = null)
    {
        $slugUnique = $ignoreId
            ? 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:blog_categories,slug,' . $ignoreId
            : 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:blog_categories,slug';

        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => $slugUnique,
        ]);
    }

    private function uniqueSlug($slug, $name, $ignoreId = null)
    {
        $slug = trim((string) $slug) !== '' ? trim((string) $slug) : Str::slug($name);
        $base = $slug !== '' ? $slug : 'category';

        $i   = 1;
        $dup = true;
        while ($dup) {
            $query = BlogCategory::query()->where('slug', $slug);
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
}
