<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'reference', 'customer_username', 'service_type',
        'country_required', 'brief_text', 'product_details',
        'contact_email', 'contact_phone', 'address_snippet',
        'value_range_min', 'value_range_max', 'status', 'is_frozen',
        'frozen_at', 'frozen_by', 'assigned_shipper_profile_id',
        'assigned_at', 'required_level', 'expires_at', 'created_by',
        'admin_internal_notes',
    ];

    protected $casts = [
        'product_details' => 'array',
        'is_frozen'       => 'boolean',
        'frozen_at'       => 'datetime',
        'assigned_at'     => 'datetime',
        'expires_at'      => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->reference)) {
                $last = static::withTrashed()->count() + 1;
                $model->reference = 'DP-SR-' . str_pad((string) $last, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }

    public function quotes()
    {
        return $this->hasMany(ShipperQuote::class, 'request_id')->orderByDesc('created_at');
    }

    public function activeQuotes()
    {
        return $this->quotes()->whereIn('status', ['pending', 'accepted']);
    }

    public function assignedShipper()
    {
        return $this->belongsTo(ShipperProfile::class, 'assigned_shipper_profile_id');
    }

    public function assignment()
    {
        return $this->hasOne(ShipperOrderAssignment::class, 'request_id');
    }

    public function adminChat()
    {
        return $this->hasMany(ShipperAdminChat::class, 'request_id')->orderBy('created_at');
    }

    /** Visible to a given shipper: open, not frozen, right country + level,
     *  not expired, not already quoted by them. */
    public function scopeVisibleToShipper($q, ShipperProfile $shipper)
    {
        return $q->where('status', 'open')
            ->where('is_frozen', false)
            // Admin-blocked countries are hidden from the marketplace entirely.
            ->whereIn('country_required', function ($sq) {
                $sq->select('iso2')->from('countries')->where('is_active', 1);
            })
            ->whereIn('country_required', $shipper->service_countries ?? [])
            ->where('required_level', '<=', $shipper->level)
            ->where(function ($sq) {
                $sq->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereDoesntHave('quotes', function ($sq) use ($shipper) {
                $sq->where('shipper_profile_id', $shipper->id)
                    ->whereIn('status', ['pending', 'accepted']);
            });
    }

    public function freeze(int $adminId): void
    {
        $this->update([
            'is_frozen' => true,
            'frozen_at' => now(),
            'frozen_by' => $adminId,
        ]);
    }

    public function unfreeze(): void
    {
        $this->update(['is_frozen' => false, 'frozen_at' => null, 'frozen_by' => null]);
    }
}
