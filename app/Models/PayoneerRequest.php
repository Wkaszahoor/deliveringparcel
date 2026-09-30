<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Payoneer link-payment request (manual mode).
 *
 * Lifecycle: requested → link_sent → (proof_submitted | marked_paid) → verified
 *            any active state → rejected | cancelled.
 * The Payment ledger row (payments table) is created when the admin sends the
 * link; verification is delegated to PaymentService so order/offer stay synced.
 */
class PayoneerRequest extends Model
{
    public const STATUS_REQUESTED       = 'requested';
    public const STATUS_LINK_SENT       = 'link_sent';
    public const STATUS_PROOF_SUBMITTED = 'proof_submitted';
    public const STATUS_MARKED_PAID     = 'marked_paid';
    public const STATUS_VERIFIED        = 'verified';
    public const STATUS_REJECTED        = 'rejected';
    public const STATUS_CANCELLED       = 'cancelled';

    public const ACTIVE_STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_LINK_SENT,
        self::STATUS_PROOF_SUBMITTED,
        self::STATUS_MARKED_PAID,
    ];

    protected $table = 'payoneer_requests';

    protected $fillable = [
        'order_id', 'user_id', 'payment_id', 'amount', 'currency', 'status',
        'link_url', 'link_note', 'reject_reason',
        'requested_at', 'link_sent_at', 'proof_submitted_at', 'marked_paid_at',
        'verified_at', 'handled_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'requested_at' => 'datetime',
        'link_sent_at' => 'datetime',
        'proof_submitted_at' => 'datetime',
        'marked_paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }
}
