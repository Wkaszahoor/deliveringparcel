<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Http;

/**
 * Pulls reviews for the configured Google Place (config('services.google_places'))
 * into the Testimonial table via the Places API "Place Details" endpoint, which
 * returns up to 5 of the place's most relevant reviews plus its aggregate rating —
 * no OAuth, no paid plan, just an API key. Imported rows land UNPUBLISHED
 * (source=google) so an admin still decides what actually shows on the site,
 * per Tools → Testimonials. Also refreshes the reviews_google_rating /
 * reviews_google_count settings used by the homepage trust badge.
 */
class GoogleReviewsSyncService
{
    public function configured(): bool
    {
        return filled(config('services.google_places.api_key')) && filled(config('services.google_places.place_id'));
    }

    /**
     * @return array{imported: int, skipped: int, rating: float|null, total: int|null, error: string|null}
     */
    public function sync(): array
    {
        if (! $this->configured()) {
            return ['imported' => 0, 'skipped' => 0, 'rating' => null, 'total' => null, 'error' => 'Google Places API key / Place ID not configured.'];
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => config('services.google_places.place_id'),
            'fields' => 'reviews,rating,user_ratings_total',
            'key' => config('services.google_places.api_key'),
        ]);

        if (! $response->successful()) {
            return ['imported' => 0, 'skipped' => 0, 'rating' => null, 'total' => null, 'error' => 'Google API request failed (HTTP ' . $response->status() . ').'];
        }

        $body = $response->json();

        if (($body['status'] ?? null) !== 'OK') {
            return ['imported' => 0, 'skipped' => 0, 'rating' => null, 'total' => null, 'error' => 'Google API error: ' . ($body['status'] ?? 'unknown')];
        }

        $result = $body['result'] ?? [];
        $reviews = $result['reviews'] ?? [];

        $imported = 0;
        $skipped = 0;

        foreach ($reviews as $review) {
            // Google doesn't give a stable review id via this endpoint — the
            // (author + time) pair is unique per review and stable across syncs.
            $externalId = md5(($review['author_name'] ?? '') . '|' . ($review['time'] ?? ''));

            $exists = Testimonial::where('source', 'google')->where('external_id', $externalId)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            Testimonial::create([
                'user_name' => $review['author_name'] ?? 'Google user',
                'role_or_company' => null,
                'content' => (string) ($review['text'] ?? ''),
                'rating' => $review['rating'] ?? null,
                'is_published' => false, // admin approves before it goes live
                'sort' => 0,
                'source' => 'google',
                'source_url' => $review['author_url'] ?? null,
                'external_id' => $externalId,
            ]);
            $imported++;
        }

        $rating = isset($result['rating']) ? (float) $result['rating'] : null;
        $total = isset($result['user_ratings_total']) ? (int) $result['user_ratings_total'] : null;

        if ($rating !== null) {
            Setting::set('reviews_google_rating', number_format($rating, 1), 'review_badges', 'string');
        }
        if ($total !== null) {
            Setting::set('reviews_google_count', (string) $total, 'review_badges', 'int');
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'rating' => $rating, 'total' => $total, 'error' => null];
    }
}
