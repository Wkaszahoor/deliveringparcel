<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CmsPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'post_type', 'title', 'slug', 'status', 'content', 'excerpt',
        'featured_image', 'parent_id', 'menu_order', 'published_at', 'author_id',
        'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'robots',
        'template', 'layout', 'show_in_header', 'show_in_footer', 'show_in_main_nav',
        'meta', 'view_count', 'is_locked',
    ];

    protected $casts = [
        'published_at'     => 'datetime',
        'meta'             => 'array',
        'show_in_header'   => 'boolean',
        'show_in_footer'   => 'boolean',
        'show_in_main_nav' => 'boolean',
        'is_locked'        => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function taxonomies()
    {
        return $this->belongsToMany(
            CmsTaxonomy::class,
            'cms_post_taxonomy',
            'post_id',
            'taxonomy_id'
        );
    }

    public function categories()
    {
        return $this->taxonomies()->where('taxonomy', 'category');
    }

    public function tags()
    {
        return $this->taxonomies()->where('taxonomy', 'tag');
    }

    public function serviceAreas()
    {
        return $this->taxonomies()->where('taxonomy', 'service_area');
    }

    public function parent()
    {
        return $this->belongsTo(CmsPost::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CmsPost::class, 'parent_id')
                    ->orderBy('menu_order');
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopePublished($q)
    {
        return $q->where('status', 'published')
                 ->where('published_at', '<=', now());
    }

    public function scopeOfType($q, string $type)
    {
        return $q->where('post_type', $type);
    }

    public function scopeTopLevel($q)
    {
        return $q->whereNull('parent_id');
    }

    public function scopeInNav($q)
    {
        return $q->where('show_in_main_nav', true)->orderBy('menu_order');
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    // Returns the correct public URL for this post based on post_type
    public function getUrlAttribute(): string
    {
        return match ($this->post_type) {
            'blog_post' => url('/blog/' . $this->slug),
            'product'   => url('/shop/' . $this->slug),
            'service'   => url('/services/' . $this->slug),
            'page'      => url('/page/' . $this->slug),
            default     => url('/page/' . $this->slug),
        };
    }

    // Featured image: returns full URL or null
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }
        if (str_starts_with($this->featured_image, 'http')) {
            return $this->featured_image;
        }
        $full = public_path($this->featured_image);
        return file_exists($full) ? asset($this->featured_image) : null;
    }

    // Auto-computed excerpt from content
    public function getSmartExcerptAttribute(): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }
        return Str::limit(strip_tags($this->content ?? ''), 160);
    }

    // Reading time (blog_post only)
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    // Product price from meta
    public function getPriceAttribute(): ?float
    {
        return $this->meta['price'] ?? null;
    }

    // Service icon from meta
    public function getServiceIconAttribute(): string
    {
        return $this->meta['icon'] ?? 'fa-cog';
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function incrementViewCount(): void
    {
        $this->timestamps = false;
        $this->increment('view_count');
        $this->timestamps = true;
    }

    public static function bustNavCache(): void
    {
        Cache::forget('cms_nav_header_main');
        Cache::forget('cms_nav_footer_col1');
        Cache::forget('cms_nav_footer_col2');
        Cache::forget('cms_nav_footer_col3');
        Cache::forget('cms_nav_footer_bottom');
        Cache::forget('cms_nav_menu_all');
    }

    // Boot: auto-slug, auto-excerpt
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = static::makeUniqueSlug($post->title);
            }
            if (empty($post->published_at) && $post->status === 'published') {
                $post->published_at = now();
            }
        });
        static::saving(function ($post) {
            // Wrapped in try/catch: cache backend may be unavailable during
            // bulk imports and must never block a save.
            try {
                static::bustNavCache();
            } catch (\Throwable $e) {
                // ignore — cache busting is best-effort
            }
        });
    }

    protected static function makeUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
