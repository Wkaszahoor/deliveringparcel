<?php

namespace App\Http\Controllers\Api\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperProfile;
use Illuminate\Http\Request;

class ShipperApiProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = ShipperProfile::with('kycDocuments')
            ->where('user_id', $request->user()->id)->firstOrFail();

        return response()->json([
            'ok'   => true,
            'data' => [
                'shipper_username'  => $profile->user->shipper_username,
                'level'             => $profile->level,
                'status'            => $profile->status,
                'kyc_status'        => $profile->kyc_status,
                'service_countries' => $profile->service_countries, // read-only, admin-controlled
                'services_offered'  => $profile->services_offered,
                'residence_type'    => $profile->residence_type,
                'has_storage'       => (bool) $profile->has_storage,
                'rating'            => (float) $profile->rating,
                'total_completed'   => (int) $profile->total_completed,
                'kyc_documents'     => $profile->kycDocuments->map(fn ($d) => [
                    'type' => $d->document_type, 'status' => $d->status,
                ]),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'residence_type'   => 'required|in:apartment,house,villa,office',
            'has_storage'      => 'nullable|boolean',
            'services_offered' => 'nullable|array',
        ]);

        $profile->update([
            'residence_type'   => $data['residence_type'],
            'has_storage'      => (bool) ($data['has_storage'] ?? false),
            'services_offered' => $data['services_offered'] ?? $profile->services_offered,
        ]);

        return response()->json(['ok' => true, 'message' => 'Profile updated.']);
    }
}
