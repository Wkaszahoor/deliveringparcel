<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bank_name',
        'account_title',
        'account_number',
        'iban',
        'branch',
        'swift_code',
        'currency',
        'instructions',
        'is_enabled',
        'sort_order',
        'recipient_address',
        'uk_account_number',
        'uk_sort_code',
        'intermediary_bic',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope a query to only include enabled bank accounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope a query to order by sort order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the payments for this bank account.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get formatted account details for display.
     *
     * @return array
     */
    public function getFormattedDetails(): array
    {
        return [
            'bank_name' => $this->bank_name,
            'account_title' => $this->account_title,
            'account_number' => $this->account_number,
            'iban' => $this->iban,
            'branch' => $this->branch,
            'swift_code' => $this->swift_code,
            'currency' => $this->currency,
            'instructions' => $this->instructions,
            'recipient_address' => $this->recipient_address,
            'uk_account_number' => $this->uk_account_number,
            'uk_sort_code' => $this->uk_sort_code,
            'intermediary_bic' => $this->intermediary_bic,
        ];
    }
}
