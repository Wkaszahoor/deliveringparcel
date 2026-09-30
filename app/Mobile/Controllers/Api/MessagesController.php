<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/mobile/v1/messages/conversations — WhatsApp-style inbox shared
 * by BOTH mobile apps (role-aware): last message, unread count, urgency
 * hints, filters and live search.
 *
 * admin  : every order, unread = messages FROM the client, search by client
 *          name or order #, client name/initials/active-today extras.
 * client : own orders only, unread = messages FROM the admin side, search
 *          by order #, route subtitle instead of client identity.
 *
 * Read-only against the REAL schema: orders + order_chats + users + payments.
 * The chat sender is identified by order_chats.from matching orders.user_id
 * (the order's client) — there is no role column.
 */
class MessagesController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->type === 'admin';

        $filter = $request->get('filter', 'all');
        $search = trim((string) $request->get('search', ''));
        $perPage = 20;

        $labels = config('mobile.order_status_labels', []);

        // Mirror-image semantics per role:
        //   admin  → counterpart = the order's client    (from =  o.user_id)
        //   client → counterpart = the admin/other side (from != o.user_id)
        // (alias-agnostic — each subquery prefixes its own table alias)
        $fromCounterpart = $isAdmin ? 'from = o.user_id' : 'from != o.user_id';

        $q = DB::table('orders as o')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->whereNull('o.archived_at');

        // Clients are scoped to their own orders only.
        if (!$isAdmin) {
            $q->where('o.user_id', (int) $user->id);
        }

        $q->select([
                'o.id as id',
                'o.order_id as order_display_id',
                'o.order_status',
                'o.user_id',
                'u.name as client_name',
                'o.shipfrom',
                'o.shipto',
                DB::raw("(SELECT COUNT(*) FROM order_chats c WHERE c.order_id = o.id AND c.read = 0 AND c.{$fromCounterpart}) as unread_count"),
                DB::raw('(SELECT c2.body FROM order_chats c2 WHERE c2.order_id = o.id ORDER BY c2.id DESC LIMIT 1) as last_message'),
                DB::raw('(SELECT c2.image FROM order_chats c2 WHERE c2.order_id = o.id ORDER BY c2.id DESC LIMIT 1) as last_image'),
                DB::raw('(SELECT c2.created_at FROM order_chats c2 WHERE c2.order_id = o.id ORDER BY c2.id DESC LIMIT 1) as last_message_at'),
                DB::raw('(SELECT c3.from FROM order_chats c3 WHERE c3.order_id = o.id ORDER BY c3.id DESC LIMIT 1) as last_from'),
                DB::raw("(SELECT c4.created_at FROM order_chats c4 WHERE c4.order_id = o.id AND c4.{$fromCounterpart} ORDER BY c4.id DESC LIMIT 1) as last_counterpart_message_at"),
                DB::raw('(SELECT COUNT(*) FROM payments p WHERE p.order_id = o.id AND p.status IN ("awaiting_payment","awaiting_verification")) as pending_payments'),
            ]);

        if ($search !== '') {
            $q->where(function ($w) use ($search, $isAdmin) {
                $w->where('o.order_id', 'like', "%{$search}%");
                // Clients only ever search their own order numbers — name
                // search would leak other customers' names.
                if ($isAdmin) {
                    $w->orWhere('u.name', 'like', "%{$search}%");
                }
            });
        }

        // "active" = order not finished
        if ($filter === 'active') {
            $q->whereNotIn('o.order_status', ['completed', 'received']);
        }

        // unread / awaiting need HAVING over computed columns
        if ($filter === 'unread') {
            $q->havingRaw('unread_count > 0');
        } elseif ($filter === 'awaiting') {
            // "Awaiting MY reply" — the last message came from the other party.
            if ($isAdmin) {
                $q->havingRaw('last_from IS NOT NULL AND last_from = o.user_id');
            } else {
                $q->havingRaw('last_from IS NOT NULL AND last_from != o.user_id');
            }
        }

        $q->groupBy(
            'o.id', 'o.order_id', 'o.order_status', 'o.user_id', 'u.name', 'o.shipfrom', 'o.shipto'
        )
        ->orderByRaw('unread_count DESC, (last_message_at IS NULL), last_message_at DESC, o.id DESC');

        $page = $q->paginate($perPage);

        $data = collect($page->items())->map(function ($r) use ($labels, $isAdmin) {
            $lastFromClient = $r->last_from !== null && (int) $r->last_from === (int) $r->user_id;

            $row = [
                'id'                   => $r->id,
                'order_display_id'     => $r->order_display_id,
                'last_message'         => $r->last_message,
                'last_message_is_image' => $r->last_message === null && $r->last_image !== null,
                'last_message_at'      => $r->last_message_at,
                'last_sender_role'     => $r->last_from === null ? null : ($lastFromClient ? 'client' : 'admin'),
                'unread_count'         => (int) $r->unread_count,
                'order_status'         => $r->order_status,
                'order_status_label'   => $labels[$r->order_status ?? ''] ?? ($r->order_status ?: 'Request Placed'),
                'has_pending_payment'  => ((int) $r->pending_payments) > 0,
            ];

            if ($isAdmin) {
                // Initials from the first two words that start with a letter
                // (skips prefixes like "[TEST]").
                $initials = collect(explode(' ', trim((string) $r->client_name)))
                    ->filter(fn ($w) => preg_match('/^\p{L}/u', $w))->take(2)
                    ->map(fn ($w) => mb_substr($w, 0, 1))->implode('');

                // counterpart = the client, for the admin app
                $row['client_name']         = $r->client_name;
                $row['client_initials']     = strtoupper($initials ?: '?');
                $row['client_active_today'] = ($r->last_counterpart_message_at !== null
                    && \Illuminate\Support\Carbon::parse($r->last_counterpart_message_at)->isToday());
                $row['hours_since_client_message'] = $r->last_counterpart_message_at !== null
                    ? (int) \Illuminate\Support\Carbon::parse($r->last_counterpart_message_at)->diffInHours(now())
                    : null;
            } else {
                // counterpart = the admin side, for the client app
                $row['route']               = trim(($r->shipfrom ?: '—') . ' → ' . ($r->shipto ?: '—'));
                $row['last_admin_reply_at'] = $r->last_counterpart_message_at;
            }

            return $row;
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'total'        => $page->total(),
            ],
        ]);
    }
}
