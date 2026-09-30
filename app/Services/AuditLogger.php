<?php

namespace App\Services;

use App\Models\AuditLog;

/**
 * Writes before/after snapshots for admin model changes.
 * Sensitive fields are masked in BOTH snapshots.
 */
class AuditLogger
{
    /** Field names (substring, case-insensitive) that must never be stored. */
    private const MASK = ['password', 'remember_token', '_token', 'secret', 'api_key', 'card', 'cvv', 'pin'];

    public static function log($model, string $action): void
    {
        try {
            $old = $action === 'created' ? [] : $model->getOriginal();
            $new = $action === 'deleted' ? [] : $model->getAttributes();

            AuditLog::create([
                'user_id'         => auth()->id(),
                'action'          => $action,
                'auditable_type'  => get_class($model),
                'auditable_id'    => $model->getKey(),
                'old_values'      => self::mask($old),
                'new_values'      => self::mask($new),
                'ip'              => request() ? request()->ip() : null,
                'user_agent'      => request() ? substr((string) request()->userAgent(), 0, 255) : null,
                'url'             => request() ? substr((string) request()->fullUrl(), 0, 500) : null,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('audit log failed: ' . $e->getMessage());
        }
    }

    private static function mask(array $values): array
    {
        $out = [];
        foreach ($values as $k => $v) {
            $masked = false;
            foreach (self::MASK as $needle) {
                if (stripos($k, $needle) !== false) {
                    $out[$k] = '***';
                    $masked = true;
                    break;
                }
            }
            if (!$masked) {
                $json = json_encode($v);
                $out[$k] = strlen((string) $json) > 4096 ? '(truncated)' : $v;
            }
        }

        return $out;
    }
}
