<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Offerorder;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function accept(Request $request, Offerorder $offer): JsonResponse
    {
        $this->ownOrFail($offer, $request);

        if ((int) $offer->offer_status === 1) {
            return response()->json(['message' => 'Offer already accepted.']);
        }
        if ((int) $offer->offer_status === 2) {
            return response()->json(['message' => 'This offer was rejected and is closed.'], 422);
        }
        if ($offer->order && $offer->order->order_status === 'Offer Accepted') {
            return response()->json(['message' => 'Offer already accepted — please proceed with the payment.']);
        }

        // LEGACY SEMANTICS: accept does NOT write offer_status — 1 means PAID
        // and is set only by payment success (web tab logic depends on this).
        // Same writes as the web accept form: order_status + active_tab=2.
        $offer->order?->update(['order_status' => 'Offer Accepted', 'active_tab' => 2]);

        $this->notifyStaffAndClient($request, $offer, 'accept');

        return response()->json(['message' => 'Offer accepted. Please proceed with the payment.', 'offer' => $offer]);
    }

    public function reject(Request $request, Offerorder $offer): JsonResponse
    {
        $this->ownOrFail($offer, $request);

        $data = $request->validate(['rejections_note' => 'nullable|string|max:1000']);

        $offer->update([
            'offer_status'    => 2,
            'rejections_note' => $data['rejections_note'] ?? null,
        ]);
        $offer->order?->update(['order_status' => 'Offer Rejected']);

        $this->notifyStaffAndClient($request, $offer, 'reject');

        return response()->json(['message' => 'Offer rejected.', 'offer' => $offer]);
    }

    private function ownOrFail(Offerorder $offer, Request $request): void
    {
        $owns = Orders::where('id', $offer->order_id)
            ->where('user_id', $request->user()->id)
            ->exists();
        abort_unless($owns, 404);
    }

    private function notifyStaffAndClient(Request $request, Offerorder $offer, string $action): void
    {
        $order = $offer->order;
        if (!$order) return;

        $admin = User::where('type', 'admin')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\TaskNotification([
                'title'       => 'Notification on Order #' . $order->order_id . ' from Shopper',
                'order_id'    => $order->id,
                'greeting'    => $request->user()->name . ($action === 'accept' ? ' accept offer' : ' reject offer'),
                'description' => '',
            ]));
        }
        if ($action === 'accept') {
            $request->user()->notify(new \App\Notifications\TaskNotification([
                'title'       => 'Notification on Order #' . $order->order_id,
                'order_id'    => $order->id,
                'greeting'    => 'Thank you for accepting our offer',
                'description' => 'Thank you for accepting our offer, please proceed with the payment.',
            ]));
        }
    }
}
