<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'google_places' => [
        // Used ONLY to pull your business's Google reviews into the admin
        // Testimonials tool (Tools → Testimonials → Sync from Google).
        // Separate from services.google (Sign in with Google) above.
        'api_key' => env('GOOGLE_PLACES_API_KEY'),
        'place_id' => env('GOOGLE_PLACES_ID'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        // Publishable key served to logged-in clients via /home2/api/stripe-config
        // and used by StripeGateway::publishableKey() for Stripe.js Elements.
        'key' => env('STRIPE_KEY'),
        // Agent PM (additive): webhook signature secret — see routes/api.php / PM-013.
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
