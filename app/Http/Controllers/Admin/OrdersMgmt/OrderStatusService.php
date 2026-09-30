<?php

namespace App\Http\Controllers\Admin\OrdersMgmt;

/*
|--------------------------------------------------------------------------
| OrderStatusService — reads the state machine from config/admin_orders.php
|--------------------------------------------------------------------------
| Single source of truth helper used by the OrdersMgmt controllers and the
| Blade partials. Status keys are the exact strings stored in orders.order_status.
*/

class OrderStatusService
{
    /**
     * All statuses with metadata (label, badge, next, final, archivable, step).
     */
    public static function all(): array
    {
        return (array) config('admin_orders.statuses');
    }

    /**
     * Metadata for one status; safe fallback for unknown/null values.
     */
    public static function meta(?string $status): array
    {
        $key = (string) $status;
        $all = self::all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        return [
            'label'       => $key === '' ? 'Request Placed' : $key,
            'badge'       => 'bg-secondary',
            'next'        => [],
            'final'       => false,
            'archivable'  => false,
            'step'        => -1,
            'description' => 'Unknown status value.',
        ];
    }

    public static function label(?string $status): string
    {
        return self::meta($status)['label'];
    }

    public static function badge(?string $status): string
    {
        return self::meta($status)['badge'];
    }

    public static function isFinal(?string $status): bool
    {
        return (bool) self::meta($status)['final'];
    }

    public static function isArchivable(?string $status): bool
    {
        return (bool) self::meta($status)['archivable'];
    }

    /**
     * Allowed next statuses for a given current status.
     */
    public static function next(?string $status): array
    {
        return (array) self::meta($status)['next'];
    }

    /**
     * Strict state machine guard: true only when $to is a known status
     * explicitly listed as an allowed transition from $from.
     */
    public static function canTransition(?string $from, ?string $to): bool
    {
        $to = (string) $to;

        if (!array_key_exists($to, self::all())) {
            return false;
        }

        return in_array($to, self::next($from), true);
    }

    /**
     * value => label map for dropdowns (includes the '' => Request Placed entry).
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::all() as $key => $meta) {
            $out[$key] = $meta['label'];
        }

        return $out;
    }

    /**
     * The main progress path used by the detail timeline.
     */
    public static function timeline(): array
    {
        return (array) config('admin_orders.timeline');
    }
}
