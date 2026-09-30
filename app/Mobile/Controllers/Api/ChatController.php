<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * /api/mobile/v1/chat/admin/{order} — thin delegate to the LIVE
 * Api\ChatController (UploadGuard-validated image handling,
 * notification fan-out). Same rationale as OrderDetailController.
 */
class ChatController extends Controller
{
    public function index(Request $request, Orders $order): JsonResponse
    {
        return app(\App\Http\Controllers\Api\ChatController::class)->index($request, $order);
    }

    public function send(Request $request, Orders $order): JsonResponse
    {
        return app(\App\Http\Controllers\Api\ChatController::class)->store($request, $order);
    }
}
