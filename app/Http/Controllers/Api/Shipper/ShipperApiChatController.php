<?php

namespace App\Http\Controllers\Api\Shipper;

use App\Http\Controllers\Controller;
use App\Models\ShipperAdminChat;
use App\Models\ShipperOrderAssignment;
use App\Models\ShipperProfile;
use Illuminate\Http\Request;

class ShipperApiChatController extends Controller
{
    public function index(Request $request, $assignmentId)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $a = ShipperOrderAssignment::where('shipper_profile_id', $profile->id)
            ->findOrFail($assignmentId);

        // Mark admin messages as read when the shipper opens the thread.
        ShipperAdminChat::where('assignment_id', $a->id)
            ->where('from_type', 'admin')->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $messages = ShipperAdminChat::where('assignment_id', $a->id)
            ->orderBy('created_at')->limit(200)->get();

        return response()->json([
            'ok'   => true,
            'data' => [
                'messages' => $messages->map(fn ($m) => [
                    'id' => $m->id,
                    'from_type' => $m->from_type,
                    'message' => $m->message,
                    'created_at' => $m->created_at?->toIso8601String(),
                ]),
            ],
        ]);
    }

    public function send(Request $request, $assignmentId)
    {
        $profile = ShipperProfile::where('user_id', $request->user()->id)->firstOrFail();
        $a = ShipperOrderAssignment::where('shipper_profile_id', $profile->id)
            ->findOrFail($assignmentId);

        $data = $request->validate(['message' => 'required|string|max:3000']);

        $chat = ShipperAdminChat::create([
            'assignment_id' => $a->id,
            'order_id'      => $a->order_id,
            'from_type'     => 'shipper',
            'from_id'       => $request->user()->id,
            'message'       => $data['message'],
        ]);

        return response()->json(['ok' => true, 'message' => 'Sent.', 'data' => ['chat' => [
            'id' => $chat->id, 'from_type' => 'shipper', 'message' => $chat->message,
            'created_at' => $chat->created_at?->toIso8601String(),
        ]]]);
    }
}
