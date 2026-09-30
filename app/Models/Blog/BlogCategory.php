<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Category for the Blog Import System (2026-09-02) — lives in the shared
 * `blog_categories` table.
 *
 * WHY A SUB-NAMESPACE: App\Models\BlogCategory already exists and belongs
 * to the LEGACY blog module (App\Models\Blog with category_id). Overwriting
 * it would break legacy admin/blogs + admin/blog-categories, so the new
 * system uses App\Models\Blog\BlogCategory instead.
 */
class BlogCategory extends Model
{
    protected $table = 'blog_categories';

    protected $fillable = ['name', 'slug', 'description', 'post_count'];

    public function posts()
    {
        return $this->belongsToMany(
            \App\Models\BlogPost::class,
            'blog_post_category',
            'category_id',
            'post_id'
        );
    }

    public static function findOrCreateByName(string $name): self
    {
        $name = trim($name);
        $slug = Str::slug($name) ?: Str::random(8);
        return static::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'slug' => $slug]
        );
    }
}
