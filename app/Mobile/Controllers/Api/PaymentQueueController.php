<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * /api/mobile/v1/payments — verification queue for the admin app.
 *
 * index() and streamProof() are READ-ONLY (queue list + secure receipt
 * streaming from the private proofs disk). verify() DELEGATES to the live
 * Api\Admin\OrderController@verifyPayment so PaymentService invariants,
 * order/payment status transitions and notifications stay in ONE place.
 */
class PaymentQueueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rows = DB::table('payments as p')
            ->join('orders as o', 'p.order_id', '=', 'o.id')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->whereIn('p.status', ['awaiting_payment', 'awaiting_verification'])
            ->orderByDesc('p.created_at')
            ->get([
                'p.id', 'p.order_id', 'p.amount', 'p.currency', 'p.gateway',
                'p.status', 'p.reference', 'p.proof_path',
                'o.order_id as order_display_id',
                'u.name as client_name',
                'p.created_at as submitted_at',
            ]);

        $data = $rows->map(function ($p) {
            return [
                'id'              => $p->id,
                'order_id'        => $p->order_id,
                'order_display_id'=> $p->order_display_id,
                'client_name'     => $p->client_name,
                'amount'          => (float) $p->amount,
                'currency'        => $p->currency ?: 'USD',
                'gateway'         => $p->gateway ?: 'bank',
                'status'          => $p->status,
                'reference'       => $p->reference,
                'has_proof'       => !empty($p->proof_path),
                // Streamed with the app's Bearer token — receipts stay private.
                'proof_url'       => !empty($p->proof_path)
                    ? url('/api/mobile/v1/payments/' . $p->id . '/proof')
                    : null,
                'submitted_at'    => $p->submitted_at,
                'hours_waiting'   => $p->submitted_at !== null
                    ? (int) \Illuminate\Support\Carbon::parse($p->submitted_at)->diffInHours(now())
                    : 0,
            ];
        })->values();

        return response()->json(['data' => $data, 'total' => $data->count()]);
    }

    /**
     * GET /payments/{payment}/proof — stream the receipt image (Sanctum-admin).
     *
     * Binary-safe: Storage::response() fpassthru's the raw bytes with a
     * Content-Length header. Content-Type is forced from the file's magic
     * bytes (Storage::mimeType sniffs content) with the extension as
     * fallback, so a proof stored WITHOUT an extension still arrives as
     * image/* and the app's blob→base64 viewer works. Missing/empty files
     * return JSON (the admin app always expects JSON on error, and abort()
     * could render an HTML page when Accept: image/* outranks application/json).
     */
    public function streamProof(Request $request, Payment $payment)
    {
        // Admin OR the owning client may view a receipt (attachments panel
        // shows payment proofs to the client too).
        $user = $request->user();
        $isOwner = false;
        if ($user && $payment->order_id) {
            $ownerId = \App\Models\Orders::query()->where('id', $payment->order_id)->value('user_id');
            $isOwner = $ownerId !== null && (int) $ownerId === (int) $user->id;
        }
        abort_if(!$user || ($user->type !== 'admin' && !$isOwner), 403);

        $resolved = $payment->proofDiskPath();
        if (!$resolved) {
            return response()->json(['message' => 'No proof uploaded for this payment.'], 404);
        }

        ['disk' => $disk, 'path' => $path] = $resolved;
        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            return response()->json(['message' => 'Proof file is missing from storage.'], 404);
        }

        if ((int) $storage->size($path) === 0) {
            // 0-byte proofs are corrupt uploads, not viewable images.
            return response()->json(['message' => 'Stored proof is empty (0 bytes) — ask the client to re-upload.'], 422);
        }

        $mime = $storage->mimeType($path);
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if (!is_string($mime) || $mime === '' || in_array($mime, ['application/x-empty', 'text/plain', 'text/x-asm'], true)) {
            $mime = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                'webp' => 'image/webp', 'gif' => 'image/gif', 'pdf' => 'application/pdf',
            ][$ext] ?? 'application/octet-stream';
        }

        return $storage->response($path, null, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /** POST /payments/{payment}/verify — action: approve|reject|mark_received. */
    public function verify(Request $request, Payment $payment): JsonResponse
    {
        return app(\App\Http\Controllers\Api\Admin\OrderController::class)
            ->verifyPayment($request, $payment);
    }
}
