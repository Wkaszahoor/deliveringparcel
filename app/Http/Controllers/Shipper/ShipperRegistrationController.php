<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperKycDocument;
use App\Models\ShipperProfile;
use App\Models\User;
use App\Services\Shipper\ShipperRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ShipperRegistrationController extends Controller
{
    /** Public program landing: perks, payout model, duties, KYC. */
    public function program()
    {
        return view('shippers.program');
    }

    /** Public anonymized directory of active, KYC-approved shippers. */
    public function directory()
    {
        $shippers = \App\Models\ShipperProfile::with('user:id,shipper_username,created_at')
            ->where('status', 'active')
            ->orderByDesc('level')->orderByDesc('rating')
            ->limit(24)->get()
            ->map(function ($sp) {
                $sp->display_name = $sp->user->shipper_username
                    ?? ('DP-SHIP-' . str_pad((string) $sp->id, 3, '0', STR_PAD_LEFT));
                $sp->countries_list = collect($sp->service_countries ?: [])
                    ->map(fn ($c) => strtoupper($c))->values()->toArray();
                return $sp;
            });

        return view('shippers.directory', ['shippers' => $shippers]);
    }
    public function showForm()
    {
        if (auth()->check() && ShipperProfile::where('user_id', auth()->id())->exists()) {
            // Already applied/active — send them to their workspace with context.
            return redirect()->route('shipper.dashboard')
                ->with('info', 'You already have a shipper application. Track its status here.');
        }

        $countries = \Illuminate\Support\Facades\DB::table('countries')
            ->where('allow_shipper', 1)->orderBy('name')
            ->get(['iso2', 'name']);

        return view('shipper.register', ['user' => auth()->user(), 'countries' => $countries]);
    }

    public function register(Request $request)
    {
        $rules = [
            'service_countries' => 'required|array|min:1',
            'service_countries.*' => 'string|max:5',
            'services_offered'  => 'required|array|min:1',
            'services_offered.*' => 'string|max:50',
            'residence_type'    => 'required|in:apartment,house,villa,office',
            'has_storage'       => 'nullable|boolean',
        ];
        if (!auth()->check()) {
            $rules += [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|max:200|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ];
        }
        $data = $request->validate($rules);

        $user = auth()->user();
        if (!$user) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'type'     => 'client',
            ]);
            if ($role = \App\Models\Role::where('slug', 'client')->first()) {
                $user->roles()->attach($role->id);
            }
            auth()->login($user);
        }

        if (ShipperProfile::where('user_id', $user->id)->exists()) {
            return redirect()->route('shipper.dashboard');
        }

        app(ShipperRegistrationService::class)->promoteToShipper(
            $user,
            $data['service_countries'],
            $data['services_offered'],
            $data['residence_type'],
            (bool) ($data['has_storage'] ?? false)
        );

        // Optional immediate KYC uploads.
        foreach (['government_id', 'selfie_photo', 'address_proof'] as $type) {
            if ($request->hasFile($type)) {
                $this->storeKyc($user, $type, $request->file($type));
            }
        }

        return redirect()->route('shipper.dashboard')
            ->with('info', 'Application submitted — pending KYC approval.');
    }

    public function uploadKyc(Request $request)
    {
        $profile = ShipperProfile::where('user_id', auth()->id())->firstOrFail();
        $data = $request->validate([
            'document_type' => 'required|in:government_id,selfie_photo,address_proof,social_media,consent_form',
            'file'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $this->storeKyc($profile->user, $data['document_type'], $request->file('file'));

        return redirect()->back()->with('success', 'KYC document uploaded.');
    }

    private function storeKyc(User $user, string $type, $file): void
    {
        $profile = ShipperProfile::where('user_id', $user->id)->first();
        if (!$profile) {
            return;
        }
        /* L12/Flysystem3 fix (parity with proof uploads): explicit putFileAs. */
        $name = \Illuminate\Support\Str::random(40) . '.' . strtolower($file->getClientOriginalExtension() ?: 'bin');
        \Illuminate\Support\Facades\Storage::disk('local')->putFileAs('shipper-kyc/' . $profile->id . '/' . $type, $file->getPathname(), $name);
        $path = 'shipper-kyc/' . $profile->id . '/' . $type . '/' . $name;
        ShipperKycDocument::create([
            'shipper_profile_id' => $profile->id,
            'document_type'      => $type,
            'file_path'          => $path,
            'original_filename'  => $file->getClientOriginalName(),
        ]);
    }
}
