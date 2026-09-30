<?php

namespace App\Services;

use App\Models\Orders;
use App\Models\Review;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * RV-001 / RV-003 / RV-008: SERVER-SIDE review eligibility.
 *
 * Rules (all enforced here, never in the browser):
 *  1. An order can only be reviewed when its `order_status` is listed in
 *     config('admin_reviews.eligible_order_statuses') (default: completed).
 *  2. The order must belong to the authenticated user.
 *  3. One review per (user, order, item, product, service, review_type)
 *     combination — friendly pre-check here + hard UNIQUE(dedup_key) in DB.
 *  4. is_verified_purchase is derived from rule 1 + 2 ONLY.
 */
class ReviewEligibility
{
    /** Configured eligible order statuses (RV-001, configurable). */
    public static function eligibleStatuses(): array
    {
        return array_values(array_map('trim', (array) config('admin_reviews.eligible_order_statuses', ['completed'])));
    }

    public static function isEligibleStatus(?string $status): bool
    {
        return in_array(trim((string) $status), self::eligibleStatuses(), true);
    }

    /**
     * All reviewable orders for a user (status eligible), newest first,
     * with the user's existing review status for each order attached.
     */
    public static function eligibleOrders($user, int $perPage = 15)
    {
        $userId = $user instanceof Authenticatable ? (int) $user->getAuthIdentifier() : (int) $user->id;

        $reviewed = Review::where('user_id', $userId)->get()
            ->keyBy(fn (Review $r) => $r->order_id . ':' . $r->review_type);

        return Orders::query()
            ->where('user_id', $userId)
            ->whereIn('order_status', self::eligibleStatuses())
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->through(function (Orders $o) use ($reviewed) {
                $orderReview = $reviewed->get($o->id . ':order');
                $o->review_status   = optional($orderReview)->status;
                $o->review_id       = optional($orderReview)->id;
                $o->reviewed_at     = optional($orderReview)->created_at;
                $o->review_verified = optional($orderReview)->is_verified_purchase;
                return $o;
            });
    }

    /**
     * Full eligibility verdict for submitting a review.
     * Returns ['ok' => bool, 'reason' => string, 'order' => ?Orders].
     */
    public static function assess($user, $orderId, string $reviewType = 'order'): array
    {
        if (!in_array($reviewType, array_keys(config('admin_reviews.types', [])), true)) {
            return ['ok' => false, 'reason' => 'Unknown review type.', 'order' => null];
        }

        $order = Orders::find($orderId);
        if (!$order) {
            return ['ok' => false, 'reason' => 'Order not found.', 'order' => null];
        }

        $userId = $user instanceof Authenticatable ? (int) $user->getAuthIdentifier() : (int) $user->id;
        if ((int) $order->user_id !== $userId) {
            // Deliberately vague: never leak other customers' order state.
            return ['ok' => false, 'reason' => 'Order not found.', 'order' => null];
        }

        if (!self::isEligibleStatus($order->order_status)) {
            return ['ok' => false, 'reason' => 'This order cannot be reviewed yet (status: ' . ($order->order_status ?: 'pending') . ').', 'order' => $order];
        }

        if (self::hasReviewed($userId, $orderId, $reviewType)) {
            return ['ok' => false, 'reason' => 'You have already submitted a ' . $reviewType . ' review for this order.', 'order' => $order];
        }

        return ['ok' => true, 'reason' => '', 'order' => $order];
    }

    /** Friendly duplicate pre-check (DB UNIQUE(dedup_key) is the hard guard). */
    public static function hasReviewed(int $userId, $orderId, string $reviewType = 'order', $orderItemId = null, $productId = null, $serviceId = null): bool
    {
        return Review::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->where('review_type', $reviewType)
            ->when($orderItemId, fn ($q) => $q->where('order_item_id', $orderItemId))
            ->when(!$orderItemId, fn ($q) => $q->whereNull('order_item_id'))
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when(!$productId, fn ($q) => $q->whereNull('product_id'))
            ->when($serviceId, fn ($q) => $q->where('service_id', $serviceId))
            ->when(!$serviceId, fn ($q) => $q->whereNull('service_id'))
            ->exists();
    }

    /**
     * RV-003: verified-purchase flag derived ONLY from real order ownership
     * + eligible (delivered/completed) status. Never accepts client input.
     */
    public static function isVerifiedPurchase($user, $orderId): bool
    {
        if (!$orderId || !$user) {
            return false;
        }
        $userId = $user instanceof Authenticatable ? (int) $user->getAuthIdentifier() : (int) $user->id;

        return Orders::where('id', $orderId)
            ->where('user_id', $userId)
            ->whereIn('order_status', self::eligibleStatuses())
            ->exists();
    }

    /** Quick counters for the customer "my reviews" page. */
    public static function countsForUser(int $userId): array
    {
        return Review::where('user_id', $userId)
            ->select('status', DB::raw('COUNT(*) AS n'))
            ->groupBy('status')
            ->pluck('n', 'status')
            ->toArray();
    }
}
