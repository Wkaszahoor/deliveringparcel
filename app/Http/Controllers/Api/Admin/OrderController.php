<?php
/* mobile-api-v4 — upload-verification marker (2026-08-27) */

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offerorder;
use App\Models\Orders;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Orders::with('offers', 'user'); // Include user for client name/email

        // Latest payment state per order — one correlated subquery per paginated row
        $q->addSelect([
            'payment_status' => Payment::select('status')
                ->whereColumn('order_id', 'orders.id')
                ->orderByDesc('id')
                ->limit(1),
        ]);

        // Archived (soft-deleted) orders stay hidden unless explicitly asked for.
        if (!$request->boolean('include_archived')) {
            $q->whereNull('archived_at');
        }

        // Advanced search (mirrors web admin-orders filters)
        if ($id = $request->input('order_id')) {
            $q->where('order_id', 'like', "%{$id}%");
        }
        // Unified search box — searches reference, route and client identity
        if ($s = $request->input('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('order_id', 'like', "%{$s}%")
                  ->orWhere('shipfrom', 'like', "%{$s}%")
                  ->orWhere('shipto', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
            });
        }
        if ($name = $request->input('name')) {
            $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$name}%"));
        }
        if ($email = $request->input('email')) {
            $q->whereHas('user', fn($u) => $u->where('email', 'like', "%{$email}%"));
        }
        if ($from = $request->input('shipfrom')) {
            $q->where('shipfrom', 'like', "%{$from}%");
        }
        if ($to = $request->input('shipto')) {
            $q->where('shipto', 'like', "%{$to}%");
        }
        if (($status = $request->input('status')) !== null && $status !== '') {
            $q->where('order_status', $status);
        }

        // Payment-axis filter: awaiting_payment|awaiting_verification|processing|paid|failed|__none__
        if ($payment = $request->input('payment')) {
            if ($payment === '__none__') {
                $q->whereNotExists(fn($w) => $w->selectRaw(1)
                    ->from('payments')->whereColumn('payments.order_id', 'orders.id'));
            } else {
                $q->whereIn('orders.id', Payment::select('order_id')->where('status', $payment));
            }
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $orders  = $q->orderByDesc('id')->paginate($perPage);

        return response()->json($orders);
    }

    public function show(Orders $order): JsonResponse
    {
        // v4: every section loads independently — a broken relation degrades to an
        // error note instead of a 500, so the app always gets usable order data.
        $errors = [];

        try {
            $order->load('user');
        } catch (\Throwable $e) { $errors['user'] = $e->getMessage(); }

        try {
            $order->load(['offers' => fn($q) => $q->with(['offerorderservices', 'offerorderproducts'])->orderByDesc('id')]);
        } catch (\Throwable $e) { $errors['offers'] = $e->getMessage(); }

        try {
            $order->load(['order_chats' => fn($q) => $q->orderByDesc('id')->limit(50)]);
        } catch (\Throwable $e) { $errors['chats'] = $e->getMessage(); }

        try {
            $order->load('orderproducts');
        } catch (\Throwable $e) { $errors['products'] = $e->getMessage(); }

        try {
            $order->setRelation('payments', Payment::where('order_id', $order->id)
                ->orderByDesc('id')->get());
        } catch (\Throwable $e) { $errors['payments'] = $e->getMessage(); }

        return response()->json([
            'order'  => $order,
            'errors' => $errors,
        ]);
    }

    public function updateStatus(Request $request, Orders $order): JsonResponse
    {
        $data = $request->validate([
            'order_status' => 'required|string',
        ]);

        $newStatus = $data['order_status'];
        $statuses  = config('admin_orders.statuses', []);

        if (!isset($statuses[$newStatus])) {
            return response()->json(['message' => "Unknown status: {$newStatus}"], 422);
        }

        $current = $order->order_status ?? '';
        $allowed = $statuses[$current]['next'] ?? [];

        if (!in_array($newStatus, $allowed, true)) {
            return response()->json([
                'message' => "Cannot transition from \"{$current}\" to \"{$newStatus}\".",
            ], 422);
        }

        $order->update(['order_status' => $newStatus]);

        return response()->json([
            'message' => 'Status updated.',
            'order'   => ['id' => $order->id, 'order_status' => $order->fresh()->order_status],        ]);
    }

    public function updateDetails(Request $request, Orders $order): JsonResponse
    {
        $data = $request->validate([
            // companyname MUST be here or Courier Company edits are silently
            // dropped (validated fields only are written below).
            'companyname'    => 'nullable|string|max:100',
            'trackingid'    => 'nullable|string|max:255',
            // string (not url) — legacy rows can hold junk URLs and a strict
            // url rule made EVERY save fail once the form prefilled them.
            'trackinglink'  => 'nullable|string|max:500',
            'tracking_status' => 'nullable|string|max:255',
            'ship_name'     => 'nullable|string|max:255',
            'ship_address1' => 'nullable|string|max:255',
            'ship_address2' => 'nullable|string|max:255',
            'ship_city'     => 'nullable|string|max:255',
            'ship_state'    => 'nullable|string|max:255',
            'ship_postalcode' => 'nullable|string|max:50',
            'ship_country'  => 'nullable|string|max:100',
            'ship_number'   => 'nullable|string|max:100',
        ]);

        $order->update(array_filter($data, fn($v) => $v !== null));

        return response()->json(['message' => 'Details updated.', 'order' => $order->fresh()]);
    }

    public function storeOffer(Request $request, Orders $order): JsonResponse
    {
        $data = $request->validate([
            'total'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            // Address we give the customer to deliver their parcel to us
            'shipingaddress' => 'nullable|string|max:1000',

            // Fixed + custom services that feed the final price (web `addmore`/`more`)
            'services'         => 'nullable|array',
            'services.*.servicename'  => 'required_with:services|string|max:120',
            'services.*.servicevalue' => 'required_with:services|numeric|min:0',

            // Offer product lines (web `product[]`) — id present = update, absent = create
            'products'               => 'nullable|array',
            'products.*.id'          => 'nullable|integer',
            'products.*.productname' => 'required_with:products|string|max:255',
            'products.*.producturl'  => 'nullable|string|max:500',
            'products.*.productquantity' => 'required_with:products|numeric|min:1',
            'products.*.productprice'   => 'nullable|numeric|min:0',
            'products.*.productspread'  => 'nullable|numeric|min:0',
            'products.*.product_total' => 'nullable|numeric|min:0',
        ]);

        $offer = Offerorder::updateOrCreate(
            ['order_id' => $order->id],
            [
                'total'           => $data['total'],
                // offerorders.description is NOT NULL — older app builds may
                // omit it (ConvertEmptyStringsToNull turns '' into null).
                'description'     => trim((string) ($data['description'] ?? '')),
                'product_total'   => $data['product_total'] ?? 0,
                'offer_status'    => 0,
                'rejections_note' => null,
                'shipingaddress'  => trim((string) ($data['shipingaddress'] ?? '')),
            ]
        );

        // ── Services: replace set (web deletes then re-inserts) ──
        if (array_key_exists('services', $data)) {
            $offer->offerorderservices()->delete();
            foreach (($data['services'] ?? []) as $svc) {
                $offer->offerorderservices()->create([
                    'servicename'  => $svc['servicename'],
                    'servicevalue' => $svc['servicevalue'],
                ]);
            }
        }

        // ── Products: upsert by id, compute missing row totals ──
        $computed = 0;
        if (array_key_exists('products', $data)) {
            $keepIds = [];
            foreach (($data['products'] ?? []) as $p) {
                $rowTotal = $p['product_total'] ?? ((float)$p['productquantity'] * (float)($p['productprice'] ?? 0) + (float)($p['productspread'] ?? 0));
                $computed += (float)$rowTotal;

                $attrs = [
                    'productname'     => $p['productname'],
                    'producturl'      => $p['producturl'] ?? '',
                    'productquantity' => $p['productquantity'],
                    'productprice'    => $p['productprice'] ?? 0,
                    'productspread'   => $p['productspread'] ?? 0,
                    'product_total'   => $rowTotal,
                ];
                if (!empty($p['id'])) {
                    $offer->offerorderproducts()->whereKey($p['id'])->update($attrs);
                    $keepIds[] = $p['id'];
                } else {
                    $row = $offer->offerorderproducts()->create($attrs);
                    $keepIds[] = $row->id;
                }
            }
            // Drop lines the admin removed from the form
            $offer->offerorderproducts()->whereNotIn('id', $keepIds)->delete();
        } else {
            $computed = (float)$offer->offerorderproducts()->sum('product_total');
        }

        // Net product total = products + services (server-authoritative when not sent)
        $servicesSum = (float)$offer->offerorderservices()->sum('servicevalue');
        $net = $computed + $servicesSum;
        if (empty($data['product_total'])) {
            $offer->update(['product_total' => $net]);
        }

        // Re-offer → client must accept again: order back to step 1
        $order->update(['order_status' => 'Offer Placed', 'active_tab' => 1]);

        // Notify client
        $client = \App\Models\User::find($order->user_id);
        if ($client) {
            $client->notify(new \App\Notifications\TaskNotification([
                'title'       => 'New offer on Order #' . $order->order_id,
                'order_id'    => $order->id,
                'greeting'    => 'Admin has placed an offer for $' . number_format($data['total'], 2),
                'description' => 'Please review and accept or reject the offer.',
            ]));
        }

        return response()->json([
            'message' => 'Offer saved.',
            'offer'   => $offer->load(['offerorderservices', 'offerorderproducts']),
            'net_product_total' => $offer->fresh()->product_total,
        ]);
    }

    /**
     * One-click "un-stick" for a stuck order: the legacy web handlers
     * (offer_accept / offer_reject) only ever wrote the rejection note or
     * never wrote offer_status, leaving offerorders.offer_status out of sync
     * with orders.order_status. This endpoint re-aligns the offer row with
     * the order's current order_status so the views render the right branch.
     *
     * POST /admin/orders/{order}/fix-offer-status
     */
    public function fixOfferStatus(Request $request, Orders $order): JsonResponse
    {
        $map = [
            ''                   => 0, // Request Placed  → pending offer
            'Offer Placed'       => 0,
            'In process'         => 0,
            'Offer Updated'      => 0,
            'Offer Accepted'     => 1,
            'Offer Rejected'     => 2,
            'Order placed'       => 1,
            'Order Placed'       => 1,
            'Confirm Shipment'   => 1,
            'Ready To Ship'      => 1,
            'Order processing'   => 1,
            'Order Processing'   => 1,
            'completed'          => 1,
            'Shipped'            => 1,
            'received'           => 1,
        ];

        $key = (string) $order->order_status;
        if (!array_key_exists($key, $map)) {
            return response()->json([
                'message' => "Unknown order_status: '{$key}' — cannot auto-fix.",
            ], 422);
        }

        $offer = \App\Models\Offerorder::where('order_id', $order->id)
            ->orderByDesc('id')->first();
        if (!$offer) {
            return response()->json([
                'message' => 'No offer row exists for this order yet.',
            ], 422);
        }

        $newStatus = $map[$key];
        $offer->update(['offer_status' => $newStatus]);

        return response()->json([
            'message'        => "Offer status re-aligned to {$newStatus} (order_status='{$key}').",
            'order_id'       => $order->id,
            'order_status'   => $key,
            'offer_id'       => $offer->id,
            'offer_status'   => $newStatus,
        ]);
    }

    /**
     * Manual bank-payment verification (PM-016) — approve or reject a
     * payment that is awaiting manual verification, or mark it received
     * straight from a bank-statement reconciliation.
     */
    public function verifyPayment(Request $request, Payment $payment): JsonResponse
    {
        $data = $request->validate([
            'action' => 'required|in:approve,reject,mark_received',
            'note'   => 'nullable|string|max:500',
        ]);

        try {
            $svc = app(\App\Services\Payments\PaymentService::class);

            if ($data['action'] === 'mark_received') {
                $payment = $svc->markBankPaymentReceived($payment, $request->user(), $data['note'] ?? null);
            } else {
                $payment = $svc->verifyBankPayment($payment, $request->user(), $data['action'] === 'approve', $data['note'] ?? null);
            }

            return response()->json([
                'message' => 'Payment ' . ($data['action'] === 'reject' ? 'rejected.' : $payment->status . '.'),
                'payment' => [
                    'id'     => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                ],
            ]);
        } catch (\App\Services\Payments\PaymentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Verification failed: ' . $e->getMessage()], 422);
        }
    }
}
