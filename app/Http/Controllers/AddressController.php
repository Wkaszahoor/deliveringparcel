<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddresses;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     * Server-side filters on the real shipping_addresses columns and
     * paginated 15 per page, newest first (same idiom as InboxController).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = ShippingAddresses::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('number')) {
            $query->where('number', 'like', '%' . $request->input('number') . '%');
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->input('city') . '%');
        }
        if ($request->filled('state')) {
            $query->where('state', 'like', '%' . $request->input('state') . '%');
        }
        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }

        $address = $query->orderByDesc('id')->paginate(15)->appends($request->query());

        return view('admin.address.index', [
            'address' => $address,
            'filters' => $request->only(['name', 'number', 'city', 'state', 'country']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.address.create');
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
        $request->validate([
            'name' => 'required',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'number' => 'required',
            'postalcode' => 'required',
        ]);

        ShippingAddresses::create($request->all());
        return redirect(route('address.index'))->with('success', 'Shipping Address added successfuly');
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
        $address = ShippingAddresses::where('id',$id)->get();
        // dd($address);
        return view('admin.address.edit', compact('address'));
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
        // $request->all();
        $request->validate([
            'name' => 'required',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'number' => 'required',
            'postalcode' => 'required',
        ]);

        $address = ShippingAddresses::find($id);

        $address->update($request->all());

        return redirect(route('address.index'))->with('success', 'Shipping Address updated successfuly');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $address = ShippingAddresses::find($id);
        $address->delete();
        return redirect(route('address.index'))->with('success', 'Shipping Address Deleted successfuly');
    }

    
}
