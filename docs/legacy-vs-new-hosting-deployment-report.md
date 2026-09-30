DeliveringParcel — Legacy vs New System & Hosting Deployment Report

Prepared: 2026-08-21
Application: DeliveringParcel / DPLive
Deployment type: Laravel / Apache shared-hosting compatible deployment guide

1. Purpose

This report documents:

What is confirmed in the legacy system.

What is confirmed in the newer system.

The functional gaps between the legacy and new order workflows.

Which Laravel folders/files must be copied for a new hosting deployment.

How to activate the legacy workflow, the new workflow, or both.

Correct .htaccess and .env setup.

Database, storage, cache, permissions, cron, queue and Stripe/webhook requirements.

A rollback-safe deployment method that keeps the legacy implementation available.

The project material explicitly says to preserve the legacy workflow as a rollback/reference implementation and not replace working legacy functionality merely because a new implementation exists.

2. Confirmed System Structure

2.1 Local development project path observed

The project material references the local Laravel project at:

C:\laragon\www\dplive\deliveringparcel

The documented files include:

resources/views/admin/payments/index.blade.php
database/migrations/2026_08_20_100001_add_forced_payment_method_to_orders.php
database/migrations/2026_08_19_090001_create_payment_tables.php

2.2 Hosting paths observed in existing error traces

The hosting/error material references:

/home/dparcel/public_html/deliveringparcel/

and also an application entry point at:

/home/dparcel/public_html/index.php

This indicates that the existing hosting deployment has used a non-standard arrangement where the Laravel application exists in a subdirectory below public_html and a web-facing entry point may exist directly in public_html.

For a new deployment, a cleaner and safer layout is recommended: keep the Laravel application outside the public web root and point the domain/subdomain document root directly at Laravel's public/ directory.

3. Legacy System — Confirmed Features

The legacy order detail page is documented as:

/order/{id}

Example reference:

/order/13

The legacy page uses the frontend OrderController@show flow and is treated as the functional source of truth for the order/offer workflow.

3.1 Legacy order workflow

The documented workflow is:

Client places order
        ↓
Admin receives order
        ↓
Admin opens order detail
        ↓
Admin selects offered services
        ↓
Admin changes applicable service charges
        ↓
Admin adds additional services
        ↓
Admin sets shipping information
        ↓
System calculates total
        ↓
Admin enters offer description
        ↓
Make Offer
        ↓
Client views/accepts offer
        ↓
Payment

3.2 Legacy order/offer functionality

Confirmed features include:

Full order information display.

Customer information.

Product/order information.

Offered-service multi-select.

Service charges/prices that can be applied to the offer.

Additional services with multiple rows.

Add/remove additional-service rows.

Shipping country/address information.

Offer description.

Offer validity/expiry.

Automatic offer total calculation.

Make Offer action.

Client offer visibility.

Client offer acceptance/rejection.

Existing Stripe payment flow.

The documented legacy examples include services such as Product Photo, Customs Declaration, Content Check, Prohibited Items Removal, Disinfection, Package Consolidation, Forwarding Service Fee and Shipping Fee.

3.3 Legacy offer interface files identified in the project material

The documented implementation references these files for the offer implementation that is being carried into the new system:

resources/views/admin/orders/offer.blade.php
routes/admin/orders.php
resources/views/admin/orders/edit.blade.php
resources/views/clients/order_offers.blade.php
app/Http/Controllers/Admin/OfferController.php

The original project material also references the existing controller/model/service/database chain rather than creating duplicate business logic.

4. New System — Confirmed / Planned Capabilities

The newer workflow is represented by the Admin order page:

/admin/orders/{id}

Example:

/admin/orders/13

The newer system already contains payment functionality that expands on the legacy behavior.

4.1 New payment capabilities

The implementation specification defines these payment policies for an order/offer:

Stripe Only
Bank Only
Stripe + Bank

The client should only see the payment methods permitted for that particular order.

The specification also requires the payment restriction to be checked server-side, not only by hiding buttons in the browser.

4.2 Bank payment capability

