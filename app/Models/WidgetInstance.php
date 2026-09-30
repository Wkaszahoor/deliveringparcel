<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetInstance extends Model
{
    protected $fillable = [
        'type', 'title', 'area', 'scope', 'scope_key', 'config',
        'conditions', 'is_active', 'sort', 'cache_minutes', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'config'     => 'array',
        'conditions' => 'array',
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
    ];

    /* Precedence rank of this instance's scope (TW-004). */
    public function scopeRank(): int
    {
        return (int) (config('admin_widgets.precedence.' . $this->scope, 1));
    }

    /* Does this instance's scope key match the current resolution context? */
    public function matchesContext(string $path, string $template, string $theme): bool
    {
        $key = trim((string) $this->scope_key);
        if ($this->scope === 'global') {
            return true;
        }
        if ($this->scope === 'page') {
            return $key === '*' || $key === '' || trim($key, '/') === trim($path, '/');
        }
        if ($this->scope === 'template') {
            return $key === '*' || $key === '' || $key === $template;
        }
        if ($this->scope === 'theme') {
            return $key === '*' || $key === '' || $key === $theme;
        }
        return false;
    }
}
