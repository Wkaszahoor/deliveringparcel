<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

/**
 * Imported/new-design blog post (Blog Import System, 2026-09-02).
 * Separate from the legacy blog (App\Models\Blog / `blogs` table).
 */
class BlogPost extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'blog_posts';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'featured_image',
        'featured_image_url', 'author_name', 'author_email', 'status',
        'source_url', 'source_guid', 'published_at', 'imported_at',
        'seo_title', 'seo_description', 'seo_keywords', 'view_count',
        'import_batch_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'imported_at'  => 'datetime',
    ];

    /* Relationships */
    public function categories()
    {
        return $this->belongsToMany(
            \App\Models\Blog\BlogCategory::class,
            'blog_post_category',
            'post_id',
            'category_id'
        );
    }

    public function tags()
    {
        return $this->belongsToMany(
            BlogTag::class,
            'blog_post_tag',
            'post_id',
            'tag_id'
        );
    }

    /* Auto-generate unique slug from title. */
    public static function generateSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $i = 1;
        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /* Scopes */
    public function scopePublished($q)
    {
        return $q->where('status', 'published')
                 ->where('published_at', '<=', now());
    }

    /* Featured image URL helper — local first, fallback to remote. */
    public function getFeaturedImageSrcAttribute(): ?string
    {
        if ($this->featured_image && file_exists(public_path($this->featured_image))) {
            return asset($this->featured_image);
        }
        return $this->featured_image_url;
    }

    /* Increment view count without touching updated_at
       (static::withoutTimestamps() is not available in Laravel 8). */
    public function recordView(): void
    {
        $this->timestamps = false;
        $this->increment('view_count');
        $this->timestamps = true;
    }
}
