<?php

namespace App\Http\Controllers\Admin\HeroSlider;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HeroSlideController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.heroslider.index', [
            'filters' => ['active' => (string) $request->input('active', '')],
        ]);
    }

    public function data(Request $request)
    {
        $query = HeroSlide::query();

        if ($request->input('active') === '1') {
            $query->where('is_active', true);
        } elseif ($request->input('active') === '0') {
            $query->where('is_active', false);
        }

        $perPage = (int) config('admin_heroslider.per_page', 10);

        return response()->json(
            $query->orderBy('position')->orderBy('id')->paginate($perPage)->through(function (HeroSlide $slide) {
                return [
                    'id'        => $slide->id,
                    'title'     => $slide->title,
                    'subtitle'  => $slide->subtitle,
                    'position'  => $slide->position,
                    'is_active' => $slide->is_active,
                    'image'     => $slide->imageUrl(),
                    'btn_text'  => $slide->btn_text,
                    'btn_link'  => $slide->btn_link,
                    'urls'      => [
                        'edit'   => route('admin.hero-slides.edit', $slide->id),
                        'delete' => route('admin.hero-slides.destroy', $slide->id),
                        'toggle' => route('admin.hero-slides.toggle', $slide->id),
                        'up'     => route('admin.hero-slides.move', ['hero_slide' => $slide->id, 'direction' => 'up']),
                        'down'   => route('admin.hero-slides.move', ['hero_slide' => $slide->id, 'direction' => 'down']),
                    ],
                ];
            })
        );
    }

    public function create()
    {
        return view('admin.heroslider.create', [
            'slide'        => new HeroSlide(['is_active' => true]),
            'optionFields' => config('admin_heroslider.option_fields'),
            'nextPosition' => (int) HeroSlide::max('position') + 1,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);

        $data['image']      = $this->storeUpload($request->file('image'));
        $data['is_active']  = $request->has('is_active');
        $data['position']   = $request->filled('position')
            ? $this->freePosition((int) $request->input('position'))
            : (int) HeroSlide::max('position') + 1;
        $data['options']    = $this->resolveOptions($request);

        HeroSlide::create($data);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide created successfully.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.heroslider.edit', [
            'slide'        => $heroSlide,
            'optionFields' => config('admin_heroslider.option_fields'),
            'options'      => $heroSlide->resolvedOptions(),
        ]);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $data = $this->validated($request, false, $heroSlide->id);

        if ($request->hasFile('image')) {
            $newFile     = $this->storeUpload($request->file('image'));
            $this->removeUpload($heroSlide->image);
            $data['image'] = $newFile;
        }
        $data['is_active'] = $request->has('is_active');
        $data['options']   = $this->resolveOptions($request);

        $wantedPosition = $request->filled('position') ? (int) $request->input('position') : $heroSlide->position;
        if ($wantedPosition !== $heroSlide->position) {
            $data['position'] = $this->freePosition($wantedPosition, $heroSlide->id);
        }

        $heroSlide->update($data);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide updated successfully.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $this->removeUpload($heroSlide->image);
        $heroSlide->delete();

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide deleted successfully.');
    }

    /**
     * Toggle is_active (AJAX).
     */
    public function toggle(Request $request, HeroSlide $heroSlide)
    {
        $heroSlide->update(['is_active' => !$heroSlide->is_active]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'        => true,
                'is_active' => $heroSlide->is_active,
                'label'     => $heroSlide->is_active ? 'Active' : 'Inactive',
                'color'     => $heroSlide->is_active ? 'success' : 'secondary',
            ]);
        }

        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide status updated.');
    }

    /**
     * Move a slide one position up/down by swapping with its neighbour (AJAX).
     */
    public function move(Request $request, HeroSlide $heroSlide, string $direction)
    {
        $direction = $direction === 'up' ? 'up' : 'down';

        $neighbour = $direction === 'up'
            ? HeroSlide::where('position', '<', $heroSlide->position)->orderByDesc('position')->first()
            : HeroSlide::where('position', '>', $heroSlide->position)->orderBy('position')->first();

        if (!$neighbour) {
            return response()->json(['ok' => false, 'message' => 'Slide is already at the ' . ($direction === 'up' ? 'top' : 'bottom') . '.'], 422);
        }

        DB::transaction(function () use ($heroSlide, $neighbour) {
            $myPos       = $heroSlide->position;
            $neighbourPos = $neighbour->position;
            $temp        = (int) HeroSlide::min('position') - 1; // unique-safe scratch position

            $heroSlide->forceFill(['position' => $temp])->save();
            $neighbour->forceFill(['position' => $myPos])->save();
            $heroSlide->forceFill(['position' => $neighbourPos])->save();
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Slide moved ' . $direction . '.']);
        }

        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide moved ' . $direction . '.');
    }

    /* ------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------ */

    private function validated(Request $request, bool $requireImage, $ignoreId = null)
    {
        $imageRule = ($requireImage ? 'required' : 'nullable') . '|file|mimes:jpg,jpeg,png,webp,gif,svg|max:2048';

        return $request->validate([
            'title'    => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image'    => $imageRule,
            'btn_text' => 'nullable|string|max:100',
            'btn_link' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0|max:999999',
        ]);
    }

    /**
     * If another slide already sits on this position, push it (and the
     * moved slide keeps the wanted spot) by shifting the existing one.
     */
    private function freePosition(int $position, $ignoreId = null)
    {
        $query = HeroSlide::query()->where('position', $position);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        $existing = $query->first();

        if ($existing) {
            // Swap with the occupant: the current slide takes $position
            // on update and the occupant receives the mover's old slot.
            DB::transaction(function () use ($existing, $ignoreId) {
                $mover = $ignoreId ? HeroSlide::find($ignoreId) : null;
                $temp  = (int) HeroSlide::min('position') - 1;

                $existing->forceFill(['position' => $temp])->save();
                $existing->forceFill(['position' => (int) optional($mover)->position])->save();
            });
        }

        return $position;
    }

    /**
     * Sanitize submitted `options[...]` against config definitions.
     */
    private function resolveOptions(Request $request)
    {
        $out = [];

        foreach (config('admin_heroslider.option_fields', []) as $key => $def) {
            $raw = $request->input('options.' . $key);

            switch ($def['type']) {
                case 'toggle':
                    $out[$key] = (bool) $request->has('options.' . $key);
                    break;

                case 'number':
                case 'range':
                    $out[$key] = is_numeric($raw) ? (float) $raw : (float) ($def['default'] ?? 0);
                    if (isset($def['min'])) {
                        $out[$key] = max((float) $def['min'], $out[$key]);
                    }
                    if (isset($def['max'])) {
                        $out[$key] = min((float) $def['max'], $out[$key]);
                    }
                    break;

                case 'select':
                    $out[$key] = isset($def['options'][$raw]) ? $raw : ($def['default'] ?? array_key_first($def['options'] ?? []));
                    break;

                case 'color':
                    $out[$key] = preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $raw) ? strtolower((string) $raw) : ($def['default'] ?? '#000000');
                    break;

                default: // text
                    $trimmed = trim((string) ($raw ?? ''));
                    $out[$key] = $trimmed !== '' ? $trimmed : (string) ($def['default'] ?? '');
            }
        }

        return $out;
    }

    /**
     * Upload destination: C:\laragon\www\dplive\uploads\heroslider
     * (web docroot uploads folder, served at /uploads/heroslider/...).
     */
    private function uploadDir()
    {
        $dir = dirname(public_path(), 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'heroslider';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private function storeUpload($file)
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = ($slug !== '' ? $slug : 'slide') . '-' . time() . '-' . strtolower(Str::random(6)) . '.' . $ext;

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
