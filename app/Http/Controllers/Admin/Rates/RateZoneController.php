<?php

namespace App\Http\Controllers\Admin\Rates;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RateZone;
use App\Models\RateZoneCountry;
use Illuminate\Http\Request;

class RateZoneController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.rates.zones.index', [
            'filters' => ['q' => (string) $request->input('q', '')],
        ]);
    }

    public function data(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $query = RateZone::query()->withCount('countries');
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)->orWhere('code', 'like', $like);
            });
        }
        $query->orderBy('name');

        return response()->json(
            $query->paginate(15)->through(function (RateZone $z) {
                return [
                    'id'        => $z->id,
                    'name'      => $z->name,
                    'code'      => $z->code,
                    'countries' => $z->countries_count,
                    'rules'     => $z->rulesFrom()->count() + $z->rulesTo()->count(),
                    'urls'      => [
                        'edit'   => route('admin.rates.zones.edit', $z->id),
                        'delete' => route('admin.rates.zones.destroy', $z->id),
                    ],
                ];
            })->toArray()
        );
    }

    public function create()
    {
        return view('admin.rates.zones.form', ['zone' => new RateZone()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $zone = RateZone::create($data);
        $this->syncCountries($request, $zone);

        return redirect()->route('admin.rates.zones.index')->with('success', 'Zone created.');
    }

    public function edit(RateZone $zone)
    {
        return view('admin.rates.zones.form', [
            'zone' => $zone,
        ]);
    }

    public function update(Request $request, RateZone $zone)
    {
        $zone->update($this->validated($request, $zone->id));
        $this->syncCountries($request, $zone);

        return redirect()->route('admin.rates.zones.index')->with('success', 'Zone updated.');
    }

    public function destroy(RateZone $zone)
    {
        $zone->countries()->delete();
        $zone->delete();

        return redirect()->route('admin.rates.zones.index')->with('success', 'Zone deleted.');
    }

    /* ------------------------------------------------------------------ */

    private function validated(Request $request, $ignoreId = null)
    {
        $unique = $ignoreId
            ? 'unique:rate_zones,code,' . $ignoreId
            : 'unique:rate_zones,code';

        return $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:20|' . $unique,
            'description' => 'nullable|string|max:1000',
        ]);
    }

    private function syncCountries(Request $request, RateZone $zone)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('country_ids', [])));
        $zone->countries()->delete();
        foreach (array_unique($ids) as $id) {
            $code = Country::where('id', $id)->value('iso2');
            RateZoneCountry::create([
                'rate_zone_id' => $zone->id,
                'country_id'   => $id,
                'country_code' => $code,
            ]);
        }
    }
}
