<?php

namespace App\Mobile\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Order archive (soft delete) — hides dead/test orders from the admin
 * list + mobile admin app without touching any order data. Restore is
 * always available from the "show archived" view.
 *
 * Routes (routes/mobile_web.php):
 *   POST /admin/order-archive/{order}          → archive
 *   POST /admin/order-archive/{order}/restore  → restore
 *   POST /admin/order-archive/bulk             → archive many by id[]
 */
class OrderArchiveController extends Controller
{
    public function archive(Request $request, Orders $order)
    {
        DB::table('orders')->where('id', $order->id)->update([
            'archived_at' => now(),
            'archived_by' => $request->user()->id,
            'updated_at'  => now(),
        ]);

        return $this->done($request, "Order #{$order->order_id} archived.");
    }

    public function restore(Request $request, Orders $order)
    {
        DB::table('orders')->where('id', $order->id)->update([
            'archived_at' => null,
            'archived_by' => null,
            'updated_at'  => now(),
        ]);

        return $this->done($request, "Order #{$order->order_id} restored.");
    }

    public function bulk(Request $request)
    {
        $ids = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ])['ids'];

        $count = DB::table('orders')
            ->whereIn('id', $ids)
            ->whereNull('archived_at')
            ->update([
                'archived_at' => now(),
                'archived_by' => $request->user()->id,
                'updated_at'  => now(),
            ]);

        return $this->done($request, "{$count} order(s) archived.");
    }

    private function done(Request $request, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()
            ->to($request->input('_return', url('admin-orders')))
            ->with('success', $message);
    }
}
