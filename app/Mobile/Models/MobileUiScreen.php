<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MobileUiScreen extends Model
{
    protected $table = 'mobile_ui_screens';

    protected $fillable = [
        'screen_key', 'screen_name', 'parent_key', 'screen_order', 'global_status',
        'admin_visible', 'admin_enabled', 'client_visible', 'client_enabled',
        'icon', 'description',
    ];

    protected $casts = [
        'admin_visible'  => 'boolean',
        'admin_enabled'  => 'boolean',
        'client_visible' => 'boolean',
        'client_enabled' => 'boolean',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('global_status', 'active');
    }

    public function scopeForRole(Builder $q, string $role): Builder
    {
        return $q->where("{$role}_visible", true)->where('global_status', 'active');
    }

    protected static function booted()
    {
        static::saved(fn () => app(\App\Mobile\Services\UIControlsService::class)->clearCache());
        static::deleted(fn () => app(\App\Mobile\Services\UIControlsService::class)->clearCache());
    }
}
