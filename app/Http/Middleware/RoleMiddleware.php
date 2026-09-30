<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
     public function handle(Request $request, Closure $next, string $role, ?string $permission = null)
    {
        if (!$request->user()) {
            abort(404);
        }

        // 2026-09-02 shipper system: middleware params split on commas, so
        // "role:shipper,shipper_pending" arrives as role+permission. Treat
        // BOTH as a role list — user passes if they hold ANY of them.
        // (No legacy route ever used "role:X,Y" form, so this is additive.)
        $roles = array_values(array_filter(array_map('trim', array_merge(
            explode(',', (string) $role),
            $permission !== null ? explode(',', (string) $permission) : []
        ))));

        if (empty($roles)) {
            $roles = [(string) $role];
        }

        if (! $request->user()->hasRole(...$roles)) {
             abort(404);
        }

        return $next($request);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    // public function handle(Request $request, Closure $next)
    // {
    //     return $next($request);
    // }

}
