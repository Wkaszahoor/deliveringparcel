<?php

namespace App\Http\Controllers;

use App\Mail\Login_Mail;
use Notification;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\Orders;
use App\Models\Orderproducts;
use App\Models\Offerorder;
use App\Models\Offerorderproducts;
use App\Models\Offerorderservices;
use Illuminate\Support\Facades\Mail;
use App\Mail\Loginmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderChat;
use App\Models\ShippingAddresses;
use Session;
use Stripe;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to view your orders.');
        }
        $id = Auth::user()->id;
        $order = Orders::where('user_id',$id)->orderBy("orders.created_at", 'desc')->get();
        // $offer = Offerorder::find($address->id)->first();
        // $offer = Orders::select('*', 'orders.id AS id')
        //     ->join('offerorders','orders.id' ,'=','offerorders.order_id')
        //     ->where('user_id',$id)
        //     ->get();
        // 2026-09-19: portal redesign (admin-style CRUD table, filters, lazy load).
        $paidIds  = \Illuminate\Support\Facades\DB::table('offerorders')->where('offer_status', 1)->pluck('order_id');
        $paid     = (clone $order)->whereIn('id', $paidIds)->count();
        $awaiting = $order->count() - $paid;
        $kpis = ['total' => $order->count(), 'paid' => $paid, 'awaiting' => $awaiting];
        return view('portal.client-orders', compact('kpis'));
    }
    
 
    public function country(Request $request)
    {
    //    dd($request->all());
        $data = ["from" => $request->from, "to" => $request->to];
        // dd($data);
        return view('specialrequest', compact('data'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\App\Models\Setting::getBool('api_turnstile_enabled', true)) {
        try {
    $captchaResponse = $request->input('cf-turnstile-response');

    $client = new \GuzzleHttp\Client();
    $response = $client->request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'form_params' => [
            'secret' => '0x4AAAAAABdxU-lYdgite5ftC53Vs1W_6A0',
            'response' => $captchaResponse,
            'remoteip' => $request->ip(),
        ]
    ]);

    $body = json_decode((string) $response->getBody());

    if (!$body->success) {
        return back()->with('error', 'Captcha verification failed. Please try again.');
    }
} catch (\Exception $e) {
    return back()->with('error', 'CAPTCHA verification error: ' . $e->getMessage());
}
        }
        // dd($request->all());
        if (Orders::all()->count() > 0) {
            $last_id = DB::table('orders')->latest()->first()->order_id;
            $order_id = $last_id + 1;
        } else {
            $order_id = 1001;
        }
        // dd('else');
        // verifying if email already exist 

        $users = User::where('email', '=', $request->input('email'))->first();
        
        if ($users != null) {
            return back()->with('error','This Email is already Registered. Please Login First.');
        } 
        else {
            
             // if user is not registered then this code will execute
            if ($request->user_id == "user-invalid") {
                // dd('hello');
                $request->validate([
                    'name'=> 'required',
                    'email'=> 'required',
                    'number'=>'required|max:20',
                    'shipfrom'=>'required',
                    'shipto'=>'required',
                    'postalcode'=>'required|max:10',
                    'address'=>'required',
                    'approximate_weight'=>'required|max:20',
                    'product_services'=>'required',
                    'total'=>'required',
                    'addmore.*.producturl' => 'required',
                    'addmore.*.productquantity' => 'required',
                    'addmore.*.productweight' => 'required',
                ]);
            $hashedRandomPassword = Str::random(12);
            $random_orderid = Str::random(6);

                // creating new role for newly registered users or clients 
                // $data = array(
                //     'name' => $request->name,
                //     'email' => $request->email,
                //     'password'=>$hashedRandomPassword,
                // );
                // Mail::to($request->email)->send(new Loginmail($data));
                $details = [
                    'title' => $request->name,
                    'email' => $request->email,
                    'password'=>$hashedRandomPassword,
                    'url' => route('login')
                ];

                // Mail failure must never abort the signup + order after the
                // row is persisted (password shown below). Unified pipeline:
                // admin template (guest_account_credentials) if configured,
                // else this legacy mailable; always logged.
                app(\App\Services\EmailService::class)->send(
                    'guest_account_credentials',
                    $request->email,
                    $details,
                    $order_id ?? null,
                    function () use ($details) {
                        Mail::to($details['email'])->send(new Login_Mail($details));
                    }
                );

            $dev_role = new Role();
            $dev_role->slug = 'client';
            $dev_role->name = 'Client';
            $dev_role->save();
            
            // Saving newly registered users 
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'number' => $request->number,
                'type' => 'client',
                'password' =>  Hash::make($hashedRandomPassword ),
            ]);
            $user->save();
            $user->roles()->attach($dev_role);

            // SERVER-SIDE PRICE VERIFICATION (2026-09-12): row totals and the order
            // product total are recomputed from quantity x price. Computed values are
            // authoritative (a tampered submission is overwritten), and any mismatch is
            // recorded on the order (price_flags) + reported to the admin. Also covers
            // the NOT NULL crash when the JS-filled total_price arrives empty.
            $serverRowTotals = [];
            $priceFlags = [];
            $serverSum = 0.0;
            foreach ((array) $request->addmore as $key => $value) {
                $qty = (float) ($value['productquantity'] ?? 0);
                $unit = (float) ($value['productprice'] ?? 0);
                $computed = round($qty * $unit, 2);
                $submitted = round((float) ($value['producttotal'] ?? 0), 2);
                if (abs($computed - $submitted) > 0.01) {
                    $priceFlags[] = 'Row ' . (((int) $key) + 1) . ' (' . trim((string) ($value['productname'] ?? '')) . '): submitted total ' . $submitted . ' but ' . $qty . ' x ' . $unit . ' = ' . $computed;
                }
                $serverRowTotals[$key] = $computed;
                $serverSum += $computed;
            }
            $product_totalprice = round($serverSum, 2);
            $submittedTotal = $request->total_price;
            if ($submittedTotal !== null && $submittedTotal !== '' && abs(round((float) $submittedTotal, 2) - $product_totalprice) > 0.01) {
                $priceFlags[] = 'Order products total: submitted ' . $submittedTotal . ' but server sum of quantity x price = ' . $product_totalprice;
            }

            $order = new Orders([
                'user_id' => $user->id,
                'shipfrom' => $request->shipfrom,
                'shipto' => $request->shipto,
                'postalcode' => $request->postalcode,
                'address' => $request->address,
                'approximate_weight' => $request->approximate_weight,
                'product_services' => $request->product_services,
                'product_photo' => $request->product_photo,
                'product_check' => $request->product_check,
                'product_customs' => $request->product_customs,
                'product_prohibited' => $request->product_prohibited,
                'product_disinfection' => $request->product_disinfection,
                'product_consolidation' => $request->product_consolidation,
                'product_purchase' =>$request->purchase_assistence,
                'total' => $request->total,
                'order_id'=> $order_id,
                'product_totalprice'=>$product_totalprice,
                // "order_status" => 'Request Submitted'
            ]);
            $order->save();
            if ($priceFlags) {
                $order->price_flags = implode(' | ', $priceFlags);
                $order->save();
                $flagAdmin = User::where('type', 'admin')->first();
                if ($flagAdmin) {
                    $flagAdmin->notify(new \App\Notifications\TaskNotification([
                        'title' => 'Price discrepancy in order #' . $order_id,
                        'order_number' => $order_id,
                        'greeting' => 'Manual review needed',
                        'order_id' => $order->id,
                        'description' => implode(' | ', $priceFlags),
                    ]));
                }
            }
            foreach ($request->addmore as $key => $value) {
                    $product = new Orderproducts([
                        'order_id' => $order->id,
                        'productname' => $value['productname'],
                        'producturl' => $value['producturl'],
                        'productquantity' => $value['productquantity'],
                        'productprice' => $value['productprice'],
                        'product_total' => $serverRowTotals[$key] ?? 0,
                        'productweight' => $value['productweight'],
                    ]);
                    $product->save();
                }
                // $user = User::find(1);
                $admin = User::where('type', 'admin')->first();
                // $user_name = User::where('id', $request->user_id)->first();
                $details = [

                    'title' => 'Notification on Order #' . $order_id . ' from Shopper',
                    'order_number' => $order_id,
                    'greeting' => $request->name. ' place order',
                    'order_id' => $order->id,
                    'description' => ''
                ];
                $admin->notify(new \App\Notifications\TaskNotification($details));
                $client = User::where('id', $user->id)->first();
                // dd($client);
                $details = [

                    'title' => 'Your request has been placed with the order number # ' . $order_id,
                    'order_number' => $order_id,
                    'greeting' => 'Request Placed',
                    'order_id' => $order->id,
                    'description' => 'Please check the dashboard for the status and you will soon get an offer which will be notified by email as well.'
                ];
                $client->notify(new \App\Notifications\TaskNotification($details));

            return redirect(route('request'))->with('success','Login detail sent to your email address');
            } 
            // if user is logined then this code will execute
            else {
                $request->validate([
                    'shipfrom'=>'required',
                    'shipto'=>'required',
                    'postalcode'=>'required|max:10',
                    'address'=>'required',
                    'approximate_weight'=>'required|max:20',
                    'product_services'=>'required',
                    'total'=>'required',
                    'addmore.*.producturl' => 'required',
                    'addmore.*.productquantity' => 'required',
                    'addmore.*.productweight' => 'required',
                ]);
                $user = $request->user_id;
                $user_name=User::where('id',$request->user_id)->first();
                // Same server-side price verification as the guest branch above.
                // SERVER-SIDE PRICE VERIFICATION (2026-09-12): row totals and the order
                // product total are recomputed from quantity x price. Computed values are
                // authoritative (a tampered submission is overwritten), and any mismatch is
                // recorded on the order (price_flags) + reported to the admin. Also covers
                // the NOT NULL crash when the JS-filled total_price arrives empty.
                $serverRowTotals = [];
                $priceFlags = [];
                $serverSum = 0.0;
                foreach ((array) $request->addmore as $key => $value) {
                    $qty = (float) ($value['productquantity'] ?? 0);
                    $unit = (float) ($value['productprice'] ?? 0);
                    $computed = round($qty * $unit, 2);
                    $submitted = round((float) ($value['producttotal'] ?? 0), 2);
                    if (abs($computed - $submitted) > 0.01) {
                        $priceFlags[] = 'Row ' . (((int) $key) + 1) . ' (' . trim((string) ($value['productname'] ?? '')) . '): submitted total ' . $submitted . ' but ' . $qty . ' x ' . $unit . ' = ' . $computed;
                    }
                    $serverRowTotals[$key] = $computed;
                    $serverSum += $computed;
                }
                $product_totalprice = round($serverSum, 2);
                $submittedTotal = $request->total_price;
                if ($submittedTotal !== null && $submittedTotal !== '' && abs(round((float) $submittedTotal, 2) - $product_totalprice) > 0.01) {
                    $priceFlags[] = 'Order products total: submitted ' . $submittedTotal . ' but server sum of quantity x price = ' . $product_totalprice;
                }
                $order = new Orders([
                        'user_id' => $user,
                        'shipfrom' => $request->shipfrom,
                        'shipto' => $request->shipto,
                        'postalcode' => $request->postalcode,
                        'address' => $request->address,
                        'approximate_weight' => $request->approximate_weight,
                        'product_services' => $request->product_services,
                        'product_photo' => $request->product_photo,
                        'product_check' => $request->product_check,
                        'product_customs' => $request->product_customs,
                        'product_prohibited' => $request->product_prohibited,
                        'product_disinfection' => $request->product_disinfection,
                        'product_consolidation' => $request->product_consolidation,
                        'product_purchase' =>$request->purchase_assistence,
                        'total' => $request->total,
                        'order_id'=> $order_id,
                        'product_totalprice'=>$product_totalprice
                ]);
                $order->save();
                if ($priceFlags) {
                    $order->price_flags = implode(' | ', $priceFlags);
                    $order->save();
                    $flagAdmin = User::where('type', 'admin')->first();
                    if ($flagAdmin) {
                        $flagAdmin->notify(new \App\Notifications\TaskNotification([
                            'title' => 'Price discrepancy in order #' . $order_id,
                            'order_number' => $order_id,
                            'greeting' => 'Manual review needed',
                            'order_id' => $order->id,
                            'description' => implode(' | ', $priceFlags),
                        ]));
                    }
                }
                foreach ($request->addmore as $key => $value) {
                    $product = new Orderproducts([
                        'order_id' => $order->id,
                        'productname' => $value['productname'],
                        'producturl' => $value['producturl'],
                        'productquantity' => $value['productquantity'],
                        'productprice' => $value['productprice'],
                        'product_total' => $serverRowTotals[$key] ?? 0,
                        'productweight' => $value['productweight'],
                    ]);
                    $product->save();
                }
                // $user = User::find(1);
                $admin = User::where('type','admin')->first();
                // dd($user);
                    $details = [
                            'title' => 'Notification on Order #' . $order_id . ' from Shopper',
                            'order_number' => $order_id,
                            'greeting' =>  $user_name->name. ' place order',
                            'order_id' => $order->id,
                            'description' => ''
                    ];
                    $admin->notify(new \App\Notifications\TaskNotification($details));

                $client = User::where('id', $request->user_id)->first();
                $details = [

                    'title' => 'Your request has been placed with the order number # ' . $order_id,
                    'order_number' => $order_id,
                    'greeting' => 'Request Placed',
                    'order_id' => $order->id,
                    'description'=> 'Please check the dashboard for the status and you will soon get an offer which will be notified by email as well.'
                ];
                $client->notify(new \App\Notifications\TaskNotification($details));
                return redirect(route('request'))->with('success','Request detail submitted successfully'); 
            }                       
        }       
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /** JSON rows for the portal orders table (DP.infiniteScroll). */
    public function dashboardData(\Illuminate\Http\Request $request)
    {
        $user = $request->user();
        $q = Orders::where('user_id', $user->id)
            ->leftJoin('offerorders', function ($j) {
                $j->on('offerorders.order_id', '=', 'orders.id')
                  ->whereRaw('offerorders.id = (SELECT MAX(o2.id) FROM offerorders o2 WHERE o2.order_id = orders.id)');
            })
            ->select('orders.*', 'offerorders.total AS offer_total', 'offerorders.offer_status')
            ->orderByDesc('orders.id');

        if ($request->filled('q')) {
            $term = $request->q;
            $q->where(function ($w) use ($term) {
                $w->where('orders.order_id', 'like', "%{$term}%")
                  ->orWhere('orders.shipto', 'like', "%{$term}%");
            });
        }
        if ($request->get('status') === 'paid')     { $q->where('offerorders.offer_status', 1); }
        if ($request->get('status') === 'awaiting') { $q->where(function ($w) { $w->where('offerorders.offer_status', 0)->orWhereNull('offerorders.offer_status'); }); }

        $rows = $q->paginate(10);

        return response()->json([
            'current_page' => $rows->currentPage(),
            'last_page'    => $rows->lastPage(),
            'data' => collect($rows->items())->map(function ($o) {
                return [
                    'id'       => $o->id,
                    'ref'      => $o->order_id,
                    'ship_to'  => $o->shipto,
                    'weight'   => $o->approximate_weight ? $o->approximate_weight . ' g' : chr(8212),
                    'total'    => $o->offer_total !== null ? 'USD ' . number_format((float) $o->offer_total, 2) : chr(8212),
                    'created'  => optional($o->created_at)->format('d M Y'),
                    'status'   => (int) $o->offer_status === 1 ? 'Paid' : 'Awaiting payment',
                    'view_url' => url('orders/' . $o->id),
                ];
            }),
        ]);
    }
    public function show($id)
    {
        // dd($id);
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $order = Orders::select('orders.*')
                ->join('users','users.id','=','orders.user_id')
                ->where('orders.id', $id)
                ->first();
        if (!$order) {
            abort(404);
        }
        // B2 hardening: the orders resource route has no middleware group,
        // so ownership must be enforced here (staff keeps legacy access).
        $this->ownOrderOrFail($order->id);
        $products = Orderproducts::where('order_id',$id)->get();
       
        $offer = Offerorder::where("order_id", $order->id)->first();
        
        if($offer != null &&  $offer->shippingaddress_id != null){
             $shipping_address = ShippingAddresses::where('id',$offer->shippingaddress_id)->get();
        }else{
            $shipping_address = [];
        }
        
        // dd($shipping_address);
        if($offer != null ){
            $offer_services = Offerorderservices::where("offer_id", $offer->id)->get();
            $offer_products = Offerorderproducts::where("offer_id", $offer->id)->get();
        }else{
            $offer_services = [];
            $offer_products = [];
        }
        $chat = OrderChat::where('order_id', $order->id)->get();
        $chat_count=0;
        // B3 fix: count unread messages from ANY admin account, not hardcoded id 1.
        $adminIds = User::where('type', 'admin')->pluck('id');
        foreach ($chat as $msg) {
            if ($adminIds->contains($msg->from) && $msg->order_id == $order->id  && $msg->notification == null) {
                $chat_count++;
            }
        }
        // $chat_count = OrderChat::find(2)->where('notification', null)->count();
    //    dd($chat_count);

        // Self-heal a stuck Stripe checkout when the customer re-opens this
        // page (paid session + missing return/webhook → finalize now).
        $stuckPayment = \App\Models\Payment::where('order_id', $order->id)
            ->whereIn('status', [\App\Models\Payment::STATUS_AWAITING_PAYMENT, \App\Models\Payment::STATUS_PROCESSING])
            ->orderByDesc('id')
            ->first();
        if ($stuckPayment) {
            app(\App\Services\Payments\PaymentService::class)->recoverStuckCheckout($stuckPayment);
        }

        return view('clients.order_offer', compact('order','chat', 'chat_count','shipping_address','offer','offer_services','offer_products','products'));
    }

    /**
     * Ownership guard for every client-facing endpoint that acts on an order.
     * Several of these routes sit outside any auth/role middleware group, so
     * membership must be enforced here. Staff (type admin / admin role) keeps
     * access for the legacy admin screens. 404 — never confirm foreign ids.
     */
    private function ownOrderOrFail($orderId)
    {
        $user = auth()->user();
        if (!$user) {
            abort(404);
        }
        if ($user->type === 'admin' || optional($user->roles->first())->slug === 'admin') {
            return;
        }
        $owns = Orders::where('id', $orderId)->where('user_id', $user->id)->exists();
        abort_unless($owns, 404);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    public function offer_accept(Request $request, $id)
    {
        $this->ownOrderOrFail($request->order_id);

        // LEGACY SEMANTICS (restored 2026-08-28): accept does NOT write
        // offer_status. In the legacy state machine offer_status=1 means PAID
        // and is set ONLY by payment success (stripePost / PaymentService
        // settle). Accepting keeps offer_status=0 so tab 2 renders the payment
        // section; after payment the engine flips it to 1 (tracking branch).
        Orders::find($request->order_id)->update(['order_status' => 'Offer Accepted']);
        Orders::find($request->order_id)->update([ 'active_tab' => $request->active]);
        $order = Orders::where('id', $request->order_id)->first();
        $user = User::where('id', $order->user_id)->first();
        // $admin = User::find(1);
        $admin = User::where('type', 'admin')->first();
        $details = [
                'title' => 'Notification on Order #' . $order->order_id . ' from Shopper',
                'order_number' => $order->order_id,
                'greeting' =>  $user->name. ' accept offer',
                'order_id' => $order->id,
                'description' => ''
            ];
        $admin->notify(new \App\Notifications\TaskNotification($details));

        $client = User::where('id', $order->user_id)->first();
        $details = [
            'title' => 'Notification on Order #' . $order->order_id,
            'order_number' => $order->order_id,
            'greeting' =>  'Thank you for accepting our offer',
            'order_id' => $order->id,
            'description' => 'Thank you for accepting our offer , we look forward to assist you , please proceed with the payment.'
        ];
        $client->notify(new \App\Notifications\TaskNotification($details));
        return back();
    }
    public function offer_reject(Request $request, $id)
    {
        $this->ownOrderOrFail($request->order_id);
        Orders::find($request->order_id)->update(['order_status' => 'Offer Rejected']);
        $offer = \App\Models\Offerorder::find($id);
        // Explicit state writes (NOT mass assignment): offer_status = 2 is what
        // the views branch on for the "Offer Rejected" screen — previously only
        // the note was saved, so the offer kept status 1/0 and the client page
        // kept showing "Offer Accepted" / payment prompts after rejection.
        if ($offer) {
            $offer->update([
                'offer_status'    => 2,
                'rejections_note' => $request->input('rejections_note'),
            ]);
        }
        Orders::find($request->order_id)->update([ 'active_tab' => $request->active]);
        $order = Orders::where('id', $request->order_id)->first();
        $user = User::where('id', $order->user_id)->first();
        // $admin = User::find(1);
        $admin = User::where('type', 'admin')->first();
        $details = [
                'title' => 'Notification on Order #' . $order->order_id . ' from Shopper',
                'order_number' => $order->order_id,
                'greeting' =>  $user->name. ' reject offer',
                'order_id' => $order->id,
                'description' => ''
            ];

        $admin->notify(new \App\Notifications\TaskNotification($details));
        return back();
    }
    public function rejected(Request $request, $id)
    {
        $request->validate([
                'reason'=>'required',
        ]);
        $offer = Offerorder::find($request->offer_id);
        if (!$offer) {
            abort(404);
        }
        $this->ownOrderOrFail($offer->order_id);
        $offer->update([ 'rejections_note' => $request->reason]);
        return back();
    }

    /**
     * B3 FIX: uploads must land inside the web server's DOCUMENT ROOT so
     * url('uploads/...') can serve them. The legacy '../public_html/...'
     * relative path only worked on cPanel (docroot = <app>/public_html);
     * locally the docroot is the parent folder, so files were written
     * OUTSIDE the served tree (e.g. C:\laragon\www\public_html) and every
     * client-submitted receipt/photo 404'd even though the DB row saved.
     */
    private function uploadsDir(string $sub): string
    {
        $docroot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
        if ($docroot === '') {
            $docroot = public_path();   // CLI/console fallback
        }
        $dir = $docroot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $sub;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    /**
     * Collision-safe filename: two clients both uploading "Screenshot.jpg"
     * must not overwrite each other's receipts.
     */
    private function uniqueName(string $dir, string $name): string
    {
        $name = preg_replace('/[^\w.\- ]+/u', '_', (string) $name);
        if ($name === '' || !file_exists($dir . DIRECTORY_SEPARATOR . $name)) {
            return $name;
        }
        $ext  = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);
        $i = 1;
        while (file_exists($dir . DIRECTORY_SEPARATOR . $base . '_' . $i . ($ext !== '' ? '.' . $ext : ''))) {
            $i++;
        }
        return $base . '_' . $i . ($ext !== '' ? '.' . $ext : '');
    }

    public function tracking_id(Request $request, $id)
    {
        /* B3 FIX: the route already carries the order id ({id}); legacy forms
           also post order_id. The new /orders/{id} page form omitted order_id,
           so $request->order_id was null and ownOrderOrFail() 404'd. */
        $orderId = (int) ($request->input('order_id') ?: $id);
        $this->ownOrderOrFail($orderId);
        //   dd($request->all());
        $request->validate([
            'product.*.trackingid' => 'required',
            'product.*.trackinglink' => 'required',
        ]);
        foreach (($request->product ?? []) as $key => $value) {
            $product = Orderproducts::find($value['product_id'] ?? null);
            if (!$product) {
                continue;
            }
            // for product receipt image (an uploaded file replaces the 'None' placeholder)
            /* FIX 2026-09-05: receipt is optional — some clients submit without
               the key at all (mobile builds / partial payloads), which crashed
               with "Undefined array key receipt". Treat missing/null as 'None'. */
            $receipt = $value['receipt'] ?? 'None';
            if (!($receipt == 'None')) {
                $dir = $this->uploadsDir('productsimages');
                $name = $this->uniqueName($dir, $receipt->getClientOriginalName());
                $receipt->move($dir, $name);
                $product->update([
                    'receipt' => $name,
                ]);
            }
            // for tracking id's and link
            if(!(is_null($value['trackingid'])) && !(is_null($value['trackinglink']))){
                    $product->update([
                    'trackingid' => $value['trackingid'],
                    'trackinglink' => \App\Support\LinkFormat::normalize($value['trackinglink'])
                ]);
            }else{
                Orders::find($orderId)?->update([
                    'tracking_status' => 0
                ]);
            }
        }
        // dd('not in the loop');
        // for status update
        Orders::find($orderId)?->update([
            'tracking_status' => 1
        ]);
        // status update end here
        Orders::find($orderId)?->update(['order_status' => 'Order placed']);
        $order = Orders::where('id', $orderId)->first();
        $user = User::where('id', $order->user_id)->first();
        // $admin = User::find(1);
        $admin = User::where('type', 'admin')->first();
        $details = [
            'title' => 'Notification on Order #' . $order->order_id . ' from Shopper',
            'order_number' => $order->order_id,
            'greeting' =>  $user->name. " added tracking id`s",
            'order_id' => $order->id,
            'description' => ''
        ];

        if ($admin) {
            $admin->notify(new \App\Notifications\TaskNotification($details));
        }
        return back();
    }
    public function image_upload(Request $request, $id)
    {
        $orderId = (int) ($request->input('order_id') ?: $id);   // B3: route {id} fallback
        $this->ownOrderOrFail($orderId);

        foreach ($request->product as $key => $value) {
            if(!($value['image'] == 'None')){
                    $dir = $this->uploadsDir('productsimages');   // B3: docroot-safe (was ../public_html)
                    $name = $this->uniqueName($dir, $value['image']->getClientOriginalName());
                    $value['image']->move($dir, $name);
                    $product = Orderproducts::find($value['product_id']);
                    $product->update([
                        'image' => $name,
                    ]);
                $order = Orders::where('id', $orderId)->first();
                $user = User::where('id', $order->user_id)->first();
                // $admin = User::find(1);
                $details = [
                    'title' => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
                    'order_number' => $order->order_id,
                    'greeting' =>'Admin Uploaded images',
                    'order_id' => $order->id,
                    'description' => ''
                ];
                $user->notify(new \App\Notifications\TaskNotification($details));
                }else{
                    // dd('hello');
                }
            }
            foreach ($request->service as $key => $value) {
                    $product = Offerorderservices::find($value['service_id']);
                    $product->update([
                            'confirmation' => $value['servicestatus']
                        ]); 
            }
        
            Orders::find($request->order_id)->update([ 'active_tab' => $request->active]);
        // status update code
        Orders::find($request->order_id)->update([
            'order_status' => 'Confirm Shipment',
            'confirmation' => $request->confirmation
        ]);
        $order = Orders::where('id',$request->order_id)->first();
        $user = User::where('id', $order->user_id)->first();
        // $admin = User::find(1);
        $details = [
            'title' => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
            'order_number' => $order->order_id,
            'greeting' =>'Admin need confirmation',
            'order_id' => $order->id,
            'description' => 'Please confirm the shipment to be shipped if you are happy with all the item which you have ordered , and the services been provided .so we can add the tracking number to the order .'
        ];
        $user->notify(new \App\Notifications\TaskNotification($details));
        return back();
    }
    public function Order_confirmation(Request $request, $id)
     {
        $this->ownOrderOrFail($request->order_id);
        // dd($request->all());
        $request->validate([
            'product.*.weight' => 'required',
            'product.*.value' => 'required',
            'category' => 'required',
            'name'=>'required',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'postalcode' => 'required',
            'country' => 'required',
            'number' => 'required',
            
        ]);
        // dd($request->all());
        foreach ($request->product as $key => $value) {
            $product = Orderproducts::find($value['product_id']);
            // dd($value['weight']);
            $product->update([
                'custom_weight' => $value['weight'],
                'custom_value' => $value['value'],
                'custom_category' => $request->category
            ]);
        }
        // $status = Orders::find($request->order_id);  
        // dd($status);
        Orders::find($request->order_id)->update([ 
            // 'active_tab' => $request->active,
            'confirmation' => $request->confirmation,
            'custom_status' =>$request->custom_status,
            'ship_name' => $request->name,
            'ship_address1' => $request->address1,
            'ship_address2' => $request->address2,
            'ship_city' => $request->city,
            'ship_state' => $request->state,
            'ship_postalcode' => $request->postalcode,
            'ship_country' => $request->country,
            'ship_number' => $request->number,
            'custom_category' => $request->category,
            'custom_total_quantity' => $request->custom_total_quantity,
            'custom_total_weight' => $request->custom_total_weight,
            'custom_total_value' => $request->custom_total_value
            
        ]);
        $order = Orders::where('id', $request->order_id)->first();
        $user = User::where('id', $order->user_id)->first();
        // $admin = User::find(1);
        $admin = User::where('type', 'admin')->first();
        $details = [
            'title' => 'Notification on Order #' . $order->order_id . ' from Shopper',
            'order_number' => $order->order_id,
            'greeting' =>  $user->name . ' confirm to ship',
            'order_id' => $order->id,
            'description' => ''
        ];
        $admin->notify(new \App\Notifications\TaskNotification($details));
        return back();   
    }
     public function order_tracking(Request $request, $id)
    {
        // dd($request->all());
        // $status = Orders::find($request->order_id);  
        // Orders::find($request->order_id)->update(['order_status' => 'Shipped']);
        Orders::find($id)->update([ 
            'active_tab' => $request->active,
            'trackingid' => $request->trackingid,
            'trackinglink' => \App\Support\LinkFormat::normalize($request->trackinglink),
            'companyname' => $request->companyname,
            'order_status' => 'Order processing'
            ]);
        $order = Orders::where('id', $request->order_id)->first();
        $user = User::where('id', $order->user_id)->first();
        // $admin = User::find(1);
        $details = [
            'title' => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
            'order_number' => $order->order_id,
            'greeting' =>  "Item`s shipped",
            'order_id' => $order->id,
            'description' => ''
        ];
        $user->notify(new \App\Notifications\TaskNotification($details));
        return back();    
    }
    public function Order_complete(Request $request, $id)
    {
        $this->ownOrderOrFail($id);
        Orders::find($id)->update([
            'order_status' => $request->order_status
        ]);
        $order = Orders::where('id',
            $request->order_id
        )->first();
        $user = User::where('id', $order->user_id)->first();
        $admin = User::where('type', 'admin')->first();
        // $admin = User::find(1);
        $details = [
            'title' => 'Notification on Order #' . $order->order_id . ' from Shopper',
            'order_number' => $order->order_id,
            'greeting' =>  $user->name . ' marked order completed',
            'order_id' => $order->id,
            'description' => ''
        ];
        $admin->notify(new \App\Notifications\TaskNotification($details));
        
        $client = User::where('id', $order->user_id)->first();
        // $admin = User::find(1);
        $details = [
            'title' => 'Notification on Order #' . $order->order_id,
            'order_number' => $order->order_id,
            'greeting' =>  'Your order marked as completed',
            'order_id' => $order->id,
            'description' => 'Thank you for your custom and giving us chance to assist you . please leave a kind remarks for us and hope to help you again in future .'
        ];
        $client->notify(new \App\Notifications\TaskNotification($details));
        return back();
    }

    /**
     * Client confirms the package arrived AFTER the order was marked
     * completed (by admin or themselves). Sets order_status = 'received'
     * so admin sees the delivery is confirmed.
     */
    public function order_received(Request $request, $id)
    {
        $this->ownOrderOrFail($id);

        if ($request->order_status !== 'received') {
            return back();
        }

        $order = Orders::find($id);
        // Only completed orders can be confirmed received — never regress others.
        if ($order->order_status !== 'completed') {
            return back();
        }

        $order->update(['order_status' => 'received']);

        $admin = User::where('type', 'admin')->first();
        if ($admin) {
            $client = User::find($order->user_id);
            $admin->notify(new \App\Notifications\TaskNotification([
                'title'       => 'Notification on Order #' . $order->order_id . ' from Shopper',
                'order_number' => $order->order_id,
                'greeting'    => ($client ? $client->name : 'Customer') . ' confirmed the package was received',
                'order_id'    => $order->id,
                'description' => '',
            ]));
        }

        return back()->with('success', 'Thank you! Your package has been marked as received.');
    }

    public function chat(Request $request)
    {
        // $this->validate($request, [
        //     'message' => 'required',
        // ]);
        if (!auth()->check()) {
            abort(404);
        }
        $this->ownOrderOrFail($request->order_id);
        $order = Orders::where('id',$request->order_id)->first();
        $client = User::where('id', $order->user_id)->first();
        // Duplicate-burst guard: if this exact text was already sent by the same
        // user on this order moments ago (client double-submit / stuck button),
        // skip the insert + notification and just return the conversation.
        if ($request->filled('message')
            && \App\Http\Controllers\Admin\InboxController::isRecentDuplicate($request->order_id, auth()->id(), $request->message)) {
            $chat = OrderChat::where('order_id', $request->order_id)->get();
            return view('messages')->with('chat', $chat);
        }
        if($request->hasfile('image') && $request->message) {
            $imageName =  $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path() . '/uploads/chatimages', $imageName);
            $chat = new OrderChat([
                'from' => auth()->id(),
                'order_id' => $request->order_id,
                'body' => $request->message,
                'image'=> $imageName 
            ]);
            $chat->save();
            $user = User::find(auth()->user()->id);
            if ($user->roles[0]->slug == 'admin') {
                $client_id = Orders::find($request->order_id)->user_id;

                $user = User::find($client_id);
                $data = [
                    'title' => 'You have received new message from Admin on Order #' . $order->order_id,
                    // 'title' => 'Order #' . $order->order_id . ' Notification from Deliveing Parcel',
                    'greeting' => 'Admin',
                    'body' => $request->message,
                    'order_number' => $order->order_id,
                    'id' => $request->order_id,
                    'description' => ''
                ];
                $user->notify(new \App\Notifications\Chatnotification($data));
            } else {
                // $user = User::find(1);
                $user = User::where('type', 'admin')->first();
                $data = [
                    // 'title' => 'You have received new message from'. $client->name,
                    'title' => 'You have received new message from'. $client->name. 'on Order #' . $order->order_id,
                    'greeting' => $client->name,
                    'body' => $request->message,
                    'order_number' => $order->order_id,
                    'id' => $request->order_id,
                    'description' => ''
                ];
                $user->notify(new \App\Notifications\Chatnotification($data));
            }
        }
        elseif($request->hasfile('image')){
            $chatDir = $this->uploadsDir('chatimages');   // B3: docroot-safe (was ../public_html)
            $imageName = $this->uniqueName($chatDir, $request->file('image')->getClientOriginalName());
            $request->file('image')->move($chatDir, $imageName);
            $chat = new OrderChat([
                'from' => auth()->id(),
                'order_id' => $request->order_id,
                'image' => $imageName
            ]);
            $chat->save();
            $user = User::find(auth()->user()->id);
            if ($user->roles[0]->slug == 'admin') {
                $client_id = Orders::find($request->order_id)->user_id;

                $user = User::find($client_id);
                $data = [
                    'title' => 'You have received new message from Admin on Order #' . $order->order_id,
                    'greeting' => 'Admin',
                    'body' => $request->message,
                    'order_number' => $order->order_id,
                    'id' => $request->order_id,
                    'description' => ''
                ];
                $user->notify(new \App\Notifications\Chatnotification($data));
            } else {
                // $user = User::find(1);
                $user = User::where('type', 'admin')->first();
                $data = [
                    'title' => 'You have received new message from' . $client->name . 'on Order #' . $order->order_id,
                    'greeting' => $client->name,
                    'body' => $request->message,
                    'order_number' => $order->order_id,
                    'id' => $request->order_id,
                    'description' => ''
                ];
                $user->notify(new \App\Notifications\Chatnotification($data));
            }
        }
        elseif($request->message){
            $chat = new OrderChat([
                'from' => auth()->id(),
                'order_id' => $request->order_id,
                'body' => $request->message,
            ]);
            $chat->save();
            $user = User::find(auth()->user()->id);
            if ($user->roles[0]->slug == 'admin') {
                $client_id = Orders::find($request->order_id)->user_id;
                $user = User::find($client_id);
                $data = [
                    'title' => 'You have received new message from Admin ' . 'on Order #' . $order->order_id,
                    'greeting' => 'Admin',
                    'body' => $request->message,
                    'order_number' => $order->order_id,
                    'id' => $request->order_id,
                    'description' => ''
                ];
                $user->notify(new \App\Notifications\Chatnotification($data));
            } else {
                // $user = User::find(1);
                $user = User::where('type', 'admin')->first();
                $data = [
                    'title' => 'You have received new message from' . $client->name . 'on Order #' . $order->order_id,
                    'greeting' => $client->name,
                    'body' => $request->message,
                    'order_number' => $order->order_id,
                    'id' => $request->order_id,
                    'description' => ''
                ];
                $user->notify(new \App\Notifications\Chatnotification($data));
            }
        }else{} 
        $chat = OrderChat::where('order_id',$request->order_id)->get();
       return view('messages')->with('chat',$chat);
    }
   
    public function read_message(Request $request)
    {
        if (!auth()->check()) {
            abort(404);
        }
        $this->ownOrderOrFail($request->id);
        $uid = auth()->id();
        $message = OrderChat::where('order_id', $request->id)->get();
        foreach ($message as $msg) {
            if ($uid != $msg->from && $msg->read == null) {
                $msg->read = 1;
                $msg->save();
            }

            if ($uid != $msg->from && $msg->notification == null) {
                $msg->notification = 1;
                $msg->save();
            }
        }
        // $affected = DB::table('order_chats')
        //                 ->where('order_id', $request->id)
        //                 ->update(['read' => 1]);
        return back();
    }
    public function chat_messages(Request $request)
    {
        if (!auth()->check()) {
            abort(404);
        }
        $this->ownOrderOrFail($request->id);
        $uid = auth()->id();
        // Mark incoming messages read, then ALWAYS render the full
        // conversation. The old version returned the bare string "null"
        // when nothing was new — that leaked into the chat widget as
        // visible "null" text and made polling fragile.
        $message = OrderChat::where('order_id', $request->id)->get();
        foreach ($message as $msg) {
            if ($uid != $msg->from && $msg->read == null) {
                $msg->read = 1;
                $msg->save();
            }
        }
        return view('messages')->with('chat', $message);
    }
    public function chat_count(Request $request)
    {
        if (!auth()->check()) {
            abort(404);
        }
        $this->ownOrderOrFail($request->id);
        $uid = auth()->id();
        $message = OrderChat::where('order_id', $request->id)->get();
        $chat_count = 0;
        foreach ($message as $msg) {
            if ($uid != $msg->from && $msg->notification == null) {
                $chat_count++;
            }
        }
        return $chat_count;
    }
    public function check_notification(Request $request)
    {
        if (!auth()->check()) {
            abort(404);
        }
        // Single COUNT query for the session user — never load rows just to count them.
        return DB::table('notifications')
            ->where('type', 'App\Notifications\TaskNotification')
            ->where('notifiable_id', auth()->id())
            ->where('notifiable_type', 'App\Models\User')
            ->whereNull('read_at')
            ->count();
    }
    public function stripePost(Request $request)
    {
        $this->ownOrderOrFail($request->order_id);
        // Security: the LIVE secret key must never be hardcoded in source.
        // Read from config so the environment controls it (mock/absent locally).
        Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        Stripe\Charge::create([
            "amount" => $request->total * 100,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => "payment"
        ]);
        $offer = Offerorder::find($request->offer_id);
        // Mass-assignment fix: a successful charge may only mark the offer
        // accepted — never echo arbitrary request fields into the offer row.
        $offer->update(['offer_status' => 1]);
        session()->flash('success', 'Your payment has been successfully processed.');
        // Session::flash('success', 'Payment successful!');
        $order = Orders::where('id', $request->order_id)->first();
        $user = User::where('id',$order->user_id)->first();
        // $admin = User::find(1);
        $admin = User::where('type', 'admin')->first();

        // Notify admin that payment was received (was silently dropped before).
        $admin->notify(new \App\Notifications\TaskNotification([
            'title'       => 'Notification on Order #' . $order->order_id . ' from Shopper',
            'greeting'    => $user->name . " pay's total",
            'order_number' => $order->order_id,
            'order_id'    => $order->id,
            'description' => '',
        ]));

        $client = User::where('id',$order->user_id)->first();
        $client->notify(new \App\Notifications\TaskNotification([
            'title'       => 'Notification on Order #' . $order->order_id,
            'greeting'    => 'Your order has been placed',
            'order_number' => $order->order_id,
            'order_id'    => $order->id,
            'description' => 'Your order has been placed, please wait till the item arrives at our address and we will notify you.',
        ]));
        return back();
    }
    
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    
}
