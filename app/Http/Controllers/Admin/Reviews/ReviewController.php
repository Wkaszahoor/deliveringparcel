<?php

namespace App\Http\Controllers\Admin\Reviews;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewFilterPreset;
use App\Models\ShopProduct;
use App\Models\Service;
use App\Services\AuditLogger;
use App\Services\ReviewFilters;
use App\Services\ReviewStats;
use Illuminate\Http\Request;

/**
 * RV-004 / RV-005 / RV-008: admin review management panel.
 * Moderation is a state machine driven by config('admin_reviews.statuses').
 */
class ReviewController extends Controller
{
    /* ================= views ================= */

    public function index()
    {
        return view('admin.reviews.index', [
            'statusMap'  => config('admin_reviews.statuses'),
            'types'      => config('admin_reviews.types'),
            'sortOpts'   => config('admin_reviews.sort_options'),
            'stats'      => ReviewStats::summary(),
            'dist'       => ReviewStats::distribution(),
            'monthly'    => ReviewStats::monthly(),
            'presets'    => ReviewFilterPreset::orderBy('name')->get(),
            'products'   => ShopProduct::orderBy('name')->limit(200)->get(['id', 'name']),
            'services'   => Service::orderBy('title')->limit(200)->get(['id', 'title']),
        ]);
    }

    /** RV-005: data endpoint — every filter AND-combinable (see ReviewFilters). */
    public function data(Request $request)
    {
        $statusMap = config('admin_reviews.statuses');

        $rows = ReviewFilters::apply(ReviewFilters::baseQuery(), $request->all())
            ->paginate(dp_per_page($request))
            ->through(function (Review $r) use ($statusMap) {
                $meta = $statusMap[$r->status] ?? ['label' => $r->status, 'color' => 'bg-secondary'];

                return [
                    'id'         => $r->id,
                    'user'       => optional($r->user)->name ?: '(user #' . $r->user_id . ')',
                    'email'      => optional($r->user)->email,
                    'type'       => $r->typeLabel(),
                    'rating'     => (int) $r->rating,
                    'title'      => $r->title,
                    'status_lbl' => $meta['label'],
                    'status'     => $r->status,
                    'color'      => $meta['color'],
                    'verified'   => (bool) $r->is_verified_purchase,
                    'featured'   => (bool) $r->is_featured,
                    'order_id'   => $r->order_id,
                    'created_at' => optional($r->created_at)->format('M d, Y'),
                    'urls'       => $this->actionUrls($r),
                ];
            });

        return response()->json($rows->toArray());
    }

    public function show(Review $review)
    {
        $review->load('user:id,name,email', 'order', 'product:id,name,slug', 'service:id,title,slug');

        return view('admin.reviews.show', [
            'review'    => $review,
            'statusMap' => config('admin_reviews.statuses'),
            'types'     => config('admin_reviews.types'),
        ]);
    }

    /* ================= moderation (RV-003) ================= */

    public function approve(Request $request, Review $review)
    {
        return $this->transition($request, $review, 'approved');
    }

    public function reject(Request $request, Review $review)
    {
        return $this->transition($request, $review, 'rejected');
    }

    public function hide(Request $request, Review $review)
    {
        return $this->transition($request, $review, 'hidden');
    }

    public function restore(Request $request, Review $review)
    {
        return $this->transition($request, $review, 'pending');
    }

    public function spam(Request $request, Review $review)
    {
        return $this->transition($request, $review, 'spam');
    }

    /** Generic state transition honoring config('admin_reviews.statuses.*.next'). */
    protected function transition(Request $request, Review $review, string $to)
    {
        $from = $review->status;
        $allowed = config('admin_reviews.statuses.' . $from . '.next', []);

        if (!in_array($to, array_keys(config('admin_reviews.statuses')), true)) {
            return $this->back($review, 'Illegal status: ' . e($to), false);
        }
        if (!in_array($to, $allowed, true)) {
            return $this->back($review, 'Cannot move a review from ' . e($from) . ' to ' . e($to) . '.', false);
        }

        $notes = $review->admin_notes;
        if ($n = trim((string) $request->input('note'))) {
            $notes = mb_substr($n, 0, 2000);
        }

        $review->fill(['status' => $to, 'admin_notes' => $notes]);
        $review->is_published = ($to === 'approved'); // unmoderated => never public

        if ($to === 'approved') {
            $review->approved_by = auth()->id();
            $review->approved_at = now();
            $review->rejected_by = null;
            $review->rejected_at = null;
        } elseif ($to === 'rejected') {
            $review->rejected_by = auth()->id();
            $review->rejected_at = now();
        } else {
            // hidden / spam / back to pending clears publication
            $review->approved_by = null;
            $review->approved_at = null;
            $review->rejected_by = null;
            $review->rejected_at = null;
        }

        $review->save();
        AuditLogger::log($review, 'review-' . $to);

        return $this->back($review, 'Review #' . $review->id . ' moved to ' . e($to) . '.');
    }

    /* ================= featured toggle (RV-004) ================= */

