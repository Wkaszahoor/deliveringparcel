<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanctionsEntry extends Model
{
    protected $fillable = ['full_name', 'dob', 'country', 'list_name', 'notes'];

    protected $casts = ['dob' => 'date'];
}
