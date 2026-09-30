<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RouteManagerSetting;

class RouteConfigController extends Controller
{
    /**
     * GET /mobile/v1/route-config
     * Returns active URLs for all routes so the mobile app can
     * dynamically point WebView links / external links to the
     * correct version without a code update. Public — no auth.
     */
    public function index()
    {
        $config = cache()->remember('route_mgr_mobile', 300, function () {
            try {
                return RouteManagerSetting::all()
                    ->keyBy('route_key')
                    ->map(fn ($r) => [
                        'url'     => $r->current_version === 'new' ? $r->new_url : $r->legacy_url,
                        'version' => $r->current_version,
                        'label'   => $r->label,
                    ])
                    ->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });

        return response()->json(['ok' => true, 'routes' => $config]);
    }
}
