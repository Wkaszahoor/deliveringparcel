<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * /api/mobile/v1/notifications — thin delegates to the LIVE
 * Api\Admin\NotificationController (Laravel notifications table — the same
 * store the web panel's "Send Notification" writes to). One source of truth;
 * grouping/filtering by date/type happens client-side in the app.
 */
class NotificationsController extends Controller
{
    protected function delegate()
    {
        return app(\App\Http\Controllers\Api\Admin\NotificationController::class);
    }

    public function index(Request $request): JsonResponse
    {
        return $this->delegate()->index($request);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        return $this->delegate()->markRead($request, $id);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        return $this->delegate()->markAllRead($request);
    }

    public function delete(Request $request, string $id): JsonResponse
    {
        return $this->delegate()->destroy($request, $id);
    }
}
