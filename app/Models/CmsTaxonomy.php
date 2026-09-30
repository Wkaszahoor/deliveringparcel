<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CmsTaxonomy extends Model
{
    protected $table = 'cms_taxonomies';

    protected $fillable = [
        'taxonomy', 'post_type', 'name', 'slug', 'description',
        'featured_image', 'parent_id', 'sort_order', 'post_count', 'meta',
    ];

    protected $casts = ['meta' => 'array'];

    public function posts()
    {
        return $this->belongsToMany(
            CmsPost::class,
            'cms_post_taxonomy',
            'taxonomy_id',
            'post_id'
        );
    }

    public function parent()
    {
        return $this->belongsTo(CmsTaxonomy::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CmsTaxonomy::class, 'parent_id')
                    ->orderBy('sort_order');
    }

    public function getUrlAttribute(): string
    {
        return match ($this->taxonomy) {
            'category' => match ($this->post_type) {
                'product' => url('/shop/category/' . $this->slug),
                'service' => url('/services/category/' . $this->slug),
                default   => url('/blog/category/' . $this->slug),
            },
            'tag'          => url('/blog/tag/' . $this->slug),
            'service_area' => url('/services/area/' . $this->slug),
            default        => url('/' . $this->slug),
        };
    }

    public function syncPostCount(): void
    {
        $this->update([
            'post_count' => $this->posts()
                                 ->where('status', 'published')
                                 ->count(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($t) {
            if (empty($t->slug)) {
                $t->slug = Str::slug($t->name);
            }
        });
    }
}
