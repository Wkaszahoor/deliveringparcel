<?php

namespace App\Http\Controllers\Admin\Rates;

use App\Http\Controllers\Controller;
use App\Models\RateRule;
use App\Models\RateZone;
use Illuminate\Http\Request;

class RateRuleController extends Controller
{
    public function index(Request $request)
    {
        $zones = RateZone::orderBy('name')->get();

        return view('admin.rates.rules.index', [
            'zones'   => $zones,
            'filters' => [
                'q'         => (string) $request->input('q', ''),
                'origin'    => (string) $request->input('origin', ''),
                'dest'      => (string) $request->input('dest', ''),
                'service'   => (string) $request->input('service', ''),
                'active'    => (string) $request->input('active', ''),
            ],
        ]);
    }

    public function data(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $query = RateRule::query()->with(['originZone:id,name,code', 'destinationZone:id,name,code']);

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('service_type', 'like', $like)
                    ->orWhereHas('originZone', fn ($z) => $z->where('name', 'like', $like))
                    ->orWhereHas('destinationZone', fn ($z) => $z->where('name', 'like', $like));
            });
        }
        if ($origin = (int) $request->input('origin')) {
            $query->where('origin_zone_id', $origin);
        }
        if ($dest = (int) $request->input('dest')) {
            $query->where('destination_zone_id', $dest);
        }
        if ($request->input('service')) {
            $query->where('service_type', $request->input('service'));
        }
        if ($request->input('active') !== '' && $request->input('active') !== null) {
            $query->where('is_active', (int) $request->input('active'));
        }

        $query->orderByDesc('priority')->orderByDesc('id');

        return response()->json(
            $query->paginate(15)->through(function (RateRule $r) {
                return [
                    'id'       => $r->id,
                    'origin'   => optional($r->originZone)->name,
                    'dest'     => optional($r->destinationZone)->name,
                    'service'  => $r->service_type,
                    'weight'   => $r->weight_min . '–' . $r->weight_max . ' kg',
                    'base'     => number_format((float) $r->base_price, 2),
                    'per_kg'   => number_format((float) $r->per_kg_price, 2),
                    'transit'  => $r->transit_days_min ? ($r->transit_days_min . '–' . ($r->transit_days_max ?? $r->transit_days_min) . 'd') : '—',
                    'priority' => $r->priority,
                    'active'   => (bool) $r->is_active,
                    'urls'     => [
                        'edit'   => route('admin.rates.rules.edit', $r->id),
                        'delete' => route('admin.rates.rules.destroy', $r->id),
                    ],
                ];
            })->toArray()
        );
    }

    public function create()
    {
        return view('admin.rates.rules.form', [
            'rule' => new RateRule(['is_active' => true, 'weight_min' => 0, 'weight_max' => 30, 'priority' => 0]),
            'zones' => RateZone::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        RateRule::create($this->validated($request));

        return redirect()->route('admin.rates.rules.index')->with('success', 'Rate rule created.');
    }

    public function edit(RateRule $rule)
    {
        return view('admin.rates.rules.form', [
            'rule' => $rule,
            'zones' => RateZone::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, RateRule $rule)
    {
        $rule->update($this->validated($request));

        return redirect()->route('admin.rates.rules.index')->with('success', 'Rate rule updated.');
    }

    public function destroy(RateRule $rule)
    {
        $rule->delete();

        return redirect()->route('admin.rates.rules.index')->with('success', 'Rate rule deleted.');
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'origin_zone_id'    => 'required|integer|exists:rate_zones,id',
            'destination_zone_id' => 'required|integer|exists:rate_zones,id|different:origin_zone_id',
            'service_type'      => 'required|string|max:50|in:' . implode(',', array_keys(config('admin_rates.service_types'))),
            'weight_min'        => 'required|numeric|min:0|max:999999',
            'weight_max'        => 'required|numeric|gte:weight_min|max:999999',
            'base_price'        => 'required|numeric|min:0',
            'per_kg_price'      => 'required|numeric|min:0',
            'transit_days_min'  => 'nullable|integer|min:0|max:365',
            'transit_days_max'  => 'nullable|integer|gte:transit_days_min|max:365',
            'is_active'         => 'nullable|boolean',
            'priority'          => 'nullable|integer|min:0|max:9999',
            'valid_from'        => 'nullable|date',
            'valid_to'          => 'nullable|date|after_or_equal:valid_from',
        ]);

        $data['is_active'] = $request->has('is_active');

        return $data;
    }
}
