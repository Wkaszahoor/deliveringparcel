<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\Orderproducts;
use App\Models\Orders;
use App\Models\Role;
use App\Models\User;
use App\Notifications\TaskNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 2026-08-20 — /home2/request2 : the legacy package-consolidation request
 * form rebuilt in the home2 design (legacy /request + /country stay as-is).
 *
 * - Public like the legacy form: guests are auto-registered (random
 *   password, emailed when mail is configured) — logged-in users get the
 *   order attached to their account.
 * - Products submitted as addmore[i][...] rows, stored in orderproducts.
 * - Photo link is a URL only — NO file uploads on this form.
 * - Cloudflare Turnstile shown/verified only while the
 *   api_turnstile_enabled admin setting is ON.
 */
class Request2Controller extends Controller
{
    /** GET /home2/request2 (optional ?from=&to= prefill like /country). */
    public function create(Request $request)
    {
        $user = auth()->user();

        return view('home2.request2', [
            'prefill' => [
                'from'    => $request->query('from', old('shipfrom', '')),
                'to'      => $request->query('to', old('shipto', '')),
                'name'    => old('name', $user->name ?? ''),
                'email'   => old('email', $user->email ?? ''),
                'number'  => old('number', $user->ship_number ?? $user->number ?? ''),
                'address' => old('address', $user->ship_address1 ?? ''),
            ],
            'isGuest'     => $user === null,
            'turnstileOn' => \App\Models\Setting::getBool('api_turnstile_enabled', true),
        ]);
    }

    /** POST /home2/request2 */
    public function store(Request $request)
    {
        if (\App\Models\Setting::getBool('api_turnstile_enabled', true)) {
            $err = $this->verifyTurnstile($request);
            if ($err !== null) {
                return back()->with('error', $err)->withInput();
            }
        }

        $data = $request->validate([
            'name'                => 'required|string|max:191',
            'email'               => 'required|email|max:191',
            'number'              => 'required|string|max:20',
            'shipfrom'            => 'required|string|max:120',
            'shipto'              => 'required|string|max:120',
            'postalcode'          => 'required|string|max:20',
            'address'             => 'required|string|max:500',
            'approximate_weight'  => 'required|numeric|min:0.1|max:20000',
            'product_services'    => 'nullable|array',
            'product_services.*'  => 'nullable|string|max:60',
            'product_photo'       => 'nullable|url|max:500',
            'purchase_assistance' => 'nullable|boolean',
            'addmore'             => 'required|array|min:1',
            'addmore.*.producturl'    => 'required|string|max:500',
            'addmore.*.productname'   => 'nullable|string|max:191',
            'addmore.*.productquantity' => 'required|integer|min:1|max:9999',
            'addmore.*.productweight'  => 'required|numeric|min:0|max:100000',
        ]);

        /* ---------- resolve the ordering user ---------- */
        $user = auth()->user();
        $newAccount = false;

        if ($user === null) {
            $existing = User::where('email', $data['email'])->first();
            if ($existing) {
                return back()
                    ->with('error', 'This email is already registered — please log in first, then submit your request.')
                    ->withInput();
            }

            $plainPassword = Str::random(12);
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'number'   => $data['number'],
                'type'     => 'client',
                'password' => Hash::make($plainPassword),
            ]);

            $role = Role::firstOrCreate(
                ['slug' => 'client'],
                ['name' => 'Client']
            );
            $user->roles()->attach($role->id);

