<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.services.categories.index', [
            'filters' => ['q' => (string) $request->input('q', '')],
        ]);
    }

    public function data(Request $request)
    {
        $query = ServiceCategory::query()->withCount('services');

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%");
            });
        }

        return response()->json(
            $query->orderBy('name')->paginate(15)->through(function (ServiceCategory $cat) {
                return [
                    'id'         => $cat->id,
                    'name'       => $cat->name,
                    'slug'       => $cat->slug,
                    'icon_class' => $cat->icon_class,
                    'services'   => $cat->services_count,
                    'urls'       => [
                        'edit'   => route('admin.service-categories.edit', $cat->id),
                        'delete' => route('admin.service-categories.destroy', $cat->id),
                    ],
                ];
            })
        );
    }

    public function create()
    {
        return view('admin.services.categories.create', ['category' => new ServiceCategory()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);

        ServiceCategory::create($data);

        return redirect()->route('admin.service-categories.index')->with('success', 'Service category created successfully.');
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        return view('admin.services.categories.edit', ['category' => $serviceCategory]);
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $data = $this->validated($request, $serviceCategory->id);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name'], $serviceCategory->id);

        $serviceCategory->update($data);

        return redirect()->route('admin.service-categories.index')->with('success', 'Service category updated successfully.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $serviceCategory->delete();

        return redirect()->route('admin.service-categories.index')->with('success', 'Service category deleted successfully.');
    }

    private function validated(Request $request, $ignoreId = null)
    {
        $slugUnique = $ignoreId
            ? 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:service_categories,slug,' . $ignoreId
            : 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:service_categories,slug';

        return $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => $slugUnique,
            'icon_class' => 'nullable|string|max:100|regex:/^[a-z0-9\- ]+$/i',
        ]);
    }

    private function uniqueSlug($slug, $name, $ignoreId = null)
    {
        $slug = trim((string) $slug) !== '' ? trim((string) $slug) : Str::slug($name);
        $base = $slug !== '' ? $slug : 'category';

        $i   = 1;
        $dup = true;
        while ($dup) {
            $query = ServiceCategory::query()->where('slug', $slug);
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
