<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->value('value');
        return $row === null ? $default : (string) $row;
    }

    public static function put(string $key, ?string $value, string $group = 'theme'): void
    {
        static::query()->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
