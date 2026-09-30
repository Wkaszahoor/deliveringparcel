<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\Offerorder;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Http\Request;

/** RQ-004 — customer view of offers: inspect, accept or reject with a reason. */
class OfferController extends Controller
{
    public function index(Request $request)
    {
        $orders = Orders::where('user_id', $request->user()->id)
            ->with('offers')
            ->orderByDesc('id')
            ->paginate(dp_per_page($request));

        return view('home2.offers.index', ['orders' => $orders]);
    }

    public function accept(Request $request, Offerorder $offer)
    {
        $this->ownOrFail($offer, $request);

        if ((int) $offer->offer_status === 1) {
            return back()->with('success', 'Offer already accepted.');
        }
        if ((int) $offer->offer_status === 2) {
            return back()->with('error', 'This offer was rejected and is closed.');
        }

        $offer->update(['offer_status' => 1]);
        // Legacy state-machine status (config/admin_orders.php) so admin and
        // legacy screens recognise the transition; 'confirmed' was unknown.
        $offer->order?->update(['order_status' => 'Offer Accepted', 'active_tab' => 3]);

        $this->notifyStaffAndClient($request, $offer, 'accept');

        return redirect()->route('home2.pay.show', $offer->order_id)
            ->with('success', 'Offer accepted — choose how you want to pay.');
    }

    public function reject(Request $request, Offerorder $offer)
    {
        $this->ownOrFail($offer, $request);

        $data = $request->validate(['rejections_note' => 'nullable|string|max:1000']);
        $offer->update([
            'offer_status'     => 2,
            'rejections_note'  => $data['rejections_note'] ?? null,
        ]);
        $offer->order?->update(['order_status' => 'Offer Rejected']);

        $this->notifyStaffAndClient($request, $offer, 'reject');

        return back()->with('success', 'Offer rejected — our team will send a revised offer.');
    }

    /** Mirror the legacy offer_accept/offer_reject notifications (database locally, mail+database in production). */
    private function notifyStaffAndClient(Request $request, Offerorder $offer, string $action): void
    {
        $order = $offer->order;
        if (!$order) {
            return;
        }
        $admin = User::where('type', 'admin')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\TaskNotification([
                'title'        => 'Notification on Order #' . $order->order_id . ' from Shopper',
                'order_number' => $order->order_id,
                'greeting'     => $request->user()->name . ($action === 'accept' ? ' accept offer' : ' reject offer'),
                'order_id'     => $order->id,
                'description'  => '',
            ]));
        }
        if ($action === 'accept') {
            $request->user()->notify(new \App\Notifications\TaskNotification([
                'title'        => 'Notification on Order #' . $order->order_id,
                'order_number' => $order->order_id,
                'greeting'     => 'Thank you for accepting our offer',
                'order_id'     => $order->id,
                'description'  => 'Thank you for accepting our offer , we look forward to assist you , please proceed with the payment.',
            ]));
        }
    }

    protected function ownOrFail(Offerorder $offer, Request $request)
    {
        $owns = Orders::where('id', $offer->order_id)->where('user_id', $request->user()->id)->exists();
        abort_unless($owns, 404); // 404, not 403: never confirm another user's resource exists
    }
}
