<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ShipperProfile;

class RequireShipperProfile
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = ShipperProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return redirect()->route('shipper.register.form')
                ->with('error', 'Please complete your shipper registration.');
        }

        if (in_array($profile->status, ['suspended', 'banned'])) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your shipper account has been suspended.');
        }

        if ($profile->status === 'pending' || $profile->kyc_status === 'pending') {
            $allowed = $request->routeIs('shipper.dashboard')
                || $request->routeIs('shipper.kyc.*')
                || $request->routeIs('shipper.profile*')
                || $request->routeIs('shipper.countries*');
            if (!$allowed) {
                return redirect()->route('shipper.dashboard')
                    ->with('info', 'Your account is pending KYC approval.');
            }
        }

        $request->merge(['shipperProfile' => $profile]);
        view()->share('shipperProfile', $profile);

        return $next($request);
    }
}
