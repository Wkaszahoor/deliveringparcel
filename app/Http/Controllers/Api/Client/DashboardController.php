<?php
/* mobile-api-v5 — upload-verification marker (2026-08-28) */

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = Orders::where('user_id', $user->id);

        $active = (clone $orders)->whereNotIn('order_status', ['completed', 'received', 'Offer Rejected'])->count();
        $completed = (clone $orders)->whereIn('order_status', ['completed', 'received'])->count();

        // Offers awaiting the client's decision
        $pendingOffers = (clone $orders)
            ->whereHas('offers', fn($q) => $q->where('offer_status', 0))
            ->count();

        // Latest payment state per order for the ones that matter
        $recent = (clone $orders)->orderByDesc('id')->limit(5)->get([
            'id', 'order_id', 'shipfrom', 'shipto', 'order_status', 'total', 'created_at',
        ])->map(function ($o) {
            $o->payment_status = Payment::where('order_id', $o->id)->orderByDesc('id')->value('status');
            return $o;
        });

        $wallet = Wallet::forUser($user->id);

        return response()->json([
            'stats' => [
                'active_orders'   => $active,
                'completed_orders'=> $completed,
                'total_orders'    => (clone $orders)->count(),
                'pending_offers'  => $pendingOffers,
                'wallet_balance'  => (float) $wallet->balance,
                'wallet_currency' => $wallet->currency ?? 'USD',
                'unread_alerts'   => $user->unreadNotifications()->count(),
            ],
            'recent_orders' => $recent,
        ]);
    }
}
