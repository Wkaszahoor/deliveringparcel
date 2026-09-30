<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
 * PROJECT ADAPTATION: the upstream spec named this model
 * App\Models\NavMenuItem, but that class name (and the nav_menu_items
 * table) are already taken by the legacy header-navigation system
 * (NavRenderer + admin Navigation manager). The CMS menu builder
 * therefore uses CmsMenuItem on the cms_menu_items table — identical
 * columns/behavior otherwise.
 */
class CmsMenuItem extends Model
{
    protected $table = 'cms_menu_items';

    protected $fillable = [
        'menu_id', 'parent_id', 'label', 'item_type', 'object_id',
        'url', 'target', 'icon', 'css_class', 'is_mega_menu',
        'show_badge', 'badge_text', 'badge_color', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_mega_menu' => 'boolean',
        'show_badge'   => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(NavMenu::class, 'menu_id');
    }

    public function children()
    {
        return $this->hasMany(CmsMenuItem::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with('children'); // recursive for 3rd level if needed
    }

    // Admin-facing: every child regardless of active state (menu builder)
    public function allChildren()
    {
        return $this->hasMany(CmsMenuItem::class, 'parent_id')
                    ->orderBy('sort_order')
                    ->with('allChildren');
    }

    public function parent()
    {
        return $this->belongsTo(CmsMenuItem::class, 'parent_id');
    }

    // Resolve the final URL for this item
    public function getResolvedUrlAttribute(): string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->item_type === 'cms_post' && $this->object_id) {
            $post = CmsPost::find($this->object_id);
            return $post?->url ?? '#';
        }

        if ($this->item_type === 'taxonomy' && $this->object_id) {
            $tax = CmsTaxonomy::find($this->object_id);
            return $tax?->url ?? '#';
        }

        return '#';
    }
}
