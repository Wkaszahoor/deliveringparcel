<?php

namespace App\Http\Controllers\Admin\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperKycDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShipperKycController extends Controller
{
    public function index()
    {
        $docs = ShipperKycDocument::with('shipperProfile.user')
            ->where('status', 'pending')->orderBy('created_at')->get()
            ->groupBy('shipper_profile_id');

        return view('admin.shippers.pending-kyc', ['grouped' => $docs]);
    }

    public function show($shipperId)
    {
        $docs = ShipperKycDocument::with('shipperProfile.user')
            ->where('shipper_profile_id', $shipperId)->get();

        return view('admin.shippers.show', ['shipper' => $docs->first()?->shipperProfile, 'kycDocs' => $docs]);
    }

    public function approveDoc($docId)
    {
        ShipperKycDocument::findOrFail($docId)->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Document approved.');
    }

    public function rejectDoc(Request $request, $docId)
    {
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        ShipperKycDocument::findOrFail($docId)->update([
            'status'       => 'rejected',
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'review_notes' => $data['reason'],
        ]);

        return redirect()->back()->with('success', 'Document rejected.');
    }

    public function downloadDoc($docId)
    {
        $doc = ShipperKycDocument::findOrFail($docId);
        if (!Storage::disk('local')->exists($doc->file_path)) {
            abort(404);
        }

        return response()->streamDownload(function () use ($doc) {
            echo Storage::disk('local')->get($doc->file_path);
        }, $doc->original_filename ?: basename($doc->file_path), [
            'Content-Type'  => 'application/octet-stream',
            'Cache-Control' => 'no-store',
        ]);
    }
}
