<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mobile\Services\UIControlsService;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/mobile/v1/ui/controls → full 4-level hierarchy (cached 10 min,
 * carries the version). GET /api/mobile/v1/ui/version → cheap change
 * detection endpoint the app polls to know when to re-sync.
 */
class UIControlsApiController extends Controller
{
    public function index(): JsonResponse
    {
        $service = app(UIControlsService::class);
        $data = $service->getFullHierarchy();

        // Unified-app contract: one app serves shopper + shipper, driven by role.
        // A user with both roles gets both workspaces in the same navigation.
        $user = auth()->user();
        $profile = $user
            ? \App\Models\ShipperProfile::where('user_id', $user->id)->first()
            : null;
        $data['workspaces'] = [
            'shopper' => [
                'enabled' => true,
                'status'  => 'active',
            ],
            'shipper' => [
                'enabled'           => (bool) $profile,
                'status'            => $profile->status ?? null,
                'kyc_status'        => $profile->kyc_status ?? null,
                'level'             => $profile->level ?? null,
                'service_countries' => $profile->service_countries ?? [],
                'needs_kyc'         => $profile ? in_array($profile->status, ['pending'], true) || $profile->kyc_status === 'pending' : false,
            ],
            'dual_role' => (bool) $profile,
        ];

        return response()
            ->json(['data' => $data])
            ->header('X-UI-Version', (string) $data['version']);
    }

    public function version(): JsonResponse
    {
        return response()->json(app(UIControlsService::class)->getVersionOnly());
    }
}
