<?php

namespace App\Http\Controllers\Admin\Carriers;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\CarrierTrackingLookup;
use App\Services\Carriers\CarrierManager;
use App\Services\TrackingNumberParser;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    /** Grid of carrier cards with status chips (never secret values). */
    public function index()
    {
        $cards = [];
        foreach (CarrierManager::adapters() as $code => $adapter) {
            $meta = config('admin_carriers.carriers.' . $code, []);
            $model = Carrier::where('code', $code)->first();
            $cards[] = [
                'code'       => $code,
                'name'       => $adapter->name(),
                'icon'       => $meta['icon'] ?? 'fa-truck',
                'color'      => $meta['color'] ?? 'secondary',
                'configured' => $adapter->configured(),
                'enabled'    => $model->is_enabled ?? true,
                'env_keys'   => $meta['env_keys'] ?? [],
                // nullsafe, NOT optional(): the property fetch on a null model
                // crashes before optional() ever sees it (no DB row yet).
                'last_check' => $model?->last_checked_at?->diffForHumans(),
                'urls'       => [
                    'ping'   => route('admin.carriers.ping', $code),
                    'toggle' => route('admin.carriers.toggle', $code),
                ],
            ];
        }

        return view('admin.carriers.index', ['cards' => $cards]);
    }

    /** Connectivity probe (mock-aware) — AJAX. */
    public function ping(string $code)
    {
        $adapter = CarrierManager::get($code);
        abort_if($adapter === null, 404);
        $result = $adapter->ping();
        Carrier::updateOrCreate(['code' => $code], ['name' => $adapter->name(), 'last_checked_at' => now()]);

        return response()->json($result);
    }

    /** Enable/disable a carrier. */
    public function toggle(string $code)
    {
        $adapter = CarrierManager::get($code);
        abort_if($adapter === null, 404);
        $model = Carrier::updateOrCreate(['code' => $code], ['name' => $adapter->name()]);
        $model->update(['is_enabled' => !$model->is_enabled]);

        return redirect()->route('admin.carriers.index')->with('success', $adapter->name() . ($model->is_enabled ? ' enabled.' : ' disabled.'));
    }

    /** Parse tool view. */
    public function parseForm()
    {
        return view('admin.carriers.parse');
    }

    /** POST: detect carrier(s) for a number — JSON. */
    public function parse(Request $request)
    {
        $data = $request->validate(['number' => 'required|string|max:100']);

        return response()->json(['number' => $data['number'], 'matches' => TrackingNumberParser::allMatches($data['number'])]);
    }

    /** Aggregated tracking view (detect → adapter → timeline). */
    public function trackForm(Request $request)
    {
        $number = trim((string) $request->input('number', ''));
        $result = null;
        $detected = null;
        if ($number !== '') {
            $detected = TrackingNumberParser::detect($number);
            $result = CarrierManager::track($detected['carrier'], $number);
            CarrierTrackingLookup::create([
                'number' => $number,
                'carrier_code' => $detected['carrier'],
                'result_status' => $result['status'] ?? null,
            ]);
        }

        return view('admin.carriers.track', ['number' => $number, 'detected' => $detected, 'result' => $result]);
    }

    /** Recent lookup history. */
    public function history()
    {
        $lookups = CarrierTrackingLookup::latest('id')->take(50)->get();

        return view('admin.carriers.history', ['lookups' => $lookups]);
    }
}
