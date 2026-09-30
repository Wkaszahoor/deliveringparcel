<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'blog_category_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /**
     * Public URL for the cover image (stored under /uploads/blog).
     */
    public function coverUrl()
    {
        return $this->cover_image ? asset('uploads/blog/' . $this->cover_image) : null;
    }
}
