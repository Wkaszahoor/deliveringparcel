<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseBin extends Model
{
    protected $fillable = ['code', 'zone', 'capacity', 'notes', 'is_active'];

    protected $casts = ['capacity' => 'integer', 'is_active' => 'boolean'];

    public function packages()
    {
        return $this->hasMany(WarehousePackage::class, 'storage_bin_id');
    }
}
