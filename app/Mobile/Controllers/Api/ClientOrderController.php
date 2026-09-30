<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * /api/mobile/v1 client order-action endpoints for the mobile client app.
 *
 * Mirrors what the frozen web client controllers do (tracking submission
 * and shipping/customs confirmation) as plain DB::table() column writes:
 * no Eloquent writes, no status-machine transitions, no notifications.
 * Every method first verifies the order actually belongs to the calling
 * client — route binding alone only guarantees the id exists.
 */
class ClientOrderController extends Controller
{
    /**
     * POST /api/mobile/v1/client/orders/{order}/tracking
     *
     * Client tracking submission (web equivalent: client tracking form):
     * writes trackingid / trackinglink on the order's own orderproducts
     * rows, then flags the order with tracking_status = 1 — the same flag
     * the frozen web tracking flow writes.
     */
    public function submitTracking(Request $request, Orders $order): JsonResponse
    {
        // (int) cast — prod PDO returns user_id as a string ("170") while
        // the auth user id is an int; strict !== 403'd the rightful owner.
        abort_if((int) $order->user_id !== (int) $request->user()->id, 403,
            'This order does not belong to your account.');

        $data = $request->validate([
            'products'                => 'required|array|min:1',
            'products.*.product_id'   => 'required|integer',
            'products.*.trackingid'   => 'nullable|string|max:255',
            'products.*.trackinglink' => 'nullable|string|max:500',
        ]);

        $updated = 0;

        foreach ($data['products'] as $product) {
            $update = [];
            if (isset($product['trackingid'])) {
                $update['trackingid'] = $product['trackingid'];
            }
            if (isset($product['trackinglink'])) {
                $update['trackinglink'] = $product['trackinglink'];
            }
            if ($update === []) {
                continue;
            }

            // Never touch products whose id does not belong to this order.
            $updated += DB::table('orderproducts')
                ->where('id', (int) $product['product_id'])
                ->where('order_id', $order->id)
                ->update($update);
        }

        DB::table('orders')->where('id', $order->id)->update([
            'tracking_status' => 1,
            // Legacy parity (web client tracking submit): once the client
            // sends their shipment tracking the order becomes "Order placed".
            'order_status'    => 'Order placed',
            'updated_at'      => now(),
        ]);

        return response()->json([
            'message' => 'Tracking submitted.',
            'updated' => $updated,
        ]);
    }

    /**
     * POST /api/mobile/v1/client/orders/{order}/confirmation
     *
     * Shipping/customs confirmation step (web equivalent: client
     * confirmation form): copies the submitted shipping address fields to
     * the ship_* columns, stores the customs category and the per-product
     * custom_weight / custom_value, and recomputes the order's customs
     * aggregates. Fixed flag writes: confirmation = 1, custom_status = 1,
     * active_tab = 4.
     */
    public function submitConfirmation(Request $request, Orders $order): JsonResponse
    {
        // (int) cast — same strict-comparison trap as submitTracking above.
        abort_if((int) $order->user_id !== (int) $request->user()->id, 403,
            'This order does not belong to your account.');

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'address1'   => 'required|string|max:255',
            'address2'   => 'nullable|string|max:255',
            'city'       => 'required|string|max:255',
            'state'      => 'nullable|string|max:255',
            'postalcode' => 'nullable|string|max:255',
            'country'    => 'nullable|string|max:255',
            'number'     => 'nullable|string|max:255',
            'category'   => 'required|string|max:100',

            'products'               => 'nullable|array',
            'products.*.product_id'  => 'required|integer',
            'products.*.weight'      => 'nullable|numeric|min:0',
            'products.*.value'       => 'nullable|numeric|min:0',
        ]);

        // request field => orders column; only non-null values are written.
        $columns = [
            'name'       => 'ship_name',
            'address1'   => 'ship_address1',
            'address2'   => 'ship_address2',
            'city'       => 'ship_city',
            'state'      => 'ship_state',
            'postalcode' => 'ship_postalcode',
            'country'    => 'ship_country',
            'number'     => 'ship_number',
            'category'   => 'custom_category',
        ];

        $update = [];
        foreach ($columns as $field => $column) {
            if (isset($data[$field])) {
                $update[$column] = $data[$field];
            }
        }

        $totalWeight = 0;
        $totalValue  = 0;

        foreach ($data['products'] ?? [] as $product) {
            $productUpdate = [];
            if (isset($product['weight'])) {
                $productUpdate['custom_weight'] = $product['weight'];
                $totalWeight += (float) $product['weight'];
            }
            if (isset($product['value'])) {
                $productUpdate['custom_value'] = $product['value'];
                $totalValue += (float) $product['value'];
            }
            if ($productUpdate === []) {
                continue;
            }

            // Never touch products whose id does not belong to this order.
            DB::table('orderproducts')
                ->where('id', (int) $product['product_id'])
                ->where('order_id', $order->id)
                ->update($productUpdate);
        }

        $update['confirmation']          = 1;
        $update['custom_status']         = 1;
        $update['active_tab']            = 4;
        $update['custom_total_quantity'] = DB::table('orderproducts')
            ->where('order_id', $order->id)
            ->sum('productquantity');
        $update['custom_total_weight'] = $totalWeight;
        $update['custom_total_value']  = $totalValue;
        $update['updated_at']          = now();

        DB::table('orders')->where('id', $order->id)->update($update);

        return response()->json(['message' => 'Confirmation sent.']);
    }
}
