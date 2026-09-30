<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestQuote extends Model
{
    use HasFactory;
    public $fillable = ['name', 'email', 'number','cargotype', 'country', 'destination', 'weight', 'width', 'height', 'detail'];
}