<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

class RequireRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user() || $request->user()->type !== $role) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        return $next($request);
    }
}
