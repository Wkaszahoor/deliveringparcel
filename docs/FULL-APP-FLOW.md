# DeliveringParcel — Full Application Flow Guide

**Scope:** the complete deliveringparcel-New application (Laravel 12) — every role, every feature, and how they connect.
**Audience:** owner / developers / future maintainers.
**Companion doc:** `SHIPPER-SYSTEM-GUIDE.md` (deep dive on the shipper marketplace: 20 business rules + full DB schema).

---

## 1. What this application is

DeliveringParcel is a **managed purchase-and-shipping platform**. A customer ("shopper's client") wants products from abroad; the company (admin) buys and/or ships them; trusted freelance **shippers** compete in a marketplace to handle the local leg. The platform holds the money, enforces the rules, and pays out.

There are two codebases in the ecosystem:

| Codebase | Laravel | Role |
|---|---|---|
| `deliveringparcel` (legacy) | 8.83 | The live production site. **FROZEN** — receives only critical fixes. |
| `deliveringparcel-New` (this package) | 12.69 | The future app. Contains the legacy order flow **plus** the portal/CMS/shipper systems. Local dev URL: `http://127.0.0.1:8083` |

Key design decision: the **legacy order flow is THE flow** — admin order page `/order/{id}`, client order page `/orders/{id}` (the `order_offer` design). The New app ports this flow verbatim (including Payoneer, price verification, and all hotfixes) and wraps it in new surroundings: an admin-designed client/shipper portal, a CMS, and the shipper marketplace. The new-design "home2" landing is **admin-only** (gated); the public landing stays the legacy design.

---

## 2. Roles at a glance

| Role | Enters via | Sees |
|---|---|---|
| Guest | `/` | Landing, services, blog, testimonials, track order, contact, free quote, terms, shipper program + directory, register/login |
| Client ("shopper") | `/dashboard` | Own orders (KPIs, filters, lazy table), order detail (offer → payment → chat → tracking → rating), notifications & messages, profile, "Become a Shipper" |
| Shipper | `/shipper/dashboard` | Marketplace, my quotes, assignments, wallet, KYC/profile, admin chat, ratings |
| Admin | `/admin-dashboard2` | Everything: orders, offers, payments verification, shipping requests, shipper management, CMS, settings, communications, analytics |

Test logins (this dev database only): `admin@deliveringparcel.com` / `TestPass123!` (admin), `bob@dptest.local` / `TestPass123!` (client), `saquu1@gmail.com` / `TestPass123!` (client + active shipper).

---

## 3. Guest / public features

- **Landing `/`** — legacy design (deliberate choice; home2 design archived but admin-only).
- **Services & blog** — served from the CMS (`cms_posts`), not static files. Root URLs `/services`, `/blog` with 301s from old paths.
- **Testimonials `/testimonials`** — country-filtered, latest 10; also a home section. Client ratings can be published as testimonials (with consent).
- **Track order `/track-order`** — GET form + POST lookup (legacy style).
- **Contact `/contact-details`** — contact page (home2 path 301s here).
- **Free quote `/freequote`** — lead capture.
- **Shipper program `/shipper-program`** — public landing explaining perks, the escrow payout model (80% instant + 20% after 7-day hold), duties (buy-for-me / ship-for-me), KYC requirements, and levels L1–L3.
- **Shipper directory `/shippers`** — anonymized cards of active shippers: display name (`DP-SHIP-xxx`), level badge, star rating, completed count, countries, member-since. No personal data.
- **Apply `/become-a-shipper`** — guest signup **or** existing-client conversion (see §5.1).
- **Auth** — register/login with Turnstile captcha (per-page admin toggles under a master switch; widgets hidden when the master is off).
- **SEO** — dynamic `/sitemap.xml` built from CMS + settings; Route Manager can rename/redirect public paths; marquee bar (admin-controlled text/speed) on 5 layouts.

---

## 4. Client (shopper) journey

### 4.1 Dashboard — `/dashboard`
Admin-design portal shell (role-aware sidebar; a shipper additionally sees the Shipper Workspace group).

- **KPI cards** — total orders, awaiting payment, in progress, completed (paid-sum driven; computed with a direct `offerorders` paid-status pluck).
- **Advanced filters** — free-text search, status dropdown, type dropdown, Apply/Reset (server-side, GET).
- **Lazy-loaded table** — 10 rows/page via JSON endpoint `GET /dashboard/data` (`DP.infiniteScroll` contract: `{current_page, last_page, data[]}`), loading-row spinner, click-through to order detail.

