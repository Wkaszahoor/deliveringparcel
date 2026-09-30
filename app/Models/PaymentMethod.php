<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Agent PM — configurable payment method catalogue (PM-001).
 *
 * Rows drive the routing resolver; admins flip enabled/priority/min-max
 * without code changes. Adding a gateway later = insert a row + adapter class.
 */
class PaymentMethod extends Model
{
    protected $fillable = [
        'code', 'name', 'gateway', 'description',
        'is_enabled', 'priority', 'min_amount', 'max_amount',
    ];

    protected $casts = [
        'is_enabled'   => 'boolean',
        'priority'     => 'integer',
        'min_amount'   => 'decimal:2',
        'max_amount'   => 'decimal:2',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(PaymentMethodRule::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /** Whether an amount (major units) fits the configured min/max window. */
    public function coversAmount(?float $amount): bool
    {
        if ($amount === null) {
            return true;
        }
        if ($this->min_amount !== null && $amount < (float) $this->min_amount) {
            return false;
        }
        if ($this->max_amount !== null && $amount > (float) $this->max_amount) {
            return false;
        }

        return true;
    }

    /** Reasons (customer-safe) why this method is not selectable for an amount. */
    public function unavailableReason(?float $amount): ?string
    {
        if (!$this->is_enabled) {
            return 'This payment method is currently unavailable.';
        }
        if ($amount !== null && $this->min_amount !== null && $amount < (float) $this->min_amount) {
            return 'Minimum amount for this method is ' . number_format((float) $this->min_amount, 2) . '.';
        }
        if ($amount !== null && $this->max_amount !== null && $amount > (float) $this->max_amount) {
            return 'Maximum amount for this method is ' . number_format((float) $this->max_amount, 2) . '.';
        }

        return null;
    }
}
