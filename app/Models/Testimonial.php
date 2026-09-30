<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['user_name', 'user_avatar', 'role_or_company', 'country', 'content', 'rating', 'is_published', 'sort', 'source', 'source_url', 'external_id'];

    protected $casts = ['rating' => 'integer', 'is_published' => 'boolean', 'sort' => 'integer'];

    /**
     * Which review platforms Admin -> Tools -> Testimonials -> "Review
     * Platforms & Trust Badges" currently has enabled. Was previously
     * duplicated identically in HomeController and TestimonialController.
     *
     * @return array<int, string>
     */
    public static function enabledSources(): array
    {
        $sources = [];
        if (Setting::getBool('reviews_google_enabled', false)) {
            $sources[] = 'google';
        }
        if (Setting::getBool('reviews_trustpilot_enabled', false)) {
            $sources[] = 'trustpilot';
        }
        if (Setting::getBool('reviews_sitejabber_enabled', false)) {
            $sources[] = 'sitejabber';
        }
        if (Setting::getBool('reviews_manual_enabled', true)) {
            $sources[] = 'manual';
        }

        return $sources;
    }

    /**
     * Published, and from a currently-enabled source. A sentinel value
     * (rather than an empty whereIn) makes the "nothing enabled" case an
     * explicit, obviously-intentional no-match instead of relying on how a
     * given query builder version happens to compile an empty whereIn.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereIn('source', static::enabledSources() ?: ['__none__']);
    }
}
