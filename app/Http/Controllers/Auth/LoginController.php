<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
// use Auth;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        // Aliased so our login() override can run the captcha gate first.
        login as traitLogin;
    }

    /**
     * Captcha gate before the standard login flow — controlled by the
     * turnstile_on_login admin setting (master switch still applies).
     */
    public function login(Request $request)
    {
        if (\App\Support\Turnstile::verify($request, 'login') === false) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'captcha' => 'Captcha verification failed. Please try again.',
            ]);
        }

        return $this->traitLogin($request);
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    // protected $redirectTo = '/dashboard';
    protected function redirectTo(){
        // Role-less users must not crash login (Collection[0] on empty) —
        // fall through to the client dashboard instead.
        $user_role = optional(Auth::user()->roles->first())->slug;

        if($user_role =='admin'){
            return 'admin-orders';
        }
        else{
            return  'dashboard';
        }
    }

    public function logout(Request $request) {
        Auth::logout();
        return redirect(route('/'));
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
