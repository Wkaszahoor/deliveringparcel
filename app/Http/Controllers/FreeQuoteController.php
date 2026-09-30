<?php

namespace App\Http\Controllers;

use App\Mail\Free_quoteEmail;
use App\Models\Orders;
use App\Models\RequestQuote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FreeQuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Server-side filtered + paginated quote list (inbox pattern).
        // request_quotes has NO status column — filterable fields are the
        // contact/cargo text columns plus created_at ordering (newest first).
        $query = RequestQuote::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }
        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            // Date-free text search across the quote's text columns.
            $query->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('number', 'like', $term)
                    ->orWhere('cargotype', 'like', $term)
                    ->orWhere('country', 'like', $term)
                    ->orWhere('destination', 'like', $term)
                    ->orWhere('detail', 'like', $term);
            });
        }

        $freequote = $query->paginate(15)->appends($request->query());

        return view('admin.freequote.index', [
            'freequote' => $freequote,
            'filters'   => $request->only(['name', 'email', 'country', 'search']),
        ]);
    }

    /**
     * GET /freequote-inbox/orders-data — server-side DataTables JSON for the
     * "Clients Orders" table. Hand-rolled (yajra/laravel-datatables is not
     * installed) against DataTables' standard request/response contract:
     * https://datatables.net/manual/server-side
     *
     * Columns (index => db column), must match the <thead>/`columns` order
     * in admin/freequote/index.blade.php:
     *   0 order_id, 1 name, 2 email, 3 shipfrom, 4 shipto, 5 total,
     *   6 created_at, 7 order_status (not sortable — derived badge), 8 actions (not sortable/searchable)
     */
    public function ordersData(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $length = $length > 0 ? min($length, 100) : 10;
        $searchValue = trim((string) $request->input('search.value', ''));

        // orders has NO name/email columns of its own (confirmed via
        // Schema::getColumnListing — the pre-migration @foreach referenced
        // $add->name/$add->email, which Eloquent silently resolved to null
        // for every row rather than erroring). Name/email live on `users`,
        // joined the same way Admin\OrdersMgmt\OrdersController::data() does.
        $sortableColumns = [
            0 => 'orders.order_id',
            1 => 'users.name',
            2 => 'users.email',
            3 => 'orders.shipfrom',
            4 => 'orders.shipto',
            5 => 'orders.total',
            6 => 'orders.created_at',
        ];

        $orderColumnIndex = (int) $request->input('order.0.column', 6);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumn = $sortableColumns[$orderColumnIndex] ?? 'orders.created_at';

        $base = \Illuminate\Support\Facades\DB::table('orders')
            ->leftJoin('users', 'users.id', '=', 'orders.user_id');

        $recordsTotal = (clone $base)->count('orders.id');

        $query = (clone $base);
        if ($searchValue !== '') {
            $query->where(function ($w) use ($searchValue) {
                $w->where('users.name', 'like', "%{$searchValue}%")
                    ->orWhere('users.email', 'like', "%{$searchValue}%")
                    ->orWhere('orders.order_status', 'like', "%{$searchValue}%");
            });
        }
        $recordsFiltered = (clone $query)->count('orders.id');

        $rows = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get([
                'orders.id', 'orders.order_id', 'users.name', 'users.email',
                'orders.shipfrom', 'orders.shipto', 'orders.total', 'orders.created_at',
                'orders.active_tab', 'orders.order_status',
            ]);

        $data = $rows->map(function ($add) {
            // Same status → badge-color mapping the original Blade @if chain used,
            // moved server-side so the AJAX rows can render the same badges the
            // JS-side badgeClasses() helper maps (see admin/contacts/index.blade.php
            // for the established client-side rendering half of this pattern).
            $statusLabel = $add->order_status ?: 'Request Placed';
            if ((int) $add->active_tab === 3) {
                $statusColor = 'yellow';
            } elseif ((int) $add->active_tab === 4) {
                $statusColor = 'green';
            } elseif (in_array($add->order_status, ['Offer Accepted', 'Offer Placed'], true)) {
                $statusColor = 'blue';
            } elseif ($add->order_status === 'Order placed') {
                $statusColor = 'slate';
            } else {
                $statusColor = 'red';
            }

            return [
                'id'            => $add->id,
                'order_id'      => $add->order_id,
                'name'          => $add->name,
                'email'         => $add->email,
                'shipfrom'      => $add->shipfrom,
                'shipto'        => $add->shipto,
                'total'         => $add->total,
                // Query-builder rows carry created_at as a raw DB string, not a Carbon
                // instance — unlike Orders::get() this is DB::table(), so no cast happens.
                'created_at'    => $add->created_at ? \Illuminate\Support\Carbon::parse($add->created_at)->format('Y-m-d H:i') : '',
                'status_label'  => $statusLabel,
                'status_color'  => $statusColor,
                'show_url'      => route('order', $add->id),
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
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

        // dd($request->all());
        if ($request->user_id == "user-invalid"){
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'number' => 'required|max:20',
                'cargotype' => 'required',
                'country' => 'required',
                'destination' => 'required',
                'weight' => 'required',
                'width' => 'required',
                'height' => 'required',
                'detail' => 'required',
            ]);
            $freequote = new RequestQuote([
                'name' => $request->name,
                'email' => $request->email,
                'number' => $request->number,
                'cargotype' => $request->cargotype,
                'country' => $request->country,
                'destination' => $request->destination,
                'weight' => $request->weight,
                'width' => $request->width,
                'height' => $request->height,
                'detail' => $request->detail,
            ]);
            $freequote->save();
            $details = [
                'title' => 'Free quote is requested by',
                'name' => $request->name,
                'email' => $request->email,
                'number' => $request->number,
                'cargotype' => $request->cargotype,
                'country' => $request->country,
                'destination' => $request->destination,
                'weight' => $request->weight,
                'width' => $request->width,
                'height' => $request->height,
                'detail' => $request->detail,
            ];
            // Quote is already saved above — mail failure must not lose it.
            // Unified pipeline: admin template (quote_submitted_admin) if
            // configured, else the legacy mailable; always logged.
            app(\App\Services\EmailService::class)->send(
                'quote_submitted_admin',
                'sales@deliveringparcel.com',
                $details,
                null,
                function () use ($details) {
                    Mail::to('sales@deliveringparcel.com')->send(new Free_quoteEmail($details));
                }
            );
            return back()->with('success', 'Your response is send successfully');
        }else{
            $request->validate([
                'cargotype' => 'required',
                'country' => 'required',
                'destination' => 'required',
                'weight' => 'required',
                'width' => 'required',
                'height' => 'required',
                'detail' => 'required',
            ]);
            $user = $request->user_id;
            $user_name = User::where('id', $request->user_id)->first();
            $freequote = new RequestQuote([
                'name' => $user_name->name,
                'email' => $user_name->email,
                'number' => $user_name->number,
                'cargotype' => $request->cargotype,
                'country' => $request->country,
                'destination' => $request->destination,
                'weight' => $request->weight,
                'width' => $request->width,
                'height' => $request->height,
                'detail' => $request->detail,
            ]);
            $freequote->save();
            $details = [
                'title' => 'Free quote is requested by',
                'name' =>  $user_name->name,
                'email' => $user_name->email,
                'number' => $user_name->number,
                'cargotype' => $request->cargotype,
                'country' => $request->country,
                'destination' => $request->destination,
                'weight' => $request->weight,
                'width' => $request->width,
                'height' => $request->height,
                'detail' => $request->detail,
            ];
            // Quote is already saved above — mail failure must not lose it.
            // Unified pipeline: admin template (quote_submitted_admin) if
            // configured, else the legacy mailable; always logged.
            app(\App\Services\EmailService::class)->send(
                'quote_submitted_admin',
                'sales@deliveringparcel.com',
                $details,
                null,
                function () use ($details) {
                    Mail::to('sales@deliveringparcel.com')->send(new Free_quoteEmail($details));
                }
            );
            return back()->with('success', 'Your response is send successfully');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // dd($id);
        $freequote = RequestQuote::where('id',$id)->get();
        // dd($freequote);
        return view('admin.freequote.show', compact('freequote'));
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
         $address = RequestQuote::find($id);
        $address->delete();
        return redirect(route('freequote.index'))->with('success', 'Quote Deleted successfuly');
    }

    
}
