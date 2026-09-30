<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class HeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'btn_text',
        'btn_link',
        'position',
        'is_active',
        'options',
    ];

    protected $casts = [
        'position'  => 'integer',
        'is_active' => 'boolean',
        'options'   => 'array',
    ];

    public function imageUrl()
    {
        return $this->image ? asset('uploads/heroslider/' . $this->image) : null;
    }

    /**
     * Options merged with the defaults from config/admin_heroslider.php.
     */
    public function resolvedOptions()
    {
        $defaults = [];
        foreach (config('admin_heroslider.option_fields', []) as $key => $def) {
            $defaults[$key] = $def['default'] ?? '';
        }

        return array_merge($defaults, Arr::only($this->options ?: [], array_keys($defaults)));
    }
}
