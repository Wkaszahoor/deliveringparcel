<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppScreenSetting extends Model
{
    protected $table = 'app_screen_settings';

    protected $fillable = [
        'screen_key', 'screen_name', 'parent_screen',
        'is_visible', 'is_enabled', 'visible_to',
        'screen_order', 'config',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_enabled' => 'boolean',
        'visible_to' => 'array',
        'config'     => 'array',
    ];

    public function scopeMain(Builder $query): Builder
    {
        return $query->whereNull('parent_screen')->orderBy('screen_order');
    }

    public function scopeSub(Builder $query): Builder
    {
        return $query->whereNotNull('parent_screen')->orderBy('parent_screen')->orderBy('screen_order');
    }

    public function getSubScreensAttribute()
    {
        return static::where('parent_screen', $this->screen_key)->orderBy('screen_order')->get();
    }

    /**
     * Nested screens array consumed by GET /api/mobile/v1/settings:
     * [key => [visible, enabled, title, visible_to, sub_screens?, visible_elements?, visible_buttons?]]
     */
    public static function allAsApiFormat(): array
    {
        return Cache::remember('app_screen_settings', 300, function () {
            $subs = static::sub()->get()->groupBy('parent_screen');
            $out = [];

            foreach (static::main()->get() as $screen) {
                $entry = [
                    'visible'    => $screen->is_visible,
                    'enabled'    => $screen->is_enabled,
                    'title'      => $screen->screen_name,
                    'visible_to' => $screen->visible_to ?: ['admin', 'client'],
                ];

                if ($subs->has($screen->screen_key)) {
                    $entry['sub_screens'] = $subs[$screen->screen_key]
                        ->mapWithKeys(fn ($s) => [$s->screen_key => ['visible' => $s->is_visible]])
                        ->toArray();
                }

                $config = $screen->config ?? [];
                if (isset($config['visible_elements'])) $entry['visible_elements'] = $config['visible_elements'];
                if (isset($config['visible_buttons'])) $entry['visible_buttons'] = $config['visible_buttons'];

                $out[$screen->screen_key] = $entry;
            }

            return $out;
        });
    }

    protected static function booted()
    {
        static::saved(fn () => Cache::forget('app_screen_settings'));
        static::deleted(fn () => Cache::forget('app_screen_settings'));
    }
}
