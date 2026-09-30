<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\OrderChat;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Client inbox — the customer-side twin of the admin inbox: readable
 * Notifications & Messages pages, navbar dropdown feed, chat reply and
 * mark-all-read. Everything is scoped to the logged-in client's own
 * orders and notifications.
 */
class InboxController extends Controller
{
    const PAGE_SIZE = 25;
    const DROPDOWN_PAGE_SIZE = 10;
    const DUPLICATE_WINDOW_SECONDS = 15;

    public function notificationsPage(Request $request)
    {
        $query = auth()->user()->notifications()->orderByDesc('created_at');

        $type = $request->input('type');
        if (in_array($type, ['task', 'chat'])) {
            $query->where('type', $type === 'task'
                ? 'App\Notifications\TaskNotification'
                : 'App\Notifications\Chatnotification');
        }

        if ($request->filled('order')) {
            $order = trim($request->input('order'));
            $orderIds = Orders::query()
                ->where('user_id', auth()->id())
                ->where(function ($w) use ($order) {
                    $w->where('order_id', 'like', "%{$order}%");
                    if (ctype_digit($order)) {
                        $w->orWhere('id', (int) $order);
                    }
                })
                ->pluck('id');

            if ($orderIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $ids = implode(',', array_map('intval', $orderIds->all()));
                $query->whereRaw(
                    "(JSON_UNQUOTE(JSON_EXTRACT(data, '$.order_id')) IN ({$ids})" .
                    " OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) IN ({$ids}))"
                );
            }
        }

        $notifications = $query->paginate(self::PAGE_SIZE)->appends($request->query());

        return view('clients.inbox.notifications', [
            'notifications' => $notifications,
            'filters' => $request->only(['type', 'order']),
        ]);
    }

    public function messagesPage(Request $request)
    {
        $query = DB::table('order_chats as c')
            ->join('orders as o', 'o.id', '=', 'c.order_id')
            ->where('o.user_id', auth()->id())
            ->select(
                'c.id', 'c.order_id', 'c.from', 'c.body', 'c.image', 'c.read', 'c.created_at',
                'o.order_id as order_number'
            )
            ->orderByDesc('c.created_at');

        if ($request->filled('order')) {
            $order = trim($request->input('order'));
            $query->where(function ($w) use ($order) {
                $w->where('o.order_id', 'like', "%{$order}%");
                if (ctype_digit($order)) {
                    $w->orWhere('o.id', (int) $order);
                }
            });
        }

        $messages = $query->paginate(self::PAGE_SIZE)->appends($request->query());

        return view('clients.inbox.messages', [
            'messages' => $messages,
            'filters' => $request->only(['order']),
        ]);
    }

    /** Latest dropdown items (unread first) for the navbar scroll-loader. */
    public function feed(Request $request)
    {
        $type = $request->input('type') === 'chat' ? 'chat' : 'task';
        $offset = max(0, (int) $request->input('offset', 0));

        $items = auth()->user()->notifications()
            ->where('type', $type === 'chat'
                ? 'App\Notifications\Chatnotification'
                : 'App\Notifications\TaskNotification')
            ->orderByRaw('read_at IS NULL DESC, created_at DESC')
            ->skip($offset)
            ->take(self::DROPDOWN_PAGE_SIZE)
            ->get();

        return view('clients.inbox._feed', ['items' => $items, 'type' => $type]);
    }

    /** Client replies to admin from the Messages inbox page. */
    public function reply(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'message' => 'required|string|max:5000',
        ]);

        $order = Orders::where('id', $validated['order_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (\App\Http\Controllers\Admin\InboxController::isRecentDuplicate($order->id, auth()->id(), $validated['message'])) {
            return redirect()->route('client.inbox.messages')
                ->with('error', 'Duplicate message blocked — the same text was already sent in the last ' . self::DUPLICATE_WINDOW_SECONDS . ' seconds.');
        }

        OrderChat::create([
            'from' => auth()->id(),
            'order_id' => $order->id,
            'body' => $validated['message'],
        ]);

        // Notify the admin account, matching the legacy chat flow.
        $admin = User::where('type', 'admin')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\Chatnotification([
                'title' => 'You have received new message from ' . auth()->user()->name . ' on Order #' . $order->order_id,
                'greeting' => auth()->user()->name,
                'body' => $validated['message'],
                'order_number' => $order->order_id,
                'id' => $order->id,
                'description' => '',
            ]));
        }

        return redirect()->route('client.inbox.messages', ['order' => $order->order_id])
            ->with('success', 'Message sent on order #' . $order->order_id . '.');
    }

    public function markAllRead(Request $request)
    {
        $query = DB::table('notifications')
            ->where('notifiable_id', auth()->id())
            ->where('notifiable_type', 'App\Models\User')
            ->whereNull('read_at');

        if ($request->input('type') === 'chat') {
            $query->where('type', 'App\Notifications\Chatnotification');
        } elseif ($request->input('type') === 'task') {
            $query->where('type', 'App\Notifications\TaskNotification');
        }

        $count = $query->update(['read_at' => now()]);

        return back()->with('success', $count . ' notification(s) marked as read.');
    }
}
