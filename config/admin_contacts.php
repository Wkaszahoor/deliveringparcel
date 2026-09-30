<?php

/**
 * Agent D — Contact/Messages configuration.
 *
 * Categories: keyword maps for App\Services\ContactClassifier.
 * Statuses:   label + AdminLTE badge color map.
 * Templates:  stubs used by ContactReplyTemplateSeeder.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Categories (7)
    |--------------------------------------------------------------------------
    | priority: lower wins ties during classification (most specific first).
    | keywords: case-insensitive substrings matched against the message.
    */
    'categories' => [

        'complaint' => [
            'label' => 'Complaint',
            'color' => 'danger',
            'icon' => 'angry',
            'priority' => 1,
            'keywords' => [
                'complaint', 'complain', 'terrible', 'awful', 'worst', 'angry', 'unacceptable',
                'frustrated', 'frustrating', 'disappointed', 'disappointment', 'poor service',
                'bad service', 'rude', 'scam', 'fraud', 'cheat', 'damaged', 'broken', 'lost my',
                'lost package', 'missing', 'never arrived', 'still waiting', 'not delivered',
                'late', 'delay', 'delayed', 'refund my money', 'worst experience',
            ],
        ],

        'billing' => [
            'label' => 'Billing',
            'color' => 'warning',
            'icon' => 'file-invoice-dollar',
            'priority' => 2,
            'keywords' => [
                'invoice', 'payment', 'paid', 'pay', 'charge', 'charged', 'overcharge',
                'double charged', 'billing', 'bill me', 'receipt', 'refund', 'reimburse',
                'card', 'transaction', 'amount', 'total due', 'fee', 'fees', 'price of',
                'how much', 'cost', 'costs', 'currency', 'stripe', 'paypal',
            ],
        ],

        'support' => [
            'label' => 'Support',
            'color' => 'info',
            'icon' => 'headset',
            'priority' => 3,
            'keywords' => [
                'tracking', 'track my', 'track order', 'shipment', 'shipping', 'shipped',
                'parcel', 'package', 'delivery', 'delivered', 'deliver', 'order status',
                'my order', 'order number', 'customs', 'weight', 'label', 'warehouse',
                'consolidat', 'repack', 'storage', 'hold my', 'address change', 'change address',
                'prohibited', 'disinfection', 'insurance', 'help me with', 'issue with',
                'problem with', 'not working', 'error',
            ],
        ],

        'sales' => [
            'label' => 'Sales',
            'color' => 'primary',
            'icon' => 'handshake',
            'priority' => 4,
            'keywords' => [
                'bulk', 'business account', 'partnership', 'partner with', 'reseller',
                'wholesale', 'discount', 'corporate', 'enterprise', 'volume', 'sales',
                'commercial', 'collaboration', 'affiliate', 'become a', 'quote for',
                'pricing for', 'enterprise plan', 'sla',
            ],
        ],

        'feedback' => [
            'label' => 'Feedback',
            'color' => 'success',
            'icon' => 'comments',
            'priority' => 5,
            'keywords' => [
                'feedback', 'suggestion', 'suggest', 'improve', 'improvement', 'feature request',
                'great service', 'good job', 'excellent', 'amazing', 'love your', 'awesome',
                'thank you', 'thanks', 'appreciate', 'review', 'testimonial', 'happy with',
                'satisfied',
            ],
        ],

        'general' => [
            'label' => 'General',
            'color' => 'secondary',
            'icon' => 'envelope',
            'priority' => 6,
            'keywords' => [
                'hello', 'hi ', 'hey', 'good morning', 'good afternoon', 'good evening',
                'question', 'questions', 'inquiry', 'enquiry', 'information', 'info about',
                'general', 'need help', 'assist', 'assistance', 'please advise', 'can you',
                'how do i', 'how to', 'want to know', 'just wondering', 'greetings',
            ],
        ],

        'other' => [
            'label' => 'Other',
            'color' => 'dark',
            'icon' => 'tag',
            'priority' => 7,
            'keywords' => [
                // fallback category — no keywords; assigned when nothing matches
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Statuses (4)
    |--------------------------------------------------------------------------
    */
    'statuses' => [

        'new' => [
            'label' => 'New',
            'color' => 'danger',
            'icon' => 'star',
        ],

        'read' => [
            'label' => 'Read',
            'color' => 'info',
            'icon' => 'envelope-open',
        ],

        'replied' => [
            'label' => 'Replied',
            'color' => 'success',
            'icon' => 'reply',
        ],

        'archived' => [
            'label' => 'Archived',
            'color' => 'secondary',
            'icon' => 'archive',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default reply templates (seeded by ContactReplyTemplateSeeder)
    |--------------------------------------------------------------------------
    | Placeholders: {name} {email} {message} {company}
    */
    'templates' => [

        'general_acknowledgement' => [
            'name' => 'General Acknowledgement',
            'subject' => 'We received your message — {company}',
            'body' => "Hello {name},\n\nThank you for contacting {company}. We have received your message and our team will review it and get back to you as soon as possible.\n\nA copy of your message:\n\"{message}\"\n\nIf you need to add anything, simply reply to this email.\n\nKind regards,\nThe {company} Team",
        ],

        'support_followup' => [
            'name' => 'Support Follow-up',
            'subject' => 'Regarding your support request — {company}',
            'body' => "Hello {name},\n\nThank you for reaching out to {company} support. We are looking into your request regarding:\n\"{message}\"\n\nOur support team will follow up with detailed instructions shortly. For faster assistance, please include your order or tracking number in your reply.\n\nKind regards,\n{company} Support Team",
        ],

        'billing_escalation' => [
            'name' => 'Billing Escalation',
            'subject' => 'Your billing inquiry has been escalated — {company}',
            'body' => "Hello {name},\n\nThank you for your patience. Your billing inquiry has been escalated to our billing specialists, who will review your account and transaction history in detail.\n\nSummary of your inquiry:\n\"{message}\"\n\nWe will contact you at {email} with an update within 1–2 business days.\n\nKind regards,\n{company} Billing Team",
        ],

        'complaint_apology' => [
            'name' => 'Complaint Apology',
            'subject' => 'We are sorry — {company}',
            'body' => "Hello {name},\n\nWe sincerely apologize for the inconvenience you have experienced. Your complaint is very important to us and has been assigned to a senior team member for immediate review.\n\nWhat you reported:\n\"{message}\"\n\nWe will get back to you at {email} with a resolution as a priority. Thank you for giving us the opportunity to make this right.\n\nWith apologies,\nThe {company} Team",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Misc behaviour
    |--------------------------------------------------------------------------
    */
    'default_category' => 'other',
    'default_status' => 'new',
    'classification_chunk' => 500,
];
