<?php

namespace App\Http\Controllers\Admin\Rates;

use App\Http\Controllers\Controller;
use App\Models\RateSurcharge;
use App\Models\RateInsurance;
use Illuminate\Http\Request;

/**
 * Compact controller for surcharges + insurance tiers (simple lists/forms).
 */
class RateExtraController extends Controller
{
    public function surchargesIndex()
    {
        return view('admin.rates.surcharges.index');
    }

    public function surchargesData()
    {
        return response()->json(
            RateSurcharge::orderBy('name')->paginate(15)->through(function (RateSurcharge $s) {
                return [
                    'id'      => $s->id,
                    'name'    => $s->name,
                    'type'    => $s->type,
                    'value'   => rtrim(rtrim(number_format((float) $s->value, 2, '.', ''), '0'), '.'),
                    'applies' => ucfirst($s->applies_to),
                    'active'  => (bool) $s->is_active,
                    'urls'    => [
                        'edit'   => route('admin.rates.surcharges.edit', $s->id),
                        'delete' => route('admin.rates.surcharges.destroy', $s->id),
                    ],
                ];
            })->toArray()
        );
    }

    public function surchargesCreate()
    {
        return view('admin.rates.surcharges.form', ['surcharge' => new RateSurcharge(['is_active' => true])]);
    }

    public function surchargesStore(Request $request)
    {
        RateSurcharge::create($this->surchargeRules($request));

        return redirect()->route('admin.rates.surcharges.index')->with('success', 'Surcharge created.');
    }

    public function surchargesEdit(RateSurcharge $surcharge)
    {
        return view('admin.rates.surcharges.form', ['surcharge' => $surcharge]);
    }

    public function surchargesUpdate(Request $request, RateSurcharge $surcharge)
    {
        $surcharge->update($this->surchargeRules($request));

        return redirect()->route('admin.rates.surcharges.index')->with('success', 'Surcharge updated.');
    }

    public function surchargesDestroy(RateSurcharge $surcharge)
    {
        $surcharge->delete();

        return redirect()->route('admin.rates.surcharges.index')->with('success', 'Surcharge deleted.');
    }

    public function insurancesIndex()
    {
        return view('admin.rates.insurances.index');
    }

    public function insurancesData()
    {
        return response()->json(
            RateInsurance::orderBy('declared_value_min')->paginate(15)->through(function (RateInsurance $i) {
                return [
                    'id'    => $i->id,
                    'range' => '$' . number_format((float) $i->declared_value_min, 0) . ($i->declared_value_max !== null ? ' – $' . number_format((float) $i->declared_value_max, 0) : ' +'),
                    'cost'  => number_format((float) $i->cost, 2),
                    'active' => (bool) $i->is_active,
                    'urls'   => [
                        'edit'   => route('admin.rates.insurances.edit', $i->id),
                        'delete' => route('admin.rates.insurances.destroy', $i->id),
                    ],
                ];
            })->toArray()
        );
    }

    public function insurancesCreate()
    {
        return view('admin.rates.insurances.form', ['insurance' => new RateInsurance(['is_active' => true])]);
    }

    public function insurancesStore(Request $request)
    {
        RateInsurance::create($this->insuranceRules($request));

        return redirect()->route('admin.rates.insurances.index')->with('success', 'Insurance tier created.');
    }

    public function insurancesEdit(RateInsurance $insurance)
    {
        return view('admin.rates.insurances.form', ['insurance' => $insurance]);
    }

    public function insurancesUpdate(Request $request, RateInsurance $insurance)
    {
        $insurance->update($this->insuranceRules($request));

        return redirect()->route('admin.rates.insurances.index')->with('success', 'Insurance tier updated.');
    }

    public function insurancesDestroy(RateInsurance $insurance)
    {
        $insurance->delete();

        return redirect()->route('admin.rates.insurances.index')->with('success', 'Insurance tier deleted.');
    }

    /* ------------------------------------------------------------------ */

    private function surchargeRules(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:percentage,fixed',
            'value'      => 'required|numeric|min:0|max:99999999',
            'applies_to' => 'required|in:all,fuel,insurance,remote',
            'is_active'  => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');

        return $data;
    }

    private function insuranceRules(Request $request)
    {
        $data = $request->validate([
            'declared_value_min' => 'required|numeric|min:0',
            'declared_value_max' => 'nullable|numeric|gt:declared_value_min',
            'cost'               => 'required|numeric|min:0',
            'is_active'          => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        $data['declared_value_max'] = $data['declared_value_max'] ?? null;

        return $data;
    }
}
