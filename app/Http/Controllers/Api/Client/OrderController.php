<?php
/* mobile-api-v4 — upload-verification marker (2026-08-27) */

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Orders::where('user_id', $request->user()->id)
            ->with(['offers', 'orderproducts'])
            ->orderByDesc('id');

        // Latest payment state per order — one correlated subquery per paginated row
        $q->addSelect([
            'payment_status' => \App\Models\Payment::select('status')
                ->whereColumn('order_id', 'orders.id')
                ->orderByDesc('id')
                ->limit(1),
        ]);

        if ($status = $request->input('status')) {
            $q->where('order_status', $status);
        }

        // Unified search box — reference + route
        if ($s = $request->input('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('order_id', 'like', "%{$s}%")
                  ->orWhere('shipfrom', 'like', "%{$s}%")
                  ->orWhere('shipto', 'like', "%{$s}%");
            });
        }

        // Payment-axis filter: awaiting_payment|awaiting_verification|processing|paid|failed
        if ($payment = $request->input('payment')) {
            $q->whereIn('orders.id', \App\Models\Payment::select('order_id')->where('status', $payment));
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        return response()->json($q->paginate($perPage));
    }

    /**
     * Client creates a new order request — mirrors Home2/WizardController@store
     * (sequential order_id, services→int columns, description→orderproducts row).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shipfrom'            => 'required|string|max:120',
            'shipto'              => 'required|string|max:120',
            'address'             => 'required|string|max:500',
            'postalcode'          => 'required|string|max:20',
            'approximate_weight'  => 'required|numeric|min:0.1|max:20000',
            // Old app builds send one text blob; new builds send products[]
            // rows (web-wizard parity). Exactly one of the two is required.
            'product_description' => 'nullable|string|min:5|max:2000',
            'products'            => 'nullable|array|max:50',
            'products.*.productname'  => 'required_with:products|string|max:255',
            'products.*.producturl'   => 'nullable|string|max:500',
            'products.*.productquantity' => 'nullable|integer|min:1|max:9999',
            'products.*.productprice'  => 'nullable|numeric|min:0',
            'product_photo'       => 'nullable|url|max:500',
            'product_services'    => 'nullable|array',
            'product_services.*'  => 'nullable|string|max:60',
            'notes'               => 'nullable|string|max:2000',
        ]);

        if (empty($data['product_description']) && empty($data['products'])) {
            return response()->json([
                'message' => 'The product description or at least one product row is required.',
                'errors'  => ['products' => ['Add at least one product.']],
            ], 422);
        }

        $next = (int) Orders::max(\DB::raw('CAST(order_id AS UNSIGNED)')) + 1;
        $svc  = array_flip($data['product_services'] ?? []);

        $order = Orders::create([
            'user_id'               => $request->user()->id,
            'order_id'              => $next,
            'shipfrom'              => $data['shipfrom'],
            'shipto'                => $data['shipto'],
            'address'               => $data['address'],
            'postalcode'            => $data['postalcode'],
            'approximate_weight'    => $data['approximate_weight'],
            // Real column keys win; short aliases kept for old app builds.
            'product_disinfection'  => (isset($svc['product_disinfection']) || isset($svc['disinfection'])) ? 1 : 0,
            'product_consolidation' => (isset($svc['product_consolidation']) || isset($svc['consolidation'])) ? 1 : 0,
            'product_customs'       => (isset($svc['product_customs']) || isset($svc['customs'])) ? 1 : 0,
            'product_check'         => isset($svc['product_check']) ? 1 : 0,
            'product_prohibited'    => isset($svc['product_prohibited']) ? 1 : 0,
            'product_purchase'      => isset($svc['product_purchase']) ? 1 : 0,
            'product_photo'         => 0,
            'order_status'          => 'pending',
            'confirmation'          => 0,
        ]);

        if (!empty($data['products'])) {
            // Web-wizard parity: one orderproducts row per item (name, url,
            // qty, price) so the admin sees a real product table.
            foreach ($data['products'] as $p) {
                \App\Models\Orderproducts::create([
                    'order_id'        => $order->id,
                    'productname'     => $p['productname'],
                    'producturl'      => $p['producturl'] ?? '',
                    'productquantity' => $p['productquantity'] ?? 1,
                    'productprice'    => $p['productprice'] ?? 0,
                    'productweight'   => $data['approximate_weight'],
                ]);
            }
        } else {
            // Legacy path: single blob → one row (unchanged behavior).
            $description = trim($data['product_description']);
            if (!empty($data['notes'])) {
                $description .= "\n\nNotes: " . $data['notes'];
            }
            if (!empty($data['product_photo'])) {
                $description .= "\nPhoto: " . $data['product_photo'];
            }
            \App\Models\Orderproducts::create([
                'order_id'        => $order->id,
                'productname'     => $description,
                'producturl'      => '',
                'productquantity' => 1,
                'productweight'   => $data['approximate_weight'],
            ]);
        }

        // Notify admins (mobile parity with web request flow)
        $admin = \App\Models\User::where('type', 'admin')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\TaskNotification([
                'title'       => 'Notification on Order #' . $order->order_id . ' from Shopper',
                'order_number' => $order->order_id,
                'greeting'    => ($request->user()->name ?: 'Customer') . ' placed a new order request',
                'order_id'    => $order->id,
                'description' => 'New request from the mobile app — review and place an offer.',
            ]));
        }

        return response()->json([
            'message' => 'Request #' . $order->order_id . ' submitted — we will send you an offer shortly.',
            'order'   => ['id' => $order->id, 'order_id' => $order->order_id],
        ], 201);
    }

    /**
     * Client confirms the parcel arrived — mirrors web OrdersController@order_received:
     * only completed orders can be confirmed, never regresses other states.
     */
    public function markReceived(Request $request, Orders $order): JsonResponse
    {
        // (int) cast: strict === breaks when PDO returns user_id as string.
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        if ($order->order_status !== 'completed') {
            return response()->json([
                'message' => 'Only completed orders can be confirmed as received.',
            ], 422);
        }

        $order->update(['order_status' => 'received']);

        // Notify admin (same notification the web flow sends)
        $admin = \App\Models\User::where('type', 'admin')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\TaskNotification([
                'title'       => 'Notification on Order #' . $order->order_id . ' from Shopper',
                'order_number' => $order->order_id,
                'greeting'    => ($request->user()->name ?: 'Customer') . ' confirmed the package was received',
                'order_id'    => $order->id,
                'description' => 'Order #' . $order->order_id . ' has been marked as received by the client.',
            ]));
        }

        return response()->json([
            'message' => 'Thank you! Parcel confirmed as received.',
            'order'   => ['id' => $order->id, 'order_status' => $order->order_status],
        ]);
    }

    public function show(Request $request, Orders $order): JsonResponse
    {
        // (int) cast: strict === breaks when PDO returns user_id as string.
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404,
            'Order not found or not accessible from this account.');

        // v4: each section loads independently — no more 500s if one relation breaks.
        $errors = [];

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
            $order->setRelation('payments', \App\Models\Payment::where('order_id', $order->id)
                ->orderByDesc('id')->get());
        } catch (\Throwable $e) { $errors['payments'] = $e->getMessage(); }

        // Experience review for THIS order (if any) — lets the app show the
        // "already reviewed" state instead of re-offering the ratings form.
        try {
            $order->setRelation('experience_review', \App\Models\Review::where('user_id', $request->user()->id)
                ->where('order_id', $order->id)
                ->where('review_type', 'experience')
                ->first(['id', 'rating', 'body', 'status', 'created_at']));
        } catch (\Throwable $e) { $errors['review'] = $e->getMessage(); }

        return response()->json([
            'order'  => $order,
            'errors' => $errors,
        ]);
    }

    /**
     * POST /api/client/orders/{order}/review
     *
     * Final step of the client flow: rate the experience after the parcel
     * was received. Creates a PENDING review for the admin moderation queue
     * (/admin/reviews) — nothing is published without approval.
     */
    public function submitReview(Request $request, Orders $order): JsonResponse
    {
        // (int) cast: strict === breaks when PDO returns user_id as string.
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404,
            'Order not found or not accessible from this account.');

        // Reviewable once delivered ('completed') or receipt-confirmed
        // ('received') — config RV-001 lists 'completed'; received is the
        // even-stronger terminal state after the client confirms.
        $eligible = array_merge(
            config('admin_reviews.eligible_order_statuses', ['completed']),
            ['received']
        );
        abort_unless(in_array($order->order_status, $eligible, true), 422,
            'You can leave a review after your parcel has been delivered.');

        $min = (int) config('admin_reviews.rating_min', 1);
        $max = (int) config('admin_reviews.rating_max', 5);

        $data = $request->validate([
            'rating' => 'required|integer|between:' . $min . ',' . $max,
            'body'   => 'nullable|string|max:2000',
            'title'  => 'nullable|string|max:120',
        ]);

        $existing = \App\Models\Review::where('user_id', $request->user()->id)
            ->where('order_id', $order->id)
            ->where('review_type', 'experience')
            ->exists();
        if ($existing) {
            return response()->json([
                'message' => 'You have already reviewed this order — thank you!',
            ], 200);
        }

        \App\Models\Review::create([
            'user_id'              => $request->user()->id,
            'order_id'             => $order->id,
            'review_type'          => 'experience',
            'rating'               => $data['rating'],
            'title'                => $data['title'] ?? null,
            'body'                 => $data['body'] ?? null,
            'status'               => 'pending',
            'is_verified_purchase' => true,
        ]);

        return response()->json([
            'message' => 'Thank you! Your review was submitted and will appear after moderation.',
        ], 201);
    }
}