    public function feature(Review $review)
    {
        $review->is_featured = true;
        $review->save();
        AuditLogger::log($review, 'review-featured');

        return $this->back($review, 'Review #' . $review->id . ' featured.');
    }

    public function unfeature(Review $review)
    {
        $review->is_featured = false;
        $review->save();
        AuditLogger::log($review, 'review-unfeatured');

        return $this->back($review, 'Review #' . $review->id . ' unfeatured.');
    }

    public function destroy(Review $review)
    {
        AuditLogger::log($review, 'review-deleted');
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Review #' . $review->id . ' deleted.');
    }

    /* ================= bulk (RV-004) ================= */

    /** Single-request bulk moderation: POST {action, ids[]}. */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,reject,hide,restore,spam,feature,unfeature,delete',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
        ]);

        $reviews = Review::whereIntegerInRaw('id', $data['ids'])->get();
        $done = 0;
        $skipped = 0;

        foreach ($reviews as $review) {
            $ok = $this->applyBulkAction($review, $data['action']);
            $ok ? $done++ : $skipped++;
        }

        $msg = 'Bulk ' . $data['action'] . ': ' . $done . ' done' . ($skipped ? ', ' . $skipped . ' skipped (invalid transition)' : '') . '.';

        return redirect()->route('admin.reviews.index')->with($skipped && !$done ? 'error' : 'success', $msg);
    }

    private function applyBulkAction(Review $review, string $action): bool
    {
        $targets = [
            'approve'  => 'approved',
            'reject'   => 'rejected',
            'hide'     => 'hidden',
            'restore'  => 'pending',
            'spam'     => 'spam',
        ];

        if (isset($targets[$action])) {
            $to = $targets[$action];
            if (!in_array($to, config('admin_reviews.statuses.' . $review->status . '.next', []), true)) {
                return false; // respect the state machine in bulk too
            }
            $review->status = $to;
            $review->is_published = ($to === 'approved');
            if ($to === 'approved') {
                $review->approved_by = auth()->id();
                $review->approved_at = now();
                $review->rejected_by = null;
                $review->rejected_at = null;
            } elseif ($to === 'rejected') {
                $review->rejected_by = auth()->id();
                $review->rejected_at = now();
            } else {
                $review->approved_by = $review->approved_at = null;
                $review->rejected_by = $review->rejected_at = null;
            }
            $review->save();
            AuditLogger::log($review, 'review-bulk-' . $to);

            return true;
        }

        if ($action === 'feature' || $action === 'unfeature') {
            $review->is_featured = ($action === 'feature');
            $review->save();
            AuditLogger::log($review, 'review-bulk-' . $action);

            return true;
        }

        if ($action === 'delete') {
            AuditLogger::log($review, 'review-bulk-deleted');
            $review->delete();

            return true;
        }

        return false;
    }

    /* ================= presets (RV-005) ================= */

    public function presetStore(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120|regex:/^[a-z0-9_\-]+$/i',
            'filters' => 'required|array',
        ]);

        ReviewFilterPreset::updateOrCreate(
            ['name' => $data['name']],
            ['filters' => ReviewFilters::sanitize($data['filters'])]
        );

        return redirect()->route('admin.reviews.index')->with('success', 'Preset "' . e($data['name']) . '" saved.');
    }

    /** Load a saved preset onto the index page as query-string filters. */
    public function presetLoad(ReviewFilterPreset $preset)
    {
        return redirect()->route('admin.reviews.index', array_filter(
            $preset->filters,
            fn ($v) => $v !== '' && $v !== null && $v !== []
        ));
    }

    public function presetDestroy(ReviewFilterPreset $preset)
    {
        $name = $preset->name;
        $preset->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Preset "' . e($name) . '" removed.');
    }

    /* ================= helpers ================= */

    /** Action URLs per row — only transitions allowed by the state machine. */
    private function actionUrls(Review $r): array
    {
        $urls = ['show' => route('admin.reviews.show', $r->id)];
        $next = config('admin_reviews.statuses.' . $r->status . '.next', []);

        if (in_array('approved', $next, true)) {
            $urls['approve'] = route('admin.reviews.approve', $r->id);
        }
        if (in_array('rejected', $next, true)) {
            $urls['reject'] = route('admin.reviews.reject', $r->id);
        }
        if (in_array('hidden', $next, true)) {
            $urls['hide'] = route('admin.reviews.hide', $r->id);
        }
        if (in_array('pending', $next, true)) {
            $urls['restore'] = route('admin.reviews.restore', $r->id);
        }
        if (in_array('spam', $next, true)) {
            $urls['spam'] = route('admin.reviews.spam', $r->id);
        }

        $r->is_featured
            ? $urls['unfeature'] = route('admin.reviews.unfeature', $r->id)
            : $urls['feature'] = route('admin.reviews.feature', $r->id);

        return $urls;
    }

    private function back(Review $review, string $msg, bool $success = true)
    {
        $to = $success ? 'success' : 'error';

        return redirect()->route('admin.reviews.show', $review->id)->with($to, $msg);
    }
}