### 4.2 Placing a request (the request form)
1. Client picks a **service** (7 fixed services) and/or adds **product lines** (name, qty × unit price).
2. Totals calculate live on the client (bound to both `input` and `keyup`; the "add more" rows are readonly totals), with NaN guards that block submission of broken input.
3. **Server-side verification recomputes qty × price** before saving. Any mismatch is stored in `orders.price_flags` and raises a TaskNotification to admin — the admin sees the discrepancy before quoting. `product_totalprice` has a server-side fallback.
4. Order is created with status **Request Placed**.

### 4.3 Offer → accept/reject (two-column state machine)
- Admin reviews the order on `/order/{id}` and creates an **offer** (price, delivery terms). Offer rows live in `offerorders.offer_status`: `0 = offered`, `1 = accepted`, `2 = rejected`.
- Client order page `/orders/{id}` renders **tabs driven by `offer_status`** (0 → offer panel with Accept/Reject + payment methods; 1 → paid/progress layout; 2 → rejected view). `orders.order_status` (text) drives the labels on top of the same layout.
- **Rule: both columns stay in lockstep** on every state change (accept/reject/payment all update both).

### 4.4 Paying
Payment options on the offer panel:

| Method | How it works |
|---|---|
| **Stripe Checkout** | Redirect to Stripe-hosted page; webhook-independent **session recovery** (step 0b resume: re-opens live session state, settles or frees the order even if the client abandoned the page). Origin-aware returns. |
| **Bank transfer** | Multi-account dropdown (`bank_account_id` saved on the payment), instructions panel, client uploads proof → **admin verifies** (`verifyBankPayment` / `markBankPaymentReceived`). |
| **Wallet** | Prepaid balance; top-up via Checkout redirect + `topupReturn` finalize. |
| **Payoneer** | Manual-link mode: admin creates a `payoneer_requests` row and sends the link; client gets a dedicated Payoneer page (`payoneer/pay`), pays externally, optionally uploads proof (setting `payoneer_proof_required`); **admin Payoneer inbox** verifies. Module toggle: `payoneer_enabled`. |

All verification funnels through the payment engine (`PaymentService`): on paid, `syncOrderOnPaid` flips the offer to accepted, order status advances, notifications fire. Stuck payments self-heal (`recoverStuckCheckout` in status endpoints); admin has a one-click **"Fix Stuck Offer Status"** button.

### 4.5 Order detail — `/orders/{id}` (legacy flow)
- Tabs (chat, tracking, attachments, receipts viewer, payment panel) with **callout blocks driven by the `order_callouts` table** (admin-editable text, verbatim legacy fallback).
- **Chat** with admin (`chat_messages`, image attachments, always renders a conversation — never a bare null; polling intervals are settings-driven).
- **Notifications & messages inbox** pages with capped badges and scroll dropdowns (legacy unread-mountains fixed by the inbox build).
- **Tracking link** appears once the shipper/admin shares it.
- **Order archive** — soft-delete own orders from the list.

### 4.6 Shipper fulfillment touchpoints (when the order uses the marketplace)
- After proof approval, client submits the **encrypted delivery address** with a privacy level (full / partial / pickup-point — `forward_level` controls what the shipper may decrypt).
- Confirms receipt; then **rates the shipper** (star form, double-rating guarded).
- May consent to publish the rating as a public testimonial.

### 4.7 Mobile app parity
The Expo client app consumes API v5 (login, dashboard/create-order, search, conversations, offer economics incl. product pricing, payment filters, bank verify, profile) and reads renamable section labels from `GET /api/mobile/labels` and UI flags from `/api/mobile/v1/ui/*`. Anything renamed in Settings → Mobile App reflects in the app without a rebuild.

---

## 5. Shipper journey

### 5.1 Acquisition
- **Public program page** explains the model: escrow, 80% released on completion, 20% held 7 days, levels, KYC.
- **Directory** shows real, anonymized active shippers (social proof).
- **Application `/become-a-shipper`:**
  - **Guest:** creates account + pending shipper profile in one form.
  - **Existing client:** same form hides account fields and shows a green panel — *"Applying with your existing account: {name} ({email}). No new password needed — your shipper workspace opens inside this same account as soon as admin approves."* This is the **client→shipper conversion** path.
  - **Countries:** a checkbox grid (from the `countries` table — active countries, ISO-2 values), no Ctrl/Cmd multi-select.
  - **Already applied?** Any user with an existing shipper profile is redirected to `/shipper/dashboard` with *"You already have a shipper application. Track its status here."*
