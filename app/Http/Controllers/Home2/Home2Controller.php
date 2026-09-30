<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\Claim;
use App\Models\Offerorder;
use App\Models\Offerorderproducts;
use App\Models\Offerorderservices;
use App\Models\OrderChat;
use App\Models\Orderproducts;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\ShippingAddresses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Home2Controller extends Controller
{
    /* ================= PUBLIC ================= */

    public function trackForm()
    {
        return view('home2.track');
    }

    /**
     * Public tracking: requires BOTH the order reference AND the email on file.
     * Only ever exposes status-level information.
     */
    public function track(Request $request)
    {
        $data = $request->validate([
            'ref'    => 'required|string|max:100',
            'email'  => 'required|email|max:255',
        ]);

        $order = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->where(function ($w) use ($data) {
                $w->where('o.order_id', trim($data['ref']))->orWhere('o.trackingid', trim($data['ref']));
            })
            ->where('u.email', trim($data['email']))
            ->select('o.order_id', 'o.order_status', 'o.trackingid', 'o.trackinglink', 'o.companyname', 'o.created_at')
            ->first();

        return view('home2.track', [
            'ref'     => $data['ref'],
            'email'   => $data['email'],
            'result'  => $order,
            'searched' => true,
        ]);
    }

    /* ================= AUTHENTICATED CUSTOMER ================= */

    public function dashboard()
    {
        $user = auth()->user();

        return view('home2.dashboard', [
            'counts' => [
                'orders'     => DB::table('orders')->where('user_id', $user->id)->count(),
                'returns'    => ReturnRequest::where('user_id', $user->id)->count(),
                'claims'     => Claim::where('user_id', $user->id)->count(),
                'quotes'     => DB::table('request_quotes')->where('email', $user->email)->count(),
                'unread'     => $user->unreadNotifications()->count(),
            ],
            'walletBalance' => app(\App\Services\Payments\WalletService::class)->balanceFor($user),
            'walletEnabled' => app(\App\Services\Payments\WalletService::class)->enabled(),
        ]);
    }

    public function myReturns(Request $request)
    {
        $returns = ReturnRequest::where('user_id', auth()->id())
            ->orderByDesc('created_at')->paginate(dp_per_page($request));

        return view('home2.returns', ['returns' => $returns, 'statusMap' => config('admin_quotes.return_statuses')]);
    }

    public function myClaims(Request $request)
    {
        $claims = Claim::where('user_id', auth()->id())
            ->orderByDesc('created_at')->paginate(dp_per_page($request));

        return view('home2.claims', ['claims' => $claims, 'statusMap' => config('admin_quotes.claim_statuses')]);
    }

    public function myQuotes(Request $request)
    {
        $quotes = DB::table('request_quotes')
            ->where('email', auth()->user()->email)
            ->orderByDesc('created_at')->paginate(dp_per_page($request));

        return view('home2.quotes', ['quotes' => $quotes]);
    }

    /**
     * M1 — customer order detail. Server-side ownership check: only the
     * owner ever reaches the page (404 otherwise).
     */
    public function orderShow(Request $request, $id)
    {
        $order = Orders::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $products      = Orderproducts::where('order_id', $order->id)->orderBy('id')->get();
        $offers        = Offerorder::where('order_id', $order->id)->orderByDesc('id')->get();
        $latestOffer   = $offers->first();
        $offerServices = $latestOffer ? Offerorderservices::where('offer_id', $latestOffer->id)->orderBy('id')->get() : collect();
        $offerProducts = $latestOffer ? Offerorderproducts::where('offer_id', $latestOffer->id)->orderBy('id')->get() : collect();
        $shippingAddress = ($latestOffer && $latestOffer->shippingaddress_id)
            ? ShippingAddresses::find($latestOffer->shippingaddress_id)
            : null;

        $latestPayment = Payment::where('order_id', $order->id)->orderByDesc('id')->first();
        $chat = OrderChat::where('order_id', $order->id)->orderBy('id')->get();
        $countries = \App\Models\Country::orderBy('name')->pluck('name');

        /* Payable = offer accepted and no payment currently in a locked state. */
        $blocked = $latestPayment && in_array($latestPayment->status, [
            Payment::STATUS_PAID, Payment::STATUS_AWAITING_VERIFICATION,
            Payment::STATUS_PROCESSING, Payment::STATUS_REFUNDED,
            Payment::STATUS_PARTIALLY_REFUNDED,
        ], true);
        $payable = $latestOffer && (int) $latestOffer->offer_status === 1 && !$blocked;

        return view('home2.orders.show', [
            'order'           => $order,
            'products'        => $products,
            'latestOffer'     => $latestOffer,
            'offerServices'   => $offerServices,
            'offerProducts'   => $offerProducts,
            'shippingAddress' => $shippingAddress,
            'latestPayment'   => $latestPayment,
            'chat'            => $chat,
            'countries'       => $countries,
            'payable'         => $payable,
        ]);
    }

    /** M7 — chat transcript as JSON for the new order page (legacy chat POST returns full HTML). */
    public function orderChat(Request $request, $id)
    {
        $order = Orders::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $messages = OrderChat::where('order_id', $order->id)->orderBy('id')->get()->map(fn ($m) => [
            'id'         => $m->id,
            'mine'       => (int) $m->from === (int) auth()->id(),
            'body'       => $m->body,
            'image'      => $m->image ? asset('uploads/chatimages/' . $m->image) : null,
            'created_at' => optional($m->created_at)->format('M d, H:i'),
        ]);

        return response()->json(['messages' => $messages]);
    }

    /** M7 — send a chat message from the new order page (from is always the session user). */
    public function orderChatSend(Request $request, $id)
    {
        $data = $request->validate(['message' => 'required|string|max:2000']);

        $order = Orders::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        OrderChat::create([
            'from'     => auth()->id(),
            'order_id' => $order->id,
            'body'     => $data['message'],
        ]);

        $admin = User::where('type', 'admin')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\Chatnotification([
                'title'        => 'You have received new message from ' . auth()->user()->name . ' on Order #' . $order->order_id,
                'greeting'     => auth()->user()->name,
                'body'         => $data['message'],
                'order_number' => $order->order_id,
                'id'           => $order->id,
                'description'  => '',
            ]));
        }

        return response()->json(['ok' => true]);
    }

    public function myNotifications(Request $request)
    {
        $notifications = auth()->user()->notifications()->orderByDesc('created_at')->paginate(dp_per_page($request));

        return view('home2.notifications', ['notifications' => $notifications]);
    }

    public function markNotificationsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->route('home2.notifications');
    }
}
