<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoCity extends Model
{
    protected $fillable = ['geo_state_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function state()
    {
        return $this->belongsTo(GeoState::class);
    }
}
