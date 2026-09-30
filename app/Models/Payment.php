<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Agent PM — authoritative payments ledger row (PM-006/PM-005/PM-012).
 *
 * - amounts are major units (e.g. USD dollars), always resolved server-side
 *   from the accepted offerorder — never from client input (PM-009)
 * - state transitions go through App\Services\Payments\PaymentService only;
 *   the map lives in config/admin_payments_engine.php
 * - NO card data ever: no PAN/CVV columns exist and none may be added
 */
class Payment extends Model
{
    public const STATUS_PENDING              = 'pending';
    public const STATUS_METHOD_SELECTED      = 'method_selected';
    public const STATUS_AWAITING_PAYMENT     = 'awaiting_payment';
    public const STATUS_AWAITING_VERIFICATION= 'awaiting_verification';
    public const STATUS_PROCESSING           = 'processing';
    public const STATUS_PAID                 = 'paid';
    public const STATUS_FAILED               = 'failed';
    public const STATUS_CANCELLED            = 'cancelled';
    public const STATUS_REFUNDED             = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED   = 'partially_refunded';

    /** Private disk + directory where bank receipts are stored (C3). */
    public const PROOF_DISK = 'local';
    public const PROOF_DIR  = 'payments-proofs';

    /** Statuses that still allow the customer to switch method (PM-004). */
    public const METHOD_CHANGEABLE = [
        self::STATUS_PENDING,
        self::STATUS_METHOD_SELECTED,
        self::STATUS_AWAITING_PAYMENT,
    ];

    /** Statuses considered "open" (an active attempt exists for the order). */
    public const OPEN_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_METHOD_SELECTED,
        self::STATUS_AWAITING_PAYMENT,
        self::STATUS_AWAITING_VERIFICATION,
        self::STATUS_PROCESSING,
    ];

    protected $fillable = [
        'reference', 'order_id', 'request_id', 'user_id', 'attempt',
        'amount', 'amount_refunded', 'currency',
        'payment_method_id', 'payment_method_code', 'gateway', 'bank_account_id',
        'gateway_transaction_id', 'status', 'status_set_by',
        'initiated_at', 'paid_at', 'failed_at', 'cancelled_at', 'refunded_at',
        'verified_at', 'verified_by', 'proof_path', 'proof_uploaded_at',
        'failure_reason', 'metadata',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'amount_refunded' => 'decimal:2',
        'metadata'        => 'array',
        'initiated_at'    => 'datetime',
        'paid_at'         => 'datetime',
        'failed_at'       => 'datetime',
        'cancelled_at'    => 'datetime',
        'refunded_at'     => 'datetime',
        'verified_at'     => 'datetime',
        'proof_uploaded_at' => 'datetime',
    ];

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /* ---------------- helpers ---------------- */

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function methodChangeable(): bool
    {
        return in_array($this->status, self::METHOD_CHANGEABLE, true);
    }

    public function stateLabel(): string
    {
        return config('admin_payments_engine.states.' . $this->status . '.label', ucwords(str_replace('_', ' ', $this->status)));
    }

    public function stateColor(): string
    {
        return config('admin_payments_engine.states.' . $this->status . '.color', 'secondary');
    }

    /** Customer-safe failure copy — technical details stay in logs/audit only. */
    public function friendlyFailure(): string
    {
        return 'We could not complete your payment. Nothing was charged. Please try again or choose another payment method.';
    }

    /**
     * Resolve the stored receipt to a ['disk' =>, 'path' =>] pair on a
     * PRIVATE disk (C3). New rows hold `payments-proofs/...` (storage-
     * relative). Legacy rows recorded a public-relative path
     * (`uploads/payments/proofs/...`); those files were migrated into
     * payments-proofs/ keeping the filename, so resolve by basename —
     * callers 404 cleanly when the file is genuinely absent.
     */
    public function proofDiskPath(): ?array
    {
        $path = trim((string) $this->proof_path);

        if ($path === '') {
            return null;
        }

        if (strpos($path, self::PROOF_DIR . '/') === 0) {
            return ['disk' => self::PROOF_DISK, 'path' => $path];
        }

        return ['disk' => self::PROOF_DISK, 'path' => self::PROOF_DIR . '/' . basename(str_replace('\\', '/', $path))];
    }

    /** True when the stored receipt actually exists on the private disk. */
    public function proofFileExists(): bool
    {
        $resolved = $this->proofDiskPath();

        return $resolved !== null && Storage::disk($resolved['disk'])->exists($resolved['path']);
    }

    /** Merge data into the JSON metadata bag (webhook dedupe, gateway refs...). */
    public function mergeMetadata(array $data): void
    {
        $this->metadata = array_merge($this->metadata ?? [], $data);
    }

    /** Stripe minor-units conversion for this row. */
    public function minorUnits(): int
    {
        return (int) round(((float) $this->amount) * 100);
    }
}
