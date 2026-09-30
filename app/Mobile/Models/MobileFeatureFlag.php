<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MobileFeatureFlag extends Model
{
    protected $table = 'mobile_feature_flags';

    protected $fillable = ['feature_key', 'feature_name', 'is_enabled', 'allowed_roles', 'settings'];

    protected $casts = [
        'is_enabled'    => 'boolean',
        'allowed_roles' => 'array',
        'settings'      => 'array',
    ];

    /** key => enabled-bool for every flag (cached). */
    public static function allEnabled(): array
    {
        return Cache::remember('mobile_feature_flags', config('mobile.cache_ttl.features', 600), function () {
            return static::query()->pluck('is_enabled', 'feature_key')->toArray();
        });
    }

    /**
     * features object for the API: top-level booleans the app can
     * truthiness-check directly, plus _detail with per-flag roles.
     */
    public static function allAsApiFormat(): array
    {
        $flags = static::allEnabled();
        $roles = static::query()->pluck('allowed_roles', 'feature_key')->toArray();

        $features = [];
        foreach ($flags as $key => $enabled) {
            $features[$key] = (bool) $enabled;
        }
        // The app reads push_notifications; the flag key is push_notifs.
        $features['push_notifications'] = $features['push_notifs'] ?? true;

        foreach ($flags as $key => $enabled) {
            $features['_detail'][$key] = [
                'enabled' => (bool) $enabled,
                'roles'   => $roles[$key] ?: ['admin', 'client'],
            ];
        }

        return $features;
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    protected static function booted()
    {
        static::saved(fn () => self::flushKeys());
        static::deleted(fn () => self::flushKeys());
    }

    private static function flushKeys(): void
    {
        Cache::forget('mobile_feature_flags');
        Cache::forget('app_settings');
    }
}
