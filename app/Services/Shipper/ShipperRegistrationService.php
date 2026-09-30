<?php

namespace App\Services\Shipper;

use App\Models\Role;
use App\Models\ShipperProfile;
use App\Models\User;
use App\Notifications\Shipper\ShipperApprovedNotification;
use App\Notifications\Shipper\ShipperRejectedNotification;
use App\Services\UsernameService;
use Illuminate\Support\Facades\DB;

class ShipperRegistrationService
{
    public function __construct(private UsernameService $usernameSvc) {}

    /** Promote an existing user (shopper) to also be a shipper. */
    public function promoteToShipper(
        User $user,
        array $serviceCountries,
        array $servicesOffered,
        string $residenceType = 'house',
        bool $hasStorage = false
    ): ShipperProfile {
        return DB::transaction(function () use (
            $user, $serviceCountries, $servicesOffered, $residenceType, $hasStorage
        ) {
            $pendingRole = Role::where('slug', 'shipper_pending')->first();
            if ($pendingRole && !$user->roles->contains($pendingRole->id)) {
                $user->roles()->attach($pendingRole->id);
            }

            $primaryCountry = $serviceCountries[0] ?? 'XX';
            $this->usernameSvc->assignShipperUsername($user->id, $primaryCountry);

            return ShipperProfile::create([
                'user_id'               => $user->id,
                'level'                 => 1,
                'status'                => 'pending',
                'service_countries'     => array_map('strtoupper', $serviceCountries),
                'services_offered'      => $servicesOffered,
                'residence_type'        => $residenceType,
                'has_storage'           => $hasStorage,
                'max_concurrent_orders' => 3,
                'kyc_status'            => 'pending',
            ]);
        });
    }

    /** Admin approves a shipper's KYC. */
    public function approveShipper(ShipperProfile $profile, int $adminId): void
    {
        DB::transaction(function () use ($profile, $adminId) {
            $profile->update([
                'status'      => 'active',
                'kyc_status'  => 'approved',
                'verified_at' => now(),
            ]);

            $user = $profile->user;

            $pendingRole = Role::where('slug', 'shipper_pending')->first();
            $activeRole  = Role::where('slug', 'shipper')->first();

            if ($pendingRole) {
                $user->roles()->detach($pendingRole->id);
            }
            if ($activeRole && !$user->roles->contains($activeRole->id)) {
                $user->roles()->attach($activeRole->id);
            }

            $user->notify(new ShipperApprovedNotification($profile));
        });
    }

    /** Admin rejects a shipper's KYC. */
    public function rejectShipper(ShipperProfile $profile, string $reason, int $adminId): void
    {
        $profile->update([
            'status'            => 'banned',
            'kyc_status'        => 'rejected',
            'suspension_reason' => $reason,
            'suspended_at'      => now(),
        ]);

        $profile->user->roles()->detach(
            Role::whereIn('slug', ['shipper', 'shipper_pending'])->pluck('id')
        );

        $profile->user->notify(new ShipperRejectedNotification($profile, $reason));
    }
}