The newer design supports a bank-transfer workflow where appropriate:

Client selects Bank
        ↓
Bank details shown
        ↓
Client transfers funds
        ↓
Client submits transaction/reference/proof
        ↓
Pending Verification
        ↓
Admin approves/rejects
        ↓
Payment status updated

Bank details are intended to be configurable through the existing CRUD style rather than hard-coded in Blade templates.

4.3 New Admin order page feature state

The project comparison identifies the following major parity items that must be preserved/added/verified:

Feature

Legacy

New Admin

Required direction

Order information

Existing

Verify

Preserve

Customer information

Existing

Verify

Preserve

Product details

Existing

Verify

Preserve

Product Photo

Existing

Missing/verify

Add

Customs Declaration

Existing

Missing/verify

Add

Content Check

Existing

Missing/verify

Add

Prohibited Items Removal

Existing

Missing/verify

Add

Disinfection

Existing

Missing/verify

Add

Package Consolidation

Existing

Missing/verify

Add

Forwarding Service Fee

Existing

Missing/verify

Add

Shipping Fee

Existing

Missing/verify

Add

Offered-service multi-select

Existing

Missing/verify

Add

Change service charges

Existing

Missing/verify

Add

Additional Services

Existing

Missing/verify

Add

Add More service rows

Existing

Missing/verify

Add

Shipping Country

Existing

Missing/verify

Add

Shipping Address

Existing

Missing/verify

Add

Offer Description

Existing

Missing/verify

Add

Total calculation

Existing

Missing/verify

Match legacy

Make Offer

Existing

Missing/verify

Add

Offer status

Existing

Verify

Preserve

Client offer visibility

Existing

Verify

Preserve

Client offer acceptance

Existing

Verify

Preserve

Payment restriction

Not legacy

New capability

Integrate

Stripe

Existing

Verify/reuse

Preserve

Bank payment

New capability

Verify

Complete

Payment policy

New capability

Verify

Complete

Server-side payment restriction

Required

Required

Enforce

5. Legacy vs New — Recommended Final Architecture

Do not delete the legacy implementation.

Use the same Laravel application and database, while exposing separate routes/features during migration:

                    SAME LARAVEL APPLICATION
                              │
              ┌───────────────┴───────────────┐
              │                               │
        LEGACY WORKFLOW                  NEW WORKFLOW
              │                               │
        /order/{id}                    /admin/orders/{id}
              │                               │
              └───────────────┬───────────────┘
                              │
                       Shared Models/DB
                              │
                    Shared Payment Layer
                              │
                  Stripe + Bank capabilities

This is safer than copying two complete Laravel applications that share the same database.

Recommended activation approach

Phase 1: Keep both available.

Legacy URL     → /order/{id}
New Admin URL  → /admin/orders/{id}

Phase 2: Make the new Admin workflow the primary admin workflow after functional parity testing.

Phase 3: Keep the legacy route operational as a controlled rollback path.

Do not switch systems by deleting files. Switch them by routing/permissions/navigation/feature configuration.

6. File and Folder Deployment Map

A normal Laravel deployment should copy the complete application, not just Blade pages.

6.1 Laravel application folders that must exist on the server

Recommended application root:

/home/dparcel/deliveringparcel/

or another private path outside public web access.

Copy these directories:

app/
bootstrap/
config/
database/
resources/
routes/
storage/
vendor/

Also copy these root files:

artisan
composer.json
composer.lock
.env                 # create server-specific file; do not blindly copy local secrets

Also preserve any project-specific files that exist in your actual source tree, such as:

package.json
vite.config.*
webpack.mix.js
phpunit.xml
server.php           # only if relevant to the project's Laravel version/setup

The correct set should ultimately be based on the real project root, especially composer.json and the current Laravel version.

6.2 Public web files

The web server should expose only:

public/

from the Laravel project.

A standard production layout is:

/home/dparcel/deliveringparcel/
    app/
    bootstrap/
    config/
    database/
    resources/
    routes/
    storage/
    vendor/
    artisan
    composer.json
    composer.lock
    .env

