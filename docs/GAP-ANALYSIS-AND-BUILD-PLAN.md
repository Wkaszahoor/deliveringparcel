# GAP ANALYSIS & BUILD PLAN — deliveringparcel-New / deliveringparcel-expoNew

Created 2026-09-02. This file is the master plan for all NEW work.
The OLD folders are FROZEN — see "Isolation rules" below.

---

## 1. What this is

| Folder | Role | Status |
|---|---|---|
| `C:\laragon\www\dplive\deliveringparcel` | OLD web app (legacy + new design) | **FROZEN — never modify** |
| `C:\laragon\www\dplive\deliveringparcel-expo` | OLD client + admin Expo apps | **FROZEN — never modify** |
| `C:\laragon\www\dplive\deliveringparcel-android` | OLD Kotlin prototypes | FROZEN (abandoned anyway) |
| `C:\laragon\www\dplive\deliveringparcel-New` | **NEW web app (this folder)** | All new web files go here |
| `C:\laragon\www\dplive\deliveringparcel-expoNew` | **NEW Expo apps** | All new app files go here |

Duplicates made 2026-09-02 (robocopy, 0 failures):
- Web: 11,037 files incl. `vendor/` (node_modules excluded — none needed to run).
- Expo: full source, `node_modules`/`.expo` excluded → run `npm install` in
  `admin/` and `client/` before first use.
- Docroot assets (css/images/assets/dashbord/frontend/uploads/robots/favicon)
  copied into `deliveringparcel-New/public/` + standard Laravel
  `public/index.php` + `public/.htaccess` added, so the New app is
  **self-contained and previewable**.

### How to run the New app (isolated from the old one)

```
cd C:\laragon\www\dplive\deliveringparcel-New
php artisan serve --host=127.0.0.1 --port=8010
# → http://127.0.0.1:8010  (login, /home2, admin all work)
```

- **Database: `deliveringparcel_new`** (full copy of the old dev DB made
  2026-09-02: 113 tables, 12 users, 58 orders). The OLD app keeps using
  `deliveringparcel` — the two never share data.
- `.env` in this folder: `APP_URL=http://127.0.0.1:8010`,
  `DB_DATABASE=deliveringparcel_new`.
- dplive.test (Laragon vhost at the docroot) still serves the OLD app only.
  The root `deliveringparcel-New/.htaccess` denies direct web access to this
  folder (protects `.env` under the live docroot).
- To serve the New app under Apache/Laragon later: add a vhost pointing at
  `deliveringparcel-New/public` (e.g. `dplivenew.test`). Not required for dev.

### Isolation rules (user-mandated 2026-09-02)

1. OLD folders (`deliveringparcel`, `deliveringparcel-expo`,
   `deliveringparcel-android`) stay 100% intact — no edits, no new files.
2. ALL new files (shipper system, gap fixes, experiments) are created in the
   `-New` folders ONLY.
3. Inside the New web app, legacy order/offer/payment/chat flows remain
   logically frozen too (same file list as before) — new features must be
   additive: new routes files, new controllers, new views, append-only edits.
