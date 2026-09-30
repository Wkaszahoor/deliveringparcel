<?php

namespace App\Http\Controllers\Admin\OrdersMgmt;

use App\Http\Controllers\Controller;
use App\Models\Offerorder;
use App\Models\Offerorderproducts;
use App\Models\Offerorderservices;
use App\Models\OrderChat;
use App\Models\Orders;
use App\Models\Orderproducts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Orders Management (new module — /admin/orders*).
 * Reads/writes the live `orders` table; every status write goes through
 * OrderStatusService::canTransition() (state machine in config/admin_orders.php).
 */
class OrdersController extends Controller
{
    /**
     * GET /admin/orders — list shell (rows load via /admin/orders/data).
     */
    public function index()
    {
        return view('admin.ordersm.index', [
            'statusOptions' => OrderStatusService::options(),
            'statusMap'     => OrderStatusService::all(),
        ]);
    }

    /**
     * GET /admin/orders/data — paginated JSON for DP.infiniteScroll.
     * Supports: ?page= ?q= ?status= ?sort= ?from= ?to= ?archived=
     */
    public function data(Request $request)
    {
        $query = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select([
                'orders.id',
                'orders.order_id',
                'orders.user_id',
                'users.name as user_name',
                'users.email as user_email',
                'orders.total',
                'orders.order_status',
                'orders.trackingid',
                'orders.trackinglink',
                'orders.companyname',
                'orders.archived_at',
                'orders.created_at',
                DB::raw('(SELECT COUNT(*) FROM orderproducts op WHERE op.order_id = orders.id) AS items_count'),
            ]);

        // --- search: order id, tracking no, user email
        if ($q = trim((string) $request->query('q', ''))) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                $w->where('orders.order_id', 'like', $like)
                    ->orWhere('orders.trackingid', 'like', $like)
                    ->orWhere('users.email', 'like', $like);
            });
        }

        // --- status filter ('' / 'none' means NULL / empty status in DB)
        $status = (string) $request->query('status', '');
        if ($status !== '') {
            if ($status === 'none') {
                $query->where(function ($w) {
                    $w->whereNull('orders.order_status')->orWhere('orders.order_status', '');
                });
            } elseif (array_key_exists($status, OrderStatusService::all())) {
                $query->where('orders.order_status', $status);
            }
        }

        // --- date range
        if ($from = $this->dateOrNull($request->query('from'))) {
            $query->whereDate('orders.created_at', '>=', $from);
        }
        if ($to = $this->dateOrNull($request->query('to'))) {
            $query->whereDate('orders.created_at', '<=', $to);
        }

        // --- archive filter: default = active only; 1 = archived only; all = both
        $archived = (string) $request->query('archived', '');
        if ($archived === '1') {
            $query->whereNotNull('orders.archived_at');
        } elseif ($archived === 'all') {
            // no filter
        } else {
            $query->whereNull('orders.archived_at');
        }

        // --- sorting (whitelist; `total` is varchar in DB so cast it)
        switch ((string) $request->query('sort', 'newest')) {
            case 'oldest':
                $query->orderBy('orders.created_at', 'asc')->orderBy('orders.id', 'asc');
                break;
            case 'amount_desc':
                $query->orderByRaw('CAST(orders.total AS UNSIGNED) DESC, orders.id DESC');
                break;
            case 'amount_asc':
                $query->orderByRaw('CAST(orders.total AS UNSIGNED) ASC, orders.id DESC');
                break;
            case 'status':
                $query->orderBy('orders.order_status', 'asc')->orderBy('orders.id', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('orders.created_at', 'desc')->orderBy('orders.id', 'desc');
                break;
        }

        $perPage = (int) config('admin_orders.per_page', 20);

        return response()->json($query->paginate($perPage)->toArray());
    }

    /**
     * GET /admin/orders/{id} — detail page.
     */
    public function show($id)
    {
        $order = Orders::findOrFail($id);

        $user         = User::find($order->user_id);
        $products     = Orderproducts::where('order_id', $order->id)
            ->orderBy('id', 'asc')->get();
        $offer        = Offerorder::where('order_id', $order->id)->first();
        // Collections, not [] — the name-matching loop below calls ->first()
        // on these even when the order has no offer yet (crashed /admin/orders/19).
        $offerProducts = collect();
        $offerServices = collect();
        if ($offer) {
            $offerProducts = Offerorderproducts::where('offer_id', $offer->id)->get();
            $offerServices = Offerorderservices::where('offer_id', $offer->id)->get();
        }

        // Key offer product rows to order products by NAME, not position, so
        // prefill survives products added/removed between offer rounds.
        $offerProductByProduct = [];
        $remaining = $offerProducts;
        foreach ($products as $product) {
            $match = $remaining->first(function ($row) use ($product) {
                return trim((string) $row->productname) === trim((string) $product->productname);
            });
            if ($match) {
                $offerProductByProduct[$product->id] = $match;
                $remaining = $remaining->reject(function ($row) use ($match) {
                    return $row->id === $match->id;
                });
            }
        }
        $chats = OrderChat::where('order_id', $order->id)
            ->leftJoin('users', 'users.id', '=', 'order_chats.from')
            ->select('order_chats.*', 'users.name as sender_name', 'users.type as sender_type')
            ->orderBy('order_chats.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.ordersm.show', [
            'order'         => $order,
            'user'          => $user,
            'products'      => $products,
            'offer'         => $offer,
            'offerProducts' => $offerProducts,
            'offerServices' => $offerServices,
            'chats'         => $chats,
            'statusMeta'    => OrderStatusService::meta($order->order_status),
            'nextStatuses'  => OrderStatusService::next($order->order_status),
            'timeline'      => OrderStatusService::timeline(),
            'statusMap'     => OrderStatusService::all(),

            // Payment-method negotiation card (2026-08-20)
            'openPayment'   => app(\App\Services\Payments\PaymentService::class)->findOpenPayment($order->id),
            'paymentMethods' => \App\Models\PaymentMethod::where('is_enabled', true)->orderBy('priority')->get(),
            'serviceContext' => app(\App\Services\Payments\PaymentService::class)->serviceContextForOrder($order),

            // Make-an-Offer form (legacy /order/{id} functional parity).
            // Service defaults mirror the legacy offer form; locked rows
            // match the legacy "checked readonly" fees. When revising a
            // pending offer, selections/prices pre-fill from stored rows.
            'countries'         => \App\Models\Country::orderBy('name')->pluck('name'),
            'shippingAddresses' => \App\Models\ShippingAddresses::orderBy('country')->orderBy('city')->get(),
            'offerForm'         => $this->offerFormState($order, $offer, $offerServices),
            'offerProductByProduct' => $offerProductByProduct,
        ]);
    }

    /**
     * PUT /admin/orders/{id}/status — single status change (state machine enforced).
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:100',
        ]);

        $order = Orders::findOrFail($id);

        if (!array_key_exists((string) $validated['status'], OrderStatusService::all())) {
            return response()->json(['ok' => false, 'message' => 'Unknown status "' . $validated['status'] . '".'], 422);
        }

        if (!OrderStatusService::canTransition($order->order_status, $validated['status'])) {
            return response()->json([
                'ok'      => false,
                'message' => 'Transition "' . OrderStatusService::label($order->order_status)
                    . '" → "' . OrderStatusService::label($validated['status']) . '" is not allowed.',
            ], 422);
        }

        $order->update(['order_status' => $validated['status']]);

        return response()->json([
            'ok'     => true,
            'status' => $validated['status'],
            'label'  => OrderStatusService::label($validated['status']),
            'badge'  => OrderStatusService::badge($validated['status']),
            'next'   => OrderStatusService::next($validated['status']),
            'message' => 'Order #' . $order->order_id . ' moved to "' . OrderStatusService::label($validated['status']) . '".',
        ]);
    }

    /**
     * POST /admin/orders/bulk-status — apply one status to many orders.
     * Only orders whose current status allows the transition are updated;
     * the rest are skipped and reported.
     */
    public function bulkStatus(Request $request)
    {
        $validated = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
            'status' => 'required|string|max:100',
        ]);

        if (!array_key_exists((string) $validated['status'], OrderStatusService::all())) {
            return response()->json(['ok' => false, 'message' => 'Unknown status "' . $validated['status'] . '".'], 422);
        }

        $applied = 0;
        $skipped = 0;
        $orders  = Orders::whereIn('id', $validated['ids'])->get();
        foreach ($orders as $order) {
            if (OrderStatusService::canTransition($order->order_status, $validated['status'])) {
                $order->update(['order_status' => $validated['status']]);
                $applied++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'ok'      => true,
            'applied' => $applied,
            'skipped' => $skipped,
            'message' => "Status applied to {$applied} order(s)" . ($skipped ? ", {$skipped} skipped (transition not allowed)" : '') . '.',
        ]);
    }

    /**
     * PUT /admin/orders/{id}/archive — toggle archived_at.
     */
    public function toggleArchive(Request $request, $id)
    {
        $order = Orders::findOrFail($id);

        if ($order->archived_at) {
            $order->update(['archived_at' => null]);

            return response()->json(['ok' => true, 'archived' => false, 'message' => 'Order #' . $order->order_id . ' restored from archive.']);
        }

        if (!OrderStatusService::isArchivable($order->order_status)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Only "' . OrderStatusService::label('completed') . '" and "' . OrderStatusService::label('Offer Rejected')
                    . '" orders can be archived (current: ' . OrderStatusService::label($order->order_status) . ').',
            ], 422);
        }

        $order->update(['archived_at' => now()]);

        return response()->json(['ok' => true, 'archived' => true, 'message' => 'Order #' . $order->order_id . ' archived.']);
    }

    /**
     * POST /admin/orders/bulk-archive — archive many orders at once.
     */
    public function bulkArchive(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $archived = 0;
        $skipped  = 0;
        $orders   = Orders::whereIn('id', $validated['ids'])->whereNull('archived_at')->get();
        foreach ($orders as $order) {
            if (OrderStatusService::isArchivable($order->order_status)) {
                $order->update(['archived_at' => now()]);
                $archived++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'ok'       => true,
            'archived' => $archived,
            'skipped'  => $skipped,
            'message'  => "{$archived} order(s) archived" . ($skipped ? ", {$skipped} skipped (not archivable)" : '') . '.',
        ]);
    }

    /**
     * PUT /admin/orders/{id}/tracking — update tracking id / link / carrier.
     */
    public function updateTracking(Request $request, $id)
    {
        $validated = $request->validate([
            'trackingid'   => 'nullable|string|max:255',
            'trackinglink' => 'nullable|string|max:255',
            'companyname'  => 'nullable|string|max:255',
        ]);

        $order = Orders::findOrFail($id);
        $order->update([
            'trackingid'   => $validated['trackingid'] ?? null,
            'trackinglink' => isset($validated['trackinglink']) ? \App\Support\LinkFormat::normalize($validated['trackinglink']) : null,
            'companyname'  => $validated['companyname'] ?? null,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Tracking updated for order #' . $order->order_id . '.',
        ]);
    }

    /**
     * POST /admin/orders/{id}/tracking — per-offer-product tracking links.
     *
     * Legacy parity with OrderOfferController@tracking_link: writes the same
     * offerorderproducts.trackingid / trackinglink columns keyed by row id,
     * flips orders.tracking_status and notifies the client (database-only
     * channel). Difference: rows are looked up scoped to the offer belonging
     * to THIS order — unknown row ids are ignored, not trusted.
     */
    public function storeTracking(Request $request, $id)
    {
        $order = Orders::findOrFail($id);

        $validated = $request->validate([
            'products'                => 'nullable|array|max:100',
            'products.*.id'           => 'required|integer',
            'products.*.trackingid'   => 'nullable|string|max:500',
            'products.*.trackinglink' => 'nullable|string|max:500',
        ]);

        $updated     = 0;
        $hasTracking = false;
        $offer       = Offerorder::where('order_id', $order->id)->first();
        if ($offer) {
            $rows = Offerorderproducts::where('offer_id', $offer->id)->get()->keyBy('id');
            foreach ((array) ($validated['products'] ?? []) as $row) {
                $product = $rows->get((int) $row['id']);
                if (!$product) {
                    continue; // row not on this order's offer — ignore, not trust
                }
                $trackingId   = trim((string) ($row['trackingid'] ?? '')) ?: null;
                $trackingLink = trim((string) ($row['trackinglink'] ?? '')) ?: null;
                $product->update([
                    'trackingid'   => $trackingId,
                    'trackinglink' => \App\Support\LinkFormat::normalize($trackingLink),
                ]);
                $updated++;
                if ($trackingId !== null) {
                    $hasTracking = true;
                }
            }
        }

        // Legacy flag: 1 once any tracking id exists, 0 when all are cleared.
        $order->update(['tracking_status' => $hasTracking ? 1 : 0]);

        // Database-only notification: order must never depend on SMTP.
        if ($updated && $order->user_id && ($user = User::find($order->user_id))) {
            try {
                $user->notifyNow(new \App\Notifications\TaskNotification([
                    'title'        => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
                    'order_number' => $order->order_id,
                    'greeting'     => 'Admin added tracking links',
                    'order_id'     => $order->id,
                    'description'  => '',
                ]), ['database']);
            } catch (\Throwable $e) {
                \Log::warning('Tracking notification not delivered: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Tracking updated for order #' . $order->order_id . " ({$updated} product row(s)).");
    }

    /**
     * POST /admin/orders/{id}/payment-method — negotiation tool (2026-08-20).
     *
     * Sets (or clears) the per-order forced payment method. When an open
     * payment attempt already exists it is ALSO switched to the chosen
     * method via PaymentService::changeMethod (audited, state-safe).
     * Empty method = clear the override, order returns to normal
     * service-context routing.
     */
    public function updatePaymentMethod(Request $request, $id)
    {
        $validated = $request->validate([
            'method' => 'nullable|string|max:30',
            'note'   => 'nullable|string|max:255',
        ]);

        $order  = Orders::findOrFail($id);
        $svc    = app(\App\Services\Payments\PaymentService::class);
        $code   = trim((string) ($validated['method'] ?? ''));
        $reason = trim((string) ($validated['note'] ?? '')) ?: 'admin negotiation override';

        if ($code === '') {
            $order->update(['forced_payment_method_code' => null]);

            return response()->json([
                'ok'      => true,
                'cleared' => true,
                'message' => 'Payment method override cleared for order #' . $order->order_id . ' — normal service routing applies.',
            ]);
        }

        // Method must exist and be enabled; keep order-level intent even
        // when the global service-context rules would not offer it (that is
        // the point of a per-order override during negotiation).
        $method = \App\Models\PaymentMethod::where('code', $code)->where('is_enabled', true)->first();
        if (!$method) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unknown or disabled payment method: ' . $code,
            ], 422);
        }

        $order->update(['forced_payment_method_code' => $method->code]);

        $switched = false;
        $payment  = $svc->findOpenPayment($order->id);
        if ($payment && $payment->payment_method_code !== $method->code) {
            try {
                $svc->changeMethod($payment, $method->code, $request->user(), $reason);
                $switched = true;
            } catch (\App\Services\Payments\PaymentException $e) {
                // Payment already locked in a gateway state — the override
                // stays recorded and applies to any future attempt.
                return response()->json([
                    'ok'      => true,
                    'switched'=> false,
                    'message' => 'Override saved for order #' . $order->order_id . ', but the open payment could not switch: ' . $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'ok'       => true,
            'switched' => $switched,
            'method'   => $method->code,
            'message'  => 'Payment method for order #' . $order->order_id . ' set to ' . $method->name
                . ($switched ? ' (open payment switched)' : ' (applies at checkout)') . '.',
        ]);
    }

    /**
     * POST /admin/orders/{id}/offer — Make an Offer / revise offer.
     *
     * Legacy parity with OrderOfferController@store (first offer) and
     * AdminordersController@update (re-offer): writes the same
     * offerorders / offerorderservices / offerorderproducts rows the
     * client tab and payment engine read. Difference: the total is
     * ALWAYS recomputed server-side from the submitted service and
     * product prices — client JS is display-only, never authoritative.
     */
    public function storeOffer(Request $request, $id)
    {
        $order = Orders::findOrFail($id);

        $validated = $request->validate([
            'description'        => 'required|string|max:5000',
            'shipingaddress'     => 'nullable|string|max:1000',
            'country'            => 'nullable|string|max:100',
            'services'           => 'nullable|array|max:50',
            'services.*.name'    => 'required_with:services|string|max:100',
            'services.*.value'   => 'required_with:services|numeric|min:0|max:999999',
            'services.*.checked' => 'nullable',
            'additional'         => 'nullable|array|max:50',
            'additional.*.name'  => 'nullable|string|max:100',
            'additional.*.value' => 'nullable|numeric|min:0|max:999999',
            'products'           => 'nullable|array|max:100',
            'products.*.id'      => 'required_with:products|integer',
            'products.*.price'   => 'required_with:products|numeric|min:0|max:999999',
            'products.*.spread'  => 'nullable|numeric|min:0|max:999999',
        ]);

        // --- Server-side pricing (legacy formula: line = price*qty+spread)
        $productTotal = 0.0;
        $productRows  = [];
        foreach ((array) ($validated['products'] ?? []) as $row) {
            $op = Orderproducts::where('order_id', $order->id)->find((int) $row['id']);
            if (!$op) {
                continue; // product not on this order — ignore, not trust
            }
            $price  = (float) $row['price'];
            $spread = (float) ($row['spread'] ?? 0);
            $line   = round($price * (float) $op->productquantity + $spread, 2);
            $productTotal += $line;
            $productRows[] = [
                'productname'    => $op->productname,
                'producturl'     => $op->producturl,
                'productquantity' => $op->productquantity,
                'productprice'   => $price,
                'productspread'  => $spread,
                'product_total'  => $line,
            ];
        }

        $serviceRows   = [];
        $servicesTotal = 0.0;
        foreach ((array) ($validated['services'] ?? []) as $row) {
            if (!empty($row['checked'])) {
                $serviceRows[]   = ['name' => $row['name'], 'value' => round((float) $row['value'], 2)];
                $servicesTotal  += (float) $row['value'];
            }
        }
        foreach ((array) ($validated['additional'] ?? []) as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $value          = round((float) ($row['value'] ?? 0), 2);
                $serviceRows[]  = ['name' => $name, 'value' => $value];
                $servicesTotal += $value;
            }
        }

        $total = round($productTotal + $servicesTotal, 2);

        // Resolve the datalist text back to a shipping_addresses row so the
        // offer keeps the legacy FK when the admin picked a known address.
        $addressText = trim((string) ($validated['shipingaddress'] ?? ''));
        $addressId = null;
        if ($addressText !== '' && $addressText !== '0') {
            foreach (\App\Models\ShippingAddresses::get() as $addr) {
                $composed = trim("{$addr->name} {$addr->address1} {$addr->address2} {$addr->city} {$addr->state} {$addr->postalcode} {$addr->country} {$addr->number}");
                if ($composed === $addressText) {
                    $addressId = $addr->id;
                    break;
                }
            }
        }

        $isNew = false;
        DB::transaction(function () use ($order, $validated, $productRows, $serviceRows, $productTotal, $total, $addressId, &$isNew) {
            $offer = Offerorder::where('order_id', $order->id)->first();
            if (!$offer) {
                $isNew = true;
                $offer = new Offerorder(['order_id' => $order->id]);
            }

            $offer->fill([
                'shipingaddress'  => (string) ($validated['shipingaddress'] ?? '') ?: '0',
                'total'           => $total,
                'product_total'   => $productTotal > 0 ? $productTotal : null,
                'description'     => $validated['description'],
                'offer_status'    => 0,
                'rejections_note' => null,
            ]);
            $offer->shippingaddress_id = $addressId;
            $offer->save();

            // Services are always replaced wholesale (legacy behaviour).
            Offerorderservices::where('offer_id', $offer->id)->delete();
            foreach ($serviceRows as $row) {
                $svc = new Offerorderservices([
                    'offer_id'     => $offer->id,
                    'servicename'  => $row['name'],
                    'servicevalue' => $row['value'],
                ]);
                $svc->save();
            }

            if ($productRows) {
                if ($isNew) {
                    foreach ($productRows as $row) {
                        $op = new Offerorderproducts($row + ['offer_id' => $offer->id]);
                        $op->save();
                    }
                } else {
                    // Re-offer: match rows by product name and consume matches,
                    // so a product added/removed between rounds cannot shift
                    // prices onto the wrong line. Rows keep tracking/image data
                    // (no wholesale replace).
                    $existing = Offerorderproducts::where('offer_id', $offer->id)->get()
                        ->keyBy(function ($row) {
                            return trim((string) $row->productname);
                        });
                    foreach ($productRows as $row) {
                        $key = trim((string) $row['productname']);
                        if (isset($existing[$key])) {
                            $existing[$key]->update($row);
                            $existing->forget($key);
                        } else {
                            $op = new Offerorderproducts($row + ['offer_id' => $offer->id]);
                            $op->save();
                        }
                    }
                }
            }

            // Same status strings the legacy endpoints wrote — they are valid
            // keys in the OrderStatusService state machine.
            $order->update([
                'order_status' => $isNew ? 'Offer Placed' : 'Offer Updated',
                'edit_offer'   => null,
                'total'        => $total,
                // Reset client page to step 1 so a re-offer after rejection
                // is actually visible (active_tab was stuck on 2).
                'active_tab'   => 1,
            ]);
        });

        // Database-only notification: order must never depend on SMTP.
        if ($order->user_id && ($user = User::find($order->user_id))) {
            try {
                $user->notifyNow(new \App\Notifications\TaskNotification([
                    'title'       => 'Notification on Order #' . $order->order_id . ' from Delivering Parcel',
                    'order_number' => $order->order_id,
                    'greeting'    => $isNew ? 'Admin placed an offer!' : 'Admin updated the offer',
                    'order_id'    => $order->id,
                    'description' => '',
                ]), ['database']);
            } catch (\Throwable $e) {
                \Log::warning('Offer notification not delivered: ' . $e->getMessage());
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Offer ' . ($isNew ? 'placed' : 'updated') . ' for order #' . $order->order_id
                . ' — total $' . number_format($total, 2) . ' (calculated server-side).',
            'total'   => $total,
            'status'  => $isNew ? 'Offer Placed' : 'Offer Updated',
        ]);
    }

    /**
     * Prefill state for the Make-an-Offer card, or null when the offer is
     * already accepted (form hidden). Kept here because this view must not
     * use multi-line @php blocks (they break the single-line @php( )
     * statements already present in the blade file).
     */
    private function offerFormState(Orders $order, $offer, $offerServices): ?array
    {
        if ($offer && (int) $offer->offer_status === 1) {
            return null;
        }

        $rows = [
            ['name' => 'Product photo',               'value' => '3',  'locked' => false],
            ['name' => 'Customs Declaration',         'value' => '4',  'locked' => false],
            ['name' => 'Content Check',               'value' => '3',  'locked' => false],
            ['name' => 'Removal of Prohibited Items', 'value' => '1',  'locked' => false],
            ['name' => 'Product Disinfection',        'value' => '1',  'locked' => false],
            ['name' => 'Package Consolidation',       'value' => '6',  'locked' => false],
            ['name' => 'Forwarding Service Fee',      'value' => '9',  'locked' => true],
            ['name' => 'shipping fee',                'value' => '0',  'locked' => false],
        ];
        if ($order->product_purchase) {
            $rows[] = ['name' => 'Purchase Assistance', 'value' => '10', 'locked' => true];
        }

        $selected = $offer ? $offerServices->pluck('servicevalue', 'servicename')->all() : [];
        $extra    = [];
        foreach ($selected as $name => $value) {
            if (! in_array($name, array_column($rows, 'name'), true)) {
                $extra[] = ['name' => $name, 'value' => $value];
            }
        }

        return [
            'rows'     => $rows,
            'selected' => $selected,
            'extra'    => $extra,
            'revise'   => (bool) $offer,
        ];
    }

    /**
     * Validate a Y-m-d date or return null.
     */
    private function dateOrNull($value): ?string
    {
        if (!$value || !is_string($value)) {
            return null;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $value);

        return $d && $d->format('Y-m-d') === $value ? $value : null;
    }

    /**
     * PM-016: verify a bank payment / manually mark it as received, straight
     * from the order page (works in the legacy dashboard and the new one).
     * approve=1 → paid (with or without an uploaded receipt — the admin
     * confirms the money on the bank statement); approve=0 → reject a
     * submitted receipt back to failed.
     */
    public function verifyPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'approve' => 'required|boolean',
            'note'    => 'nullable|string|max:1000',
        ]);

        $order = Orders::findOrFail($id);
        $svc   = app(\App\Services\Payments\PaymentService::class);

        $payment = \App\Models\Payment::query()
            ->where('order_id', $order->id)
            ->whereIn('status', [\App\Models\Payment::STATUS_AWAITING_PAYMENT, \App\Models\Payment::STATUS_AWAITING_VERIFICATION])
            ->orderByDesc('id')
            ->first();

        if (!$payment) {
            $msg = 'No bank payment is waiting on this order.';

            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        try {
            $payment = $validated['approve']
                ? $svc->markBankPaymentReceived($payment, $request->user(), $validated['note'] ?? null)
                : $svc->verifyBankPayment($payment, $request->user(), false, $validated['note'] ?? null);
        } catch (\App\Services\Payments\PaymentException $e) {
            $svc->logTech('error', 'order-page payment verify rejected: ' . ($e->technical ?? $e->getMessage()), ['payment' => $payment->reference]);

            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }

        $msg = $payment->status === \App\Models\Payment::STATUS_PAID
            ? 'Payment ' . $payment->reference . ' marked as received and verified. The order can now move to the next step.'
            : 'Receipt for ' . $payment->reference . ' rejected. The customer can start a new payment attempt.';

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'message' => $msg, 'status' => $payment->status])
            : back()->with('success', $msg);
    }
}
