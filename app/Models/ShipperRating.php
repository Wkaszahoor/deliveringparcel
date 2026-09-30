<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperRating extends Model
{
    protected $fillable = [
        'assignment_id', 'order_id', 'shipper_profile_id', 'rated_by_user_id',
        'overall_rating', 'communication_rating', 'speed_rating',
        'value_rating', 'condition_rating', 'review_text',
        'consent_testimonial', 'admin_approved', 'published_as_testimonial',
        'admin_approved_at',
    ];

    protected $casts = [
        'admin_approved'          => 'boolean',
        'published_as_testimonial' => 'boolean',
        'admin_approved_at'       => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::created(function ($rating) {
            if ($shipper = ShipperProfile::find($rating->shipper_profile_id)) {
                $shipper->recalculateRating();
                $shipper->checkLevelProgression();
            }
        });
    }

    public function assignment()
    {
        return $this->belongsTo(ShipperOrderAssignment::class, 'assignment_id');
    }

    public function shipper()
    {
        return $this->belongsTo(ShipperProfile::class, 'shipper_profile_id');
    }

    public function ratedBy()
    {
        return $this->belongsTo(User::class, 'rated_by_user_id');
    }
}
