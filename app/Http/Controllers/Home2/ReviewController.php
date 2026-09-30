<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Review;
use App\Models\ReviewFilterPreset;
use App\Services\ReviewEligibility;
use App\Services\ReviewStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * RV-007: customer review submission.
 *
 * SECURITY: user_id, is_verified_purchase, is_published, status and all
 * moderation stamps are derived SERVER-SIDE only — the browser may never
 * submit any of them (unknown keys are simply ignored, fillable is strict).
 */
class ReviewController extends Controller
{
    /** GET /home2/reviews — eligible (delivered/completed) orders + review state. */
    public function eligibleOrders(Request $request)
    {
        $orders = ReviewEligibility::eligibleOrders($request->user(), 10);

        return view('home2.reviews.index', [
            'orders'         => $orders,
            'statusMap'      => config('admin_reviews.statuses'),
            'eligibleLabel'  => implode(' / ', ReviewEligibility::eligibleStatuses()),
        ]);
    }

    /** GET /home2/reviews/create?order=X — submission form (re-checked on store). */
    public function create(Request $request)
    {
        $verdict = ReviewEligibility::assess($request->user(), (int) $request->input('order', 0));
        if (!$verdict['ok']) {
            return redirect()->route('home2.reviews.index')->with('error', $verdict['reason']);
        }

        return view('home2.reviews.create', [
            'order' => $verdict['order'],
            'types' => config('admin_reviews.types'),
        ]);
    }

    /** POST /home2/reviews — store a PENDING review (RV-001/003/007). */
    public function store(Request $request)
    {
        $min = (int) config('admin_reviews.rating_min', 1);
        $max = (int) config('admin_reviews.rating_max', 5);
        $types = implode(',', array_keys(config('admin_reviews.types', [])));

        $data = $request->validate([
            'rating'       => 'required|integer|between:' . $min . ',' . $max,
            'title'        => 'nullable|string|max:' . config('admin_reviews.validation.title_max', 120),
            'body'         => 'required|string|min:' . config('admin_reviews.validation.body_min', 10) . '|max:' . config('admin_reviews.validation.body_max', 2000),
            'order_id'     => 'required|integer',
            'review_type'  => 'nullable|in:' . $types,
        ], [
            'rating.between' => "Rating must be between {$min} and {$max}.",
            'body.min'       => 'Please write at least :min characters.',
        ]);

        $reviewType = $data['review_type'] ?? 'order';
        $verdict = ReviewEligibility::assess($request->user(), (int) $data['order_id'], $reviewType);
        if (!$verdict['ok']) {
            throw ValidationException::withMessages(['order_id' => $verdict['reason']]);
        }

        // Forged client keys (user_id / is_verified_purchase / status / is_published / …)
        // never reach the model — only vetted fields below.
        try {
            $review = Review::create([
                'user_id'              => $request->user()->id,                 // server-derived
                'order_id'             => (int) $data['order_id'],
                'is_verified_purchase' => ReviewEligibility::isVerifiedPurchase($request->user(), (int) $data['order_id']), // server-derived
                'review_type'          => $reviewType,
                'rating'               => (int) $data['rating'],
                'title'                => trim((string) ($data['title'] ?? '')) ?: null,
                'body'                 => trim((string) $data['body']),
                'status'               => 'pending',                            // always pre-moderated
                'is_published'         => false,                                // approval publishes
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Hard DB guard: UNIQUE(dedup_key) fired (race or forged retry).
            if (\Illuminate\Support\Str::contains($e->getMessage(), ['Duplicate entry', 1062])) {
                throw ValidationException::withMessages(['order_id' => 'You have already reviewed this order.']);
            }
            Log::warning('review store failed: ' . $e->getMessage());
            throw ValidationException::withMessages(['order_id' => 'Could not save your review, please try again.']);
        }

        return redirect()->route('home2.reviews.mine')
            ->with('success', 'Thank you! Your review has been submitted and is awaiting moderation before it appears on the site.');
    }

    /** GET /home2/reviews/mine — my submissions + their moderation state. */
    public function myReviews(Request $request)
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with('order:id,order_id,order_status')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('home2.reviews.mine', [
            'reviews'   => $reviews,
            'statusMap' => config('admin_reviews.statuses'),
        ]);
    }

    /**
     * GET /home2/reviews/widget-preview — RV-006 demo page rendering the
     * anonymous <x-review-widget> component with several instance configs
     * (incl. the seeded 'homepage_reviews' preset). Public: only APPROVED
     * reviews can ever appear (enforced in ReviewStats::publicListing).
     */
    public function widgetPreview(Request $request)
    {
        $preset = ReviewFilterPreset::where('name', 'homepage_reviews')->first();

        return view('home2.reviews.widget-preview', [
            'instances' => [
                [
                    'title'  => 'Preset: homepage_reviews (seeded, approved + rating>=4 + verified)',
                    'widget' => $preset ? array_merge($preset->filters, ['title' => 'What our customers say']) : ['status' => 'approved', 'rating_min' => 4, 'verified' => 1, 'limit' => 6, 'title' => 'What our customers say'],
                ],
                [
                    'title'  => 'Instance: grid layout, newest, limit 3',
                    'widget' => ['layout' => 'grid', 'limit' => 3, 'sort' => 'newest', 'title' => 'Latest reviews', 'show_body' => false],
                ],
                [
                    'title'  => 'Instance: list layout, highest rated',
                    'widget' => ['layout' => 'list', 'limit' => 3, 'sort' => 'rating_high', 'title' => 'Top rated', 'show_date' => false],
                ],
                [
                    'title'  => 'Instance: testimonial layout (featured only)',
                    'widget' => ['layout' => 'testimonial', 'featured' => 1, 'limit' => 2, 'sort' => 'featured', 'title' => 'Featured testimonials', 'show_summary' => false],
                ],
            ],
            'summaryStats' => ReviewStats::summary(),
        ]);
    }
}
