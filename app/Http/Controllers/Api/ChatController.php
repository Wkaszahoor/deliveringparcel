<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderChat;
use App\Models\Orders;
use App\Support\UploadGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request, Orders $order): JsonResponse
    {
        $this->authorizeAccess($request, $order);

        $perPage = min((int) $request->input('per_page', 50), 100);

        $messages = $order->order_chats()
            ->orderByDesc('id')
            ->paginate($perPage);

        // Match legacy web behavior: fetching the thread marks the OTHER
        // party's messages read (read=1) and consumed (notification=1).
        // Without this, mobile unread indicators never clear.
        $order->order_chats()
            ->where('from', '!=', $request->user()->id)
            ->where(function ($q) {
                $q->where('read', 0)->orWhere('notification', 0);
            })
            ->update(['read' => 1, 'notification' => 1]);

        return response()->json($messages);
    }

    public function store(Request $request, Orders $order): JsonResponse
    {
        $this->authorizeAccess($request, $order);

        $data = $request->validate([
            'body'  => 'required_without:image|string|max:2000',
            'image' => 'nullable|file|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = UploadGuard::store($request->file('image'), 'chatimages');
            if ($imagePath === null) {
                return response()->json(['message' => 'Invalid image file.'], 422);
            }
        }

        $chat = OrderChat::create([
            'from'     => $request->user()->id,
            'body'     => $data['body'] ?? null,
            'order_id' => $order->id,
            'image'    => $imagePath,
            'read'     => 0,
        ]);

        return response()->json(['message' => $chat], 201);
    }

    private function authorizeAccess(Request $request, Orders $order): void
    {
        $user = $request->user();
        if ($user->type === 'admin') return;

        // (int) cast: strict === breaks when PDO returns user_id as string.
        abort_unless((int) $order->user_id === (int) $user->id, 404);
    }
}
