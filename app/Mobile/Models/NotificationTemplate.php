<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $table = 'mobile_notification_templates';

    protected $fillable = ['template_name', 'notification_type', 'title', 'body', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