            $newAccount = true;
            $this->mailCredentials($data['email'], $data['name'], $plainPassword);
        }

        /* ---------- order + products ---------- */
        /* Service selections map to the per-service int columns on orders
           (product_services is an int column — an imploded list would be
           silently truncated to 0 by MySQL). */
        $svc = array_flip($data['product_services'] ?? []);

        $order = Orders::create([
            'user_id'             => $user->id,
            'order_id'            => (int) Orders::max(\DB::raw('CAST(order_id AS UNSIGNED)')) + 1,
            'shipfrom'            => $data['shipfrom'],
            'shipto'              => $data['shipto'],
            'postalcode'          => $data['postalcode'],
            'address'             => $data['address'],
            'approximate_weight'  => $data['approximate_weight'],
            'product_disinfection'  => isset($svc['disinfection']) ? 1 : 0,
            'product_consolidation' => isset($svc['consolidation']) ? 1 : 0,
            'product_customs'       => isset($svc['customs']) ? 1 : 0,
            'product_check'         => isset($svc['check']) ? 1 : 0,
            'product_photo'       => 0, // int flag column on orders; the link itself is kept on the product row
            'product_purchase'    => !empty($data['purchase_assistance']) ? 1 : 0,
            'total'               => 0, // offer stage — nothing is charged at request time
            'order_status'        => 'pending',
            'confirmation'        => 0,
        ]);

        foreach ($data['addmore'] as $row) {
            Orderproducts::create([
                'order_id'       => $order->id,
                'productname'    => $row['productname'] ?? '',
                'producturl'     => $row['producturl'],
                'productquantity' => $row['productquantity'],
                'productweight'  => $row['productweight'],
            ]);
        }

        /* orders.product_photo is an int flag column — keep the pasted link
           on the first product row so it is not lost. (Queried directly:
           the Orders::orderproducts relation derives a wrong FK name.) */
        if (!empty($data['product_photo'])) {
            $first = Orderproducts::where('order_id', $order->id)->orderBy('id')->first();
            if ($first) {
                $first->productname = trim($first->productname . "\nPhoto: " . $data['product_photo']);
                $first->save();
            }
        }

        /* ---------- notifications (order_id key kept for the fixed dropdown views) ---------- */
        $admin = User::where('type', 'admin')->first();
        if ($admin) {
            $admin->notifyNow(new TaskNotification([
                'title'        => 'New request #' . $order->order_id . ' (home2 form)',
                'order_number' => $order->order_id,
                'greeting'     => $data['name'] . ' placed a request',
                'order_id'     => $order->id,
                'description'  => '',
            ]), ['database']);
        }
        $user->notifyNow(new TaskNotification([
            'title'        => 'Your request has been placed — order #' . $order->order_id,
            'order_number' => $order->order_id,
            'greeting'     => 'Request Placed',
            'order_id'     => $order->id,
            'description'  => 'Please check the dashboard for the status — you will receive an offer shortly.',
        ]), ['database']);

        $msg = 'Request #' . $order->order_id . ' submitted — we will send you an offer shortly.'
            . ($newAccount ? ' Login details were emailed to you.' : '');

        return redirect()->route('home2.index')->with('success', $msg);
    }

    /**
     * Cloudflare Turnstile siteverify (only called while the toggle is ON).
     * Hard-allowlisted https endpoint — no arbitrary hosts, no private IPs.
     * @return string|null error message, null on success
     */
    private function verifyTurnstile(Request $request): ?string
    {
        try {
            $token = (string) $request->input('cf-turnstile-response', '');
            if ($token === '') {
                return 'Please complete the captcha before submitting.';
            }

            $secret = config('services.turnstile.secret')
                ?: '0x4AAAAAABdxU-lYdgite5ftC53Vs1W_6A0'; // legacy hardcoded secret
            $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

            $scheme = parse_url($url, PHP_URL_SCHEME);
            $host   = parse_url($url, PHP_URL_HOST);
            if ($scheme !== 'https' || $host !== 'challenges.cloudflare.com') {
                return 'Captcha verification is temporarily unavailable.';
            }

            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $res = $client->request('POST', $url, [
                'form_params' => [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ],
            ]);

            $body = json_decode((string) $res->getBody());
            if (empty($body->success)) {
                return 'Captcha verification failed. Please try again.';
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('request2 turnstile verify failed: ' . $e->getMessage());

            return 'Captcha verification error. Please try again shortly.';
        }
    }

    /** Email new guests their login details; never blocks the request.
     *  Unified pipeline: admin template (guest_account_credentials) if
     *  configured, else the legacy mailable; always logged. */
    private function mailCredentials(string $email, string $name, string $plainPassword): void
    {
        $details = [
            'title'    => $name,
            'email'    => $email,
            'password' => $plainPassword,
            'url'      => route('login'),
        ];

        app(\App\Services\EmailService::class)->send(
            'guest_account_credentials',
            $email,
            $details,
            null,
            function () use ($details) {
                \Mail::to($details['email'])->send(new \App\Mail\Login_Mail($details));
            }
        );
    }
}