- On submit: `shipper_profiles` row (status `pending`) + services offered (buy_for_me / ship_for_me flags) + KYC slot.

### 5.2 KYC + approval gate
1. Applicant uploads **KYC documents** (stored on the **private** disk; never public URLs).
2. Admin sees the pending-KYC queue, reviews documents in-app, then **Approves** (status `active`, `verified_at` set) or **Rejects**.
3. Only `active` shippers: appear in the directory, can see marketplace requests, can quote.

### 5.3 Shipper workspace (portal design, same as client)
- **Dashboard `/shipper/dashboard`** — personal KPIs: todos, velocity, earnings breakdown, rating snapshot.
- **Marketplace `/shipper/requests`** (+ JSON `/requests/data`) — open shipping requests with masked briefs.
  - Visibility rule (`scopeVisibleToShipper`): request must be open + not frozen + required country ∈ shipper's `service_countries` + required level ≤ shipper level + shipper has no pending/accepted own quote on it.
- **My quotes** — submitted quotes with status (pending/accepted/lost).
- **Assignments `/shipper/assignments`** (+ JSON `/assignments/data`) — assigned work with deadlines, proof upload, tracking form, admin chat.
- **Wallet `/shipper/wallet`** (+ JSON `/wallet/data`) — balance, pending (held) and paid-out totals, full transaction ledger.

### 5.4 Working an assignment (buy_for_me vs ship_for_me)
1. **Admin generates** a masked brief from the order (no customer PII; value shown as a ±30% range) and **publishes** it to the marketplace.
2. Shipper **quotes** a fee; admin compares quotes and **selects** a shipper. The fee is **frozen at selection** into the 80/20 split: 80% credit + 20% hold with `hold_release_at = +7 days`. The request freezes; other quotes lose.
3. **buy_for_me:** shipper purchases the product and uploads the **purchase receipt** (2-day deadline). **ship_for_me:** shipper confirms **package received**.
4. Shipper uploads **proof** (photos; private disk) → admin approves.
5. Client submits the encrypted delivery address (privacy level per §4.6).
6. Shipper buys/ships, then enters **tracking details**; admin verifies and **shares** the tracking to the client's order page.
7. Client confirms receipt → **release**: shipper wallet +80% (hold transaction +20%), `total_earned` up, order completed. The nightly **`shipper:release-holds`** command (03:10) auto-releases matured holds — idempotent, `lockForUpdate`, disbursement transactions recorded.
8. Shipper requests **payout** → admin marks paid (payout request lifecycle).

### 5.5 Reputation & progression
- **Ratings:** client stars the shipper post-completion; average + count recalculated automatically; admin can approve / publish as testimonial (consent enforced).
- **Levels:** L1 (Buy-for-Me entry) → L2 at 5 completions / 4.0★ / 3 ratings → L3 (Unlimited) at 25 / 4.5★ / 15. Level gates which marketplace requests are visible; capacity (`max_concurrent_orders`) gates quoting when busy.
- **Admin chat:** per-assignment thread with unread counts both ways.
- **Service countries governance:** shippers can't edit their countries directly — `/shipper/countries` shows the approved list and lets them send a **change request** (tick from the admin-enabled grid + optional note; one pending request at a time). Admin approves (countries apply instantly) or rejects (with note); both sides get notifications.

---

## 6. Admin journey

### 6.1 Dashboards
- `/admin-dashboard2` — KPI dashboard (discoverable from sidebar; legacy `/admin-dashboard` redirects here).
- Modules grid on the admin home links every subsystem.
- Analytics — revenue charts, contact submissions, email queue monitor.

### 6.2 Orders
- **List `/admin-orders`** — 10/page, **advanced search** (6 server-side GET filters; `__none__` = "Request Placed"), responsive cards on mobile.
- **Order page `/order/{id}`** (legacy flow, the heart of the app):
  - create/edit the **offer**, update status, share tracking links, open order chat, view payments/proofs,
  - **Generate Shipping Request** → masked brief → publish to marketplace (§5.4),
  - appended shipper cards (quote review, select-shipper, proof approval, address decrypt view, release-payment),
  - one-click **Fix Stuck Offer Status**,
  - `price_flags` discrepancy banner when the client-side form was tampered.

