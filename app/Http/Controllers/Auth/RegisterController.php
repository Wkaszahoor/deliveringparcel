<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\welcomemail;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers {
        // Aliased so our register() override can still run the trait flow
        // after the captcha gate (public sign-up is a spam target).
        register as traitRegister;
    }

    /**
     * Spam gate before the standard registration flow — same Turnstile
     * captcha as the request/contact forms. Skipped when the captcha
     * setting is off.
     */
    public function register(Request $request)
    {
        if (\App\Support\Turnstile::verify($request, 'register') === false) {
            throw ValidationException::withMessages([
                'captcha' => 'Captcha verification failed. Please try again.',
            ]);
        }

        return $this->traitRegister($request);
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    //  protected $redirectTo = 'client-dashbord';
    protected $redirectTo = 'dashboard';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // dd($data);
        $dev_role = new Role();
		$dev_role->slug = 'client';
		$dev_role->name = 'Client';
		$dev_role->save();

        $user = User::create([
            'name' => $data['name'],
            'type' => 'client',
            'email' => $data['email'],
            'number' => $data['number'],
            'password' => Hash::make($data['password']),
        ]);

        $user->save();
		$user->roles()->attach($dev_role);

        $details = [
            'title' => $data['name'],
            'email' => $data['email'],
            'url' => route('login')
        ];
        // The account already exists above — a mail failure must not abort
        // the registration. Unified pipeline: admin template (welcome_email)
        // if configured, else this legacy mailable; always logged.
        app(\App\Services\EmailService::class)->send(
            'welcome_email',
            $data['email'],
            $details,
            null,
            function () use ($details) {
                Mail::to($details['email'])->send(new welcomemail($details));
            }
        );
        return $user;
    }
}
