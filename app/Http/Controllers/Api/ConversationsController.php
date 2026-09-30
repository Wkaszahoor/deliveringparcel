<?php
/* mobile-api-v5 — upload-verification marker (2026-08-28) */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderChat;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Real chat inbox: one row per order that has chat activity (or is recent),
 * with the last message preview and a per-order unread count.
 */
class ConversationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->type === 'admin';

        $perPage = min((int) $request->input('per_page', 15), 50);

        // Orders with chat activity first, then recent orders without any.
        $base = Orders::query()
            ->select(['id', 'order_id', 'user_id', 'shipfrom', 'shipto', 'order_status', 'created_at'])
            ->with('user:id,name,email');

        if (!$isAdmin) {
            $base->where('user_id', $user->id);
        }

        $orders = $base
            ->orderByRaw(
                '(SELECT MAX(id) FROM order_chats WHERE order_chats.order_id = orders.id) IS NULL, ' .
                '(SELECT MAX(id) FROM order_chats WHERE order_chats.order_id = orders.id) DESC, ' .
                'orders.id DESC'
            )
            ->paginate($perPage);

        $rows = $base->newQuery() // fresh builder for the hydration below
            ->select(['id', 'order_id', 'user_id', 'shipfrom', 'shipto', 'order_status', 'created_at'])
            ->with('user:id,name,email');

        // Re-run for the page items only (avoid N+1 with 2 aggregate queries)
        $ids = $orders->getCollection()->pluck('id')->all();
        $rows = $rows->whereIn('orders.id', $ids)->get()->keyBy('id');

        $lastChats = OrderChat::whereIn('order_id', $ids)
            ->orderByDesc('id')
            ->get()
            ->groupBy('order_id')
            ->map(fn($g) => $g->sortByDesc('id')->values());

        // Unread = messages from the OTHER party still unread (read=0)
        $unread = OrderChat::whereIn('order_id', $ids)
            ->where('from', '!=', $user->id)
            ->where('read', 0)
            ->get()
            ->groupBy('order_id')
            ->map(fn($g) => $g->count());

        $data = $orders->getCollection()->map(function ($o) use ($rows, $lastChats, $unread, $isAdmin) {
            $chats = $lastChats->get($o->id);
            $last = $chats?->first();
            return [
                'id'           => $o->id,
                'order_number' => $o->order_id,
                'shipfrom'     => $o->shipfrom,
                'shipto'       => $o->shipto,
                'order_status' => $o->order_status,
                'created_at'   => $o->created_at?->toDateTimeString(),
                'client'       => $isAdmin && $o->user ? [
                    'name'  => $o->user->name,
                    'email' => $o->user->email,
                ] : null,
                'last_chat'    => $last ? [
                    'body'       => $last->body,
                    'image'      => $last->image,
                    'from'       => $last->from,
                    'created_at' => $last->created_at?->toDateTimeString(),
                ] : null,
                'unread'       => $unread->get($o->id, 0),
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
            'total'        => $orders->total(),
        ]);
    }
}