### 6.3 Money
- **Bank verification** — proof viewer, verify/receive actions.
- **Payoneer inbox `/admin/payoneer`** — create request → send link → verify proof → mark paid (module settings: enabled / proof required / instructions text).
- **Payment methods CRUD** incl. forced-gateway rules, **bank accounts** multi-account manager, wallet admin, refund destinations, COD + webhook logs (PM-014…017 suite).

### 6.4 Shipper management
- `/admin/shippers/overview` — mission control KPIs (velocity, marketplace health, network, money, quality, live feed).
- Applications list, **KYC queue** (document viewer, approve/reject), profiles (suspend/ban, level override), ratings approval + publish-as-testimonial, assignments monitor, holds/payouts.
- **Country Requests** — inbox for shipper countries-change requests (approve applies the new list, reject with note) plus a **direct override editor**; the **Countries** page (Admin → Content → Countries) enables/blocks network countries — blocked countries' requests are invisible marketplace-wide and never notified.

### 6.5 CMS (single content store)
- **All Content `/admin/cms/posts?type=…`** — one CRUD for `cms_posts` with type tabs: page / blog post / service / product. (The three old blog UIs and shop UI are retired as named redirects here.)
- **Menus** — visual builder over `nav_menus` + `cms_menu_items` (merged nav for site + panel).
- **Site Sections**, **Media**, **Blog Import** — WordPress WXR / atom / RSS / sitemap importer → `cms_posts` + import logs (idempotent by `meta.legacy_ref`).
- **Route Manager** — rename/redirect public routes (`route_manager_settings`), consumed by a `$routeLinks` composer in every view + public `/api/mobile/v1/route-config`.

### 6.6 Settings & configuration
Mobile app UI controls (4-level flags per screen), renamable section labels, sitemap settings, marquee (enabled/text/speed), Turnstile per-page toggles, Payoneer settings, **Order Callouts CRUD** (text of every callout block on the order pages), email master + queue toggles.

### 6.7 Communications
- **Email pipeline** (`EmailService`): templates CRUD → logs → queue monitor; queued sends via `dp:queue-drain` (cron); legacy mailable fallback.
- **Notifications/Messages inbox** — all TaskNotifications with mark-read, capped badges (admin twin of client inbox).
- **Contacts** — submissions list (dp-loading fix applied).

### 6.8 Users & access
Users CRUD, roles via `users_roles` pivot, permissions via `users_permissions`, role middleware accepts comma lists. Shipper role is just another role + a `shipper_profiles` row.

---

## 7. How everything connects

### 7.1 The spine: the order state machine
```
Request Placed ──admin offers──> Offered(0) ──client accepts+pays──> Accepted(1) ──fulfilment──> Shipped ──> Completed
                                      └──client rejects──> Rejected(2)
```
- `orders.order_status` (text) = the label shown.
- `offerorders.offer_status` (int 0/1/2) = the layout/tab driver.
- Payment engine transitions are the only writer of "accepted/paid" states — everything else keys off them.

### 7.2 Money flow (who holds what, when)
```
Client pays (Stripe | Bank | Wallet | Payoneer)
        │  admin verifies (bank/payoneer) or webhook/checkout settles (stripe)
        ▼
   Order paid ── offer flipped to accepted ── fulfilment starts
        │  (if marketplace route)
        ▼
Shipper fee frozen at selection: 80% shipper / 20% platform hold (7 days)
        │  release on client-confirm or admin release or 03:10 cron
        ▼
Shipper wallet balance ── payout request ── admin marks paid
```

### 7.3 The marketplace lifecycle
```
order ──admin generates──> shipping_request (masked brief)
      ──publish──> visible to matching shippers (country+level+capacity)
      ──quotes──> admin selects ONE ──> assignment (80/20 frozen, request frozen)
      ──receipt/package-received ── proof ── approve
      ── encrypted address ── tracking verify ── share to client
      ── client confirms ── release (80 credit + 20 hold) ── rating
```

