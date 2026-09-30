<?php

/**
 * Agent D — Settings module configuration.
 *
 * Declares EVERY settings key consumed by the admin (Agent D modules).
 * Structure: tabs => fields.
 *   type:     string | text | bool | select | color | int | readonly
 *   default:  seed / fallback value
 * Every key defined here is REAL — it is consumed by Agent D views/behaviours.
 */

return [

    'cache_key' => 'dp_settings_all',

    'tabs' => [

        /* ------------------------------------------------ business */
        'business' => [
            'label' => 'Business',
            'icon' => 'building',
            'fields' => [

                'business_company_name' => [
                    'label' => 'Company name',
                    'type' => 'string',
                    'default' => 'Deliveringparcel',
                    'help' => 'Used in reply templates ({company}) and module headers.',
                ],

                'business_support_email' => [
                    'label' => 'Support email',
                    'type' => 'string',
                    'default' => 'service@deliveringparcel.com',
                    'help' => 'Recipient for new-contact notifications and default reply-from.',
                    'validate' => 'nullable|email',
                ],

                'business_support_phone' => [
                    'label' => 'Support phone',
                    'type' => 'string',
                    'default' => '',
                ],

                'business_address' => [
                    'label' => 'Address',
                    'type' => 'text',
                    'default' => '',
                ],

                'business_hours' => [
                    'label' => 'Business hours',
                    'type' => 'string',
                    'default' => 'Mon–Fri, 9:00–18:00',
                ],

                'business_currency' => [
                    'label' => 'Currency',
                    'type' => 'select',
                    'default' => 'USD',
                    'options' => ['USD' => 'USD — US Dollar', 'EUR' => 'EUR — Euro', 'GBP' => 'GBP — Pound Sterling', 'CAD' => 'CAD — Canadian Dollar', 'AUD' => 'AUD — Australian Dollar'],
                    'help' => 'Shown as the amount prefix on the Payments module.',
                ],

                'business_timezone' => [
                    'label' => 'Timezone',
                    'type' => 'string',
                    'default' => 'UTC',
                    'help' => 'Dates in the Contacts / Payments modules are displayed in this timezone.',
                ],
            ],
        ],

        /* ------------------------------------------------ seo */
        'seo' => [
            'label' => 'SEO',
            'icon' => 'search',
            'fields' => [

                'seo_meta_title_suffix' => [
                    'label' => 'Meta title suffix',
                    'type' => 'string',
                    'default' => 'Deliveringparcel Admin',
                    'help' => 'Appended to the browser title of the admin modules.',
                ],

                'seo_meta_description' => [
                    'label' => 'Meta description',
                    'type' => 'text',
                    'default' => 'Parcel forwarding and shipping services.',
                    'help' => 'Emitted as a meta description tag on the admin modules.',
                ],

                'seo_google_analytics_id' => [
                    'label' => 'Google Analytics ID',
                    'type' => 'string',
                    'default' => '',
                    'help' => 'Measurement ID (e.g. G-XXXXXXX). Stored as a reference only — shown with a status chip on this page.',
                    'validate' => 'nullable|max:32',
                ],

                'seo_robots' => [
                    'label' => 'Robots',
                    'type' => 'select',
                    'default' => 'noindex, nofollow',
                    'options' => [
                        'noindex, nofollow' => 'noindex, nofollow (admin recommended)',
                        'noindex, follow' => 'noindex, follow',
                        'index, nofollow' => 'index, nofollow',
                        'index, follow' => 'index, follow',
                    ],
                    'help' => 'Emitted as a robots meta tag on the admin modules.',
                ],
            ],
        ],

        /* ------------------------------------------------ theme */
        'theme' => [
            'label' => 'Theme',
            'icon' => 'palette',
            'fields' => [

                'theme_admin_skin' => [
                    'label' => 'Admin skin',
                    'type' => 'select',
                    'default' => 'light',
                    'options' => ['light' => 'Light', 'dark' => 'Dark'],
                    'help' => 'Applied to the live preview panel on this page.',
                ],

                'theme_primary_color' => [
                    'label' => 'Primary color',
                    'type' => 'color',
                    'default' => '#0d6efd',
                    'help' => 'Drives accents (KPI icons, links) across the Contacts / Payments modules.',
                ],

                'theme_sidebar_collapsed' => [
                    'label' => 'Sidebar collapsed by default',
                    'type' => 'bool',
                    'default' => false,
                    'help' => 'When enabled, the sidebar starts collapsed on the admin modules.',
                ],
            ],
        ],

        /* ------------------------------------------------ api integrations
           SECURITY: only the ENABLED flag is stored in the database.
           Secret VALUES live exclusively in .env; this page only reads
           config presence and shows configured / not set chips.
        ------------------------------------------------------------- */
        'api' => [
            'label' => 'API Integrations',
            'icon' => 'plug',
            'fields' => [

                'api_stripe_enabled' => [
                    'label' => 'Stripe payments',
                    'type' => 'bool',
                    'default' => false,
                    'integration' => [
                        'name' => 'Stripe',
                        'env_vars' => ['STRIPE_KEY', 'STRIPE_SECRET'],
                        'config_paths' => ['services.stripe.secret'],
                    ],
                    'help' => 'Enables the refund action on the Payments module (requires the STRIPE_SECRET env var to be present).',
                ],

                'api_mail_enabled' => [
                    'label' => 'Outgoing email',
                    'type' => 'bool',
                    'default' => false,
                    'integration' => [
                        'name' => 'Mail / SMTP',
                        'env_vars' => ['MAIL_HOST', 'MAIL_USERNAME'],
                        'config_paths' => ['mail.host'],
                    ],
                    'help' => 'Enables email delivery of contact replies and new-contact notifications (requires mail.host in .env).',
                ],

                'api_turnstile_enabled' => [
                    'label' => 'Cloudflare Turnstile captcha (master)',
                    'type' => 'bool',
                    'default' => true,
                    'integration' => [
                        'name' => 'Cloudflare Turnstile',
                        'env_vars' => [],
                        'config_paths' => [],
                    ],
                    'help' => 'Master switch for every Turnstile captcha (request, register, login, password reset, contact, free quote). When off, ALL widgets disappear and server checks are skipped. Use the per-page switches below to control individual pages.',
                ],

                'turnstile_on_register' => [
                    'label' => 'Turnstile on Register page',
                    'type' => 'bool',
                    'default' => true,
                    'help' => 'Shows the Turnstile captcha on /register and verifies it before an account is created. Only effective while the master switch above is enabled.',
                ],

                'turnstile_on_login' => [
                    'label' => 'Turnstile on Login page',
                    'type' => 'bool',
                    'default' => true,
                    'help' => 'Shows the Turnstile captcha on /login and verifies it before sign-in. Only effective while the master switch above is enabled.',
                ],

                'turnstile_on_password_reset' => [
                    'label' => 'Turnstile on Password Reset pages',
                    'type' => 'bool',
                    'default' => true,
                    'help' => 'Shows the Turnstile captcha on /password/reset (and the legacy /password/resetlegacy variant) and verifies it before a reset link is emailed or a new password is saved. Only effective while the master switch above is enabled.',
                ],
            ],
        ],

        /* ------------------------------------------------ communications */
        'email' => [
            'label' => 'Email System',
            'icon' => 'paper-plane',
            'fields' => [

                'email_master_enabled' => [
                    'label' => 'Send emails (master switch)',
                    'type' => 'bool',
                    'default' => true,
                    'help' => 'Master kill-switch for ALL outgoing email. When off, no email is attempted anywhere and nothing is logged. Turn off only during a mail outage.',
                ],

                'email_use_queue' => [
                    'label' => 'Queue emails (cron worker)',
                    'type' => 'bool',
                    'default' => false,
                    'help' => 'ON = templated emails go to the database queue and are sent by the cron worker (php artisan dp:queue-drain every minute — pages never wait for SMTP). OFF = emails send inline during the request (works with no worker, but the page waits for the mail server).',
                ],
            ],
        ],

        /* ------------------------------------------------ review badges */
        'review_badges' => [
            'label' => 'Review Badges',
            'icon' => 'star',
            'fields' => [

                'reviews_google_enabled' => [
                    'label' => 'Show Google badge',
                    'type' => 'bool',
                    'default' => false,
                    'help' => 'Shows a "Google ★ rating (count reviews)" trust badge on the homepage. Rating/count below can be kept in sync automatically by Tools → Testimonials → Sync from Google, or set by hand.',
                ],
                'reviews_google_rating' => [
                    'label' => 'Google rating',
                    'type' => 'string',
                    'default' => '',
                    'validate' => 'nullable|regex:/^[0-5](\.[0-9])?$/',
                    'help' => 'e.g. 4.9',
                ],
                'reviews_google_count' => [
                    'label' => 'Google review count',
                    'type' => 'int',
                    'default' => 0,
                ],
                'reviews_google_url' => [
                    'label' => 'Google profile URL',
                    'type' => 'string',
                    'default' => '',
                    'validate' => 'nullable|url',
                    'help' => 'Where the badge links to — your Google Business Profile / review page.',
                ],

                'reviews_trustpilot_enabled' => [
                    'label' => 'Show Trustpilot badge',
                    'type' => 'bool',
                    'default' => false,
                    'help' => 'No live sync available (Trustpilot\'s API requires a paid Business plan) — enter your real rating/count by hand.',
                ],
                'reviews_trustpilot_rating' => [
                    'label' => 'Trustpilot rating',
                    'type' => 'string',
                    'default' => '',
                    'validate' => 'nullable|regex:/^[0-5](\.[0-9])?$/',
                ],
                'reviews_trustpilot_count' => [
                    'label' => 'Trustpilot review count',
                    'type' => 'int',
                    'default' => 0,
                ],
                'reviews_trustpilot_url' => [
                    'label' => 'Trustpilot profile URL',
                    'type' => 'string',
                    'default' => '',
                    'validate' => 'nullable|url',
                ],

                'reviews_sitejabber_enabled' => [
                    'label' => 'Show SiteJabber badge',
                    'type' => 'bool',
                    'default' => false,
                    'help' => 'SiteJabber has no public API — enter your real rating/count by hand.',
                ],
                'reviews_sitejabber_rating' => [
                    'label' => 'SiteJabber rating',
                    'type' => 'string',
                    'default' => '',
                    'validate' => 'nullable|regex:/^[0-5](\.[0-9])?$/',
                ],
                'reviews_sitejabber_count' => [
                    'label' => 'SiteJabber review count',
                    'type' => 'int',
                    'default' => 0,
                ],
                'reviews_sitejabber_url' => [
                    'label' => 'SiteJabber profile URL',
                    'type' => 'string',
                    'default' => '',
                    'validate' => 'nullable|url',
                ],
                'reviews_manual_enabled' => [
                    'label' => 'Show Direct / Manual testimonials',
                    'type' => 'bool',
                    'default' => true,
                    'help' => 'Shows direct customer quotes entered by administrators.',
                ],
            ],
        ],

        /* ------------------------------------------------ legal */
        'legal' => [
            'label' => 'Legal',
            'icon' => 'file-contract',
            'fields' => [

                'terms_content' => [
                    'label' => 'Terms & Conditions content',
                    'type' => 'text',
                    'validate' => 'max:5900',
                    'default' => '<h2>1. About these Terms</h2><p>www.deliveringparcel.com is operated by DeliveringParcel ("we", "us"). By registering or using any of our services you accept these Terms &amp; Conditions, which form a legal agreement between you and us.</p><h2>2. Our Services</h2><p>We provide international package forwarding, consolidation, personal shopper / purchase assistance, and related shipping services. A unique delivery address may be assigned to registered members for receiving goods on their behalf.</p><h2>3. Accounts</h2><p>You must provide accurate registration details and keep them current. You are responsible for all activity under your account. Guest orders automatically create an account so you can track your shipment.</p><h2>4. Quotes, Offers and Acceptance</h2><p>All prices are provided by quotation or offer. An order proceeds only after you explicitly accept an offer. Acceptance records the agreed items, quantities, service fees, shipping costs and destination.</p><h2>5. Payments</h2><p>We accept card payments (Stripe), PayPal where enabled, bank transfer and wallet balance where available. Card payments are processed by Stripe; we never store card details. Bank transfers must quote your payment reference. <strong>Uploading a transfer receipt does not confirm a payment</strong> — funds must clear and be verified by our team before an order is processed.</p><h2>6. Purchase Assistance</h2><p>Where you instruct us to purchase goods on your behalf, we act strictly per your approved offer. Items are normally purchased within 2 working days. If an item is unavailable or out of stock, the amount charged for that item is refunded. We are not responsible for supplier price changes after your acceptance unless we re-quote.</p><h2>7. Shipping and Delivery</h2><p>Delivery estimates are indicative, not guaranteed. Tracking details are added to your order once available. Instruct couriers to use door-to-door signature service; we are not responsible for parcels dropped at pickup points or left in mailrooms when you arrange your own inbound delivery.</p><h2>8. Prohibited Goods</h2><p>You must not send prohibited, restricted, hazardous, counterfeit, illegal or perishable goods, or any item whose import/export is unlawful at origin, transit or destination. Such goods may be seized or destroyed without compensation.</p><h2>9. Cancellation and Refunds</h2><p>Cancel free of charge before purchase/processing begins. After goods are purchased or shipped on your instruction, refunds are limited to recoverable amounts (e.g. resale of unshipped stock less costs) and are not automatic merely because you change your mind. Processor fees are non-refundable.</p><h2>10. Claims and Disputes</h2><p>Report loss, damage or discrepancy within 7 days of delivery (or expected delivery) with photos and supporting evidence. Delivery records, proof of hand-over and your accepted offer may be used to resolve disputes. Card chargebacks and bank recall claims are handled under the relevant provider rules; abusing chargebacks for delivered services may result in account suspension.</p><h2>11. Liability</h2><p>Our liability for any shipment is limited to the lesser of the declared value or the amount paid for the affected service, except where mandatory law provides otherwise. We are not liable for delays caused by customs, carriers, weather or events beyond our reasonable control.</p><h2>12. Changes</h2><p>We may update these Terms; the version in force applies to each new order. This page shows the current version and its last update date.</p>',
                    'help' => 'Rendered at /terms-and-conditions when set (leave empty to show the original legacy page). Basic HTML allowed (h2, p, strong, ul/li, a). Max ~5900 characters.',
                ],
            ],
        ],

        /* ------------------------------------------------ preferences */
        'preferences' => [
            'label' => 'Preferences',
            'icon' => 'sliders',
            'fields' => [

                'preferences_per_page' => [
                    'label' => 'Records per page',
                    'type' => 'int',
                    'default' => 20,
                    'validate' => 'nullable|integer|min:5|max:100',
                    'help' => 'Used by Contacts and Payments pagination.',
                ],

                'preferences_date_format' => [
                    'label' => 'Date format',
                    'type' => 'select',
                    'default' => 'M d, Y H:i',
                    'options' => [
                        'M d, Y H:i' => 'Aug 17, 2026 14:30',
                        'd/m/Y H:i' => '17/08/2026 14:30',
                        'm/d/Y h:i A' => '08/17/2026 02:30 PM',
                        'Y-m-d H:i' => '2026-08-17 14:30',
                        'd M Y' => '17 Aug 2026',
                    ],
                    'help' => 'Used when rendering dates in the Contacts / Payments modules.',
                ],

                'preferences_notify_new_contact' => [
                    'label' => 'Email on new contact message',
                    'type' => 'bool',
                    'default' => false,
                    'help' => 'When enabled and mail is configured, an internal notification is emailed to the support address for each new contact message.',
                ],

                'preferences_maintenance_banner' => [
                    'label' => 'Maintenance banner text',
                    'type' => 'text',
                    'default' => '',
                    'help' => 'When not empty, displayed as a warning banner on top of the Contacts / Payments / Settings pages.',
                ],

                'site_marquee_enabled' => [
                    'label' => 'Site-wide marquee bar',
                    'type' => 'bool',
                    'default' => false,
                    'help' => 'When enabled, a scrolling announcement bar appears at the very top of every page on both designs.',
                ],

                'site_marquee_text' => [
                    'label' => 'Marquee message',
                    'type' => 'text',
                    'default' => 'Due To Technical Services You May Get Error while browsing website, For any Urgent Issue Please Contact On our Whatsapp Or Email',
                    'help' => 'Text shown in the scrolling bar (shown only while the marquee is enabled).',
                ],

                'site_marquee_speed' => [
                    'label' => 'Marquee speed (seconds per loop)',
                    'type' => 'int',
                    'default' => 26,
                    'validate' => 'nullable|integer|min:5|max:180',
                    'help' => 'How many seconds one full scroll takes — HIGHER = SLOWER. e.g. 20 = fast, 40 = relaxed, 60 = slow.',
                ],

                'preferences_notification_timer' => [
                    'label' => 'Notification API interval (ms)',
                    'type' => 'int',
                    'default' => 30000,
                    'validate' => 'nullable|integer|min:1000|max:120000',
                    'help' => 'How often the navbar bell badge is refreshed (check_notification API) on order pages, in milliseconds. Lower = snappier badges but higher server load. Recommended: 30000-60000.',
                ],

                'preferences_message_timer' => [
                    'label' => 'Message API interval (ms)',
                    'type' => 'int',
                    'default' => 15000,
                    'validate' => 'nullable|integer|min:1000|max:120000',
                    'help' => 'How often the unread-message count is refreshed (chat_count API) on order pages, in milliseconds. Recommended: 15000-30000.',
                ],

                'preferences_chat_timer' => [
                    'label' => 'Chat API interval (ms)',
                    'type' => 'int',
                    'default' => 15000,
                    'validate' => 'nullable|integer|min:1000|max:120000',
                    'help' => 'How often the open conversation thread is reloaded (chat_messages API) on order pages, in milliseconds. Recommended: 15000-30000.',
                ],

                'payment_mode' => [
                    'label' => 'Payment mode',
                    'type' => 'select',
                    'default' => 'advanced',
                    'options' => [
                        'advanced' => 'Advanced — multi-gateway engine (Stripe, Wallet, Bank transfer)',
                        'legacy' => 'Legacy — direct single Stripe payment (old client tab)',
                    ],
                    'validate' => 'nullable|in:advanced,legacy',
                    'help' => 'Advanced: checkout offers every enabled gateway (card, wallet, bank transfer) with admin per-order/product/quote forcing. Legacy: only the old direct Stripe form on the client order tab. Shop checkout pays through the engine only in Advanced mode.',
                ],

                'payments_engine_enabled' => [
                    'label' => 'Enable new payment engine',
                    'type' => 'bool',
                    'default' => true,
                    'help' => 'When ON, uses the new multi-method payment system (Stripe + Bank Transfer). When OFF, falls back to legacy Stripe-only form on client payment tab. Requires payment tables to exist.',
                ],

                'wallet_enabled' => [
                    'label' => 'Enable wallet (pay from balance)',
                    'type' => 'bool',
                    'default' => true,
                    'help' => 'When ON, customers get a wallet they can top up by card and pay orders from instantly. When OFF, the wallet method is hidden and top-ups are blocked (balances and history are kept).',
                ],

                'wallet_topup_min' => [
                    'label' => 'Wallet top-up minimum',
                    'type' => 'int',
                    'default' => 10,
                    'validate' => 'nullable|integer|min:1|max:100000',
                    'help' => 'Smallest amount a customer can top up in one transaction.',
                ],

                'wallet_topup_max' => [
                    'label' => 'Wallet top-up maximum',
                    'type' => 'int',
                    'default' => 5000,
                    'validate' => 'nullable|integer|min:1|max:1000000',
                    'help' => 'Largest amount a customer can top up in one transaction.',
                ],
            ],
        ],

        /* ==============================================================
           Mobile App — section titles shown in the admin Android app.
           Rename any section from this screen; the app fetches the
           labels from the API on launch (fallback = defaults here).
           ============================================================== */
        'mobile' => [
            'title'  => 'Mobile App',
            'icon'   => 'mobile-alt',
            'fields' => [

                'mobile_label_order' => [
                    'label'    => 'Section: Order Detail',
                    'type'     => 'string',
                    'default'  => 'Order Detail',
                    'validate' => 'nullable|string|max:60',
                    'help'     => 'Top section: order info + client details + progress + chat + edit buttons.',
                ],
                'mobile_label_shipping' => [
                    'label'    => 'Section: Shipping Detail',
                    'type'     => 'string',
                    'default'  => 'Shipping Detail',
                    'validate' => 'nullable|string|max:60',
                ],
                'mobile_label_products' => [
                    'label'    => 'Section: Product List',
                    'type'     => 'string',
                    'default'  => 'Product List',
                    'validate' => 'nullable|string|max:60',
                    'help'     => 'Inside Shipping Detail — customer items (name, url, qty, price, weight).',
                ],
                'mobile_label_tracking' => [
                    'label'    => 'Section: Tracking',
                    'type'     => 'string',
                    'default'  => 'Tracking',
                    'validate' => 'nullable|string|max:60',
                    'help'     => 'Holds both customer-provided and admin-provided tracking.',
                ],
                'mobile_label_tracking_customer' => [
                    'label'    => 'Tracking: Customer Provided',
                    'type'     => 'string',
                    'default'  => 'Customer Provided Tracking',
                    'validate' => 'nullable|string|max:60',
                    'help'     => 'Tracking the customer submits after payment (items → our warehouse).',
                ],
                'mobile_label_tracking_admin' => [
                    'label'    => 'Tracking: Admin Provided',
                    'type'     => 'string',
                    'default'  => 'Admin Provided Tracking',
                    'validate' => 'nullable|string|max:60',
                    'help'     => 'Tracking when we ship the parcel to the customer destination.',
                ],
                'mobile_label_services' => [
                    'label'    => 'Section: Services',
                    'type'     => 'string',
                    'default'  => 'Services',
                    'validate' => 'nullable|string|max:60',
                    'help'     => 'Services adopted by the customer on the request.',
                ],
                'mobile_label_status' => [
                    'label'    => 'Section: Change Status',
                    'type'     => 'string',
                    'default'  => 'Change Status',
                    'validate' => 'nullable|string|max:60',
                ],
                'mobile_label_offer' => [
                    'label'    => 'Section: Offer',
                    'type'     => 'string',
                    'default'  => 'Offer',
                    'validate' => 'nullable|string|max:60',
                    'help'     => 'Offer builder incl. services, product pricing and the shipping address we provide the customer.',
                ],
                'mobile_label_chat' => [
                    'label'    => 'Section: Chat',
                    'type'     => 'string',
                    'default'  => 'Chat',
                    'validate' => 'nullable|string|max:60',
                ],
            ],
        ],
    ],
];
