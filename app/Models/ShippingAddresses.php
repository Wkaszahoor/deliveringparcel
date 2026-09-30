<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddresses extends Model
{
    use HasFactory;
    public $fillable = ['name', 'address1', 'address2', 'city', 'state', 'country', 'number', 'postalcode'];
}
