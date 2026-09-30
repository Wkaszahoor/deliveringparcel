<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Shared "Sign in with <provider>" flow — additive alongside the existing
 * email/password login (LoginController). One controller per supported
 * Socialite driver (GoogleController, FacebookController, ...), each just
 * declaring $provider (the Socialite driver name) and $idColumn (the users
 * table column that stores that provider's account id).
 *
 * Matches an incoming social account to a local user by $idColumn first,
 * then by email (so an existing email/password account gets linked rather
 * than duplicated), else creates a new client user.
 */
abstract class SocialAuthController extends Controller
{
    protected string $provider;
    protected string $idColumn;

    public function redirect()
    {
        return Socialite::driver($this->provider)->redirect();
    }

    public function callback()
    {
        $socialUser = Socialite::driver($this->provider)->stateless()->user();

        $user = User::where($this->idColumn, $socialUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                $user->forceFill([$this->idColumn => $socialUser->getId()])->save();
            }
        }

        if (! $user) {
            $role = Role::firstOrCreate(['slug' => 'client'], ['name' => 'Client']);

            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: ucfirst($this->provider) . ' User',
                'type' => 'client',
                'email' => $socialUser->getEmail(),
                $this->idColumn => $socialUser->getId(),
                'number' => '',
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
            ]);
            $user->roles()->attach($role);
        }

        Auth::login($user, true);

        $userRole = optional($user->roles->first())->slug;

        return redirect()->intended($userRole === 'admin' ? 'admin-orders' : 'dashboard');
    }
}
