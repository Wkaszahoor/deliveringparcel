<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Agent PM — service-context → payment method routing rules (PM-001).
 *
 * One row per (service_context, payment_method). Admin editable matrix;
 * the resolver only ever offers methods with is_allowed = 1.
 */
class PaymentMethodRule extends Model
{
    public const CONTEXT_SHIP_FOR_ME = 'ship_for_me';
    public const CONTEXT_SHOP_FOR_ME = 'shop_for_me';
    public const CONTEXT_CUSTOM      = 'custom';
    public const CONTEXT_DEFAULT     = 'default';

    protected $fillable = [
        'service_context', 'payment_method_id', 'is_allowed', 'priority',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
        'priority'   => 'integer',
    ];

    public static function contexts(): array
    {
        return [
            self::CONTEXT_SHIP_FOR_ME => 'Ship For Me',
            self::CONTEXT_SHOP_FOR_ME => 'Book For Me (Shop For Me)',
            self::CONTEXT_CUSTOM      => 'Custom Order',
            self::CONTEXT_DEFAULT     => 'Default (fallback)',
        ];
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
