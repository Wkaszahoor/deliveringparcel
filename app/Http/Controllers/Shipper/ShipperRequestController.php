<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperQuote;
use App\Models\ShippingRequest;
use Illuminate\Http\Request;

class ShipperRequestController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->get('shipperProfile');
        $requests = ShippingRequest::visibleToShipper($profile)
            ->withCount('quotes')->orderBy('expires_at')->paginate(10)->withQueryString();

        // 2026-09-19 portal redesign: JSON rows for DP.infiniteScroll.
        if ($request->ajax() || $request->wantsJson() || $request->filled('json')) {
            return response()->json([
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'data' => collect($requests->items())->map(fn ($r) => [
                    'id'            => $r->id,
                    'reference'     => $r->reference,
                    'service_type'  => $r->service_type,
                    'service_label' => $r->service_type === 'buy_for_me' ? 'Buy for Me' : 'Ship for Me',
                    'country'       => $r->country_required,
                    'level'         => $r->required_level,
                    'quotes_count'  => $r->quotes_count,
                    'expires'       => optional($r->expires_at)->format('d M Y') ?: chr(8212),
                    'view_url'      => route('shipper.requests.show', $r->id),
                ]),
            ]);
        }

        return view('portal.shipper-requests', ['requests' => $requests]);
    }

    /** JSON rows for the portal marketplace table. */
    public function data(Request $request)
    {
        $request->merge(['json' => 1]);
        return $this->index($request);
    }
    public function show(Request $request, $id)
    {
        $profile = $request->get('shipperProfile');
        $sr = ShippingRequest::findOrFail($id);

        // Shippers only see OPEN requests (never assigned/frozen/cancelled ones).
        if ($sr->status !== 'open' || $sr->is_frozen) {
            return redirect()->route('shipper.requests.index')->with('error', 'This request is no longer available.');
        }

        $myQuote = ShipperQuote::where('request_id', $sr->id)
            ->where('shipper_profile_id', $profile->id)->first();

        return view('shipper.requests.show', [
            'request'  => $sr,
            'myQuote'  => $myQuote,
            'canQuote' => !$myQuote && $profile->can_accept_orders,
        ]);
    }

    public function submitQuote(Request $request, $id)
    {
        $profile = $request->get('shipperProfile');
        $data = $request->validate([
            'quoted_amount' => 'required|numeric|min:1',
            'estimated_days' => 'required|integer|min:1|max:120',
            'notes'         => 'nullable|string|max:2000',
        ]);
        $sr = ShippingRequest::where('status', 'open')->where('is_frozen', false)->findOrFail($id);

        // Country must still be admin-enabled at quote time.
        $countryEnabled = \Illuminate\Support\Facades\DB::table('countries')
            ->where('iso2', $sr->country_required)->where('is_active', 1)->exists();
        if (!$countryEnabled) {
            return redirect()->back()->with('error', 'This request is no longer available (country blocked by admin).');
        }

        if (!$profile->can_accept_orders) {
            return redirect()->back()->with('error', 'You cannot submit quotes right now (capacity or KYC).');
        }

        ShipperQuote::updateOrCreate(
            ['request_id' => $sr->id, 'shipper_profile_id' => $profile->id],
            $data + ['status' => 'pending']
        );

        return redirect()->route('shipper.quotes.index')->with('success', 'Quote submitted for ' . $sr->reference . '.');
    }

    public function withdrawQuote($quoteId)
    {
        ShipperQuote::where('id', $quoteId)
            ->where('shipper_profile_id', auth()->id() ? app(\App\Models\ShipperProfile::class)->where('user_id', auth()->id())->value('id') : 0)
            ->where('status', 'pending')->firstOrFail()
            ->update(['status' => 'withdrawn']);

        return redirect()->back()->with('success', 'Quote withdrawn.');
    }

    public function myQuotes()
    {
        $profile = \App\Models\ShipperProfile::where('user_id', auth()->id())->firstOrFail();
        $quotes = ShipperQuote::with('request')->where('shipper_profile_id', $profile->id)
            ->latest()->paginate(15);

        return view('shipper.requests.my-quotes', ['quotes' => $quotes]);
    }
}
