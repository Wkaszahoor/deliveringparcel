<?php

namespace App\Http\Controllers\Admin\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShippingRequest;
use App\Models\ShipperAdminChat;
use App\Models\ShipperProfile;
use App\Services\Shipper\ShippingRequestService;
use Illuminate\Http\Request;

class ShippingRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = ShippingRequest::withCount('quotes')
            ->when($request->filled('username'), function ($x) use ($request) {
                $s = $request->username;
                $x->where(function ($w) use ($s) {
                    $w->where('customer_username', 'like', "%{$s}%")
                        ->orWhere('reference', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('country'), fn ($x) => $x->where('country_required', strtoupper($request->country)))
            ->when($request->filled('status'), fn ($x) => $x->where('status', $request->status))
            ->when($request->filled('service_type'), fn ($x) => $x->where('service_type', $request->service_type))
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('admin.shipping-requests.index', ['requests' => $q]);
    }

    /** 1-click: build a pre-filled brief from an existing order. */
    public function generate(Request $request)
    {
        $data = $request->validate(['order_id' => 'required|integer|exists:orders,id']);
        $payload = app(ShippingRequestService::class)
            ->generateBriefFromOrder((int) $data['order_id'], (int) auth()->id());

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'data' => $payload]);
        }

        return redirect()->route('admin.shipping-requests.create', ['order_id' => $data['order_id']])
            ->with('generated', $payload);
    }

    public function create(Request $request)
    {
        $generated = session('generated');
        if (!$generated && $request->filled('order_id')) {
            $generated = app(ShippingRequestService::class)
                ->generateBriefFromOrder((int) $request->order_id, (int) auth()->id());
        }

        return view('admin.shipping-requests.create', ['generated' => $generated ?: null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id'          => 'required|integer|exists:orders,id',
            'service_type'      => 'required|in:buy_for_me,ship_for_me',
            'country_required'  => 'required|string|max:10',
            'brief_text'        => 'required|string',
            'product_details'   => 'nullable|array',
            'contact_email'     => 'nullable|email|max:200',
            'contact_phone'     => 'nullable|string|max:50',
            'address_snippet'   => 'nullable|string|max:300',
            'value_range_min'   => 'nullable|numeric',
            'value_range_max'   => 'nullable|numeric',
            'required_level'    => 'required|integer|in:1,2,3',
            'expires_hours'     => 'nullable|integer|min:1|max:720',
            'admin_internal_notes' => 'nullable|string',
            'publish'           => 'nullable|boolean',
        ]);

        $svc = app(ShippingRequestService::class);
        $base = $svc->generateBriefFromOrder((int) $data['order_id'], (int) auth()->id());

        $sr = ShippingRequest::create([
            'order_id'          => $data['order_id'],
            'customer_username' => $base['customer_username'],
            'service_type'      => $data['service_type'],
            'country_required'  => strtoupper($data['country_required']),
            'brief_text'        => $data['brief_text'],
            'product_details'   => $data['product_details'] ?? $base['product_details'],
            'contact_email'     => $data['contact_email'] ?? '',
            'contact_phone'     => $data['contact_phone'] ?? '',
            'address_snippet'   => $data['address_snippet'] ?? $base['address_snippet'],
            'value_range_min'   => $data['value_range_min'] ?? $base['value_range_min'],
            'value_range_max'   => $data['value_range_max'] ?? $base['value_range_max'],
            'required_level'    => $data['required_level'],
            'expires_at'        => now()->addHours((int) ($data['expires_hours'] ?? 48)),
            'created_by'        => auth()->id(),
            'admin_internal_notes' => $data['admin_internal_notes'] ?? '',
            'status'            => 'draft',
        ]);

        if (!empty($data['publish'])) {
            $svc->publishRequest($sr);
        }

        return redirect()->route('admin.shipping-requests.show', $sr->id)
            ->with('success', 'Shipping request ' . $sr->reference . ' created' . (!empty($data['publish']) ? ' and published.' : ' as draft.'));
    }

    public function show($id)
    {
        $sr = ShippingRequest::with([
            'order', 'quotes.shipper.user', 'assignment', 'adminChat',
        ])->withCount('quotes')->findOrFail($id);

        return view('admin.shipping-requests.show', ['request' => $sr]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'brief_text'      => 'required|string',
            'contact_email'   => 'nullable|email|max:200',
            'contact_phone'   => 'nullable|string|max:50',
            'country_required' => 'required|string|max:10',
            'required_level'  => 'required|integer|in:1,2,3',
            'admin_internal_notes' => 'nullable|string',
        ]);
        $sr = ShippingRequest::findOrFail($id);
        $data['country_required'] = strtoupper($data['country_required']);
        $sr->update($data);

        return redirect()->back()->with('success', 'Request updated.');
    }

    public function freeze($id)
    {
        ShippingRequest::findOrFail($id)->freeze((int) auth()->id());

        return redirect()->back()->with('success', 'Request frozen.');
    }

    public function unfreeze($id)
    {
        ShippingRequest::findOrFail($id)->unfreeze();

        return redirect()->back()->with('success', 'Request unfrozen.');
    }

    public function publish($id)
    {
        $sr = ShippingRequest::whereIn('status', ['draft', 'frozen'])->findOrFail($id);
        app(ShippingRequestService::class)->publishRequest($sr);

        return redirect()->back()->with('success', 'Request published to eligible shippers.');
    }

    public function selectShipper(Request $request, $id)
    {
        $data = $request->validate([
            'shipper_profile_id' => 'required|integer|exists:shipper_profiles,id',
            'shipper_fee'        => 'required|numeric|min:0',
            'platform_fee'       => 'required|numeric|min:0',
        ]);
        $sr     = ShippingRequest::findOrFail($id);
        $shipper = ShipperProfile::findOrFail($data['shipper_profile_id']);

        $assignment = app(ShippingRequestService::class)->assignShipper(
            $sr, $shipper,
            (float) $data['shipper_fee'],
            (float) $data['platform_fee'],
            (float) $data['shipper_fee'] + (float) $data['platform_fee'],
            (int) auth()->id()
        );

        return redirect()->route('admin.shipper-assignments.show', $assignment->id)
            ->with('success', 'Shipper selected — assignment created.');
    }

    public function sendMessage(Request $request, $id)
    {
        $data = $request->validate(['message' => 'required|string|max:3000']);
        ShippingRequest::findOrFail($id);

        ShipperAdminChat::create([
            'request_id' => $id,
            'from_type'  => 'admin',
            'from_id'    => auth()->id(),
            'message'    => $data['message'],
        ]);

        return redirect()->back()->with('success', 'Message sent.');
    }

    public function cancel($id)
    {
        $sr = ShippingRequest::findOrFail($id);
        $sr->update(['status' => 'cancelled']);

        return redirect()->route('admin.shipping-requests.index')
            ->with('success', 'Request cancelled.');
    }
}
