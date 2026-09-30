<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

/** Sanctum API guard: only users holding the active shipper role pass. */
class RequireShipper
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('shipper')) {
            return response()->json([
                'ok'      => false,
                'message' => 'Shipper access required.',
            ], 403);
        }

        return $next($request);
    }
}
