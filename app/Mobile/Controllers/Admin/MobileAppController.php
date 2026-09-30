<?php

namespace App\Mobile\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mobile\Models\AppScreenSetting;
use App\Mobile\Models\MobileFeatureFlag;
use App\Mobile\Models\MobileLabel;
use App\Mobile\Models\NotificationLog;

/**
 * Mobile App Management — dashboard / entry point.
 * GET /admin/mobile-app → mobile.admin.dashboard
 */
class MobileAppController extends Controller
{
    public function index()
    {
        $stats = [
            'screens'       => AppScreenSetting::count(),
            'labels'        => MobileLabel::count(),
            'flags'         => MobileFeatureFlag::count(),
            'notifications' => NotificationLog::count(),
        ];

        $recentNotifications = NotificationLog::query()->orderByDesc('id')->limit(5)->get();

        return view('mobile-admin.dashboard', compact('stats', 'recentNotifications'));
    }
}
