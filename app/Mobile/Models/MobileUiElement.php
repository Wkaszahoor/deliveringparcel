<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MobileUiElement extends Model
{
    protected $table = 'mobile_ui_elements';

    protected $fillable = [
        'section_key', 'element_key', 'element_name', 'element_type', 'element_order',
        'global_status', 'admin_visible', 'admin_enabled', 'client_visible',
        'client_enabled', 'description',
    ];

    protected $casts = [
        'admin_visible'  => 'boolean',
        'admin_enabled'  => 'boolean',
        'client_visible' => 'boolean',
        'client_enabled' => 'boolean',
    ];

    public function scopeForSection(Builder $q, string $sectionKey): Builder
    {
        return $q->where('section_key', $sectionKey);
    }

    protected static function booted()
    {
        static::saved(fn () => app(\App\Mobile\Services\UIControlsService::class)->clearCache());
        static::deleted(fn () => app(\App\Mobile\Services\UIControlsService::class)->clearCache());
    }
}
