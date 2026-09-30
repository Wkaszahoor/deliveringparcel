<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'service_category_id',
        'short_desc',
        'description',
        'image',
        'type',
        'price',
        'is_available',
        'sort',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
        'sort'         => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Public URL for the service image (stored under /uploads/services).
     */
    public function imageUrl()
    {
        return $this->image ? asset('uploads/services/' . $this->image) : null;
    }
}
