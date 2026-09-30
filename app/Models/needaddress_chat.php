<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class needaddress_chat extends Model
{
    use HasFactory;
    public $fillable = ['from','body','order_id'];
    public function needaddresses()
    {
        return $this->belongsTo('App\Models\needaddresses');
    }
}
