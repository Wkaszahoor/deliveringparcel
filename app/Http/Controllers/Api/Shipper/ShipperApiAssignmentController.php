<?php

namespace App\Http\Controllers\Api\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperAdminChat;
use App\Models\ShipperOrderAssignment;
use App\Models\ShipperProfile;
use App\Models\ShipperProof;
use App\Models\ShipperTrackingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShipperApiAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $assignments = ShipperOrderAssignment::with('request')
            ->where('shipper_profile_id', $profile->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15);

        return response()->json([
            'ok'   => true,
            'data' => [
                'assignments' => collect($assignments->items())->map(fn ($a) => [
                    'id' => $a->id, 'order_id' => $a->order_id, 'status' => $a->status,
                    'service_type' => $a->request?->service_type,
                    'fee' => (float) $a->shipper_fee,
                    'purchase_deadline' => $a->purchase_deadline?->toIso8601String(),
                    'created_at' => $a->created_at?->toIso8601String(),
                ]),
                'pagination' => ['current_page' => $assignments->currentPage(), 'last_page' => $assignments->lastPage()],
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $a = ShipperOrderAssignment::with(['request', 'proofs', 'deliveryAddress', 'trackingDetails'])
            ->where('shipper_profile_id', $profile->id)->findOrFail($id);

        // Opening the detail marks admin chat as read (same as the chat endpoint).
        ShipperAdminChat::where('assignment_id', $a->id)
            ->where('from_type', 'admin')->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        // Staged reveal: address only after admin forwarded it; wallet economics only own fee.
        $address = $a->deliveryAddress?->forwarded_to_shipper
            ? $a->deliveryAddress->maskedForForwardLevel() : null;

        return response()->json([
            'ok'   => true,
            'data' => [
                'assignment' => [
                    'id' => $a->id, 'order_id' => $a->order_id, 'status' => $a->status,
                    'service_type' => $a->request?->service_type,
                    'purchase_deadline' => $a->purchase_deadline?->toIso8601String(),
                ],
                'brief_text' => $a->request?->brief_text,
                'products'   => $a->request?->product_details ?? [],
                'proof_required' => in_array($a->status, ['package_received', 'proof_uploaded', 'purchased']),
                'proofs_uploaded' => $a->proofs->map(fn ($p) => [
                    'id' => $p->id, 'proof_type' => $p->proof_type,
                    'approved' => (bool) $p->admin_approved,
                ]),
                'delivery_address' => $address,
                'tracking' => $a->trackingDetails ? [
                    'carrier' => $a->trackingDetails->carrier,
                    'tracking_number' => $a->trackingDetails->tracking_number,
                    'shared_with_customer' => (bool) $a->trackingDetails->shared_with_customer,
                ] : null,
                'wallet_info' => [
                    'will_earn'   => (float) $a->wallet_credit_amount,
                    'held_amount' => (float) $a->wallet_hold_amount,
                ],
                'chat_unread_count' => (int) $a->adminChat()->where('from_type', 'admin')->where('is_read', false)->count(),
                'chat_messages' => $a->adminChat()->orderBy('created_at')->limit(100)->get()
                    ->map(fn ($m) => [
                        'id' => $m->id, 'from_type' => $m->from_type, 'message' => $m->message,
                        'created_at' => $m->created_at?->toIso8601String(),
                    ]),
                'next_action' => $this->nextAction($a),
                'errors' => (object) [],
            ],
        ]);
    }

    private function nextAction(ShipperOrderAssignment $a): array
    {
        return match ($a->status) {
            'assigned', 'accepted' => $a->request?->service_type === 'buy_for_me'
                ? ['label' => 'Purchase the item', 'action' => 'mark_purchased', 'deadline' => $a->purchase_deadline?->toIso8601String()]
                : ['label' => 'Confirm package received', 'action' => 'mark_package_received', 'deadline' => null],
            'purchased'            => ['label' => 'Wait for package / upload purchase receipt', 'action' => 'upload_proof', 'deadline' => null],
            'package_received', 'proof_uploaded' => ['label' => 'Upload proof photos', 'action' => 'upload_proof', 'deadline' => null],
            'proof_approved', 'address_received' => ['label' => 'Awaiting delivery address', 'action' => 'wait', 'deadline' => null],
            'address_forwarded'    => ['label' => 'Ship the package', 'action' => 'submit_tracking', 'deadline' => null],
            default                => ['label' => 'Nothing to do', 'action' => 'none', 'deadline' => null],
        };
    }

    public function uploadProof(Request $request, $id)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $a = ShipperOrderAssignment::where('shipper_profile_id', $profile->id)->findOrFail($id);

        $data = $request->validate([
            'proof_type' => 'required|in:item_received,before_repack,after_repack,dispatch_receipt,damage_report,purchase_receipt',
            'file'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->storeAs('shipper-proofs/' . $a->id, uniqid() . '.' . $file->getClientOriginalExtension(), 'local');

        ShipperProof::create([
            'assignment_id'      => $a->id,
            'shipper_profile_id' => $profile->id,
            'proof_type'         => $data['proof_type'],
            'file_path'          => $path,
            'mime_type'          => $file->getMimeType(),
            'file_size'          => $file->getSize(),
        ]);

        if (!in_array($a->status, ['proof_approved', 'address_received', 'address_forwarded', 'dispatched', 'tracking_added', 'tracking_shared', 'delivered', 'completed'])) {
            $a->update(['status' => 'proof_uploaded']);
        }

        return response()->json(['ok' => true, 'message' => 'Proof uploaded.']);
    }

    public function submitTracking(Request $request, $id)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $a = ShipperOrderAssignment::where('shipper_profile_id', $profile->id)->findOrFail($id);

        $data = $request->validate([
            'carrier'            => 'required|string|max:100',
            'tracking_number'    => 'required|string|max:200',
            'tracking_url'       => 'nullable|url|max:500',
            'ship_date'          => 'required|date',
            'estimated_delivery' => 'nullable|date',
        ]);

        ShipperTrackingDetail::updateOrCreate(
            ['assignment_id' => $a->id],
            $data + ['order_id' => $a->order_id]
        );
        $a->update(['status' => 'tracking_added', 'dispatched_at' => $a->dispatched_at ?? now()]);

        return response()->json(['ok' => true, 'message' => 'Tracking submitted.']);
    }

    public function markPurchased(Request $request, $id)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        ShipperOrderAssignment::where('shipper_profile_id', $profile->id)->findOrFail($id)
            ->update(['status' => 'purchased', 'purchased_at' => now()]);

        return response()->json(['ok' => true, 'message' => 'Marked as purchased.']);
    }

    public function markPackageReceived(Request $request, $id)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        ShipperOrderAssignment::where('shipper_profile_id', $profile->id)->findOrFail($id)
            ->update(['status' => 'package_received', 'package_received_at' => now()]);

        return response()->json(['ok' => true, 'message' => 'Package received.']);
    }
}
