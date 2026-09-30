<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavMenu extends Model
{
    protected $fillable = ['name', 'location', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function items()
    {
        return $this->hasMany(CmsMenuItem::class, 'menu_id')
                    ->whereNull('parent_id')
                    ->with('children')
                    ->orderBy('sort_order');
    }

    public function allItems()
    {
        return $this->hasMany(CmsMenuItem::class, 'menu_id')
                    ->orderBy('sort_order');
    }

    public static function forLocation(string $location): ?self
    {
        return static::where('location', $location)
                     ->where('is_active', true)
                     ->first();
    }
}
