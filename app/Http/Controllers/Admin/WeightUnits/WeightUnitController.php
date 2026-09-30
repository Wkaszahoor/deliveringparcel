<?php

namespace App\Http\Controllers\Admin\WeightUnits;

use App\Http\Controllers\Controller;
use App\Models\WeightUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeightUnitController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.weightunits.index', [
            'filters' => [
                'q'      => (string) $request->input('q', ''),
                'active' => (string) $request->input('active', ''),
                'default'=> (string) $request->input('default', ''),
            ],
        ]);
    }

    public function data(Request $request)
    {
        $query = WeightUnit::query();

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('symbol', 'like', "%{$q}%");
            });
        }
        if ($request->input('active') === '1') {
            $query->where('is_active', true);
        } elseif ($request->input('active') === '0') {
            $query->where('is_active', false);
        }
        if ($request->input('default') === '1') {
            $query->where('is_default', true);
        }

        return response()->json(
            $query->orderByDesc('is_default')->orderBy('name')->paginate(15)->through(function (WeightUnit $unit) {
                return [
                    'id'         => $unit->id,
                    'name'       => $unit->name,
                    'symbol'     => $unit->symbol,
                    'grams'      => $unit->grams,
                    'is_default' => $unit->is_default,
                    'is_active'  => $unit->is_active,
                    'urls'       => [
                        'edit'    => route('admin.weight-units.edit', $unit->id),
                        'delete'  => route('admin.weight-units.destroy', $unit->id),
                        'default' => route('admin.weight-units.default', $unit->id),
                    ],
                ];
            })
        );
    }

    public function create()
    {
        return view('admin.weightunits.create', ['unit' => new WeightUnit(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->has('is_active');

        $makeDefault = $request->has('is_default');

        DB::transaction(function () use ($data, $makeDefault, &$unit) {
            if ($makeDefault) {
                $data['is_default'] = true;
            }
            $unit = WeightUnit::create($data);

            if ($makeDefault) {
                WeightUnit::where('id', '!=', $unit->id)->update(['is_default' => false]);
            }
        });

        return redirect()->route('admin.weight-units.index')->with('success', 'Weight unit created successfully.');
    }

    public function edit(WeightUnit $weightUnit)
    {
        return view('admin.weightunits.edit', ['unit' => $weightUnit]);
    }

    public function update(Request $request, WeightUnit $weightUnit)
    {
        $data           = $this->validated($request);
        $data['is_active'] = $request->has('is_active');

        DB::transaction(function () use ($data, $request, $weightUnit) {
            if ($request->has('is_default')) {
                $data['is_default'] = true;
                WeightUnit::where('id', '!=', $weightUnit->id)->update(['is_default' => false]);
            } else {
                $data['is_default'] = false;
            }

            $weightUnit->update($data);
        });

        return redirect()->route('admin.weight-units.index')->with('success', 'Weight unit updated successfully.');
    }

    public function destroy(WeightUnit $weightUnit)
    {
        if ($weightUnit->is_default) {
            return redirect()->route('admin.weight-units.index')->with('error', 'The default weight unit cannot be deleted. Set another unit as default first.');
        }

        $weightUnit->delete();

        return redirect()->route('admin.weight-units.index')->with('success', 'Weight unit deleted successfully.');
    }

    /**
     * Make this unit the single default (AJAX).
     */
    public function makeDefault(Request $request, WeightUnit $weightUnit)
    {
        DB::transaction(function () use ($weightUnit) {
            WeightUnit::query()->update(['is_default' => false]);
            $weightUnit->update(['is_default' => true]);
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $weightUnit->name . ' is now the default unit.']);
        }

        return redirect()->route('admin.weight-units.index')->with('success', $weightUnit->name . ' is now the default unit.');
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'grams'  => 'required|integer|min:0|max:2147483647',
        ]);

        $data['grams'] = (int) $data['grams'];

        return $data;
    }
}
