<?php

namespace App\Http\Controllers\Admin\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperCountryRequest;
use App\Models\ShipperPayoutRequest;
use App\Models\ShipperProfile;
use App\Models\ShipperWalletTransaction;
use App\Notifications\Shipper\CountryRequestDecisionNotification;
use App\Services\Shipper\ShipperRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipperManagementController extends Controller
{
    /** Admin inbox for shipper countries change requests + direct country editor. */
    public function countryRequests(Request $request)
    {
        $pending = ShipperCountryRequest::with('profile.user')
            ->where('status', 'pending')->latest()->get();

        $reviewed = ShipperCountryRequest::with('profile.user')
            ->whereIn('status', ['approved', 'rejected'])->latest('reviewed_at')
            ->limit(20)->get();

        $activeCountries = DB::table('countries')->where('is_active', 1)->orderBy('name')->get(['name', 'iso2']);

        $editShipper = null;
        $editIsos = [];
        if ($request->filled('edit')) {
            $editShipper = ShipperProfile::with('user')->findOrFail($request->edit);
            $editIsos = $editShipper->service_countries ?? [];
        }
        $shippers = ShipperProfile::with('user')->orderBy('id')->get();

        return view('admin.shippers.country-requests', [
            'pending'         => $pending,
            'reviewed'        => $reviewed,
            'activeCountries' => $activeCountries,
            'editShipper'     => $editShipper,
            'editIsos'        => $editIsos,
            'shippers'        => $shippers,
        ]);
    }

    /** Approve a countries change request — applies it to the shipper profile. */
    public function approveCountryRequest($id)
    {
        $req = ShipperCountryRequest::with('profile')->where('status', 'pending')->findOrFail($id);
        $req->profile->update(['service_countries' => $req->countries]);
        $req->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        $req->profile->user->notify(new CountryRequestDecisionNotification('approved', $req->countries));

        return back()->with('success', 'Approved — shipper countries updated to: ' . implode(', ', $req->countries));
    }

    /** Reject a countries change request. */
    public function rejectCountryRequest(Request $request, $id)
    {
        $data = $request->validate(['admin_note' => 'nullable|string|max:500']);
        $req = ShipperCountryRequest::with('profile')->where('status', 'pending')->findOrFail($id);
        $req->update([
            'status'      => 'rejected',
            'admin_note'  => $data['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        $req->profile->user->notify(new CountryRequestDecisionNotification('rejected', $req->countries, $data['admin_note'] ?? ''));

        return back()->with('success', 'Countries change request rejected.');
    }

    /** Direct country edit — admin overrides a shipper's countries without a request. */
    public function updateCountries(Request $request, $id)
    {
        $profile = ShipperProfile::findOrFail($id);
        $data = $request->validate([
            'service_countries'   => 'nullable|array',
            'service_countries.*' => 'string|max:5',
        ]);
        $allIso2 = DB::table('countries')->pluck('iso2')->map(fn ($v) => strtoupper($v))->all();
        $countries = collect($data['service_countries'] ?? [])->map(fn ($v) => strtoupper(trim($v)))
            ->unique()->filter(fn ($v) => in_array($v, $allIso2, true))->values()->all();

        $profile->update(['service_countries' => $countries]);
        // A pending request for this shipper is now moot — close it silently.
        ShipperCountryRequest::where('shipper_profile_id', $profile->id)
            ->where('status', 'pending')->update([
                'status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now(),
                'admin_note' => 'Superseded by direct admin edit',
            ]);

        return redirect()->route('admin.shippers.country-requests')
            ->with('success', $profile->shipper_username . ' countries set to: ' . (implode(', ', $countries) ?: 'none'));
    }

    public function index(Request $request)
    {
        $q = ShipperProfile::with('user')
            ->when($request->filled('country'), fn ($x) => $x->whereJsonContains('service_countries', strtoupper($request->country)))
            ->when($request->filled('level'), fn ($x) => $x->where('level', (int) $request->level))
            ->when($request->filled('status'), fn ($x) => $x->where('status', $request->status))
            ->when($request->filled('kyc_status'), fn ($x) => $x->where('kyc_status', $request->kyc_status))
            ->when($request->filled('search'), function ($x) use ($request) {
                $s = $request->search;
                $x->whereHas('user', function ($u) use ($s) {
                    $u->where('email', 'like', "%{$s}%")
                        ->orWhere('shipper_username', 'like', "%{$s}%")
                        ->orWhere('name', 'like', "%{$s}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('admin.shippers.index', ['shippers' => $q]);
    }

    public function show($id)
    {
        $shipper = ShipperProfile::with([
            'user', 'kycDocuments', 'assignments.order', 'ratings', 'walletTransactions' => fn ($q) => $q->latest()->limit(50),
        ])->findOrFail($id);

        return view('admin.shippers.show', ['shipper' => $shipper]);
    }

    public function approve(Request $request, $id)
    {
        $shipper = ShipperProfile::findOrFail($id);
        app(ShipperRegistrationService::class)->approveShipper($shipper, (int) auth()->id());

        return redirect()->back()->with('success', 'Shipper approved and activated.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        $shipper = ShipperProfile::findOrFail($id);
        app(ShipperRegistrationService::class)->rejectShipper($shipper, $data['reason'], (int) auth()->id());

        return redirect()->back()->with('success', 'Shipper rejected.');
    }

    public function suspend(Request $request, $id)
    {
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        ShipperProfile::findOrFail($id)->update([
            'status'            => 'suspended',
            'suspension_reason' => $data['reason'],
            'suspended_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'Shipper suspended.');
    }

    public function reinstate($id)
    {
        ShipperProfile::findOrFail($id)->update([
            'status'            => 'active',
            'suspension_reason' => null,
            'suspended_at'      => null,
        ]);

        return redirect()->back()->with('success', 'Shipper reinstated.');
    }

    public function promote($id)
    {
        $shipper = ShipperProfile::findOrFail($id);
        if ($shipper->level < 3) {
            $shipper->update([
                'level'                 => $shipper->level + 1,
                'max_concurrent_orders' => $shipper->level + 1 === 2 ? 10 : 999,
            ]);
        }

        return redirect()->back()->with('success', 'Shipper promoted to level ' . $shipper->fresh()->level . '.');
    }

    public function pendingKyc()
    {
        $shippers = ShipperProfile::with('user', 'kycDocuments')
            ->where('kyc_status', 'pending')->orderBy('created_at')->get();

        return view('admin.shippers.pending-kyc', ['shippers' => $shippers]);
    }

    public function performance()
    {
        $top = ShipperProfile::with('user')->where('total_ratings', '>', 0)
            ->orderByDesc('rating')->limit(10)->get();
        $lowRating = ShipperProfile::with('user')->where('rating', '<', 3.5)
            ->where('total_ratings', '>', 0)->get();
        $overdue = \App\Models\ShipperOrderAssignment::with('shipper.user')
            ->where('purchase_deadline', '<', now())
            ->whereIn('status', ['assigned', 'accepted', 'purchasing'])->get()
            ->filter(fn ($a) => $a->isPurchaseOverdue());

        return view('admin.shippers.performance', [
            'top' => $top, 'lowRating' => $lowRating, 'overdue' => $overdue,
        ]);
    }

    public function payoutRequests()
    {
        $requests = ShipperPayoutRequest::with('shipper.user')
            ->orderByDesc('created_at')->paginate(20);

        return view('admin.shippers.payout-requests', ['requests' => $requests]);
    }

    public function approvePayout(Request $request, $id)
    {
        $data = $request->validate(['reference' => 'nullable|string|max:100']);
        $payout = ShipperPayoutRequest::where('status', 'pending')->findOrFail($id);
        $shipper = $payout->shipper;

        \DB::transaction(function () use ($payout, $shipper, $data) {
            $shipper->decrement('wallet_balance', (float) $payout->amount);
            ShipperWalletTransaction::create([
                'shipper_profile_id' => $shipper->id,
                'type'               => 'payout_paid',
                'amount'             => $payout->amount,
                'balance_after'      => $shipper->fresh()->wallet_balance,
                'reference'          => $data['reference'] ?? null,
                'note'               => 'Payout paid',
                'processed_by'       => auth()->id(),
                'status'             => 'completed',
            ]);
            $payout->update([
                'status'       => 'paid',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Payout marked paid.');
    }

    public function rejectPayout(Request $request, $id)
    {
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        ShipperPayoutRequest::where('status', 'pending')->findOrFail($id)
            ->update([
                'status'       => 'rejected',
                'admin_notes'  => $data['reason'],
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Payout rejected.');
    }

    /** Moderation: approve a customer rating (visible to shipper stats). */
    public function approveRating($id)
    {
        \App\Models\ShipperRating::where('admin_approved', false)->findOrFail($id)
            ->update(['admin_approved' => true, 'admin_approved_at' => now()]);

        return redirect()->back()->with('success', 'Rating approved.');
    }

    /** Moderation: publish an approved rating as a public testimonial. */
    public function publishRating($id)
    {
        $rating = \App\Models\ShipperRating::findOrFail($id);
        if ($rating->consent_testimonial === 'no') {
            return redirect()->back()->with('error', 'Customer kept this review private — it cannot be published.');
        }
        $rating->update(['admin_approved' => true, 'admin_approved_at' => $rating->admin_approved_at ?? now(),
                         'published_as_testimonial' => !$rating->published_as_testimonial]);

        return redirect()->back()->with('success', 'Testimonial flag toggled.');
    }

    public function walletAdjust(Request $request, $id)
    {        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note'   => 'required|string|max:500',
        ]);
        $shipper = ShipperProfile::findOrFail($id);

        if ($data['amount'] > $shipper->wallet_balance) {
            return redirect()->back()->with('error', 'Amount exceeds wallet balance.');
        }

        \DB::transaction(function () use ($shipper, $data) {
            $shipper->decrement('wallet_balance', (float) $data['amount']);
            ShipperWalletTransaction::create([
                'shipper_profile_id' => $shipper->id,
                'type'               => 'debit',
                'amount'             => $data['amount'],
                'balance_after'      => $shipper->fresh()->wallet_balance,
                'note'               => 'Admin adjustment: ' . $data['note'],
                'processed_by'       => auth()->id(),
                'status'             => 'completed',
            ]);
        });

        return redirect()->back()->with('success', 'Wallet adjusted.');
    }
}
