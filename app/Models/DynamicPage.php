<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DynamicPage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'route_path', 'nav_label', 'section', 'icon',
        'meta_title', 'meta_description', 'meta_keywords',
        'content', 'layout', 'show_in_header', 'show_in_footer',
        'show_in_main_nav', 'is_active', 'is_locked', 'sort_order',
        'controller_override',
    ];

    protected $casts = [
        'show_in_header'   => 'boolean',
        'show_in_footer'   => 'boolean',
        'show_in_main_nav' => 'boolean',
        'is_active'        => 'boolean',
        'is_locked'        => 'boolean',
    ];

    // Auto-generate slug and route_path from title
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
            if (empty($page->route_path)) {
                $page->route_path = '/' . ltrim(Str::slug($page->title), '/');
            }
        });
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeInHeader($q)
    {
        return $q->where('show_in_header', true)->active()->orderBy('sort_order');
    }

    public function scopeInFooter($q)
    {
        return $q->where('show_in_footer', true)->active()->orderBy('sort_order');
    }

    public function scopeInMainNav($q)
    {
        return $q->where('show_in_main_nav', true)->active()->orderBy('sort_order');
    }

    public function scopeBySection($q, $section)
    {
        return $q->where('section', $section);
    }

    // Full public URL
    public function getUrlAttribute(): string
    {
        return url($this->route_path);
    }
}
