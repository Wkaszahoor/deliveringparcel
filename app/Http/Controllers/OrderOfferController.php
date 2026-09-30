<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\User;
use App\Models\Offerorder;
use App\Models\Offerorderproducts;
use App\Models\Offerorderservices;

class OrderOfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        // dd($request->all());
        $this->validate($request, [
            'shipping_detail' => 'required',
        ]);
        Orders::find($request->order_id)->update([
            'order_status'=> 'Offer Placed',
            // Client lands on step 1 (Accept an Offer) for the fresh offer
            'active_tab'  => 1,
        ]);
        if( ! (is_null($request->net_ttotal))){
                $offer = new Offerorder([
                            'shippingaddress_id' => $request->address_id,
                            'shipingaddress' => $request->shipingaddress,
                            'total' => $request->total,
                            'product_total' => $request->net_ttotal,
                            'order_id' => $request->order_id,
                            'description' => $request->shipping_detail,
                        ]);
                $offer->save();
        }else{
            $offer = new Offerorder([
                    'shippingaddress_id' => $request->address_id,
                    'shipingaddress' => $request->shipingaddress,
                    'total' => $request->total,
                    'order_id' => $request->order_id,
                    'description' => $request->shipping_detail,
                ]);
        $offer->save();
        }
        if( ! (is_null($request->product))){
            foreach ($request->product as $key => $value) {
                $product = new Offerorderproducts([
                    'offer_id' => $offer->id,
                    'productname' => $value['productname'],
                    'producturl' => $value['producturl'],
                    'productquantity' => $value['productquantity'],
                    'productprice' => $value['productprice'],
                    'productspread' => $value['spread'],
                    'product_total' => $value['total'],
                ]);
                $product->save();
            };
        }else{}
        foreach ($request->addmore as $key => $value) {
            if($value['servicestatus'] == '1'){
                $services = new Offerorderservices([
                    'offer_id' => $offer->id,
                    'servicename' => $value['servicename'],
                    'servicevalue' => $value['servicevalue'],
                ]);
                $services->save();
            }else{}
        }
        foreach ($request->more as $key => $value) {
            if( ! (is_null($value['servicename'] ))){
                $services = new Offerorderservices([
                    'offer_id' => $offer->id,
                    'servicename' => $value['servicename'],
                    'servicevalue' => $value['servicevalue'],
                ]);
                $services->save();
            }else{}
        }
        $order = Orders::where('id',$request->order_id)->first();
        $user = User::where('id',$order->user_id)->first();
        $details = [
            'title' => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
            'order_number' => $order->order_id,
            'greeting' =>  'Admin place offer !',
            'order_id' => $order->id,
            'description' => ''
        ];

        $user->notify(new \App\Notifications\TaskNotification($details));
        return back();
    }
    public function tracking_link(Request $request, $id)
    {
        $status = Orders::find($request->order_id);    
            $status->update([
                'tracking_status' => 1
            ]);
        foreach ($request->product as $key => $value) {
            if(!(is_null($value['trackingid']))){
                    $product = Offerorderproducts::find($value['product_id']);
                    $product->update([
                        'trackinglink' => \App\Support\LinkFormat::normalize($value['trackinglink']),
                        'trackingid' => $value['trackingid']
                    ]);
                    $order = Orders::where('id', $request->order_id)->first();
                    $user = User::where('id', $order->user_id)->first();
                    $details = [
                        'title' => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
                        'order_number' => $order->order_id,
                        'greeting' =>  'Admin added tracking links',
                        'order_id' => $order->id,
                        'description' => ''
                    ];
                    $user->notify(new \App\Notifications\TaskNotification($details));
            }else{
                $product = Orders::find($request->order_id);
                $product->update([
                    'tracking_status' => 0
                ]);
            }
        }
        return back();
        
    }
    /**
     * B3 FIX: docroot-safe upload target (see OrdersController::uploadsDir).
     * The legacy '../public_html/...' path saved outside the served tree.
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

    public function purchase_image(Request $request, $id)
    {
        // dd($request->all());
        Orders::find($request->order_id)->update([
            'order_status' => 'Ready To Ship',
            'confirmation' => $request->confirmation,
        ]);
        foreach ($request->product as $key => $value) {
            if(!($value['image'] == 'None')){
                    $dir = $this->uploadsDir('productsimages');   // B3: docroot-safe (was ../public_html)
                    $name = $this->uniqueName($dir, $value['image']->getClientOriginalName());
                    $value['image']->move($dir, $name);
                    $product = Offerorderproducts::find($value['product_id']);
                    $product->update([
                        'image' => $name,
                    ]);
                $order = Orders::where('id', $request->order_id)->first();
                $user = User::where('id', $order->user_id)->first();
                $details = [
                    'title' => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
                    'order_number' => $order->order_id,
                    'greeting' => 'Admin Uploaded images',
                    'order_id' => $order->id,
                    'description' => ''
                ];

                $user->notify(new \App\Notifications\TaskNotification($details));
                }else{
                    // dd('hello');
                }
                
        }
        foreach ($request->service as $key => $value) {
            // dd($value['servicestatus']);
            $product = Offerorderservices::find($value['service_id']);
            $product->update([
                'confirmation' => $value['servicestatus']
            ]);   
        }
        Orders::find($request->order_id)->update([ 'active_tab' => $request->active]);
        $order = Orders::where('id',$request->order_id)->first();
        $user = User::where('id', $order->user_id)->first();
        $details = [
            'title' => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
            'order_number' => $order->order_id,
            'greeting' =>  'Shipping confirmation needed',
            'order_id' => $order->id,
            'description' => ''
        ];
        $user->notify(new \App\Notifications\TaskNotification($details));
        return back();
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