4. Never point the New app at the old DB or vice versa.
5. Deploy story: when a New build is proven locally, it ships as a full
   package from the New folders (never re-upload the old folder's variants).

---

## 2. Consolidated GAP list to incorporate (from the 2026-09-01/02 audits)

Everything below is verified with file:line in the OLD code; the New copy
inherits all of it. Fix these IN THE NEW FOLDERS as part of the build.

### 2.1 Security / correctness — legacy web (highest priority)

1. `OrdersController@stripePost` (~1005): deprecated Stripe Charge API,
   **amount taken from client request** (not validated vs offer), no try/catch.
2. `POST /Order_complete/{id}` (~727): writes `order_status` verbatim from
   the client request — mass assignment.
3. `OrdersController@store` (~line 81): inline Turnstile verify with
   **hardcoded secret** (bypasses `App\Support\Turnstile`).
4. order_id race: `DB::latest()+1` (~97).
5. `RegisterController::create`: inserts a NEW `roles` row 'client' per
   registration (role-table bloat).
6. UploadGuard bypass on `/chat`, `/tracking_id`, `/image_upload`,
   `/purchase_image` (raw client filenames into `uploads/chatimages`).
7. Admin avatar hardcodes `../public_html/uploads/profile`.
8. `Home2\OfferController@accept` writes `offer_status=1` pre-payment —
   violates the "1 = PAID" two-column semantics every other writer honors.
9. GET-logout in home2 footer (CSRF); `/clear-cache` hardening already done.

### 2.2 New web design (Home2)

1. `home2/pay.blade.php` ~263–313: orphaned bank-details JS **outside any
   <script> tag — renders as visible text on the checkout page**. Delete block.
2. `blog/show.blade.php:36`: `{!! $post->content !!}` unsanitized (imported
   content → stored XSS). Use `HtmlSanitizer::clean()` like service-show:17.
3. `home2/request2.blade.php:118`: hardcoded Turnstile sitekey — use the
   shared partial + admin toggle.
4. Title double-suffix (`layouts/app.blade.php:7`).
5. Wallet page uses default `->links()` (unstyled) — use the shared partial.
6. No home2 404/500; testimonials + terms-content still legacy-skinned;
   login legacy.
7. Order statuses render as raw text (no config labels, no stepper);
   'pending' status missing from `config/admin_orders.php`.
8. SEO: no OG/canonical sitewide, no JSON-LD, no prev/next on pagination.
9. Parity gaps: no profile/password/avatar page in home2; home2 chat send is
   text-only (legacy can upload images).

### 2.3 Admin dashboard (KPI at /admin-dashboard2)

1. **Undiscoverable** — nothing links to it; add hub card + menu partial.
2. Revenue is gross (ignores `amount_refunded`); two conflicting refund
   definitions on one page; multi-currency summed as hardcoded `$`.
3. Funnel 'paid' step missing `archived_at` filter; 'Shipped' missing from
   accepted + in_transit lists; `tracking_needed` misses NULL tracking_status
   (48/58 rows locally!).
4. Range pill misleading: Action Required/Operations/Customers ignore range
   unlabeled; ~42 uncached queries per load.
5. Hub (`admin.home`): unguarded queries; "Users" includes admins; "Offer
   Orders" counts every historical offer row.
6. Definition drift vs mobile DashboardController (offer_rejected,
   tracking_needed, no_offer, avg_delivery_days) — extract shared
   MetricsService eventually.
7. Chart.js + Poppins from CDN — vendor locally.

### 2.4 Mobile apps (Expo = authoritative; Kotlin = abandoned prototype)

Client app: labels API not integrated (titles hardcoded); order-archive
endpoint never called; no register/forgot-password; no wallet topup; FCM
token defined-never-sent; `api.ts` request() drops Laravel `errors` bags
globally; many silent `catch {}`; dead Offer screen; console.log PII;
receipt upload hardcodes receipt.jpg.
Admin app: no clients screen; no refunds; no archive; 6-filter search not
exposed; `_sectionErrors` captured but never rendered; Android
LoginViewModel sets role "admin" unconditionally; force_update fetched but
never enforced (both apps); deep links unwired.
API: payments/queue unpaginated; dashboard ~20 aggregates/hit; uncached
`/mobile/labels` closure; ChatController hides chat under `{message}` key;
60/min throttle headroom thin.

---

## 3. SHIPPER SYSTEM (pasted spec) — audited against the real code

The pasted build spec is broadly sound. Audit corrections (2026-09-02,
verified against the real schema/code — use THESE in the build):

| Spec says | Reality | Action in build |
|---|---|---|
| roles pivot `user_roles` | pivot is **`users_roles`** | Role model already handles; never write `user_roles` |
| `roles.slug` column | ✅ EXISTS (name + slug) | `shipper` / `shipper_pending` inserts fine |
| `Country::where('active', true)` | column is **`is_active`** | use `where('is_active', true)` |
| `asset($proof->file_path)` for proofs | proofs are on the PRIVATE disk | serve via authenticated stream route (pattern: `/mobile/v1/payments/{id}/proof`) |
| `role:shipper,shipper_pending` multi-role | RoleMiddleware takes ONE role today | extend to comma-separated whereIn (New copy only) |
| append routes after web.php line 400 | fine, but prefer separate `routes/shipper.php` required from web.php bottom | additive require lines only |
| FK `unsignedInteger` users | users.id is int — ✅ fine | keep |
| notifications table | Laravel `notifications` exists | fine |

Build order (recommended):
1. Migrations 1–15 (all `2026_09_10_*`), UsernameService + command, roles seed.
2. Models (8+) with `::class` relations ONLY (Linux case trap).
3. Services (ShippingRequestService, ShipperRegistrationService) + 4 notifications.
4. Admin controllers + routes/admin/shipper.php + admin views + menu partial.
5. Shipper web portal (routes/shipper.php, home2-style layout) + middleware.
6. Client-facing additions (proofs/address/tracking) — new-design views only,
   never order_offer.blade.php.
7. API controllers + routes/api/shipper_api.php.
8. Expo additions in `deliveringparcel-expoNew` only (client + admin).
9. Fold in §2 fixes opportunistically per touched area (e.g. sanitize output
   in any new rich-text surface; surface `errors` in every new screen).

Standing build rules: `php -l` every file; `::class` refs always;
exact-case view names (grep `view(` before zipping); one migration class per
file; LF endings; user tests every flow personally; never POST to pay/accept
endpoints during verification (read-only checks only).

---

## 4. Verification snapshot (2026-09-02, post-copy)

- `route:list`: 1,035 admin/api routes registered in New copy (incl. `/admin-dashboard2`).
- `migrate:status`: all migrations "Yes" (DB copy carries the migrations table).
- Tinker: `DB=deliveringparcel_new users=12 orders=58`.
- HTTP smoke: `/login` 200 (real title), `/css/home2.css` 200, `/home2` 200
  via `php artisan serve` on 8010.
- Old folders untouched; MySQL + artisan serve started and stopped cleanly
  (stack was down before, down after).

## 5. LIVE URLS (2026-09-02, second session)

The New app is now served under the real domain via per-prefix front controllers:

| URL | Serves |
|---|---|
| `http://dplive.test/` (+ all normal paths) | OLD app — untouched, verified 200 |
| `http://dplive.test/home3` ... `/home3/anything` | **NEW app** (new-design entry — /home3/home2 etc.) |
| `http://dplive.test/legacy2` ... `/legacy2/anything` | **NEW app** (legacy-design entry — /legacy2/login, /legacy2/request ...) |

How it works: `dplive/home3/` and `dplive/legacy2/` each contain a Laravel
front controller (`index.php` requiring `../deliveringparcel-New/...`) +
standard `.htaccess`, with **directory junctions** to
`deliveringparcel-New/public/{css,images,assets,dashbord,frontend,uploads,blog,js}`
so assets and future uploads serve from the NEW app's own public dir
(prefix-relative, fully isolated from old assets). Because the deepest
existing directory decides which `.htaccess` applies, old URLs keep hitting
the old front controller — zero old files modified.

Cookie isolation: New app uses `SESSION_COOKIE=dpnew_session` (old uses
`deliveringparcel_session`) — both apps can be logged into in the same
browser simultaneously. Verified via Set-Cookie headers.

Verified live: `/home3`, `/home3/login` (real title), `/home3/home2`,
`/home3/css/home2.css`, `/legacy2`, `/legacy2/home2`, `/legacy2/css/...`
all 200; asset URLs inside rendered HTML correctly carry the `/home3`
prefix; old app 200s unchanged.

## 6. SHIPPER PHASE 1 — BUILT (2026-09-02)

- 15 migrations `2026_09_10_000001..000015` — ALL MIGRATED on
  `deliveringparcel_new` (12 shipper tables + shipping_requests + users
  username cols + orders shipper flags).
  Fix applied during migrate: two composite index names exceeded MySQL's
  64-char identifier limit → explicit short names
  (`sp_assign_appr_vis_idx` on shipper_proofs,
  `swt_profile_type_created_idx` on shipper_wallet_transactions).
- `app/Services/UsernameService.php` (CUS-/SHP- generation, ensure/assign).
- `app/Console/Commands/GenerateUsernames.php` + registered in Kernel.
- 12 models (ShipperProfile w/ wallet ops + level progression,
  ShippingRequest w/ reference boot + visibleToShipper scope, Assignment,
  Quote, Proof, DeliveryAddress w/ forward-level masking, TrackingDetail,
  WalletTransaction, PayoutRequest, Rating w/ rating-recalc boot,
  KycDocument, AdminChat) — all relations via `::class`.
- Backfill results: all 12 users have `customer_username` (e.g. CUS-6EC7QH);
  `shipper` + `shipper_pending` roles seeded in `roles`.
- `composer dump-autoload -o` run (5,767 classes).

**Next phases:** services (ShippingRequestService, registration) → admin
controllers/views/routes → shipper portal → API → Expo screens.

## 7. SHIPPER SYSTEM — FULLY BUILT (2026-09-02, phases 2-7)

E2E-PROVEN in the isolated DB (chain test, real order 66):
request DP-SR-0001 → published → user 2 promoted to SHP-AFG-9476 → KYC
approved → quote $25 → assigned (20 credit + 5 hold = 20% split, request
frozen, order flagged, capacity++) → payment released (wallet 20, held 5,
total_completed 1, order completed, 4 notifications fired).

Built:
- 4 notifications (database-only locally, mail+db in prod) + 2 services
- Middleware: RequireShipperProfile (new alias `shipper.profile`);
  RoleMiddleware now accepts comma role lists (additive); new
  api.shipper guard
- Admin: 5 controllers + routes/admin/shipper.php + 8 views + auto-loaded
  menu partial (admin/menu/shippers.blade.php)
- Shipper portal: 4 controllers + routes/shipper.php + 9 views
  (own layout, home2.css tokens) incl. /become-a-shipper (public,
  guest-capable) + customer delivery-address submit
- API: 8 controllers + routes/api/shipper_api.php (115 shipper routes
  total) — shipper app, admin app, client proofs/address/tracking
  (private-disk streams)
- Order pages: admin ordersm/show got the 1-click "Create Shipping
  Request" card; home2 orders/show got proofs grid + address form +
  tracking card (all append-only)
- expoNew: client api.ts types+endpoints + ShipperOrderSections component
  wired into OrderDetail; admin api.ts endpoints + 4 new screens
  (ShipperOverview, ShippingRequests, ShipperAssignments,
  ShipperAssignmentDetail) registered in App.tsx + Dashboard quick-action
- view:cache compiles ALL blades clean; every new PHP file lint-clean

Test data left in deliveringparcel_new for the user's first look:
DP-SR-0001 flow + shipper SHP-AFG-9476 (user id 2 also got roles).

## 8. PHASE 8 — FORMER "NOT-BUILT" ITEMS DONE (2026-09-02, same day)

1. **Hold auto-release**: `shipper:release-holds` command (transactional,
   lockForUpdate + hold_release_at nulled = idempotent), scheduled daily
   03:10 in Kernel. LIVE-PROVEN: made test hold due → run 1 "Released 1
   hold(s)" (wallet 20→25, pending→0, hold_release txn written) → run 2
   "No due holds". Prod note: needs `php artisan schedule:run` cron like
   the queue drain.
2. **Ratings**: customer web form (star picker) appended to home2 order
   page (shows when assignment completed; shows own rating afterwards);
   POST /rate-shipper/{orderId} (owner-checked) + API
   POST api/client/orders/{id}/rate-shipper; double-rating guarded;
   ShipperProfile boot auto-recalcs rating (proven 5★ → rating 5.00);
   admin moderation buttons (Approve / Publish-as-testimonial w/ consent
   enforcement) in shipper profile ratings table.
3. **3rd Expo app** `deliveringparcel-expoNew/shipper/`: full app —
   app.json (com.deliveringparcel.shipper, scheme dp-shipper), package.json
   (same deps as client), shared copied + lean shipperApi.ts, App.tsx auth
   gate + 8 screens (Login, Dashboard, Requests, RequestDetail w/ quote
   form, Assignments, AssignmentDetail w/ staged actions + image-picker
   proof upload + tracking form + chat, Wallet w/ payout, Profile w/ edit
   + KYC status). Assignment API now returns chat_messages + marks admin
   chat read on open. Run `npm install` then `npx expo start` in the
   folder; eas init needed for own EAS projectId.

## 9. KPI ENGINE + DASHBOARDS (2026-09-03, per pasted KPI spec — audited then built)

- **ShipperKpiService** (`app/Services/Shipper/ShipperKpiService.php`): 8 admin
  zones (critical queue incl. quotes-waiting>24h + stale-KYC, flow velocity
  from lifecycle timestamps, stuck>48h per status, marketplace health
  (avg quotes/acceptance/expiry/by-country), network health (utilization,
  capacity, idle-7d, by-level, problem shippers), money flow (holds,
  releasing-7d, payouts, platform revenue 30d, avg fees), quality
  (rating, completion/dispute rates, testimonials), 24h activity feed) +
  per-shipper personal KPIs (month earnings, next hold release, success
  rate, avg completion, purchase/dispatch velocity, rating breakdown,
  prioritized to-do list, top-3 request snapshot). Every zone guarded.
  Live-proven on real data (rev30d=$5, rating 5.00, 100% completion, feed 3).
- **Admin**: overview.blade rebuilt into mission-control (velocity table,
  action queue with direct links, money/marketplace/network/quality zones,
  feed). Admin API overview now returns money/velocity/quality; admin expo
  ShipperOverview shows revenue/holds/rating stats.
- **Shipper**: web dashboard rebuilt (to-do list w/ priority colors +
  deep links, earnings/performance banner, marketplace snapshot); API
  dashboard returns full `kpi` object; expo shipper Dashboard renders
  todos, performance card, velocity, rating breakdown, request snapshot.
- **Shopper (customer-facing KPIs)**: "📦 Your Order Progress" box on the
  home2 order page — 6-step tracker from assignment status, ETA from
  tracking, partner performance (rating + level + deliveries).

### 🔴 TWO BUILD LESSONS (hard-won, 2026-09-03)
1. Laravel array-style Route::group name key is `'as'`, NOT `'name'`
   ('name' silently ignored → unprefixed route names).
2. **Blade directive corruption in this app**: a `@php...@endphp` block
   followed by @if directives caused the compiled view to contain raw
   uncompiled directives + a stray endif (500 "unexpected token endif" /
   "Undefined variable $dpAssignment"). Comment-stripping also half-failed
   on the same file. FIX: rebuild the customer block DIRECTIVE-FREE
   (raw `<?php if/foreach/for ?>` + `{{ }}` echoes only) — now 200 over
   real HTTP. RULE for all future blade work here: after any raw PHP
   block, do NOT rely on @php/@if pairing — use raw PHP control
   structures; run a REAL logged-in HTTP render check, not just
   view:cache (which compiles but never executes).
3. ShipperAdminChat model needed explicit `protected $table =
   'shipper_admin_chat'` (singular; Eloquent guessed plural).
