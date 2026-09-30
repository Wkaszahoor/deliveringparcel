<?php

namespace App\Http\Controllers;

use App\Models\Contactus;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\contactus as MailContactus;

class ContactedusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Contactus::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where('detail', 'like', "%{$keyword}%");
        }

        $contact = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.contact_us.index', [
            'contact' => $contact,
            'filters' => $request->only(['name', 'email', 'q']),
        ]);
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
        // Spam gate — same Turnstile captcha as the request form.
        if (\App\Support\Turnstile::verify($request) === false) {
            return back()->with('error', 'Captcha verification failed. Please try again.');
        }

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

        // Agent D — auto-classify + default status (category column has a DB
        // default, so classified_at is the "not classified yet" marker).
        if (empty($contact->classified_at)) {
            try {
                $contact->category = app(\App\Services\ContactClassifier::class)->classify((string) $request->message);
            } catch (\Throwable $e) {
                $contact->category = $contact->category ?: config('admin_contacts.default_category', 'other');
            }
            $contact->classified_at = now();
            $contact->status = $contact->status ?: config('admin_contacts.default_status', 'new');
            $contact->save();
        }

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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Contactus = Contactus::where('id', $id)->get();
        // dd($freequote);
        return view('admin.contact_us.show', compact('Contactus'));
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
        $address = Contactus::find($id);
        $address->delete();
        return redirect(route('contactus.index'))->with('success', 'Contacted us details Deleted successfuly');
    }
}
