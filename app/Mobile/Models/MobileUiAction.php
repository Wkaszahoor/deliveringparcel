<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MobileUiAction extends Model
{
    protected $table = 'mobile_ui_actions';

    protected $fillable = [
        'section_key', 'action_key', 'action_name', 'action_label', 'action_type',
        'action_order', 'global_status', 'admin_visible', 'admin_enabled',
        'client_visible', 'client_enabled', 'allowed_order_statuses',
        'confirmation_required', 'confirmation_message', 'icon', 'color', 'description',
    ];

    protected $casts = [
        'admin_visible'        => 'boolean',
        'admin_enabled'        => 'boolean',
        'client_visible'       => 'boolean',
        'client_enabled'       => 'boolean',
        'allowed_order_statuses' => 'array',
        'confirmation_required' => 'boolean',
    ];

    public function scopeForSection(Builder $q, string $sectionKey): Builder
    {
        return $q->where('section_key', $sectionKey);
    }

    /** Actions whose allowed_order_statuses (if set) include the given status. */
    public function scopeForStatus(Builder $q, ?string $orderStatus): Builder
    {
        return $q->where(function ($w) use ($orderStatus) {
            $w->whereNull('allowed_order_statuses')
                ->orWhere('allowed_order_statuses', 'like', '[]')
                ->orWhereRaw('JSON_LENGTH(allowed_order_statuses) = 0');
            if ($orderStatus !== null && $orderStatus !== '') {
                $w->orWhereRaw('JSON_CONTAINS(allowed_order_statuses, ?)', [json_encode($orderStatus)]);
            }
        });
    }

    protected static function booted()
    {
        static::saved(fn () => app(\App\Mobile\Services\UIControlsService::class)->clearCache());
        static::deleted(fn () => app(\App\Mobile\Services\UIControlsService::class)->clearCache());
    }
}
