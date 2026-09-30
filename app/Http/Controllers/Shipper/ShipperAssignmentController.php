<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\ShipperAdminChat;
use App\Models\ShipperDeliveryAddress;
use App\Models\ShipperOrderAssignment;
use App\Models\ShipperProof;
use App\Models\ShipperCountryRequest;
use App\Models\ShipperProfile;
use App\Models\ShipperTrackingDetail;
use App\Models\ShipperWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShipperAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->get('shipperProfile');
        $assignments = ShipperOrderAssignment::with('order')
            ->where('shipper_profile_id', $profile->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('order', fn ($w) => $w->where('order_id', 'like', '%' . $request->q . '%')))
            ->latest()->paginate(15)->withQueryString();

        // 2026-09-19 portal redesign: JSON rows for DP.infiniteScroll.
        if ($request->ajax() || $request->wantsJson() || $request->filled('json')) {
            return response()->json([
                'current_page' => $assignments->currentPage(),
                'last_page'    => $assignments->lastPage(),
                'data' => collect($assignments->items())->map(fn ($a) => [
                    'id'           => $a->id,
                    'reference'    => ($a->request->reference ?? chr(8212)),
                    'order_ref'    => $a->order->order_id ?? $a->order_id,
                    'fee'          => 'USD ' . number_format((float) $a->shipper_fee, 2),
                    'status'       => $a->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $a->status)),
                    'created'      => optional($a->created_at)->format('d M Y'),
                    'view_url'     => route('shipper.assignments.show', $a->id),
                ]),
            ]);
        }

        return view('portal.shipper-assignments', ['assignments' => $assignments]);
    }

    /** JSON rows for the portal assignments table. */
    public function data(Request $request)
    {
        $request->merge(['json' => 1]);
        return $this->index($request);
    }

    /** JSON rows for the portal wallet table. */
    public function walletData(Request $request)
    {
        $request->merge(['json' => 1]);
        return $this->walletHistory();
    }
    public function show(Request $request, $id)
    {
        $profile = $request->get('shipperProfile');
        $a = ShipperOrderAssignment::with(['order', 'proofs', 'deliveryAddress', 'trackingDetails', 'adminChat'])
            ->where('shipper_profile_id', $profile->id)->findOrFail($id);

        return view('shipper.assignments.show', ['assignment' => $a, 'profile' => $profile]);
    }

    public function uploadProof(Request $request, $id)
    {
        $profile = $request->get('shipperProfile');
        $a = ShipperOrderAssignment::where('shipper_profile_id', $profile->id)->findOrFail($id);

        $data = $request->validate([
            'proof_type' => 'required|in:item_received,before_repack,after_repack,dispatch_receipt,damage_report,purchase_receipt',
            'file'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        /* L12/Flysystem3 fix (2026-09-19): storeAs('...', '...', 'local') returned an
           empty temp path ("Path cannot be empty") — use the same explicit pattern as
           the payments proof flow. */
        $name = \Illuminate\Support\Str::random(40) . '.' . strtolower($file->getClientOriginalExtension() ?: 'bin');
        \Illuminate\Support\Facades\Storage::disk('local')->putFileAs('shipper-proofs/' . $a->id, $file->getPathname(), $name);
        $path = 'shipper-proofs/' . $a->id . '/' . $name;

        ShipperProof::create([
            'assignment_id'       => $a->id,
            'shipper_profile_id'  => $profile->id,
            'proof_type'          => $data['proof_type'],
            'file_path'           => $path,
            'mime_type'           => $file->getMimeType(),
            'file_size'           => $file->getSize(),
            'shipper_notes'       => $data['notes'] ?? null,
        ]);

        if (!in_array($a->status, ['proof_approved', 'address_received', 'address_forwarded', 'dispatched', 'tracking_added', 'tracking_shared', 'delivered', 'completed'])) {
            $a->update(['status' => 'proof_uploaded']);
        }

        return redirect()->back()->with('success', 'Proof uploaded — awaiting admin approval.');
    }

    public function submitTracking(Request $request, $id)
    {
        $profile = $request->get('shipperProfile');
        $a = ShipperOrderAssignment::where('shipper_profile_id', $profile->id)->findOrFail($id);

        $data = $request->validate([
            'carrier'            => 'required|string|max:100',
            'tracking_number'    => 'required|string|max:200',
            'tracking_url'       => 'nullable|url|max:500',
            'ship_date'          => 'required|date',
            'estimated_delivery' => 'nullable|date',
            'notes'              => 'nullable|string|max:1000',
        ]);

        ShipperTrackingDetail::updateOrCreate(
            ['assignment_id' => $a->id],
            $data + ['order_id' => $a->order_id, 'shipper_notes' => $data['notes'] ?? null]
        );

        $a->update(['status' => 'tracking_added', 'dispatched_at' => $a->dispatched_at ?? now()]);

        return redirect()->back()->with('success', 'Tracking submitted — admin will share it with the customer.');
    }

    public function markPurchased(Request $request, $id)
    {
        $profile = $request->get('shipperProfile');
        $a = ShipperOrderAssignment::where('shipper_profile_id', $profile->id)->findOrFail($id);
        $a->update(['status' => 'purchased', 'purchased_at' => now()]);

        return redirect()->back()->with('success', 'Marked as purchased.');
    }

    public function markPackageReceived(Request $request, $id)
    {
        $profile = $request->get('shipperProfile');
        $a = ShipperOrderAssignment::where('shipper_profile_id', $profile->id)->findOrFail($id);
        $a->update(['status' => 'package_received', 'package_received_at' => now()]);

        return redirect()->back()->with('success', 'Package received — you can now upload proofs.');
    }

    public function walletHistory()
    {
        $profile = ShipperProfile::where('user_id', auth()->id())->firstOrFail();
        $transactions = ShipperWalletTransaction::where('shipper_profile_id', $profile->id)
            ->latest()->paginate(20);
        $payouts = $profile->payoutRequests()->latest()->limit(10)->get();

        // 2026-09-19 portal redesign: JSON rows for DP.infiniteScroll.
        if (request()->ajax() || request()->wantsJson() || request()->filled('json')) {
            return response()->json([
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'data' => collect($transactions->items())->map(fn ($t) => [
                    'id'         => $t->id,
                    'type'       => $t->type,
                    'type_label' => ucfirst(str_replace('_', ' ', $t->type)),
                    'amount'     => ($t->type === 'debit' || $t->type === 'payout' ? '-' : '+') . 'USD ' . number_format((float) $t->amount, 2),
                    'detail'     => (string) ($t->notes ?? ''),
                    'created'    => optional($t->created_at)->format('d M Y, h:i A'),
                ]),
            ]);
        }

        return view('portal.shipper-wallet', [
            'profile' => $profile,
            'balance' => (float) $profile->wallet_balance,
            'held'    => (float) $profile->wallet_pending,
            'kpis'    => ['paid_out' => (float) $profile->total_earned],
        ]);
    }

    public function requestPayout(Request $request)
    {
        $profile = ShipperProfile::where('user_id', auth()->id())->firstOrFail();
        $data = $request->validate([
            'amount'  => 'required|numeric|min:1',
            'method'  => 'required|in:bank,paypal,wise',
            'details' => 'required|string|max:2000',
        ]);

        if ($data['amount'] > ($profile->wallet_balance - $profile->wallet_pending)) {
            return redirect()->back()->with('error', 'Amount exceeds available balance.');
        }

        \App\Models\ShipperPayoutRequest::create([
            'shipper_profile_id' => $profile->id,
            'amount'             => $data['amount'],
            'method'             => $data['method'],
            'payment_details'    => encrypt($data['details']),
        ]);

        return redirect()->back()->with('success', 'Payout requested — awaiting admin processing.');
    }

    public function myProfile()
    {
        $profile = ShipperProfile::with('kycDocuments')->where('user_id', auth()->id())->firstOrFail();

        return view('shipper.profile', ['profile' => $profile]);
    }

    /** Shipper's service-countries page: current list + any pending change request. */
    public function countries()
    {
        $profile = ShipperProfile::where('user_id', auth()->id())->firstOrFail();
        $activeCountries = \Illuminate\Support\Facades\DB::table('countries')
            ->where('allow_shipper', 1)->orderBy('name')->get(['name', 'iso2']);
        $pending = ShipperCountryRequest::where('shipper_profile_id', $profile->id)
            ->where('status', 'pending')->latest()->first();

        return view('shipper.countries', [
            'profile'         => $profile,
            'activeCountries' => $activeCountries,
            'pending'         => $pending,
        ]);
    }

    /** Shipper submits a countries change request — admin must approve it. */
    public function submitCountryRequest(Request $request)
    {
        $profile = ShipperProfile::where('user_id', auth()->id())->firstOrFail();
        $activeIso2 = \Illuminate\Support\Facades\DB::table('countries')
            ->where('allow_shipper', 1)->pluck('iso2')->map(fn ($v) => strtoupper($v))->all();

        $data = $request->validate([
            'service_countries'   => 'required|array|min:1',
            'service_countries.*' => 'string',
            'note'                => 'nullable|string|max:500',
        ]);

        $countries = collect($data['service_countries'])
            ->map(fn ($v) => strtoupper(trim($v)))
            ->unique()
            ->filter(fn ($v) => in_array($v, $activeIso2, true))
            ->values()->all();

        if (count($countries) === 0) {
            return back()->withErrors(['service_countries' => 'Select at least one enabled country.'])->withInput();
        }

        // One pending request at a time — re-submitting replaces the pending one.
        ShipperCountryRequest::updateOrCreate(
            ['shipper_profile_id' => $profile->id, 'status' => 'pending'],
            ['countries' => $countries, 'note' => $data['note'] ?? null]
        );

        \App\Models\User::where('type', 'admin')->get()->each(function ($admin) use ($profile, $countries) {
            $admin->notifyNow(new \App\Notifications\TaskNotification([
                'title'        => 'Shipper ' . ($profile->shipper_username ?? ('#' . $profile->id)) . ' requested a service countries change',
                'order_number' => $profile->shipper_username ?? ('SHP-' . $profile->id),
                'greeting'     => 'New shipper countries change request',
                'order_id'     => 0,
                'body'         => 'Requested countries: ' . implode(', ', $countries),
                'url'          => url('admin/shippers/country-requests'),
            ]));
        });

        return redirect()->route('shipper.countries')
            ->with('success', 'Your countries change request was sent to admin for approval.');
    }

    public function updateProfile(Request $request)
    {
        $profile = ShipperProfile::where('user_id', auth()->id())->firstOrFail();
        $data = $request->validate([
            'residence_type'   => 'required|in:apartment,house,villa,office',
            'has_storage'      => 'nullable|boolean',
            'services_offered' => 'nullable|array',
        ]);
        // service_countries intentionally NOT updatable by shipper (admin controls).
        $profile->update([
            'residence_type'   => $data['residence_type'],
            'has_storage'      => (bool) ($data['has_storage'] ?? false),
            'services_offered' => $data['services_offered'] ?? $profile->services_offered,
        ]);

        return redirect()->back()->with('success', 'Profile updated.');
    }

    /** CUSTOMER-facing: rate the shipper after delivery/completion. */
    public function rateShipper(Request $request, $orderId)
    {
        $order = Orders::where('id', $orderId)
            ->where('user_id', auth()->id())->firstOrFail();

        $assignment = ShipperOrderAssignment::where('order_id', $order->id)
            ->whereIn('status', ['tracking_shared', 'delivered', 'completed'])->firstOrFail();

        if ($assignment->rating()->exists()) {
            return redirect()->back()->with('error', 'You already rated this delivery.');
        }

        $data = $request->validate([
            'overall_rating'     => 'required|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'speed_rating'       => 'nullable|integer|min:1|max:5',
            'value_rating'       => 'nullable|integer|min:1|max:5',
            'condition_rating'   => 'nullable|integer|min:1|max:5',
            'review_text'        => 'nullable|string|max:2000',
            'consent_testimonial' => 'required|in:no,anonymous,first_name_only,full_name',
        ]);

        \App\Models\ShipperRating::create($data + [
            'assignment_id'       => $assignment->id,
            'order_id'            => $order->id,
            'shipper_profile_id'  => $assignment->shipper_profile_id,
            'rated_by_user_id'    => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Thank you! Your rating helps us grow the network.');
    }

    /** CUSTOMER-facing: submit the delivery address after proof approval. */
    public function submitDeliveryAddress(Request $request, $orderId)
    {
        $order = Orders::where('id', $orderId)
            ->where('user_id', auth()->id())->firstOrFail();

        if (!$order->shipper_proof_approved || $order->delivery_address_submitted) {
            return redirect()->back()->with('error', 'Address submission is not available for this order right now.');
        }

        $data = $request->validate([
            'recipient_name'        => 'required|string|max:200',
            'address_line_1'        => 'required|string|max:300',
            'address_line_2'        => 'nullable|string|max:300',
            'city'                  => 'required|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'postal_code'           => 'required|string|max:20',
            'country'               => 'required|string|max:100',
            'phone'                 => 'nullable|string|max:50',
            'delivery_instructions' => 'nullable|string|max:1000',
        ]);

        $assignment = ShipperOrderAssignment::where('order_id', $order->id)
            ->whereNotIn('status', ['cancelled'])->firstOrFail();

        ShipperDeliveryAddress::create($data + [
            'order_id'            => $order->id,
            'assignment_id'       => $assignment->id,
            'submitted_by_user_id' => auth()->id(),
        ]);

        $assignment->update(['status' => 'address_received']);
        \DB::table('orders')->where('id', $order->id)->update(['delivery_address_submitted' => true]);

        return redirect()->back()->with('success', 'Delivery address submitted — we will forward it securely.');
    }
}
