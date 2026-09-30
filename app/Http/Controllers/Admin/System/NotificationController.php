<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index');
    }

    public function data(Request $request)
    {
        $user = $request->user();
        $query = $user->notifications()->orderByDesc('created_at');

        if ($request->input('unread') === '1') {
            $query->whereNull('read_at');
        }

        $rows = $query->paginate((int) config('admin_notifications.per_page', 15))->through(function ($n) {
            return [
                'id'      => $n->id,
                'type'    => class_basename($n->type),
                'data'    => $n->data,
                'read'    => $n->read_at !== null,
                'read_at' => optional($n->read_at)->format('M d, Y H:i'),
                'at'      => optional($n->created_at)->format('M d, Y H:i'),
            ];
        });

        $rows->appends($request->only('unread'));

        return response()->json($rows->toArray());
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
    }

    public function markRead(Request $request, string $id)
    {
        $request->user()->unreadNotifications->where('id', $id)->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }

    /* ---------------- Preference matrix ---------------- */

    public function preferences(Request $request)
    {
        $types = (array) config('admin_notifications.types');
        $userId = $request->user()->id;

        $matrix = [];
        foreach ($types as $key => $label) {
            $matrix[$key] = [
                'label' => $label,
                'in_app' => NotificationPreference::isEnabled('in_app', $key, $userId),
                'email'  => NotificationPreference::isEnabled('email', $key, $userId),
            ];
        }

        return view('admin.notifications.preferences', ['matrix' => $matrix]);
    }

    public function updatePreference(Request $request)
    {
        $data = $request->validate([
            'channel'    => 'required|in:in_app,email',
            'type'       => 'required|string|max:60|in:' . implode(',', array_keys((array) config('admin_notifications.types'))),
            'is_enabled' => 'required|boolean',
        ]);

        NotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id, 'channel' => $data['channel'], 'type' => $data['type']],
            ['is_enabled' => (bool) $data['is_enabled']]
        );

        return response()->json(['ok' => true]);
    }
}
