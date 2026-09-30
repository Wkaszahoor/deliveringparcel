<?php

namespace App\Services\Carriers;

use Illuminate\Support\Facades\Http;

/**
 * Registry + tracking facade over the carrier adapters.
 */
class CarrierManager
{
    /** @var array<string, CarrierAdapterInterface> */
    private static ?array $adapters = null;

    public static function adapters(): array
    {
        if (self::$adapters === null) {
            $adapters = [];
            foreach (glob(app_path('Services/Carriers/Adapters/*.php')) ?: [] as $file) {
                $class = 'App\\Services\\Carriers\\Adapters\\' . basename($file, '.php');
                if (class_exists($class) && is_subclass_of($class, \App\Services\Carriers\Contracts\CarrierAdapterInterface::class)) {
                    $instance = app($class);
                    $adapters[$instance->code()] = $instance;
                }
            }
            self::$adapters = $adapters;
        }

        return self::$adapters;
    }

    public static function get(string $code): ?\App\Services\Carriers\Contracts\CarrierAdapterInterface
    {
        return self::adapters()[$code] ?? null;
    }

    /** Cached aggregated track. */
    public static function track(string $code, string $number): array
    {
        $adapter = self::get($code);
        if (!$adapter) {
            return ['error' => 'Unknown carrier'];
        }

        return \Cache::remember('carrier.track.' . $code . '.' . md5($number), now()->addMinutes((int) config('admin_carriers.cache_minutes', 10)), function () use ($adapter, $number) {
            return $adapter->track($number);
        });
    }
}
