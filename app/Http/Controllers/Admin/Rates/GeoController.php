<?php

namespace App\Http\Controllers\Admin\Rates;

use App\Http\Controllers\Controller;
use App\Models\GeoState;
use App\Models\GeoCity;
use App\Models\Country;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    /* ---------------- States ---------------- */

    public function statesIndex(Request $request)
    {
        return view('admin.geo.states.index', [
            'filters' => ['q' => (string) $request->input('q', ''), 'country' => (string) $request->input('country', '')],
            'countries' => $this->countries(),
        ]);
    }

    public function statesData(Request $request)
    {
        $query = GeoState::query()->withCount('cities');
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)->orWhere('code', 'like', $like);
            });
        }
        if ($country = (int) $request->input('country')) {
            $query->where('country_id', $country);
        }
        $query->orderBy('name');

        return response()->json(
            $query->paginate(dp_per_page($request))->through(function (GeoState $s) {
                return [
                    'id'      => $s->id,
                    'name'    => $s->name,
                    'code'    => $s->code,
                    'country' => $this->countryName($s->country_id, $s->country_code),
                    'cities'  => $s->cities_count,
                    'active'  => (bool) $s->is_active,
                    'urls'    => [
                        'edit'   => route('admin.geo.states.edit', $s->id),
                        'delete' => route('admin.geo.states.destroy', $s->id),
                    ],
                ];
            })->toArray()
        );
    }

    public function statesCreate()
    {
        return view('admin.geo.states.form', [
            'state' => new GeoState(['is_active' => true]),
            'countries' => $this->countries(),
        ]);
    }

    public function statesStore(Request $request)
    {
        GeoState::create($this->stateRules($request));

        return redirect()->route('admin.geo.states.index')->with('success', 'State created.');
    }

    public function statesEdit(GeoState $state)
    {
        return view('admin.geo.states.form', [
            'state' => $state,
            'countries' => $this->countries(),
        ]);
    }

    public function statesUpdate(Request $request, GeoState $state)
    {
        $state->update($this->stateRules($request));

        return redirect()->route('admin.geo.states.index')->with('success', 'State updated.');
    }

    public function statesDestroy(GeoState $state)
    {
        $state->cities()->delete();
        $state->delete();

        return redirect()->route('admin.geo.states.index')->with('success', 'State deleted.');
    }

    /* ---------------- Cities ---------------- */

    public function citiesIndex(Request $request)
    {
        return view('admin.geo.cities.index', [
            'filters' => ['q' => (string) $request->input('q', ''), 'state' => (string) $request->input('state', '')],
            'states' => GeoState::where('is_active', true)->orderBy('name')->get(['id', 'name', 'country_id', 'country_code']),
        ]);
    }

    public function citiesData(Request $request)
    {
        $query = GeoCity::query()->with('state:id,name,country_id,country_code');
        if ($q = trim((string) $request->input('q', ''))) {
            $query->where('name', 'like', '%' . $q . '%');
        }
        if ($state = (int) $request->input('state')) {
            $query->where('geo_state_id', $state);
        }
        $query->join('geo_states', 'geo_states.id', '=', 'geo_cities.geo_state_id')->orderBy('geo_states.name')->orderBy('geo_cities.name')->select('geo_cities.*');

        return response()->json(
            $query->paginate(dp_per_page($request))->through(function (GeoCity $c) {
                return [
                    'id'      => $c->id,
                    'name'    => $c->name,
                    'state'   => optional($c->state)->name,
                    'country' => $c->state ? $this->countryName($c->state->country_id, $c->state->country_code) : null,
                    'active'  => (bool) $c->is_active,
                    'urls'    => [
                        'edit'   => route('admin.geo.cities.edit', $c->id),
                        'delete' => route('admin.geo.cities.destroy', $c->id),
                    ],
                ];
            })->toArray()
        );
    }

    public function citiesCreate()
    {
        return view('admin.geo.cities.form', [
            'city' => new GeoCity(['is_active' => true]),
            'states' => GeoState::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function citiesStore(Request $request)
    {
        GeoCity::create($this->cityRules($request));

        return redirect()->route('admin.geo.cities.index')->with('success', 'City created.');
    }

    public function citiesEdit(GeoCity $city)
    {
        return view('admin.geo.cities.form', [
            'city' => $city,
            'states' => GeoState::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function citiesUpdate(Request $request, GeoCity $city)
    {
        $city->update($this->cityRules($request));

        return redirect()->route('admin.geo.cities.index')->with('success', 'City updated.');
    }

    public function citiesDestroy(GeoCity $city)
    {
        $city->delete();

        return redirect()->route('admin.geo.cities.index')->with('success', 'City deleted.');
    }

    /* ---------------- Dependent dropdown JSON ---------------- */

    public function statesByCountry(Request $request)
    {
        $request->validate(['country_id' => 'nullable|integer']);

        $states = GeoState::where('is_active', true)
            ->when((int) $request->input('country_id'), fn ($q, $id) => $q->where('country_id', $id))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $states]);
    }

    public function citiesByState(Request $request)
    {
        $request->validate(['state_id' => 'required|integer']);

        $cities = GeoCity::where('is_active', true)
            ->where('geo_state_id', (int) $request->input('state_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $cities]);
    }

    /* ---------------- helpers ---------------- */

    private function countries()
    {
        if (!\Schema::hasTable('countries')) {
            return collect();
        }

        return Country::orderBy('name')->get(['id', 'name', 'iso2']);
    }

    private function countryName($countryId, $countryCode)
    {
        static $map = null;
        if ($map === null) {
            $map = \Schema::hasTable('countries') ? Country::pluck('name', 'id') : collect();
        }
        if ($countryId && isset($map[$countryId])) {
            return $map[$countryId];
        }

        return $countryCode ?: '—';
    }

    private function stateRules(Request $request)
    {
        $data = $request->validate([
            'country_id'   => 'nullable|integer',
            'country_code' => 'nullable|string|max:8',
            'name'         => 'required|string|max:255',
            'code'         => 'nullable|string|max:20',
            'is_active'    => 'nullable|boolean',
        ]);
        /* HTML checkbox: absent = unchecked, present (any value incl. "on") = checked. */
        $data['is_active'] = $request->has('is_active');

        return $data;
    }

    private function cityRules(Request $request)
    {
        $data = $request->validate([
            'geo_state_id' => 'required|integer|exists:geo_states,id',
            'name'         => 'required|string|max:255',
            'is_active'    => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');

        return $data;
    }
}
