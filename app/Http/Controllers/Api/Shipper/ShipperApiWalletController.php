<?php

namespace App\Http\Controllers\Api\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperPayoutRequest;
use App\Models\ShipperProfile;
use App\Models\ShipperWalletTransaction;
use Illuminate\Http\Request;

class ShipperApiWalletController extends Controller
{
    public function index(Request $request)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $transactions = ShipperWalletTransaction::where('shipper_profile_id', $profile->id)
            ->latest()->paginate(20);

        return response()->json([
            'ok'   => true,
            'data' => [
                'wallet' => [
                    'balance'      => (float) $profile->wallet_balance,
                    'pending'      => (float) $profile->wallet_pending,
                    'total_earned' => (float) $profile->total_earned,
                    'withdrawable' => (float) max(0, $profile->wallet_balance - $profile->wallet_pending),
                ],
                'transactions' => collect($transactions->items())->map(fn ($t) => [
                    'type' => $t->type, 'amount' => (float) $t->amount,
                    'balance_after' => (float) $t->balance_after, 'note' => $t->note,
                    'created_at' => $t->created_at?->toIso8601String(),
                ]),
                'payouts' => $profile->payoutRequests()->latest()->limit(10)->get()
                    ->map(fn ($p) => ['id' => $p->id, 'amount' => (float) $p->amount, 'method' => $p->method, 'status' => $p->status]),
                'pagination' => ['current_page' => $transactions->currentPage(), 'last_page' => $transactions->lastPage()],
            ],
        ]);
    }

    public function requestPayout(Request $request)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'amount'  => 'required|numeric|min:1',
            'method'  => 'required|in:bank,paypal,wise',
            'details' => 'required|string|max:2000',
        ]);

        if ($data['amount'] > ($profile->wallet_balance - $profile->wallet_pending)) {
            return response()->json(['ok' => false, 'message' => 'Amount exceeds available balance.'], 422);
        }

        $payout = ShipperPayoutRequest::create([
            'shipper_profile_id' => $profile->id,
            'amount'             => $data['amount'],
            'method'             => $data['method'],
            'payment_details'    => encrypt($data['details']),
        ]);

        return response()->json(['ok' => true, 'message' => 'Payout requested.', 'data' => ['id' => $payout->id]]);
    }
}
