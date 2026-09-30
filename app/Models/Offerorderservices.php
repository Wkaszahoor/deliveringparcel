<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offerorderservices extends Model
{
    use HasFactory;
    public $fillable = ['servicename','servicevalue','offer_id','confirmation'];

    public function offerorder()
    {
        // Exact class case — lowercase 'offerorder' 500s on Linux hosts.
        return $this->belongsTo(\App\Models\Offerorder::class);
    }
}
