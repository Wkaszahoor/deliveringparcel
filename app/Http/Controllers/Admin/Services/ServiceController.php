<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.services.index', [
            'categories' => ServiceCategory::orderBy('name')->get(),
            'types'      => config('admin_services.types'),
            'filters'    => [
                'q'            => (string) $request->input('q', ''),
                'category_id'  => (string) $request->input('category_id', ''),
                'type'         => (string) $request->input('type', ''),
                'availability' => (string) $request->input('availability', ''),
            ],
        ]);
    }

    public function data(Request $request)
    {
        $query = Service::query()->with('category');

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('short_desc', 'like', "%{$q}%");
            });
        }
        if ($categoryId = (int) $request->input('category_id')) {
            $query->where('service_category_id', $categoryId);
        }
        if (array_key_exists((string) $request->input('type'), config('admin_services.types'))) {
            $query->where('type', $request->input('type'));
        }
        if ($request->input('availability') !== null && $request->input('availability') !== '') {
            $query->where('is_available', (int) $request->input('availability'));
        }

        $perPage = (int) config('admin_services.per_page', 10);

        return response()->json(
            $query->orderBy('sort')->orderByDesc('id')->paginate($perPage)->through(function (Service $service) {
                $avail = config('admin_services.availability.' . ($service->is_available ? 1 : 0));

                return [
                    'id'           => $service->id,
                    'title'        => $service->title,
                    'slug'         => $service->slug,
                    'category'     => optional($service->category)->name,
                    'type'         => $service->type,
                    'type_label'   => config('admin_services.types.' . $service->type, ucfirst($service->type)),
                    'type_color'   => config('admin_services.type_colors.' . $service->type, 'secondary'),
                    'price'        => $service->price !== null ? number_format((float) $service->price, 2) : null,
                    'is_available' => $service->is_available,
                    'avail_label'  => $avail['label'] ?? 'Unknown',
                    'avail_color'  => $avail['color'] ?? 'secondary',
                    'sort'         => $service->sort,
                    'image'        => $service->imageUrl(),
                    'urls'         => [
                        'edit'   => route('admin.services.edit', $service->id),
                        'delete' => route('admin.services.destroy', $service->id),
                    ],
                ];
            })
        );
    }

    public function create()
    {
        return view('admin.services.create', [
            'service'    => new Service(['type' => 'standard', 'is_available' => true, 'sort' => 0]),
            'categories' => ServiceCategory::orderBy('name')->get(),
            'types'      => config('admin_services.types'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'));
        }
        $data['is_available'] = $request->has('is_available');

        Service::create($data);

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', [
            'service'    => $service,
            'categories' => ServiceCategory::orderBy('name')->get(),
            'types'      => config('admin_services.types'),
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validated($request, $service->id);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title'], $service->id);

        if ($request->hasFile('image')) {
            $newFile = $this->storeUpload($request->file('image'));
            $this->removeUpload($service->image);
            $data['image'] = $newFile;
        } elseif ($request->input('remove_image') === '1') {
            $this->removeUpload($service->image);
            $data['image'] = null;
        }
        $data['is_available'] = $request->has('is_available');

        $service->update($data);

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $this->removeUpload($service->image);
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }

    /* ------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------ */

    private function validated(Request $request, $ignoreId = null)
    {
        $types  = implode(',', array_keys(config('admin_services.types', [])));
        $slugUnique = $ignoreId
            ? 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:services,slug,' . $ignoreId
            : 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/|unique:services,slug';

        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'slug'               => $slugUnique,
            'service_category_id'=> 'nullable|integer|exists:service_categories,id',
            'short_desc'         => 'nullable|string|max:500',
            'description'        => 'nullable|string|max:4294900000',
            'image'              => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg|max:2048',
            'type'               => 'required|in:' . $types,
            'price'              => 'nullable|numeric|min:0|max:99999999.99',
            'sort'               => 'nullable|integer|min:0|max:999999',
        ]);

        $data['service_category_id'] = !empty($data['service_category_id']) ? (int) $data['service_category_id'] : null;
        $data['price']      = ($data['price'] ?? null) === '' || ($data['price'] ?? null) === null ? null : $data['price'];
        $data['sort']       = (int) ($data['sort'] ?? 0);

        return $data;
    }

    private function uniqueSlug($slug, $title, $ignoreId = null)
    {
        $slug = trim((string) $slug) !== '' ? trim((string) $slug) : Str::slug($title);
        $base = $slug !== '' ? $slug : 'service';

        $i   = 1;
        $dup = true;
        while ($dup) {
            $query = Service::withTrashed()->where('slug', $slug);
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
     * Upload destination: C:\laragon\www\dplive\uploads\services
     * (web docroot uploads folder, served at /uploads/services/...).
     */
    private function uploadDir()
    {
        $dir = dirname(public_path(), 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'services';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private function storeUpload($file)
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = ($slug !== '' ? $slug : 'service') . '-' . time() . '-' . strtolower(Str::random(6)) . '.' . $ext;

        $file->move($this->uploadDir(), $name);

        return $name;
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
