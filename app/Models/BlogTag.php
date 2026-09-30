<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Tag for the Blog Import System (2026-09-02).
 */
class BlogTag extends Model
{
    protected $table = 'blog_tags';

    protected $fillable = ['name', 'slug'];

    public function posts()
    {
        return $this->belongsToMany(
            BlogPost::class,
            'blog_post_tag',
            'tag_id',
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
