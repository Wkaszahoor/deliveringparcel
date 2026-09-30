<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoState extends Model
{
    protected $fillable = ['country_id', 'country_code', 'name', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function cities()
    {
        return $this->hasMany(GeoCity::class);
    }
}
