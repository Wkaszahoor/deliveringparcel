<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FE-004 / FE-005 — one header navigation entry.
 *
 * Frontend visibility (everyone/guest/auth) is PRESENTATION ONLY —
 * protected routes still enforce their own auth middleware.
 */
class NavMenuItem extends Model
{
    public const VISIBILITIES = ['everyone', 'guest', 'auth'];

    protected $fillable = [
        'parent_id', 'title', 'label', 'url', 'route_name',
        'icon_class', 'sort', 'is_active', 'new_tab', 'visibility',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'sort'      => 'integer',
        'is_active' => 'boolean',
        'new_tab'   => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort')->orderBy('id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /** Text shown in the menu (label falls back to title). */
    public function displayLabel(): string
    {
        return trim((string) ($this->label ?: $this->title));
    }

    /** Visibility filter for the CURRENT visitor. */
    public function visibleToCurrentUser(): bool
    {
        if ($this->visibility === 'guest') {
            return !auth()->check();
        }
        if ($this->visibility === 'auth') {
            return auth()->check();
        }

        return true;
    }
}
