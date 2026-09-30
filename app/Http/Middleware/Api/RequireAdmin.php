<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

class RequireAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->type !== 'admin') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        return $next($request);
    }
}
