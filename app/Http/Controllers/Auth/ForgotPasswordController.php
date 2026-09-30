<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists with sending these notifications from
    | your application to your users. Feel free to explore the trait.
    |
    */

    use SendsPasswordResetEmails {
        sendResetLinkEmail as traitSendResetLinkEmail;
    }

    /**
     * GET /password/reset — NEW-design (home2 theme) email request form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * GET /password/resetlegacy — the previous AdminLTE-style form, kept
     * available on its own URL.
     */
    public function showLinkRequestFormLegacy()
    {
        return view('auth.passwords.legacy-email');
    }

    /**
     * POST /password/email — captcha gate + mailer-failure safety.
     * A broken/unreachable SMTP host must never white-page the form
     * (prod 2026-08-25: wrong MAIL_HOST made this request hang and 500).
     */
    public function sendResetLinkEmail(Request $request)
    {
        if (\App\Support\Turnstile::verify($request, 'password_reset') === false) {
            return back()->with('error', 'Captcha verification failed. Please try again.');
        }

        try {
            return $this->traitSendResetLinkEmail($request);
        } catch (\Throwable $e) {
            \Log::error('Password reset mail failed: ' . $e->getMessage());
            return back()->with('error', 'The reset link could not be emailed right now (mail server error). Please try again later or contact support.');
        }
    }
}
