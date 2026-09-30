<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Agent WL — per-user wallet balance (PM-014).
 *
 * The balance column is ONLY ever written by WalletService inside a
 * lockForUpdate transaction — never from controllers or views.
 */
class Wallet extends Model
{
    protected $fillable = [
        'user_id', 'balance', 'currency', 'is_locked', 'locked_reason',
    ];

    protected $casts = [
        'balance'   => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest('id');
    }

    /** Get (or lazily create) the wallet row for a user — balance starts at 0. */
    public static function forUser($user): self
    {
        $userId = is_object($user) ? $user->id : (int) $user;

        return static::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
    }
}
