<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'channel', 'type', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    public static function isEnabled(string $channel, string $type, int $userId, bool $default = true): bool
    {
        $row = static::where('user_id', $userId)->where('channel', $channel)->where('type', $type)->first();

        return $row ? (bool) $row->is_enabled : $default;
    }
}
