<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | remove these methods and replace with your own implementations.
    |
    */

    use ResetsPasswords {
        reset as traitReset;
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * GET /password/reset/{token} — NEW-design (home2 theme) reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * GET /password/resetlegacy/{token} — the previous AdminLTE-style form.
     */
    public function showResetFormLegacy(Request $request, $token = null)
    {
        return view('auth.passwords.legacy-reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * POST /password/reset — captcha gate + mailer-failure safety.
     * The trait saves the new password FIRST, then emails the confirmation;
     * if that mail throws (SMTP down), the password is already changed, so
     * fail gracefully instead of a white-page 500.
     */
    public function reset(Request $request)
    {
        if (\App\Support\Turnstile::verify($request, 'password_reset') === false) {
            return back()->with('error', 'Captcha verification failed. Please try again.');
        }

        try {
            return $this->traitReset($request);
        } catch (\Throwable $e) {
            \Log::error('Password reset confirmation mail failed: ' . $e->getMessage());
            return redirect('/')
                ->with('status', 'Your password was reset. (The confirmation email could not be sent, but you can sign in with your new password.)');
        }
    }
}
