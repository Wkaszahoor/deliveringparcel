<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class EmailTemplate extends Model
{
    protected $fillable = ['key', 'name', 'category', 'subject', 'body', 'placeholder_help', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    public const CATEGORIES = [
        'registration' => 'Registration',
        'order'        => 'Orders',
        'offer'        => 'Offers',
        'payment'      => 'Payments',
        'notification' => 'Notifications',
        'message'      => 'Messages / Contact',
        'blog'         => 'Blog',
        'system'       => 'System / Admin',
    ];

    /** Known email keys the system can send (drives the admin dropdown). */
    public const KNOWN_KEYS = [
        'welcome_email'                 => 'Welcome email (new registration)',
        'password_reset'                => 'Password reset link (framework — not template-driven, toggle only)',
        'order_created_customer'        => 'Order created — to customer',
        'order_created_admin'           => 'Order created — to admin',
        'offer_placed_customer'         => 'Offer placed — to customer',
        'offer_accepted_admin'          => 'Offer accepted — to admin',
        'offer_rejected_admin'          => 'Offer rejected — to admin',
        'payment_paid_customer'         => 'Payment confirmed — to customer',
        'payment_paid_admin'            => 'Payment confirmed — to admin',
        'payment_verification_admin'    => 'Bank receipt uploaded — to admin',
        'payment_refund_customer'       => 'Refund issued — to customer',
        'contact_submitted_admin'       => 'Contact form submitted — to admin',
        'contact_replied_customer'      => 'Contact reply — to customer',
        'guest_account_credentials'     => 'Guest auto-account credentials',
        'quote_submitted_admin'         => 'Free quote requested — to admin',
        'notification_task'             => 'Order notification email (TaskNotification)',
        'notification_chat'             => 'Chat notification email (Chatnotification)',
        'custom'                        => 'Custom / ad-hoc email',
    ];

    /**
     * Active template row for a key, or null when the admin has not created
     * one (senders then keep their original mailable). Table missing (pre-
     * migrate) also returns null safely.
     */
    public static function activeFor(string $key): ?self
    {
        try {
            return self::query()->where('key', $key)->where('is_enabled', true)->first();
        } catch (QueryException $e) {
            return null;
        }
    }
}
