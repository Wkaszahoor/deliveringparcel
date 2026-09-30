<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MobileUiSection extends Model
{
    protected $table = 'mobile_ui_sections';

    protected $fillable = [
        'screen_key', 'section_key', 'section_name', 'section_order', 'global_status',
        'admin_visible', 'admin_enabled', 'client_visible', 'client_enabled',
        'description',
    ];

    protected $casts = [
        'admin_visible'  => 'boolean',
        'admin_enabled'  => 'boolean',
        'client_visible' => 'boolean',
        'client_enabled' => 'boolean',
    ];

    public function scopeForScreen(Builder $q, string $screenKey): Builder
    {
        return $q->where('screen_key', $screenKey);
    }

    public function scopeForRole(Builder $q, string $role): Builder
    {
        return $q->where("{$role}_visible", true);
    }

    protected static function booted()
    {
        static::saved(fn () => app(\App\Mobile\Services\UIControlsService::class)->clearCache());
        static::deleted(fn () => app(\App\Mobile\Services\UIControlsService::class)->clearCache());
    }
}
