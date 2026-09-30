<?php

/**
 * Global admin helpers.
 */

if (!function_exists('dp_per_page')) {
    /**
     * Sanitized per-page size for admin data endpoints (?per_page=).
     * Allowed: 15, 25, 50, 100 (default 15).
     */
    function dp_per_page($request, int $default = 15): int
    {
        $allowed = [15, 25, 50, 100];
        $wanted = (int) optional($request)->input('per_page', $default);

        return in_array($wanted, $allowed, true) ? $wanted : $default;
    }
}
