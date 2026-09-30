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

class AdminorderssController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     
     orderss controller checking testing 
order status 
completed
in process
Confirm Shipment
Offer Accepted
Offer Placed
Offer Rejected
Offer Updated
Order Placed
Order Processing
Ready to Ship
Request Placed

     */
  
    public function index()
    {      
 


 $inprocess = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'in process')
                    ->get();
      
        return view('admin.orderss',compact('inprocess'));
   
    $confirmshipment = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'confirm shipment')
                    ->get();
      
              return view('admin.orderss',compact('confirmshipment'));
         
               $completed = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'completed')
                    ->get();
      
        return view('admin.orderss',compact('completed'));
        
        
           $orderplaced = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'NULL')
                    ->get();
      
        return view('admin.orderss',compact('orderplaced'));
        
    $offerplaced = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'offer placed')
                    ->get();
      
        return view('admin.orderss',compact('offerplaced'));
        
           $offeraccepted = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'offer accepted')
                    ->get();
      
        return view('admin.orderss',compact('offeraccepted'));
        
           $offerrejected = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'Offer Rejected')
                    ->get();
      
        return view('admin.orderss',compact('offerrejected'));
        
           $offerupdated = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'offer updated')
                    ->get();
      
        return view('admin.orderss',compact('offerupdated'));
        
           $orderplaced = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->whereNull('orders.order_status')
                    ->get();
      
        return view('admin.orderss',compact('orderplaced'));
        
           $orderprocessing = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'Order Processing')
                    ->get();
      
        return view('admin.orderss',compact('orderprocessing'));
        
           $Readytoship = DB::table('users')
                    ->join('orders', 'orders.user_id','=','users.id')
                    ->orderBy(DB::raw("orders.created_at"), 'desc') 
                    ->where('orders.order_status', '=', 'Ready to Ship')
                    ->get();
      
        return view('admin.orderss',compact('readytoship'));
        
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
    public function showaddress($id)
    {
        
        $order = Orders::select('*','orders.id AS id')
                    ->join('users','users.id','=','orders.user_id')
                    ->where('orders.id','=',$id)
                    ->first();
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
        return view('admin.Order_offer', compact('order', 'shipingaddress', 'chat_count','products','offer','offer_services','offer_products','chat'));
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
            'total' => $request->total,
            // Re-offer resets client acceptance: put their page back on
            // step 1 (Accept an Offer) — after a rejection active_tab was
            // stuck on 2 (Offer Response) and the new offer was invisible.
            'active_tab' => 1,
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
