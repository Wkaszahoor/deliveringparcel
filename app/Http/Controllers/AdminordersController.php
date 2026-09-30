<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Orders;
use App\Models\OrderChat;
use App\Models\Orderproducts;
use App\Models\Offerorderproducts;
use App\Models\Offerorder;
use App\Models\Offerorderservices;
use App\Models\ShippingAddresses;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Hash;

class AdminordersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $order = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    // Latest payment state per order (Payment column on the list)
                    ->select(
                        'orders.*',
                        'users.name',
                        'users.email',
                        DB::raw('(SELECT p.status FROM payments p WHERE p.order_id = orders.id ORDER BY p.id DESC LIMIT 1) as payment_status')
                    )
                    ->when($request->filled('order_id'), function ($query) use ($request) {
                        $term = trim($request->input('order_id'));
                        // Search the public order_id (e.g. DP-XXXX) or the numeric PK.
                        $query->where(function ($q) use ($term) {
                            $q->where('orders.order_id', 'like', '%'.$term.'%');
                            if (ctype_digit($term)) {
                                $q->orWhere('orders.id', (int) $term);
                            }
                        });
                    })
                    ->when($request->filled('name'), function ($query) use ($request) {
                        $query->where('users.name', 'like', '%'.trim($request->input('name')).'%');
                    })
                    ->when($request->filled('email'), function ($query) use ($request) {
                        $query->where('users.email', 'like', '%'.trim($request->input('email')).'%');
                    })
                    ->when($request->filled('shipfrom'), function ($query) use ($request) {
                        $query->where('orders.shipfrom', 'like', '%'.trim($request->input('shipfrom')).'%');
                    })
                    ->when($request->filled('shipto'), function ($query) use ($request) {
                        $query->where('orders.shipto', 'like', '%'.trim($request->input('shipto')).'%');
                    })
                    ->when($request->input('status') === '__none__', function ($query) {
                        // "Request Placed" in the UI = no status stored yet.
                        $query->where(function ($q) {
                            $q->whereNull('orders.order_status')->orWhere('orders.order_status', '');
                        });
                    }, function ($query) use ($request) {
                        $query->when($request->filled('status'), function ($q2) use ($request) {
                            $q2->where('orders.order_status', trim($request->input('status')));
                        });
                    })
                    // Archived (soft-deleted) orders are hidden unless the
                    // admin explicitly opens the "show archived" view.
                    ->when($request->input('archived') === '1', function ($query) {
                        $query->whereNotNull('orders.archived_at');
                    }, function ($query) {
                        $query->whereNull('orders.archived_at');
                    })
                    ->orderBy(DB::raw("orders.created_at"), 'desc')
                    ->paginate(10)
                    ->appends($request->query());

        return view('admin.orders',compact('order'));
    }
    
    
      public function indexx()
    {      
        $order = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->get();
        // $address = ShippingAddresses::all();
        // dd($address);
       
        return view('admin.orderss',compact('order'));
    }
    
    
    public function Avatar_index()
    {
        $users = User::find(auth()->user()->id);
        return view("admin.profile", [
            'user' => $users
        ]);
    }
    public function Avatar_update(Request $request)
    {
        // dd($request);
        $user = User::find(auth()->user()->id);
        $filename = 'user.png';
        if ($request->hasfile('avatar')) {
            $filename = $request->file('avatar')->getClientOriginalName();
            $photo = $request->file('avatar');
            $destinationPath = '../public_html/uploads/profile';
            // intervention/image v3 (Laravel 12 upgrade): ImageManager->read() replaces make();
            // with no image driver installed, store the original file untouched.
            if (class_exists('\Intervention\Image\Drivers\Gd\Driver')) {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $manager->read($photo)->fit(200, 200)->save($destinationPath . '/' . $filename);
            } elseif (class_exists('\Intervention\Image\Drivers\Imagick\Driver')) {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Imagick\Driver());
                $manager->read($photo)->fit(200, 200)->save($destinationPath . '/' . $filename);
            } else {
                $photo->move($destinationPath, $filename);
            }
        }
        // dd($filename);public_path('/uploads/profile')
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
        ]);
        $user = User::find(auth()->user()->id);
        $user->update(array_merge($request->all(), ['avatar' => $filename]));
        return redirect('avatar')->with('success', 'Profile updated successfuly');
    }
    public function password()
    {
        $user = User::find(auth()->user()->id);
        return view("admin.profile", [
            'user' => $user
        ]);
    }
    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'old_password'     => 'required',
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);
        $data = $request->all();
        $user = User::find(auth()->user()->id);
      
        if (!Hash::check($data['old_password'], $user->password)) {
            return back()->with('error', 'You have Entered Wrong Password');
        } else {
            $user->password = Hash::make($request->new_password);
            if ($user->save()) {
                return Redirect("Admin_password")->with('success', 'Password is Updated Successfully!');
            }
        }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // GET /adminorders/{id} previously 500'd (resource route expects show()).
        // Delegates to the same admin order + chat page as showaddress().
        return $this->showaddress($id);
    }

    public function showaddress($id)
    {
        
        // leftJoin + 404 guard (parity port): an order whose user row is gone
        // (or a bad id) previously 500'd with "property id on null".
        $order = Orders::select('*','orders.id AS id')
                    ->leftJoin('users','users.id','=','orders.user_id')
                    ->where('orders.id','=',$id)
                    ->first();
        if ($order === null) {
            abort(404, 'Order not found.');
        }
        // dd($order);
        $products = Orderproducts::where('order_id', $id)->get();
        // dd($products);
        $offer = Offerorder::where("order_id", $order->id)->first();
        // dd($offer);
        if($offer != null ){
            $offer_services = Offerorderservices::where("offer_id", $offer->id)->get();
            $offer_products = Offerorderproducts::where("offer_id", $offer->id)->get();
        }else{
            $offer_services = [];
            $offer_products = [];
        }
        // dd($offer_products);
        $shipingaddress = [];
        $chat = OrderChat::where('order_id', $order->id)->get();
        $chat_count = 0;
        foreach ($chat as $msg) {
            if ($msg->from == $order->user_id && $msg->order_id == $order->id  && $msg->notification == null) {
                $chat_count++;
            }
        }
        // PM-016: latest engine payment for this order (verify / mark-received panel).
        $payment = \App\Models\Payment::where('order_id', $order->id)->orderByDesc('id')->first();
        return view('admin.Order_offer', compact('order', 'shipingaddress', 'chat_count','products','offer','offer_services','offer_products','chat', 'payment'));
    }
    public function countryaddress(Request $request)
    {
        // dd($request);
       $shipingaddress = ShippingAddresses::where('country',$request->country)->get();
    //    dd($shipingaddress);
       return view('admin.address')->with('shipingaddress', $shipingaddress);
    }
    public function edit_offer(Request $request)
    {
        // dd($request->all());
        $order = Orders::find($request->order_id);
        $order->update([
            'active_tab' => 1,
            'order_status' => 'In process',
            'edit_offer' => 1
        ]);
        return back();
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $orders = Orders::find($request->order_id)->update([
            'order_status' => 'Offer Updated',
            'edit_offer' => null,
            'total' => $request->total
            ]);
       $previous_offer = Offerorder::where('order_id', $request->order_id)->update([
            'offer_status' => 0,
            'rejections_note' => null,
        ]);
        $offer = Offerorder::where('order_id',$request->order_id)->first();

        $offer_product = Offerorderproducts::where('offer_id', $offer->id)->get();
        // dd($offer->id);
        $offer_services = Offerorderservices::where('offer_id', $offer->id)->delete();
        foreach ($request->addmore as $key => $value) {
            if ($value['servicestatus'] == '1') {
                $services = new Offerorderservices([
                    'offer_id' => $offer->id,
                    'servicename' => $value['servicename'],
                    'servicevalue' => $value['servicevalue'],
                ]);
                $services->save();
            } else {}
        }
        foreach ($request->more as $key => $value) {
            if (!(is_null($value['servicename']))) {
                $services = new Offerorderservices([
                    'offer_id' => $offer->id,
                    'servicename' => $value['servicename'],
                    'servicevalue' => $value['servicevalue'],
                ]);
                $services->save();
            } else {}
        }
        if (!(is_null($request->net_ttotal))) {
            $offer = Offerorder::where('order_id', $request->order_id)->update([
                'shipingaddress' => $request->shipingaddress,
                'total' => $request->total,
                'product_total' => $request->net_ttotal,
                'description' => $request->shipping_detail,
            ]);
        } else {
            $offer = Offerorder::where('order_id', $request->order_id)->update([
                'shipingaddress' => $request->shipingaddress,
                'total' => $request->total,
                'description' => $request->shipping_detail,
            ]);
        }
        if (!(is_null($request->product))) {
            foreach ($request->product as $key => $value) {
                    Offerorderproducts::find($value['product_id'])->update([
                        'productname' => $value['productname'],
                        'producturl' => $value['producturl'],
                        'productquantity' => $value['productquantity'],
                        'productprice' => $value['productprice'],
                        'productspread' => $value['spread'],
                        'product_total' => $value['total'],
                ]);
            };
        } else {}

        $order = Orders::where('id', $request->order_id)->first();
        $user = User::where('id', $order->user_id)->first();
        $details = [
            'title' => 'Order Notification from Delivering Parcel',
            'greeting' => 'Admin Updated the offer',
            'order_id' => $order->id,
            'description' => ''
        ];
        $user->notify(new \App\Notifications\TaskNotification($details));
        return back();
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
