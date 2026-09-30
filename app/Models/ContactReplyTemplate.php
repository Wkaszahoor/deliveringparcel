<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactReplyTemplate extends Model
{
    use HasFactory;

    protected $table = 'contact_reply_templates';

    protected $fillable = ['name', 'subject', 'body', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Replace the supported placeholders ({name}, {email}, {message}, {company})
     * inside a template body for a given contact.
     */
    public static function renderBody(string $body, Contactus $contact, ?string $company = null): string
    {
        $company = $company ?: (string) Setting::get('business_company_name', 'Deliveringparcel');

        return str_replace(
            ['{name}', '{email}', '{message}', '{company}'],
            [
                (string) $contact->name,
                (string) $contact->email,
                (string) $contact->detail,
                $company,
            ],
            $body
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
