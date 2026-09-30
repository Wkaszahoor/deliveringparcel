<?php

namespace App\Http\Controllers\Admin\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperAdminChat;
use App\Models\ShipperOrderAssignment;
use App\Models\ShipperProof;
use App\Services\Shipper\ShippingRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShipperAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $q = ShipperOrderAssignment::with(['shipper.user', 'order'])
            ->when($request->filled('status'), fn ($x) => $x->where('status', $request->status))
            ->when($request->filled('order_id'), fn ($x) => $x->where('order_id', (int) $request->order_id))
            ->when($request->filled('shipper'), function ($x) use ($request) {
                $x->whereHas('shipper.user', fn ($u) => $u->where('shipper_username', 'like', "%{$request->shipper}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('admin.shipper-assignments.index', ['assignments' => $q]);
    }

    public function show($id)
    {
        $a = ShipperOrderAssignment::with([
            'shipper.user', 'order', 'proofs', 'deliveryAddress', 'trackingDetails', 'rating',
        ])->withCount(['adminChat as unread_chat' => fn ($x) => $x->where('from_type', 'shipper')->where('is_read', false)])
            ->findOrFail($id);

        return view('admin.shipper-assignments.show', ['assignment' => $a]);
    }

    public function approveProof(Request $request, $id, $proofId)
    {
        $data = $request->validate(['customer_visible' => 'nullable|boolean']);
        $a     = ShipperOrderAssignment::findOrFail($id);
        $proof = ShipperProof::where('assignment_id', $a->id)->findOrFail($proofId);

        $proof->update([
            'admin_approved'     => true,
            'customer_visible'   => (bool) ($data['customer_visible'] ?? true),
            'admin_approved_at'  => now(),
            'admin_approved_by'  => auth()->id(),
        ]);

        if (!in_array($a->status, ['address_received', 'address_forwarded', 'dispatched', 'tracking_added', 'tracking_shared', 'delivered', 'completed'])) {
            $a->update(['status' => 'proof_approved']);
        }
        \DB::table('orders')->where('id', $a->order_id)->update(['shipper_proof_approved' => true]);

        return redirect()->back()->with('success', 'Proof approved' . (!empty($data['customer_visible']) ? ' and made visible to customer.' : '.'));
    }

    public function rejectProof(Request $request, $id, $proofId)
    {
        $data  = $request->validate(['note' => 'required|string|max:1000']);
        $a     = ShipperOrderAssignment::findOrFail($id);
        $proof = ShipperProof::where('assignment_id', $a->id)->findOrFail($proofId);

        $proof->update([
            'admin_approved' => false,
            'admin_notes'    => $data['note'],
        ]);

        return redirect()->back()->with('success', 'Proof rejected — shipper notified to re-upload.');
    }

    public function forwardAddress(Request $request, $id)
    {
        $data = $request->validate([
            'forward_level' => 'required|in:full,address_only,city_country',
        ]);
        $a = ShipperOrderAssignment::with('deliveryAddress')->findOrFail($id);
        $address = $a->deliveryAddress;

        if (!$address) {
            return redirect()->back()->with('error', 'Customer has not submitted a delivery address yet.');
        }

        $address->update([
            'admin_reviewed'       => true,
            'admin_reviewed_by'    => auth()->id(),
            'forwarded_to_shipper' => true,
            'forwarded_at'         => now(),
            'forwarded_by'         => auth()->id(),
            'forward_level'        => $data['forward_level'],
        ]);

        $a->update(['status' => 'address_forwarded']);
        \DB::table('orders')->where('id', $a->order_id)->update(['delivery_address_forwarded' => true]);

        // Shipper sees the masked address on their assignment page + chat message.
        $masked = $address->maskedForForwardLevel();
        ShipperAdminChat::create([
            'assignment_id' => $a->id,
            'from_type'     => 'admin',
            'from_id'       => auth()->id(),
            'message'       => "DELIVERY ADDRESS FORWARDED ({$data['forward_level']}):\n"
                . collect($masked)->map(fn ($v, $k) => ucfirst(str_replace('_', ' ', $k)) . ': ' . $v)->implode("\n"),
        ]);

        return redirect()->back()->with('success', 'Address forwarded to shipper.');
    }

    public function shareTracking($id, $trackingId)
    {
        $a = ShipperOrderAssignment::findOrFail($id);
        $t = \App\Models\ShipperTrackingDetail::where('assignment_id', $a->id)->findOrFail($trackingId);

        $t->update([
            'admin_reviewed'       => true,
            'shared_with_customer' => true,
            'shared_at'            => now(),
            'shared_by'            => auth()->id(),
        ]);

        $a->update(['status' => 'tracking_shared']);
        \DB::table('orders')->where('id', $a->order_id)->update(['shipper_tracking_shared' => true]);

        return redirect()->back()->with('success', 'Tracking shared with customer.');
    }

    public function releasePayment($id)
    {
        $a = ShipperOrderAssignment::whereIn('status', ['delivered', 'tracking_shared'])->findOrFail($id);
        app(ShippingRequestService::class)->releasePayment($a);

        return redirect()->back()->with('success', 'Payment released to shipper wallet.');
    }

    public function addNote(Request $request, $id)
    {
        $data = $request->validate(['admin_notes' => 'required|string|max:3000']);
        ShipperOrderAssignment::findOrFail($id)->update(['admin_notes' => $data['admin_notes']]);

        return redirect()->back()->with('success', 'Internal note saved.');
    }

    public function streamProof($proofId)
    {
        $proof = ShipperProof::findOrFail($proofId);
        if (!Storage::disk('local')->exists($proof->file_path)) {
            abort(404);
        }

        return response()->streamDownload(function () use ($proof) {
            echo Storage::disk('local')->get($proof->file_path);
        }, basename($proof->file_path), [
            'Content-Type'  => $proof->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'no-store',
        ]);
    }
}
