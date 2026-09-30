<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UsernameService
{
    private string $cusChars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /** Generate CUS-XXXXXX for shoppers. */
    public function generateCustomerUsername(): string
    {
        $attempts = 0;
        do {
            $rand = '';
            for ($i = 0; $i < 6; $i++) {
                $rand .= $this->cusChars[random_int(0, strlen($this->cusChars) - 1)];
            }
            $username = 'CUS-' . $rand;
            $attempts++;
        } while (DB::table('users')->where('customer_username', $username)->exists()
            && $attempts < 100);

        return $username;
    }

    /** Generate SHP-CC-XXXX for shippers (CC = country code). */
    public function generateShipperUsername(string $countryCode): string
    {
        $cc = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $countryCode) ?: 'XX', 0, 3));
        $attempts = 0;
        do {
            $num = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $username = 'SHP-' . $cc . '-' . $num;
            $attempts++;
        } while (DB::table('users')->where('shipper_username', $username)->exists()
            && $attempts < 100);

        return $username;
    }

    /** Assign a customer username to a user if not already set. */
    public function ensureCustomerUsername(int $userId): string
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if ($user && $user->customer_username) {
            return $user->customer_username;
        }
        $username = $this->generateCustomerUsername();
        DB::table('users')->where('id', $userId)->update([
            'customer_username'     => $username,
            'username_generated_at' => now(),
        ]);

        return $username;
    }

    /** Assign a shipper username when a shipper profile is created. */
    public function assignShipperUsername(int $userId, string $country): string
    {
        $username = $this->generateShipperUsername($country);
        DB::table('users')->where('id', $userId)->update([
            'shipper_username' => $username,
        ]);

        return $username;
    }
}
