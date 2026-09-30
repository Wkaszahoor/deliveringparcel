<?php

namespace App\Http\Controllers\Api\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperProfile;
use App\Models\ShipperQuote;
use App\Models\ShippingRequest;
use Illuminate\Http\Request;

class ShipperApiRequestController extends Controller
{
    public function index(Request $request)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();

        $requests = ShippingRequest::visibleToShipper($profile)->orderBy('expires_at')->paginate(10);

        return response()->json([
            'ok'   => true,
            'data' => [
                'requests' => collect($requests->items())->map(fn ($r) => [
                    'id'            => $r->id,
                    'reference'     => $r->reference,
                    'service_type'  => $r->service_type,
                    'country_required' => $r->country_required,
                    'product_count' => count($r->product_details ?? []),
                    'value_range_min' => (float) $r->value_range_min,
                    'value_range_max' => (float) $r->value_range_max,
                    'deadline_hours_remaining' => $r->expires_at ? (int) max(0, now()->diffInHours($r->expires_at)) : null,
                    'required_level' => $r->required_level,
                ]),
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page'    => $requests->lastPage(),
                    'total'        => $requests->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $sr = ShippingRequest::findOrFail($id);

        if ($sr->status !== 'open' || $sr->is_frozen) {
            return response()->json(['ok' => false, 'message' => 'Request no longer available.'], 404);
        }

        $myQuote = ShipperQuote::where('request_id', $sr->id)
            ->where('shipper_profile_id', $profile->id)->first();

        // SAFE fields only — brief_text is the masked document, never raw PII.
        return response()->json([
            'ok'   => true,
            'data' => [
                'request' => [
                    'id' => $sr->id, 'reference' => $sr->reference,
                    'service_type' => $sr->service_type, 'country_required' => $sr->country_required,
                    'value_range_min' => (float) $sr->value_range_min, 'value_range_max' => (float) $sr->value_range_max,
                    'required_level' => $sr->required_level,
                    'expires_at' => $sr->expires_at?->toIso8601String(),
                ],
                'products'  => $sr->product_details ?? [],
                'brief_text' => $sr->brief_text,
                'my_quote'  => $myQuote ? [
                    'amount' => (float) $myQuote->quoted_amount,
                    'days'   => (int) $myQuote->estimated_days,
                    'status' => $myQuote->status,
                ] : null,
                'can_quote' => !$myQuote && $profile->can_accept_orders,
            ],
        ]);
    }

    public function submitQuote(Request $request, $id)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'quoted_amount'  => 'required|numeric|min:1',
            'estimated_days' => 'required|integer|min:1|max:120',
            'notes'          => 'nullable|string|max:2000',
        ]);
        $sr = ShippingRequest::where('status', 'open')->where('is_frozen', false)->findOrFail($id);

        if (!$profile->can_accept_orders) {
            return response()->json(['ok' => false, 'message' => 'Cannot quote right now (capacity/KYC).'], 422);
        }

        $quote = ShipperQuote::updateOrCreate(
            ['request_id' => $sr->id, 'shipper_profile_id' => $profile->id],
            $data + ['status' => 'pending']
        );

        return response()->json(['ok' => true, 'message' => 'Quote submitted.', 'data' => ['id' => $quote->id]]);
    }

    public function withdrawQuote(Request $request, $quoteId)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $quote = ShipperQuote::where('id', $quoteId)
            ->where('shipper_profile_id', $profile->id)->where('status', 'pending')->firstOrFail();
        $quote->update(['status' => 'withdrawn']);

        return response()->json(['ok' => true, 'message' => 'Quote withdrawn.']);
    }

    public function myQuotes(Request $request)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $quotes = ShipperQuote::with('request')->where('shipper_profile_id', $profile->id)
            ->latest()->paginate(15);

        return response()->json([
            'ok'   => true,
            'data' => [
                'quotes' => collect($quotes->items())->map(fn ($q) => [
                    'id' => $q->id, 'reference' => $q->request?->reference,
                    'amount' => (float) $q->quoted_amount, 'days' => (int) $q->estimated_days,
                    'status' => $q->status, 'admin_response' => $q->admin_response,
                ]),
                'pagination' => ['current_page' => $quotes->currentPage(), 'last_page' => $quotes->lastPage()],
            ],
        ]);
    }
}
