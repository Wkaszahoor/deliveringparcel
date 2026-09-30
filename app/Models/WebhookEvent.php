<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Agent WL — webhook delivery history (PM-017).
 *
 * Every Stripe webhook delivery is persisted here with its outcome and full
 * payload: processed, ignored (allow-list), signature_failed, error. The
 * admin page matches these rows against the Stripe account's event list.
 */
class WebhookEvent extends Model
{
    protected $fillable = [
        'event_id', 'gateway', 'type', 'api_version', 'status',
        'payment_id', 'result', 'payload', 'received_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'received_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function statusColor(): string
    {
        return [
            'processed'        => 'success',
            'ignored'          => 'secondary',
            'received'         => 'info',
            'signature_failed' => 'danger',
            'error'            => 'danger',
        ][$this->status] ?? 'secondary';
    }
}
