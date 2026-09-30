<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * RV-002 / RV-003 / RV-008: Customer review.
 *
 * Hard model guards (defense in depth — validation also runs at the
 * controller/service layer):
 *  - rating:  config('admin_reviews.rating_min'..'rating_max') only,
 *             plus a DB CHECK constraint.
 *  - status:  only keys of config('admin_reviews.statuses').
 *  - dedup_key: computed server-side on insert; UNIQUE index makes the
 *             same (user, order, item, product, service, type)
 *             combination impossible to insert twice.
 */
class Review extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'order_item_id',
        'product_id',
        'service_id',
        'review_type',
        'rating',
        'title',
        'body',
        'status',
        'is_featured',
        'is_verified_purchase',
        'is_published',
        'admin_notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'is_featured'         => 'boolean',
        'is_verified_purchase'=> 'boolean',
        'is_published'        => 'boolean',
        'approved_at'         => 'datetime',
        'rejected_at'         => 'datetime',
    ];

    /* ---------------- guards ---------------- */

    public function setRatingAttribute($value)
    {
        $min = (int) config('admin_reviews.rating_min', 1);
        $max = (int) config('admin_reviews.rating_max', 5);
        if (!is_numeric($value) || (int) $value < $min || (int) $value > $max) {
            throw new InvalidArgumentException("Rating must be between {$min} and {$max}.");
        }
        $this->attributes['rating'] = (int) $value;
    }

    public function setStatusAttribute($value)
    {
        if (!in_array($value, array_keys(config('admin_reviews.statuses', [])), true)) {
            throw new InvalidArgumentException("Illegal review status: {$value}.");
        }
        $this->attributes['status'] = $value;
    }

    public function setReviewTypeAttribute($value)
    {
        if (!in_array($value, array_keys(config('admin_reviews.types', [])), true)) {
            throw new InvalidArgumentException("Illegal review type: {$value}.");
        }
        $this->attributes['review_type'] = $value;
    }

    /* ---------------- duplicate guard ---------------- */

    /**
     * Deterministic duplicate key: nullable FKs collapse to 0 so the UNIQUE
     * index on dedup_key works despite MySQL NULL semantics.
     */
    public static function dedupKey(int $userId, $orderId, $orderItemId, $productId, $serviceId, string $reviewType): string
    {
        return implode(':', [
            $userId,
            (int) ($orderId ?: 0),
            (int) ($orderItemId ?: 0),
            (int) ($productId ?: 0),
            (int) ($serviceId ?: 0),
            $reviewType,
        ]);
    }

    protected static function booted()
    {
        static::creating(function (Review $review) {
            if (empty($review->dedup_key)) {
                $review->dedup_key = static::dedupKey(
                    (int) $review->user_id,
                    $review->order_id,
                    $review->order_item_id,
                    $review->product_id,
                    $review->service_id,
                    (string) $review->review_type
                );
            }
        });
    }

    /* ---------------- scopes ---------------- */

    /** RV-003: ONLY approved reviews may ever be shown publicly. */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'approved')->where('is_published', true);
    }

    /* ---------------- relations ---------------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(Orderproducts::class, 'order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /* ---------------- helpers ---------------- */

    public function statusMeta(): array
    {
        return config('admin_reviews.statuses.' . $this->status, ['label' => $this->status, 'color' => 'bg-secondary', 'next' => []]);
    }

    public function typeLabel(): string
    {
        return (string) config('admin_reviews.types.' . $this->review_type, $this->review_type);
    }
}