/home/dparcel/public_html/
    index.php
    .htaccess
    build/ or compiled assets, if applicable
    css/js/images uploaded into public/

Even better, set the subdomain document root directly to:

/home/dparcel/deliveringparcel/public

so the Laravel public/ directory itself is the web root.

7. What NOT to expose publicly

These must not be accessible from the browser:

.env
app/
bootstrap/
config/
database/
routes/
storage/
vendor/
composer.json
composer.lock
artisan

Do not create download routes or aliases that expose these directories.

If the hosting provider forces the whole application inside public_html, use a safer structure where the application is stored in a subdirectory and only the contents of public/ are exposed through the site's document root/entry point.

8. Recommended Hosting Folder Layout

Option A — Best / Recommended

Set the domain/subdomain document root to:

/home/dparcel/deliveringparcel/public

Application:

/home/dparcel/deliveringparcel

This is the cleanest Laravel deployment.

Option B — Shared Hosting where document root must be public_html

Use:

/home/dparcel/deliveringparcel/          ← application
/home/dparcel/public_html/               ← public web root

Copy the contents of:

/home/dparcel/deliveringparcel/public/*

to:

/home/dparcel/public_html/

Then verify public_html/index.php points to the correct Laravel application paths.

Typical path adjustments in index.php are conceptually:

require __DIR__.'/../deliveringparcel/vendor/autoload.php';
$app = require_once __DIR__.'/../deliveringparcel/bootstrap/app.php';

The exact path must match the actual hosting directory layout.

Option C — Existing DPLive-style subdirectory deployment

The historical traces show:

/home/dparcel/public_html/deliveringparcel/

If this arrangement must be retained, verify exactly which file is the active public entry point before copying/replacing anything. Do not overwrite the existing production entry point until the new application is tested.

9. .htaccess — Recommended Laravel Public File

Place this file at the actual Apache document root that serves Laravel's public/index.php.

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Preserve Authorization header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Preserve X-XSRF-Token header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-TOKEN}]

    # Redirect trailing slashes for non-directories
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send all non-existing files/directories to Laravel
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

This is intended for the public Laravel document root, not the private Laravel application root.

If the hosting provider has disabled mod_rewrite, the provider must enable it or Apache/Nginx must be configured with equivalent routing rules.

10. If the Site Must Use public_html as the Web Root

Do not simply expose the entire Laravel project directly.

Preferred structure:

/home/dparcel/deliveringparcel/       ← private
/home/dparcel/public_html/            ← public

Then put only the contents of Laravel public/ into public_html.

The public index.php should reference the private application directory.

Example:

<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../deliveringparcel/vendor/autoload.php';

$app = require_once __DIR__.'/../deliveringparcel/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
    $request = Request::capture()
))->send();

$kernel->terminate($request, $response);

Important: Do not blindly replace the application's existing index.php; compare it with the actual Laravel version/project first.

11. .env Production Configuration

Create a new server-specific .env file. Do not upload your development .env unchanged.

Example structure:

APP_NAME="DeliveringParcel"
APP_ENV=production
APP_KEY=base64:GENERATE_A_NEW_OR_USE_THE_EXISTING_PROJECT_KEY_AS_APPROPRIATE
APP_DEBUG=false
APP_URL=https://www.example.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=YOUR_DATABASE_NAME
DB_USERNAME=YOUR_DATABASE_USER
DB_PASSWORD=YOUR_DATABASE_PASSWORD

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=YOUR_SMTP_USERNAME
MAIL_PASSWORD=YOUR_SMTP_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

STRIPE_KEY=pk_live_xxxxxxxxxxxxxxxxx
STRIPE_SECRET=sk_live_xxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxx

Important production settings

APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-real-domain

Do not use development values such as:

APP_ENV=local
APP_DEBUG=true
MAIL recipient @dptest.local

The existing project has an identified development SMTP problem involving bob@dptest.local; the deployment report therefore treats production SMTP configuration as mandatory.

12. APP_KEY Handling

Do not generate a different application key when moving an existing production application unless you intentionally want to invalidate encrypted application data/sessions/cookies.

For a normal migration of the same live application:

Existing production APP_KEY
        ↓
Copy securely to new server .env

If this is a brand-new independent installation, a new key can be generated with Laravel's normal command, but that should not be done casually against an existing production database.

13. Database Migration

Before deployment:

Export the current production database.

Create the new hosting database.

Import the database.

Confirm database user permissions.

Verify charset/collation.

Run only the migrations that belong to the deployed application version.

For the documented new offer/payment work, migrations include files such as:

database/migrations/2026_08_20_100001_add_forced_payment_method_to_orders.php
database/migrations/2026_08_19_090001_create_payment_tables.php

The project plan also proposes/adds offer-related tables such as:

offers
offer_items

Do not run migrations blindly on production without first backing up the database.

14. Composer / Dependencies

If the server supports Composer:

cd /home/dparcel/deliveringparcel
composer install --no-dev --optimize-autoloader

If vendor/ is deployed from a controlled build machine instead, make sure it exactly matches composer.lock.

Do not mix dependencies from a different Laravel project.

15. Laravel Cache / Configuration Activation

After setting .env, clear stale configuration/cache and rebuild production caches.

Typical commands are:

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

Use the commands supported by the project's actual Laravel version.

If cache/config problems are suspected during first deployment, start with:

php artisan optimize:clear

and then enable only the cache commands appropriate for the project.

16. Storage and Uploads

The application has a Laravel storage/ tree and the error traces show compiled Blade files under:

storage/framework/views/

Ensure these directories exist and are writable by the web server:

storage/app/
storage/framework/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
storage/logs/
bootstrap/cache/

If the application uses Laravel's public storage disk, ensure the public storage link exists according to the project's configured filesystem.

Do not delete customer uploads during deployment.

17. Permissions

The web server user must be able to write to:

storage/
bootstrap/cache/

On shared hosting, use the hosting control panel's file ownership/permissions rather than applying overly broad permissions such as 777 unless the provider explicitly requires it.

18. Stripe Production Activation

Change the production environment from test to live credentials only after the application is tested.

Required concepts:

STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

Stripe webhook URL should point to the live deployment, for example:

https://www.example.com/stripe/webhook

or the project's actual webhook route.

The existing Stripe integration should be reused instead of creating a second independent Stripe implementation.

If wallet functionality is added, Stripe top-ups, wallet ledger entries, refunds and disputes should remain linked by transaction IDs.

19. Email Production Activation

The local system currently has an SMTP problem where bob@dptest.local is rejected. That is a development configuration issue, not an acceptable production configuration.

Production should use:

Valid SMTP host
Valid port
Valid credentials
Valid TLS/SSL mode
Valid sender address
Real recipient domains

Recommended test order:

Application test
   ↓
Send one internal test email
   ↓
Send one customer test email
   ↓
Check SMTP logs
   ↓
Only then enable production notifications

20. Queue / Scheduled Jobs

If the application uses queued mail, notifications, payment processing, or scheduled maintenance, configure the hosting environment accordingly.

Typical queue worker concept:

php artisan queue:work --tries=3

Cron concept:

* * * * * cd /home/dparcel/deliveringparcel && php artisan schedule:run >> /dev/null 2>&1

The exact queue driver and scheduled tasks must be based on the project's actual .env, config/queue.php, app/Console/Kernel.php, and job usage.

21. How to Activate the Legacy System on a New Host

If the legacy system is the fallback/temporary production system:

Step 1

Deploy the complete Laravel project.

Step 2

Import the production database.

Step 3

Use the existing production .env values adapted for the new host.

Step 4

Point the domain/subdomain document root to:

/path/to/deliveringparcel/public

Step 5

Verify:

/order/13

Step 6

Test:

Login
Order list
Order detail
Make Offer
Client offer
Offer acceptance
Payment
Notifications
Uploads
Admin access

The legacy workflow must remain usable before attempting the new-system cutover.

22. How to Activate the New System

The new system should be activated in the same deployed Laravel application after parity checks.

Verify:

/admin/orders/13

Then verify all legacy-required business functions are available:

Service selection
Service price handling
Additional services
Shipping
Offer description
Total calculation
Make Offer
Offer status
Client visibility
Client acceptance

Then verify new capabilities:

Stripe Only
Bank Only
Stripe + Bank
Server-side payment restriction
Bank proof verification

Only after this testing should the new Admin route become the primary operational path.

23. Do Not Create Separate Databases for Legacy and New System

Recommended:

                         ONE PRODUCTION DATABASE
                                   │
                 ┌─────────────────┴─────────────────┐
                 │                                   │
            Legacy workflow                    New workflow
                 │                                   │
                 └─────────────────┬─────────────────┘
                                   │
                             Shared orders
                             Shared customers
                             Shared services
                             Shared payments

Separate databases create synchronization and data-loss risks unless a full migration architecture has been designed.

24. Safe Cutover Plan

Stage 0 — Backup

Take:

Full database backup
Application code backup
Uploads backup
.env backup (secured privately)

Stage 1 — Deploy new host

Deploy complete Laravel application.

Stage 2 — Restore database

Import database and verify table counts.

Stage 3 — Configure .env

Set production domain/database/mail/Stripe values.

Stage 4 — Test legacy

Confirm:

/order/13

works.

Stage 5 — Test new Admin

Confirm:

/admin/orders/13

works.

Stage 6 — Compare business totals

Use the same order and compare:

Base amount
Selected services
Additional services
Shipping
Discounts/taxes if applicable
Final total

Stage 7 — Test payment

Test Stripe in test/sandbox mode before enabling live mode.

Stage 8 — Test Bank

Submit a bank proof and verify admin approval/rejection.

Stage 9 — Enable live Stripe

Only after webhook/payment verification.

Stage 10 — DNS cutover

Move the domain/subdomain to the new host.

Stage 11 — Keep old host intact

Do not delete the old application immediately.

Maintain it as the rollback source until the new hosting environment has been stable and fully verified.

25. Rollback Plan

If the new hosting system fails:

DNS / document root
        ↓
Old production environment
        ↓
Legacy workflow

Rollback must not require reconstructing the old server from memory.

Maintain:

Old source backup
Old database backup
Old .env backup
Old uploads
Old deployment configuration

Do not roll back a database after financial transactions have been written to the new system unless the financial/data reconciliation has been explicitly handled.

26. First Deployment Checklist

Full source backup created

Full database backup created

Uploads backup created

Production .env created

APP_DEBUG=false

Correct APP_URL

Correct database credentials

Correct APP_KEY

Production mail configured

Stripe production/test mode selected intentionally

Stripe webhook configured

Composer dependencies installed

storage/ writable

bootstrap/cache/ writable

Laravel caches rebuilt

.htaccess in actual public document root

Domain/subdomain document root points to Laravel public/

/order/13 tested

/admin/orders/13 tested

Make Offer tested

Client acceptance tested

Stripe payment tested

Bank payment tested

Email tested

Upload/storage tested

Queue/cron verified if used

Legacy fallback retained

27. Files That Should Be Treated as Critical

From the documented project material, these are especially important during migration:

app/Http/Controllers/OrdersController.php
app/Http/Controllers/Admin/OfferController.php
routes/admin/orders.php
resources/views/admin/orders/edit.blade.php
resources/views/admin/orders/offer.blade.php
resources/views/clients/order_offers.blade.php
resources/views/clients/order_offer.blade.php
resources/views/admin/payments/index.blade.php

database/migrations/*
database/*
config/*
resources/views/*
public/*
storage/*

Also preserve all existing project-specific controllers, models, service classes, middleware, policies, notifications, jobs, payment code, JavaScript and assets even when they are not listed individually in this report.

28. Critical Existing Problems to Check Before Going Live

28.1 SMTP problem

Observed:

Swift_TransportException
Expected response code 354 but got code 503

The invalid development recipient observed was:

bob@dptest.local

This must not be present in the production notification path.

28.2 Client offer rendering error

The project error material also contains an exception from:

resources/views/clients/order_offer.blade.php

with an attempt to read id from a null object.

This should be fixed and regression-tested before enabling the new offer workflow for production users.

28.3 CommonMark deprecation

A convertToHtml() deprecation warning was identified separately from the SMTP failure. It should be addressed appropriately, but it is not the cause of the SMTP 503 error.

29. Final Recommended Deployment Model

Use this layout:

/home/dparcel/
│
├── deliveringparcel/                 ← PRIVATE LARAVEL ROOT
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   ├── composer.json
│   ├── composer.lock
│   └── .env
│
└── public_html/                      ← WEB ROOT
    ├── index.php
    ├── .htaccess
    ├── build/ or assets/
    ├── css/
    ├── js/
    └── images/

Best alternative: configure the hosting document root directly to:

/home/dparcel/deliveringparcel/public

That avoids exposing the application root and eliminates the need for a public-root path workaround.

30. Final Activation Decision

Legacy-only

Use when:

New Admin workflow is not yet fully verified.

Primary business flow:

/order/{id}

Keep /admin/orders/{id} available only to administrators/testers if needed.

New-only

Use only after functional parity and payment testing are complete.

Primary admin flow:

/admin/orders/{id}

Legacy can remain available as a restricted rollback/reference route.

Dual-mode — Recommended during migration

Customers/Admins
      │
      ├── Legacy route retained
      │
      └── New route enabled for controlled users

Shared database
Shared orders
Shared customer data
Shared payment infrastructure

This gives the safest migration path.

31. Important Distinction: What Is Confirmed vs What Must Be Verified

Confirmed from the available project material

Legacy order page: /order/13.

New Admin order page: /admin/orders/13.

Legacy order page contains the full Make Offer workflow.

New payment policy supports Stripe Only / Bank Only / Stripe + Bank.

Server-side payment restriction is required.

Existing Stripe code should be reused.

Hosting traces show /home/dparcel/public_html/deliveringparcel.

Existing hosting traces also reference /home/dparcel/public_html/index.php.

Local project path is under C:\laragon\www\dplive\deliveringparcel.

SMTP error is associated with bob@dptest.local.

Client offer view has had a null-object rendering error.

Must be verified from the actual project before final cutover

Exact Laravel version.

Exact PHP version required by composer.lock.

Exact active domain document root.

Exact active index.php.

Exact current .env variable names.

Exact Stripe webhook route.

Exact queue driver.

Exact scheduled tasks.

Exact upload/storage configuration.

Exact service CRUD implementation.

Whether a separate home2 view or alternate home system exists in the current source tree.

Whether legacy and new systems share the same database tables/controllers for every module.

32. Executive Recommendation

The production migration should not be performed as a simple "copy everything into public_html" operation.

The safest architecture is:

Private Laravel application
        ↓
Public document root = Laravel /public
        ↓
Production .env
        ↓
Production database
        ↓
Production mail
        ↓
Stripe + webhook
        ↓
Legacy + New routes in SAME application
        ↓
New workflow becomes primary only after parity tests
        ↓
Legacy remains as rollback/reference

The project documentation explicitly requires preserving the legacy workflow and avoiding destructive migration/data loss. The recommended deployment architecture above follows that requirement.

Deployment command summary

Typical sequence from the Laravel application root:

composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

Run migrate --force only after a verified database backup and after confirming that the migrations belong to the deployed application version.

Rollout order

1. Backup
2. Copy application
3. Configure document root
4. Create .env
5. Import database
6. Install dependencies
7. Fix permissions
8. Clear/rebuild Laravel cache
9. Configure mail
10. Configure Stripe/webhook
11. Test legacy
12. Test new Admin
13. Test offer workflow
14. Test payments
15. Test uploads/notifications
16. DNS cutover
17. Keep legacy environment for rollback

Status: Deployment/reporting specification prepared from the available DPLive/DeliveringParcel project material plus standard Laravel hosting requirements.