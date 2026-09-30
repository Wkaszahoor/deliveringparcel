<?php

namespace App\Http\Controllers\Admin\Widgets;

use App\Http\Controllers\Controller;
use App\Models\WidgetInstance;
use App\Support\HtmlSanitizer;
use App\Services\WidgetRenderer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WidgetController extends Controller
{
    public function index()
    {
        return view('admin.widgets.index', [
            'areas'  => config('admin_widgets.areas'),
            'types'  => collect(config('admin_widgets.types'))->map(fn ($t) => $t['label'])->all(),
        ]);
    }

    /** Lazy-table JSON feed. */
    public function data(Request $request)
    {
        $q = WidgetInstance::query()
            ->when($request->filled('q'), function ($s) use ($request) {
                $v = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $request->input('q')) . '%';
                $s->where(function ($w) use ($v) {
                    $w->where('title', 'like', $v)->orWhere('type', 'like', $v);
                });
            })
            ->when($request->filled('area'), fn ($s) => $s->where('area', $request->input('area')))
            ->orderBy('area')->orderBy('sort')->orderBy('id');

        $page  = max(1, (int) $request->input('page', 1));
        $rows  = $q->paginate(dp_per_page($request), ['*'], 'page', $page);

        return response()->json([
            'rows'  => $rows->map(fn (WidgetInstance $w) => [
                'id'   => $w->id,
                'cols' => [
                    e($w->title),
                    e(config('admin_widgets.types.' . $w->type . '.label', $w->type)),
                    e(config('admin_widgets.areas.' . $w->area, $w->area)),
                    e($w->scope . ($w->scope_key ? ': ' . $w->scope_key : '')),
                    (string) $w->sort,
                    $w->is_active
                        ? '<span class="badge badge-success">active</span>'
                        : '<span class="badge badge-secondary">off</span>',
                ],
                'urls' => [
                    'edit'    => route('admin.widgets.edit', $w->id),
                    'toggle'  => route('admin.widgets.toggle', $w->id),
                    'destroy' => route('admin.widgets.destroy', $w->id),
                ],
            ])->all(),
            'page'  => $rows->currentPage(),
            'total' => $rows->total(),
        ]);
    }

    public function create()
    {
        return view('admin.widgets.form', ['widget' => new WidgetInstance([
            'scope' => 'global', 'area' => 'home_bottom', 'type' => 'review_widget', 'sort' => 100,
        ])] + $this->formOptions());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        WidgetInstance::create($data);
        WidgetRenderer::flush();
        return redirect()->route('admin.widgets.index')->with('success', 'Widget created.');
    }

    public function edit(WidgetInstance $widget)
    {
        return view('admin.widgets.form', ['widget' => $widget] + $this->formOptions());
    }

    public function update(Request $request, WidgetInstance $widget)
    {
        $widget->update($this->validated($request));
        WidgetRenderer::flush();
        return redirect()->route('admin.widgets.index')->with('success', 'Widget updated.');
    }

    public function toggle(WidgetInstance $widget)
    {
        $widget->update(['is_active' => !$widget->is_active]);
        WidgetRenderer::flush();
        return back()->with('success', 'Widget ' . ($widget->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function destroy(WidgetInstance $widget)
    {
        $widget->delete();
        WidgetRenderer::flush();
        return back()->with('success', 'Widget deleted.');
    }

    protected function formOptions(): array
    {
        return [
            'areas'     => config('admin_widgets.areas'),
            'scopes'    => config('admin_widgets.scopes'),
            'types'     => config('admin_widgets.types'),
            'blocks'    => collect(config('admin_widgets.blocks'))->map(fn ($b) => $b['label'])->all(),
        ];
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'type'      => ['required', Rule::in(array_keys(config('admin_widgets.types')))],
            'title'     => 'required|string|max:150',
            'area'      => ['required', Rule::in(array_keys(config('admin_widgets.areas')))],
            'scope'     => ['required', Rule::in(array_keys(config('admin_widgets.scopes')))],
            'scope_key' => 'nullable|string|max:190',
            'sort'      => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
            'cache_minutes' => 'nullable|integer|min:0|max:1440',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
            'auth_mode' => 'nullable|in:any,guest,user',
            'config'    => 'nullable|array',
        ]);

        $config = $request->input('config', []);
        if (($data['type'] ?? '') === 'html') {
            $config['html'] = HtmlSanitizer::clean((string) ($config['html'] ?? '')); // TW-009 safe by construction
        }
        $conditions = [];
        if (!empty($data['auth_mode']) && $data['auth_mode'] !== 'any') {
            $conditions['auth'] = $data['auth_mode'];
        }
        unset($data['auth_mode']);

        return [
            'type'          => $data['type'],
            'title'         => $data['title'],
            'area'          => $data['area'],
            'scope'         => $data['scope'],
            'scope_key'     => ($data['scope_key'] ?? null) ?: null,
            'sort'          => (int) ($data['sort'] ?? 100),
            'is_active'     => (bool) ($data['is_active'] ?? false),
            'cache_minutes' => ($data['cache_minutes'] ?? null) !== null ? (int) $data['cache_minutes'] : null,
            'starts_at'     => ($data['starts_at'] ?? null) ?: null,
            'ends_at'       => ($data['ends_at'] ?? null) ?: null,
            'config'        => $config,
            'conditions'    => $conditions ?: null,
        ];
    }
}
