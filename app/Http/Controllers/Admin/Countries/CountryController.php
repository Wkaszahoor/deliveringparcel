<?php

namespace App\Http\Controllers\Admin\Countries;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.countries.index', [
            'filters' => [
                'q'      => (string) $request->input('q', ''),
                'active' => (string) $request->input('active', ''),
            ],
        ]);
    }

    public function data(Request $request)
    {
        $query = Country::query();

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('iso2', 'like', "%{$q}%")
                    ->orWhere('iso3', 'like', "%{$q}%")
                    ->orWhere('dial_code', 'like', "%{$q}%");
            });
        }
        if ($request->input('active') === '1') {
            $query->where('is_active', true);
        } elseif ($request->input('active') === '0') {
            $query->where('is_active', false);
        }

        $matrixCounts = [
            'is_active'          => (int) Country::where('is_active', 1)->count(),
            'allow_request_from' => (int) Country::where('allow_request_from', 1)->count(),
            'allow_delivery_to'  => (int) Country::where('allow_delivery_to', 1)->count(),
            'allow_shipper'      => (int) Country::where('allow_shipper', 1)->count(),
        ];
        $paged = $query->orderBy('name')->paginate(15)->through(function (Country $country) {
                return [
                    'id'            => $country->id,
                    'name'          => $country->name,
                    'iso2'          => strtoupper($country->iso2),
                    'iso3'          => strtoupper($country->iso3),
                    'dial_code'     => $country->dial_code,
                    'is_active'     => $country->is_active,
                    'allow_request_from' => (bool) $country->allow_request_from,
                    'allow_delivery_to'  => (bool) $country->allow_delivery_to,
                    'allow_shipper'      => (bool) $country->allow_shipper,
                    'shipping_rate' => $country->shipping_rate !== null ? number_format((float) $country->shipping_rate, 2) : null,
                    'urls'          => [
                        'edit'   => route('admin.countries.edit', $country->id),
                        'delete' => route('admin.countries.destroy', $country->id),
                        'toggle' => route('admin.countries.toggle', $country->id),
                    ],
                ];
            });

        return response()->json(array_merge($paged->toArray(), ['matrix_counts' => $matrixCounts]));
    }

    public function create()
    {
        return view('admin.countries.create', ['country' => new Country(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        Country::create($this->validated($request) + ['is_active' => $request->has('is_active')]);

        return redirect()->route('admin.countries.index')->with('success', 'Country created successfully.');
    }

    public function edit(Country $country)
    {
        return view('admin.countries.edit', ['country' => $country]);
    }

    public function update(Request $request, Country $country)
    {
        $country->update($this->validated($request, $country->id) + ['is_active' => $request->has('is_active')]);

        return redirect()->route('admin.countries.index')->with('success', 'Country updated successfully.');
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('admin.countries.index')->with('success', 'Country deleted successfully.');
    }

    /**
     * Inline activate/deactivate (AJAX).
     */
    /** Toggle any matrix column: overall, request-from, delivery-to, shipper. */
    public function toggle(Request $request, Country $country)
    {
        $allowed = ['is_active', 'allow_request_from', 'allow_delivery_to', 'allow_shipper'];
        $column = $request->input('column', 'is_active');
        if (!in_array($column, $allowed, true)) {
            $column = 'is_active';
        }

        $country->update([$column => !$country->{$column}]);
        $now = (bool) $country->fresh()->{$column};

        if ($request->expectsJson() || $request->ajax()) {
            $labels = [
                'is_active'          => $now ? 'Enabled' : 'Disabled',
                'allow_request_from' => $now ? 'Shoppers can request from' : 'Hidden in Ship From',
                'allow_delivery_to'  => $now ? 'Delivery available' : 'Not delivering',
                'allow_shipper'      => $now ? 'Shippers can serve' : 'Shipper list hidden',
            ];

            return response()->json([
                'ok'     => true,
                'column' => $column,
                'value'  => $now,
                'label'  => $labels[$column],
                'color'  => $now ? 'success' : 'secondary',
            ]);
        }

        return redirect()->route('admin.countries.index')->with('success', 'Country status updated.');
    }

    private function validated(Request $request, $ignoreId = null)
    {
        $isoRules = $ignoreId
            ? ['nullable', 'string', 'size:2', 'unique:countries,iso2,' . $ignoreId]
            : ['required', 'string', 'size:2', 'unique:countries,iso2'];
        $iso3Rules = $ignoreId
            ? ['nullable', 'string', 'size:3', 'unique:countries,iso3,' . $ignoreId]
            : ['required', 'string', 'size:3', 'unique:countries,iso3'];

        return $request->validate([
            'name'          => 'required|string|max:255',
            'iso2'          => array_merge($isoRules, ['regex:/^[A-Za-z]{2}$/']),
            'iso3'          => array_merge($iso3Rules, ['regex:/^[A-Za-z]{3}$/']),
            'dial_code'     => 'nullable|string|max:10|regex:/^\+?[0-9]{1,6}$/',
            'shipping_rate' => 'nullable|numeric|min:0|max:99999999.99',
        ]) + [
            'iso2' => null,
            'iso3' => null,
            'dial_code' => null,
            'shipping_rate' => null,
        ];
    }
}
