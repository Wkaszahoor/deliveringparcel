<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanctionsScreening extends Model
{
    protected $fillable = ['subject_user_id', 'input_name', 'match_count', 'result'];

    protected $casts = ['result' => 'array'];
}
