<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Payment;
use App\Support\UploadGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * /api/mobile/v1 order-detail endpoints for the mobile admin app.
 *
 * DELIBERATE ARCHITECTURE CHOICE: these methods are thin delegates to
 * the LIVE controllers (App\Http\Controllers\Api\Admin\OrderController,
 * Api\ChatController) instead of raw DB::table() writes. The order
 * domain has hard-won invariants that live in those controllers only:
 *   - orders.order_status AND offerorders.offer_status must change in
 *     lockstep (two-column offer state machine)
 *   - payments flow through PaymentService/webhook recovery logic
 *   - chat uploads go through UploadGuard validation
 * Raw writes from a second copy of that logic previously desynced
 * statuses and broke flows. Delegating keeps ONE copy of the truth;
 * the mobile module still owns its routes, settings and tables.
 */
class OrderDetailController extends Controller
{
    protected function delegate()
    {
        return app(\App\Http\Controllers\Api\Admin\OrderController::class);
    }

    public function show(Request $request, Orders $order): JsonResponse
    {
        return $this->delegate()->show($order);
    }

    public function updateStatus(Request $request, Orders $order): JsonResponse
    {
        return $this->delegate()->updateStatus($request, $order);
    }

    public function updateDetails(Request $request, Orders $order): JsonResponse
    {
        return $this->delegate()->updateDetails($request, $order);
    }

    public function storeOffer(Request $request, Orders $order): JsonResponse
    {
        return $this->delegate()->storeOffer($request, $order);
    }

    public function fixOfferStatus(Request $request, Orders $order): JsonResponse
    {
        return $this->delegate()->fixOfferStatus($request, $order);
    }

    public function verifyPayment(Request $request, Payment $payment): JsonResponse
    {
        return $this->delegate()->verifyPayment($request, $payment);
    }

    /**
     * POST /api/mobile/v1/admin/orders/{order}/package-received
     *
     * Records the package-received step of the legacy Tab-3 flow
     * (web equivalent: OrdersController::image_upload): writes the
     * confirmation flag on the orders row and stores UploadGuard-validated
     * photos against the matching orderproducts rows. Multipart files
     * arrive as images[<orderproducts-id>].
     */
    public function packageReceived(Request $request, Orders $order): JsonResponse
    {
        $data = $request->validate([
            'confirmation'           => 'required|integer|in:0,1,2',
            // FormData multipart can deliver the list either as real array
            // entries or as one comma-joined string — accept both.
            'confirmed_services'     => 'nullable',
        ]);

        $confirmedServices = $data['confirmed_services'] ?? [];
        if (is_string($confirmedServices)) {
            $confirmedServices = array_values(array_filter(array_map('trim', explode(',', $confirmedServices))));
        }
        $confirmedServices = is_array($confirmedServices) ? $confirmedServices : [];

        DB::table('orders')->where('id', $order->id)->update([
            'confirmation' => (int) $data['confirmation'],
            // Legacy Tab-3 parity (web OrdersController confirmation step):
            // submitting the package-received photos moves the order to
            // "Confirm Shipment" so the client sees the next stage.
            'order_status' => ((int) $data['confirmation'] === 2) ? 'Confirm Shipment' : DB::raw('order_status'),
            'updated_at'   => now(),
        ]);

        $errors        = [];
        $imagesUpdated = 0;

        // images[<orderproducts-id>] => UploadedFile
        $files = $request->file('images');
        $files = is_array($files) ? $files : [];

        foreach ($files as $key => $file) {
            $productId = is_numeric($key) ? (int) $key : 0;
            if ($productId <= 0) {
                $errors[(string) $key] = 'Invalid product id key.';
                continue;
            }

            // Never touch products whose id does not belong to this order.
            $belongsToOrder = DB::table('orderproducts')
                ->where('id', $productId)
                ->where('order_id', $order->id)
                ->exists();
            if (! $belongsToOrder) {
                $errors[(string) $key] = "Product #{$productId} does not belong to this order.";
                continue;
            }

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                $errors[(string) $key] = 'Invalid or missing upload.';
                continue;
            }

            // Same guard the chat/product flows use; legacy web product
            // photos live in uploads/productsimages/.
            $path = UploadGuard::store($file, 'productsimages');
            if ($path === null) {
                $errors[(string) $key] = 'Image rejected (type or size not allowed).';
                continue;
            }

            DB::table('orderproducts')
                ->where('id', $productId)
                ->where('order_id', $order->id)
                ->update(['image' => $path]);
            $imagesUpdated++;
        }

        return response()->json([
            'message'            => 'Package received recorded.',
            'confirmation'       => (int) $data['confirmation'],
            'confirmed_services' => $confirmedServices,
            'images_updated'     => $imagesUpdated,
            'errors'             => $errors,
        ]);
    }

    /**
     * PUT /api/mobile/v1/admin/orders/{order}/tracking
     *
     * Dispatch-tracking step of the legacy Tab-3 flow (web equivalent:
     * OrdersController::order_tracking): plain column writes on orders —
     * companyname / trackinglink / trackingid (+ optional confirmation and
     * active_tab). No offer columns, no status-machine transitions. Only
     * provided, non-null columns are written.
     */
    public function updateTracking(Request $request, Orders $order): JsonResponse
    {
        $data = $request->validate([
            'companyname'  => 'nullable|string|max:100',
            'trackinglink' => 'nullable|string|max:500',
            'trackingid'   => 'nullable|string|max:255',
            'confirmation' => 'nullable|integer|in:0,1,2',
            'active_tab'   => 'nullable|integer|between:0,9',
        ]);

        $update = [];
        foreach (['companyname', 'trackinglink', 'trackingid', 'confirmation', 'active_tab'] as $column) {
            if (isset($data[$column])) {
                $update[$column] = $data[$column];
            }
        }

        // Legacy Tab-4 parity (web OrdersController::order_tracking): saving
        // the DISPATCH tracking moves the order to "Order processing".
        if ((int) ($data['active_tab'] ?? 0) === 4 || isset($data['trackingid'], $data['companyname'])) {
            $update['order_status'] = 'Order processing';
        }

        $update['updated_at'] = now();

        DB::table('orders')->where('id', $order->id)->update($update);

        return response()->json(['message' => 'Tracking updated.']);
    }
}
