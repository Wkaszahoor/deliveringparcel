<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\ShipperDeliveryAddress;
use App\Models\ShipperOrderAssignment;
use App\Models\ShipperProof;
use App\Models\ShipperTrackingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Client-app endpoints for the shipper workflow: proofs, address, tracking. */
class ShipperClientApiController extends Controller
{
    public function proofs(Request $request, $orderId)
    {
        $order = $this->ownOrder($orderId);

        $proofs = ShipperProof::whereHas('assignment', fn ($q) => $q->where('order_id', $order->id))
            ->where('customer_visible', true)->get();

        return response()->json(['ok' => true, 'data' => ['proofs' => $proofs->map(fn ($p) => [
            'id' => $p->id,
            'proof_type' => $p->proof_type,
            'url' => url("/api/client/orders/{$order->id}/shipper-proofs/{$p->id}/file"),
        ])]]);
    }

    /** Authenticated binary stream — proofs live on the PRIVATE disk. */
    public function proofFile(Request $request, $orderId, $proofId)
    {
        $order = $this->ownOrder($orderId);
        $proof = ShipperProof::whereHas('assignment', fn ($q) => $q->where('order_id', $order->id))
            ->where('customer_visible', true)->findOrFail($proofId);

        if (!Storage::disk('local')->exists($proof->file_path)) {
            abort(404);
        }

        return response()->streamDownload(function () use ($proof) {
            echo Storage::disk('local')->get($proof->file_path);
        }, 'proof.' . pathinfo($proof->file_path, PATHINFO_EXTENSION), [
            'Content-Type'  => $proof->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function submitAddress(Request $request, $orderId)
    {
        $order = $this->ownOrder($orderId);

        if (!$order->shipper_proof_approved || $order->delivery_address_submitted) {
            return response()->json(['ok' => false, 'message' => 'Address submission not available for this order.'], 422);
        }

        $data = $request->validate([
            'recipient_name'        => 'required|string|max:200',
            'address_line_1'        => 'required|string|max:300',
            'address_line_2'        => 'nullable|string|max:300',
            'city'                  => 'required|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'postal_code'           => 'required|string|max:20',
            'country'               => 'required|string|max:100',
            'phone'                 => 'nullable|string|max:50',
            'delivery_instructions' => 'nullable|string|max:1000',
        ]);

        $assignment = ShipperOrderAssignment::where('order_id', $order->id)
            ->whereNotIn('status', ['cancelled'])->first();
        if (!$assignment) {
            return response()->json(['ok' => false, 'message' => 'No active shipper assignment.'], 422);
        }

        ShipperDeliveryAddress::create($data + [
            'order_id'             => $order->id,
            'assignment_id'        => $assignment->id,
            'submitted_by_user_id' => $request->user()->id,
        ]);
        $assignment->update(['status' => 'address_received']);
        \DB::table('orders')->where('id', $order->id)->update(['delivery_address_submitted' => true]);

        return response()->json(['ok' => true, 'message' => 'Address submitted.']);
    }

    public function tracking($orderId)
    {
        $order = $this->ownOrder($orderId);

        $t = ShipperTrackingDetail::whereHas('assignment', fn ($q) => $q->where('order_id', $order->id))
            ->where('shared_with_customer', true)->first();

        return response()->json(['ok' => true, 'data' => ['tracking' => $t ? [
            'carrier' => $t->carrier,
            'tracking_number' => $t->tracking_number,
            'tracking_url' => $t->tracking_url,
            'ship_date' => $t->ship_date?->toDateString(),
            'estimated_delivery' => $t->estimated_delivery?->toDateString(),
        ] : null]]);
    }

    /** Customer rates the shipper after delivery/completion. */
    public function rate(Request $request, $orderId)
    {
        $order = $this->ownOrder($orderId);

        $assignment = ShipperOrderAssignment::where('order_id', $order->id)
            ->whereIn('status', ['tracking_shared', 'delivered', 'completed'])->first();
        if (!$assignment) {
            return response()->json(['ok' => false, 'message' => 'This delivery cannot be rated yet.'], 422);
        }
        if ($assignment->rating()->exists()) {
            return response()->json(['ok' => false, 'message' => 'You already rated this delivery.'], 422);
        }

        $data = $request->validate([
            'overall_rating'      => 'required|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'speed_rating'        => 'nullable|integer|min:1|max:5',
            'value_rating'        => 'nullable|integer|min:1|max:5',
            'condition_rating'    => 'nullable|integer|min:1|max:5',
            'review_text'         => 'nullable|string|max:2000',
            'consent_testimonial' => 'required|in:no,anonymous,first_name_only,full_name',
        ]);

        $rating = \App\Models\ShipperRating::create($data + [
            'assignment_id'      => $assignment->id,
            'order_id'           => $order->id,
            'shipper_profile_id' => $assignment->shipper_profile_id,
            'rated_by_user_id'   => $request->user()->id,
        ]);

        // ShipperProfile boot recalculates rating + level progression automatically.
        return response()->json(['ok' => true, 'message' => 'Rating submitted.', 'data' => ['id' => $rating->id]]);
    }

    private function ownOrder($orderId): Orders
    {
        return Orders::where('id', (int) $orderId)
            ->where('user_id', auth()->id())->firstOrFail();
    }
}
