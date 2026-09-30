<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipperDeliveryAddress extends Model
{
    protected $fillable = [
        'order_id', 'assignment_id', 'submitted_by_user_id',
        'recipient_name', 'address_line_1', 'address_line_2',
        'city', 'state', 'postal_code', 'country', 'phone', 'email',
        'delivery_instructions', 'admin_reviewed', 'admin_reviewed_by',
        'admin_review_notes', 'forwarded_to_shipper', 'forwarded_at',
        'forwarded_by', 'forward_level',
    ];

    protected $casts = [
        'admin_reviewed'      => 'boolean',
        'forwarded_to_shipper' => 'boolean',
        'forwarded_at'        => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function assignment()
    {
        return $this->belongsTo(ShipperOrderAssignment::class, 'assignment_id');
    }

    /** Masked representation actually forwarded to the shipper. */
    public function maskedForForwardLevel(): array
    {
        $base = [
            'recipient_name'   => $this->recipient_name,
            'address_line_1'   => $this->address_line_1,
            'address_line_2'   => $this->address_line_2,
            'city'             => $this->city,
            'state'            => $this->state,
            'postal_code'      => $this->postal_code,
            'country'          => $this->country,
            'delivery_instructions' => $this->delivery_instructions,
        ];
        if ($this->forward_level === 'full') {
            $base['phone'] = $this->phone;
        }
        if ($this->forward_level === 'city_country') {
            return [
                'city'    => $this->city,
                'country' => $this->country,
            ];
        }

        return $base;
    }
}
