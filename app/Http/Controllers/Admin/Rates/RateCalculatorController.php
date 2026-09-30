<?php

namespace App\Http\Controllers\Admin\Rates;

use App\Http\Controllers\Controller;
use App\Models\RateZone;
use App\Services\RateCalculator;
use Illuminate\Http\Request;

class RateCalculatorController extends Controller
{
    public function index()
    {
        return view('admin.rates.calculator.index', [
            'zones' => RateZone::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function compute(Request $request)
    {
        $data = $request->validate([
            'origin'         => 'required|integer|exists:rate_zones,id',
            'destination'    => 'required|integer|exists:rate_zones,id|different:origin',
            'weight'         => 'required|numeric|min:0.01|max:999999',
            'service_type'   => 'required|in:' . implode(',', array_keys(config('admin_rates.service_types'))),
            'declared_value' => 'nullable|numeric|min:0|max:99999999',
            'apply_fuel'     => 'nullable|boolean',
            'apply_insurance' => 'nullable|boolean',
            'length_cm'      => 'nullable|numeric|min:0',
            'width_cm'       => 'nullable|numeric|min:0',
            'height_cm'      => 'nullable|numeric|min:0',
        ]);

        $weight = (float) $data['weight'];
        $vol = null;
        if (!empty($data['length_cm']) && !empty($data['width_cm']) && !empty($data['height_cm'])) {
            $calc = app(RateCalculator::class);
            $vol = $calc->volumetricWeight((float) $data['length_cm'], (float) $data['width_cm'], (float) $data['height_cm']);
            $weight = $calc->chargeableWeight($weight, (float) $data['length_cm'], (float) $data['width_cm'], (float) $data['height_cm']);
        }

        $result = app(RateCalculator::class)->calculate(
            (int) $data['origin'],
            (int) $data['destination'],
            $weight,
            $data['service_type'],
            [
                'declared_value'  => $data['declared_value'] ?? 0,
                'apply_fuel'      => !empty($data['apply_fuel']),
                'apply_insurance' => !empty($data['apply_insurance']),
            ]
        );

        $result['chargeable_weight'] = $weight;
        $result['volumetric_weight'] = $vol;

        return response()->json($result);
    }
}
