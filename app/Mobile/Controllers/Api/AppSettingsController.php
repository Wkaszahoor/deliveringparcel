<?php

namespace App\Mobile\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mobile\Models\MobileLabel;
use App\Mobile\Services\MobileSettingsService;
use Illuminate\Http\JsonResponse;

/**
 * Mobile app settings + labels (module v1).
 *
 * GET /api/mobile/v1/settings → full nested config
 * GET /api/mobile/v1/labels   → admin-renamable section titles
 */
class AppSettingsController extends Controller
{
    public function settings(): JsonResponse
    {
        $data = app(MobileSettingsService::class)->getFullSettings();

        return response()->json(['data' => $data], 200);
    }

    public function labels(): JsonResponse
    {
        return response()->json(['labels' => MobileLabel::allKeyed()], 200);
    }
}
