<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $statuses = config('admin_orders.statuses', []);

            $counts = [];
            foreach ($statuses as $key => $cfg) {
                $label = $cfg['label'];
                $counts[$label] = Orders::where('order_status', $key === '' ? null : $key)->count();
            }

            $totalOrders   = Orders::count();
            $totalClients  = User::where('type', 'client')->count();

            // Sum only paid payments (Payment model has no order() relation — plain query only)
            $totalRevenue = (float) Payment::where('status', 'paid')->sum('amount');

            $unreadNotifs  = $request->user()->unreadNotifications()->count();

            // Get recent orders with client info (avoid eager load failure)
            $recentOrders = Orders::select('id', 'order_id', 'order_status', 'user_id', 'created_at')
                ->with('user:id,name')
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(fn($o) => [
                    'id'           => $o->id,
                    'order_id'     => $o->order_id,
                    'status'       => $o->order_status ?? '',
                    'status_label' => $statuses[$o->order_status ?? '']['label'] ?? 'Unknown',
                    'client'       => $o->user?->name,
                    'created'      => $o->created_at?->toDateTimeString(),
                ]);

            return response()->json([
                'counts'        => $counts,
                'total_orders'  => $totalOrders,
                'total_clients' => $totalClients,
                'total_revenue' => $totalRevenue,
                'unread_notifications' => $unreadNotifs,
                'recent_orders' => $recentOrders,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Dashboard API error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => $e->getMessage(),
                'counts' => [],
                'total_orders' => 0,
                'total_clients' => 0,
                'total_revenue' => 0,
                'unread_notifications' => 0,
                'recent_orders' => [],
            ], 500);
        }
    }
}
