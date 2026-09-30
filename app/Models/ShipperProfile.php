<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipperProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'level', 'status', 'service_countries',
        'services_offered', 'residence_type', 'has_storage',
        'max_concurrent_orders', 'current_active_orders',
        'wallet_balance', 'wallet_pending', 'total_earned',
        'rating', 'total_ratings', 'total_completed',
        'kyc_status', 'payout_method', 'payout_details_encrypted',
        'social_links', 'reference_1', 'reference_2',
        'verified_at', 'suspended_at', 'suspension_reason',
    ];

    protected $casts = [
        'service_countries' => 'array',
        'services_offered'  => 'array',
        'social_links'      => 'array',
        'reference_1'       => 'array',
        'reference_2'       => 'array',
        'has_storage'       => 'boolean',
        'verified_at'       => 'datetime',
        'suspended_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kycDocuments()
    {
        return $this->hasMany(ShipperKycDocument::class, 'shipper_profile_id');
    }

    public function assignments()
    {
        return $this->hasMany(ShipperOrderAssignment::class, 'shipper_profile_id');
    }

    public function quotes()
    {
        return $this->hasMany(ShipperQuote::class, 'shipper_profile_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(ShipperWalletTransaction::class, 'shipper_profile_id');
    }

    public function ratings()
    {
        return $this->hasMany(ShipperRating::class, 'shipper_profile_id');
    }

    public function payoutRequests()
    {
        return $this->hasMany(ShipperPayoutRequest::class, 'shipper_profile_id');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeForCountry($q, string $country)
    {
        return $q->whereJsonContains('service_countries', strtoupper($country));
    }

    public function scopeLevel($q, int $level)
    {
        return $q->where('level', '>=', $level);
    }

    public function scopeHasCapacity($q)
    {
        return $q->whereRaw('current_active_orders < max_concurrent_orders');
    }

    // ── Accessors ───────────────────────────────────────────

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getCanAcceptOrdersAttribute(): bool
    {
        return $this->status === 'active'
            && $this->current_active_orders < $this->max_concurrent_orders
            && $this->kyc_status === 'approved';
    }

    // ── Wallet operations (always through these methods) ────

    public function creditWallet(float $amount, string $note = '', $orderId = null, $assignmentId = null): void
    {
        $this->increment('wallet_balance', $amount);
        $this->increment('total_earned', $amount);
        ShipperWalletTransaction::create([
            'shipper_profile_id' => $this->id,
            'type'               => 'credit',
            'amount'             => $amount,
            'balance_after'      => $this->fresh()->wallet_balance,
            'order_id'           => $orderId,
            'assignment_id'      => $assignmentId,
            'note'               => $note,
            'status'             => 'completed',
        ]);
    }

    public function holdAmount(float $amount, $assignmentId = null): void
    {
        $this->increment('wallet_pending', $amount);
        ShipperWalletTransaction::create([
            'shipper_profile_id' => $this->id,
            'type'               => 'hold',
            'amount'             => $amount,
            'balance_after'      => $this->fresh()->wallet_balance,
            'assignment_id'      => $assignmentId,
            'note'               => '20% dispute buffer held',
            'status'             => 'pending',
        ]);
    }

    public function releaseHold(float $amount, $assignmentId = null): void
    {
        $this->decrement('wallet_pending', $amount);
        $this->increment('wallet_balance', $amount);
        ShipperWalletTransaction::create([
            'shipper_profile_id' => $this->id,
            'type'               => 'hold_release',
            'amount'             => $amount,
            'balance_after'      => $this->fresh()->wallet_balance,
            'assignment_id'      => $assignmentId,
            'note'               => 'Dispute buffer released',
            'status'             => 'completed',
        ]);
    }

    public function recalculateRating(): void
    {
        $avg   = ShipperRating::where('shipper_profile_id', $this->id)->avg('overall_rating');
        $count = ShipperRating::where('shipper_profile_id', $this->id)->count();
        $this->update(['rating' => round((float) ($avg ?? 0), 2), 'total_ratings' => $count]);
    }

    public function checkLevelProgression(): void
    {
        if ($this->level < 2
            && $this->total_completed >= 5
            && $this->rating >= 4.0
            && $this->total_ratings >= 3) {
            $this->update([
                'level'                 => 2,
                'max_concurrent_orders' => 10,
            ]);
        }
        if ($this->level < 3
            && $this->total_completed >= 25
            && $this->rating >= 4.5
            && $this->total_ratings >= 15) {
            $this->update([
                'level'                 => 3,
                'max_concurrent_orders' => 999,
            ]);
        }
    }
}
