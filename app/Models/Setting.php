<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Agent D — Settings module.
 *
 * Static cached accessor layer over the `settings` table.
 * - Setting::get($key, $default)
 * - Setting::set($key, $value, $group, $type)
 * - Setting::group($group)
 *
 * The whole table is cached with rememberForever() and the cache is
 * flushed on every write. If the table does not exist yet (pre-migration),
 * every call degrades gracefully to the provided default.
 */
class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value', 'group', 'type'];

    public const CACHE_KEY = 'dp_settings_all';

    /**
     * All settings as key => value (cached).
     *
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                if (!self::tableExists()) {
                    return [];
                }

                return (array) DB::table(self::tableFromConfig())->pluck('value', 'key')->toArray();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Get one setting (falls back to $default when missing). */
    public static function get(string $key, $default = null)
    {
        $all = self::allCached();

        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        return $default;
    }

    /**
     * Get one setting cast to bool ("1"/"true" => true).
     * Missing key falls back to the default declared in config/admin_settings.php.
     */
    public static function getBool(string $key, ?bool $default = null): bool
    {
        $default = $default ?? (bool) self::configDefault($key, false);

        $all = self::allCached();
        if (!array_key_exists($key, $all) || $all[$key] === null || $all[$key] === '') {
            return $default;
        }

        return in_array(strtolower((string) $all[$key]), ['1', 'true', 'yes', 'on'], true);
    }

    /** Get one setting cast to int (missing key falls back to config default). */
    public static function getInt(string $key, ?int $default = null): int
    {
        $default = $default ?? (int) self::configDefault($key, 0);

        $all = self::allCached();
        if (!array_key_exists($key, $all) || $all[$key] === null || $all[$key] === '') {
            return $default;
        }

        $casted = (int) $all[$key];

        return $casted === 0 && !is_numeric($all[$key]) ? $default : $casted;
    }

    /** Persist one setting and flush the cache. */
    public static function set(string $key, $value, ?string $group = null, ?string $type = null)
    {
        if (!self::tableExists()) {
            return false;
        }

        $definition = self::configDefinition($key);
        $group = $group ?: ($definition['group'] ?? 'general');
        $type = $type ?: ($definition['type'] ?? 'string');

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        DB::table(self::tableFromConfig())->updateOrInsert(
            ['key' => $key],
            [
                'value' => $value === null ? null : (string) $value,
                'group' => $group,
                'type' => $type,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        self::flushCache();

        return true;
    }

    /**
     * All key => value pairs for one group merged over config defaults.
     *
     * @return array<string, mixed>
     */
    public static function group(string $group): array
    {
        $defaults = [];
        foreach (self::configKeys() as $key => $definition) {
            if (($definition['group'] ?? '') === $group) {
                $defaults[$key] = $definition['default'] ?? null;
            }
        }

        $stored = [];
        foreach (self::allCached() as $key => $value) {
            if (isset(self::configKeys()[$key]) && self::configKeys()[$key]['group'] === $group) {
                $stored[$key] = $value;
            } elseif (str_starts_with($key, $group . '_')) {
                // tolerate stored keys whose group prefix matches
                $stored[$key] = $value;
            }
        }

        return array_merge($defaults, $stored);
    }

    /** Flush the static settings cache. */
    public static function flushCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            // cache backend unavailable — ignore, reads degrade to defaults
        }
    }

    /** @return array<string, array> flattened config/admin_settings.php definitions */
    protected static function configKeys(): array
    {
        $flat = [];
        foreach (config('admin_settings.tabs', []) as $tabKey => $tab) {
            foreach ($tab['fields'] ?? [] as $fieldKey => $field) {
                $flat[$fieldKey] = array_merge($field, ['group' => $tabKey]);
            }
        }

        return $flat;
    }

    protected static function configDefinition(string $key): array
    {
        return self::configKeys()[$key] ?? [];
    }

    protected static function configDefault(string $key, $fallback)
    {
        return self::configKeys()[$key]['default'] ?? $fallback;
    }

    protected static function tableFromConfig(): string
    {
        return 'settings';
    }

    protected static function tableExists(): bool
    {
        try {
            return Cache::rememberForever('dp_settings_table_exists', function () {
                return \Illuminate\Support\Facades\Schema::hasTable('settings');
            });
        } catch (\Throwable $e) {
            return false;
        }
    }
}
