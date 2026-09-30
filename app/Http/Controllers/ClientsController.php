<?php

namespace App\Http\Controllers;

use App\Mail\contactus as MailContactus;
use App\Models\Contactus;
use App\Models\Orders;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        $orders = User::query()
            ->select(['users.id', 'users.name', 'users.email', 'users.number', 'users.created_at'])
            ->selectSub(function ($q) {
                $q->selectRaw('count(*)')
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id');
            }, 'orders_items')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('slug', 'admin');
            })
            ->when($request->filled('user_id'), function ($q) use ($request) {
                $q->where('users.id', $request->input('user_id'));
            })
            ->when($request->filled('name'), function ($q) use ($request) {
                $q->where('users.name', 'like', '%' . $request->input('name') . '%');
            })
            ->when($request->filled('email'), function ($q) use ($request) {
                $q->where('users.email', 'like', '%' . $request->input('email') . '%');
            })
            ->orderByDesc('users.created_at')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.clients', compact('orders'));
    }
    public function show($id)
    {
        // dd($id);
        $user = User::find($id);
        $orders = Orders::where('user_id',$id)->orderBy(DB::raw("orders.created_at"), 'desc')->get();
        // dd($orders);
        return view('admin.client_detail', compact('orders','user'));
    }
    public function contactus(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'name'     => 'required',
            'email'     => 'required',
            'number' => 'required',
            'address' => 'required',
            'message' => 'required',
        ]);

        $contact = new Contactus([
            'name'     => $request->name,
            'email'     => $request->email,
            'number' => $request->number,
            'address' => $request->address,
            'detail' => $request->message,
        ]);

        $contact->save();
        $details = [
            'title' => 'contacted us on Deliveringparcel',
            'name'     => $request->name,
            'email'     => $request->email,
            'number' => $request->number,
            'address' => $request->address,
            'message' => $request->message,
        ];
        // Submission is already saved — mail failure must not lose it.
        // Unified pipeline: admin template (contact_submitted_admin) if
        // configured, else the legacy mailable; always logged.
        app(\App\Services\EmailService::class)->send(
            'contact_submitted_admin',
            'service@deliveringparcel.com',
            $details,
            null,
            function () use ($details) {
                Mail::to('service@deliveringparcel.com')->send(new MailContactus($details));
            }
        );
        return back()->with('success', 'Your response is send successfully');
    }
    
}
