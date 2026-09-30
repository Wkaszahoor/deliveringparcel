<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppUiSetting extends Model
{
    protected $table = 'app_ui_settings';

    protected $fillable = ['setting_key', 'setting_value', 'setting_group'];

    /** [group => [key => value]] (cached). */
    public static function grouped(): array
    {
        return Cache::remember('app_ui_settings', 300, function () {
            $out = [];
            foreach (static::query()->get() as $row) {
                $out[$row->setting_group][$row->setting_key] = $row->setting_value;
            }

            return $out;
        });
    }

    /** Flat key => value across all groups. */
    public static function allKeyed(): array
    {
        $flat = [];
        foreach (static::grouped() as $group) {
            $flat = array_merge($flat, $group);
        }

        return $flat;
    }

    /** ui object for the API: message_colors, colors, typography, bottom_padding. */
    public static function allAsApiFormat(): array
    {
        $ui = static::allKeyed();

        return [
            'message_colors' => [
                'admin' => [
                    'bubble'   => $ui['admin_bubble_color']  ?? '#1565C0',
                    'text'     => $ui['admin_text_color']    ?? '#FFFFFF',
                    'position' => $ui['admin_position']      ?? 'right',
                ],
                'client' => [
                    'bubble'   => $ui['client_bubble_color'] ?? '#E8F5E9',
                    'text'     => $ui['client_text_color']   ?? '#212121',
                    'position' => $ui['client_position']     ?? 'left',
                ],
                'system' => [
                    'bubble'   => $ui['system_bubble_color'] ?? '#FFF3E0',
                    'text'     => $ui['system_text_color']   ?? '#E65100',
                    'position' => $ui['system_position']     ?? 'center',
                ],
            ],
            'colors' => [
                'primary'    => $ui['primary_color']    ?? '#2196F3',
                'secondary'  => $ui['secondary_color']  ?? '#FF9800',
                'background' => $ui['background_color'] ?? '#FFFFFF',
            ],
            'typography' => [
                'base'    => (int) ($ui['font_size_base']    ?? 14),
                'header'  => (int) ($ui['font_size_header']  ?? 18),
                'message' => (int) ($ui['font_size_message'] ?? 14),
            ],
            /* Space below scrollable content so nothing hides behind the
               tab bar / quick actions — admin-configurable (UI page). */
            'bottom_padding' => (int) ($ui['screen_bottom_padding'] ?? 90),
        ];
    }

    protected static function booted()
    {
        static::saved(fn () => self::flushKeys());
        static::deleted(fn () => self::flushKeys());
    }

    private static function flushKeys(): void
    {
        Cache::forget('app_ui_settings');
        Cache::forget('app_settings');
    }
}
