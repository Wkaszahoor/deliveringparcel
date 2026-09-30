<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Notification;
use App\Models\User;
use App\Models\needaddress_chat;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Needaddress;
use App\Models\Needaddressproducts;
use Illuminate\Support\Facades\Mail;
use App\Mail\Loginmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Needaddressservices;

class chatneedaddressController extends Controller
{
     public function store_new(Request $request)
    {
        dd($request->all());
         foreach ($request->addmore as $key => $value) {
            // dd($value);
            if($value['servicestatus'] == '1'){
                $services = new Needaddressservices([
                    'servicename' => $value['servicename'],
                    'servicevalue' => $value['servicevalue'],
                ]);
                $services->save();
            }else{
                 return back();
            } 
        }
        foreach ($request->more as $key => $value) {
                $services = new Needaddressservices([
                    'servicename' => $value['servicename'],
                    'servicevalue' => $value['servicevalue'],
                ]);
            $services->save();
        }          
        return back();
    }
    public function chat(Request $request){
        // dd($request);
       $user = User::find(auth()->user()->id);
        $chat = new needaddress_chat([
                'from' => $user->id,
                'order_id'=>$request->order_id,
                'body' => $request->message
            ]);
        $chat->save();
        if($user->roles[0]->slug == 'admin'){
            $client_id=Needaddress::find($request->order_id)->user_id;
            // dd($client_id);
            $user = User::find($client_id);
            $data = [
                    'greeting' => 'client_Chat',
                    'body' => 'This is for caht',
            ];
            $user->notify(new \App\Notifications\Chatnotification($data));
        }else
        {
            $user = User::find(8);
                $data = [
                        'greeting' => 'admin_Chat',
                        'body' => 'This is for caht',
                ];
                $user->notify(new \App\Notifications\Chatnotification($data));
        }
        return back();
    }
}