### 7.4 Notification matrix
| Event | Client | Shipper | Admin |
|---|---|---|---|
| Order placed / price flag mismatch | — | — | ✅ TaskNotification |
| Offer created | ✅ | — | — |
| Offer accepted/rejected | — | — | ✅ |
| Payment verified / settled | ✅ | — | — |
| Request published / assigned / proof approved / hold released | — | ✅ (4 shipper notification classes, database channel) | — |
| Proof uploaded / address submitted / receipt confirmed | — | — | ✅ |
| Tracking shared | ✅ | — | — |
| Rating submitted | — | ✅ (recalc) | ✅ (approval queue) |

### 7.5 Chat surfaces (three, deliberately separate)
1. **Order chat** (`chat_messages`) — client ↔ admin on `/order/{id}` & `/orders/{id}`; image attachments; settings-driven polling.
2. **Shipper admin chat** (`shipper_admin_chat`) — per-assignment, shipper ↔ admin, unread counts in both consoles.
3. **Mobile conversations** — API v5 inbox feeding the Expo app (same underlying tables).

### 7.6 Cross-cutting utilities
`UploadGuard` (all uploads validated, execution killed in upload dirs), Turnstile, route manager, settings table (single source for toggles), marquee, callouts, sitemap.

---

## 8. Database map (deliveringparcel_new)

| Domain | Tables |
|---|---|
| Orders | `orders` (+ `price_flags` col), `orderproducts`, `offerorders`, `order_callouts` |
| Payments | `payments`, `payment_methods`, `bank_accounts`, `payoneer_requests`, wallet tables, webhook logs |
| Shipper (14) | `shipper_profiles`, `shipper_kyc_documents`, `shipping_requests`, `shipper_quotes`, `shipper_order_assignments`, `shipper_proofs`, `shipper_delivery_addresses`, `shipper_tracking_details`, `shipper_wallet_transactions`, `shipper_payout_requests`, `shipper_ratings`, `shipper_admin_chat`, `shipper_country_requests`, + shipper columns on `orders` + username cols on `users` |
| CMS (10) | `cms_posts`, `cms_taxonomies`, `cms_post_taxonomy`, `cms_media`, `nav_menus`, `cms_menu_items`, `site_sections`, `blog_posts/categories/tags` + pivots, `blog_import_logs`, `route_manager_settings` |
| Users/Auth | `users` (+`customer_username`,`shipper_username`), `users_roles`, `users_permissions`, `countries` |
| Messaging | `tasknotifications`, `chatnotifications`, `chat_messages`, `notifications` (Laravel database channel) |
| Email | `email_templates`, `email_logs` |
| Content ops | `testimonials`, `settings`, `site_sections` |

Full column-level schema for the shipper tables: see `SHIPPER-SYSTEM-GUIDE.md`.

---

## 9. API surface (mobile + shipper apps)

- **Sanctum** token auth throughout.
- `/api/mobile/v5/*` — client app (auth, dashboard/create-order, search, conversations, offers, payments, profile, labels).
- `/api/mobile/v1/ui/*` — UI control flags; `/api/mobile/v1/route-config` — route renames.
- `/api/shipper/*` — 3rd Expo shipper app (auth, marketplace, quotes, assignments incl. proof upload, wallet, chat; proofs stream from the **private** disk).
- Admin/client shipper API twins for the Expo admin app (`Api\Admin\ShipperAdminApiController`, `Api\Client\ShipperClientApiController`).

## 10. Scheduled jobs & commands

| Command | Schedule | Purpose |
|---|---|---|
| `shipper:release-holds` | daily 03:10 | Release matured 20% holds to shipper wallets (idempotent) |
| `dp:queue-drain` | cron | Drain queued emails |
| `queue:work` | everyMinute (without-overlap) | Laravel queues |

## 11. Security highlights

Uploads validated through `UploadGuard` + `.htaccess` exec-kill on upload dirs; KYC/proofs/address data on the **private** disk, streamed only to authorized viewers; delivery addresses encrypted with forward-level trust tiers; Turnstile on public forms; CSRF everywhere; role middleware on all admin/shipper surfaces; production sits behind Cloudflare with origin-lock rules and a Stripe-webhook exemption.

---

*Generated 2026-09-20 from the deliveringparcel-New codebase (Laravel 12.69). Section numbers in `SHIPPER-SYSTEM-GUIDE.md` hold the deep shipper detail (business rules 1–20, table schemas).*
