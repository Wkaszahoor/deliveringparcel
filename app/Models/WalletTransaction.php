<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Agent WL — append-only wallet ledger (PM-014).
 *
 * Every row carries the balance_after snapshot, so the running balance can
 * be re-derived/audited from the ledger alone. Rows are never updated or
 * deleted by application code.
 */
class WalletTransaction extends Model
{
    public const TYPE_TOPUP         = 'topup';          // credit — money in via gateway
    public const TYPE_ORDER_PAYMENT = 'order_payment';  // debit  — paying an order from balance
    public const TYPE_REFUND        = 'refund';         // credit — order payment refunded back to wallet
    public const TYPE_ADJUSTMENT    = 'adjustment';     // admin credit/debit (manual correction)

    public const DIRECTION_CREDIT = 'credit';
    public const DIRECTION_DEBIT  = 'debit';

    protected $fillable = [
        'wallet_id', 'user_id', 'type', 'direction', 'amount', 'balance_after',
        'payment_id', 'order_id', 'reference', 'description', 'performed_by', 'metadata',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata'      => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function label(): string
    {
        return [
            static::TYPE_TOPUP         => 'Top-up',
            static::TYPE_ORDER_PAYMENT => 'Order payment',
            static::TYPE_REFUND        => 'Refund to wallet',
            static::TYPE_ADJUSTMENT    => 'Admin adjustment',
        ][$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }
}
