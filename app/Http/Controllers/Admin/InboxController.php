<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderChat;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin inbox — readable Notifications & Messages pages, navbar dropdown
 * feed, chat reply, mark-all-read and duplicate-burst cleanup.
 *
 * The legacy navbar rendered EVERY unread notification into the dropdown
 * (tens of thousands of rows on production); everything here is capped,
 * paginated and COUNT-ed in SQL instead.
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

        $orderIds = $this->resolvedOrderIds($request);
        if ($orderIds !== null) {
            if (empty($orderIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $ids = implode(',', array_map('intval', $orderIds));
                // TaskNotification stores the order id in data.order_id,
                // Chatnotification stores it in data.id.
                $query->whereRaw(
                    "(JSON_UNQUOTE(JSON_EXTRACT(data, '$.order_id')) IN ({$ids})" .
                    " OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) IN ({$ids}))"
                );
            }
        }

        $notifications = $query->paginate(self::PAGE_SIZE)->appends($request->query());

        // Resolve the owning client for each order on this page so the table
        // can show a clickable Client column.
        $pageOrderIds = $notifications->getCollection()
            ->map(function ($n) {
                return $n->data['order_id'] ?? $n->data['id'] ?? null;
            })
            ->filter()->unique()->values();
        $orderRows    = $pageOrderIds->isEmpty() ? collect() : Orders::whereIn('id', $pageOrderIds)->get(['id', 'user_id']);
        $clientUsers  = User::whereIn('id', $orderRows->pluck('user_id')->unique())->get(['id', 'name', 'email'])->keyBy('id');
        $clientsByOrder = $orderRows->mapWithKeys(function ($o) use ($clientUsers) {
            return [$o->id => $clientUsers->get($o->user_id)];
        });

        return view('admin.inbox.notifications', [
            'notifications'  => $notifications,
            'clientsByOrder' => $clientsByOrder,
            'filters' => $request->only(['type', 'email', 'name', 'order', 'user_id']),
        ]);
    }

    public function messagesPage(Request $request)
    {
        $query = DB::table('order_chats as c')
            ->join('orders as o', 'o.id', '=', 'c.order_id')
            ->join('users as sender', 'sender.id', '=', 'c.from')
            ->join('users as client', 'client.id', '=', 'o.user_id')
            ->select(
                'c.id', 'c.order_id', 'c.from', 'c.body', 'c.image', 'c.read', 'c.created_at',
                'o.order_id as order_number',
                'client.id as client_id', 'client.name as client_name', 'client.email as client_email'
            )
            ->orderByDesc('c.created_at');

        if ($request->filled('email')) {
            $query->where('client.email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->filled('name')) {
            $query->where('client.name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('user_id')) {
            $query->where('o.user_id', (int) $request->input('user_id'));
        }
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

        return view('admin.inbox.messages', [
            'messages' => $messages,
            'filters' => $request->only(['email', 'name', 'order', 'user_id']),
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

        return view('admin.inbox._feed', ['items' => $items, 'type' => $type]);
    }

    /** Admin replies to a client from the Messages inbox page. */
    public function reply(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'message' => 'required|string|max:5000',
        ]);

        $order = Orders::findOrFail($validated['order_id']);

        if ($this->isRecentDuplicate($order->id, auth()->id(), $validated['message'])) {
            return redirect()->route('admin.inbox.messages')
                ->with('error', 'Duplicate reply blocked — the same message was already sent on this order in the last ' . self::DUPLICATE_WINDOW_SECONDS . ' seconds.');
        }

        OrderChat::create([
            'from' => auth()->id(),
            'order_id' => $order->id,
            'body' => $validated['message'],
        ]);

        $client = User::find($order->user_id);
        if ($client) {
            $client->notify(new \App\Notifications\Chatnotification([
                'title' => 'You have received new message from Admin on Order #' . $order->order_id,
                'greeting' => 'Admin',
                'body' => $validated['message'],
                'order_number' => $order->order_id,
                'id' => $order->id,
                'description' => '',
            ]));
        }

        return redirect()->route('admin.inbox.messages', ['order' => $order->order_id])
            ->with('success', 'Reply sent on order #' . $order->order_id . '.');
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

    /** Delete duplicate burst messages (keep the first of each). */
    public function dedupe(Request $request)
    {
        $deleted = DB::affectingStatement(self::dedupeSql());
        return back()->with('success', $deleted . ' duplicate message(s) deleted — kept the first of each burst.');
    }

    /** True when the exact same message was just written by the same user on the same order. */
    public static function isRecentDuplicate($orderId, $userId, $body)
    {
        return OrderChat::where('order_id', $orderId)
            ->where('from', $userId)
            ->where('body', $body)
            ->where('created_at', '>=', now()->subSeconds(self::DUPLICATE_WINDOW_SECONDS))
            ->exists();
    }

    /** Single-statement cleanup: identical text+order+sender within the window, keep lowest id. */
    public static function dedupeSql()
    {
        return "DELETE c1 FROM order_chats c1
                INNER JOIN order_chats c2
                    ON c1.order_id = c2.order_id
                   AND c1.`from` = c2.`from`
                   AND c1.body = c2.body
                   AND c2.created_at <= c1.created_at
                   AND c2.id < c1.id
                   AND TIMESTAMPDIFF(SECOND, c2.created_at, c1.created_at) <= " . self::DUPLICATE_WINDOW_SECONDS;
    }

    /**
     * Resolve email/name/order filters to a list of order ids for the
     * notifications page. Null = no filters given; empty array = filters
     * matched nothing.
     */
    private function resolvedOrderIds(Request $request)
    {
        if (! $request->filled('email') && ! $request->filled('name') && ! $request->filled('order') && ! $request->filled('user_id')) {
            return null;
        }

        $userIds = null;
        if ($request->filled('user_id')) {
            $userIds = collect([(int) $request->input('user_id')]);
        } elseif ($request->filled('email') || $request->filled('name')) {
            $userIds = User::query()
                ->when($request->filled('email'), function ($q) use ($request) {
                    $q->where('email', 'like', '%' . $request->input('email') . '%');
                })
                ->when($request->filled('name'), function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->input('name') . '%');
                })
                ->pluck('id');
            if ($userIds->isEmpty()) {
                return [];
            }
        }

        $orderQuery = Orders::query();
        if ($userIds !== null) {
            $orderQuery->whereIn('user_id', $userIds);
        }
        if ($request->filled('order')) {
            $order = trim($request->input('order'));
            $orderQuery->where(function ($w) use ($order) {
                $w->where('order_id', 'like', "%{$order}%");
                if (ctype_digit($order)) {
                    $w->orWhere('id', (int) $order);
                }
            });
        }

        return $orderQuery->pluck('id')->all();
    }
}
