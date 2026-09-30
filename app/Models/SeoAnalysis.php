<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SEO Shield — one persisted analyzer run (post / URL / pasted content).
 * Full analyzer payload lives in `report`; `categories` mirrors the
 * per-category scores for quick listing.
 */
class SeoAnalysis extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'categories' => 'array',
        'report'     => 'array',
    ];
}
