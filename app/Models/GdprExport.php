<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GdprExport extends Model
{
    public $timestamps = true;

    protected $fillable = ['user_id', 'admin_id', 'file_rows'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
