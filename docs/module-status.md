# Module Status (2026-08-18)

## Live & stabilized

| Module | Route | Notes |
|---|---|---|
| Dashboard | /admin-dashbord | new responsive shell + live module grid |
| Analytics | /admin/analytics/* + /admin/dashbord2 | KPIs, lazy charts, revenue/demand/RFM |
| Orders | /admin/orders | state machine, bulk, tracking, archive |
| Users | /admin/users | roles, resets, order history |
| Blog (+categories) | /admin/blogs, /admin/blog-categories | SEO fields |
| Services (+categories) | /admin/services, /admin/service-categories | type/availability maps |
| Hero Slider | /admin/hero-slides | reorder, 20+ UI options — also drives /home2 |
| Messages | /admin/contacts, /admin/contact-templates | auto-classify + templates |
| Payments | /admin/payments | ledger + refund action |
| Settings | /admin/settings | 5 tabs, persisted (settings table) |
| Countries | /admin/countries | toggle, rates |
| Weight Units | /admin/weight-units | default handling |
| Rate Engine | /admin/rates/* | zones/rules/surcharges/insurance/calculator (verified compute) |
| States & Cities | /admin/geo/* | dependent dropdowns |
| Notifications | /admin/notifications | center + per-type preference matrix |
| Audit Log | /admin/audit | before/after diff, masked secrets |
| Quotes & Offers | /admin/quotes, /admin/quotes/offers | read-models over request_quotes/offerorders |
| Returns & Claims | /admin/returns, /admin/returns/claims | enforced status flows |
| /home2 frontend | /home2/* | all pages verified 200 |

All lists: server-side pagination (`?per_page=` 15/25/50/100), lazy images,
infinite scroll, mobile card-collapse tables.

## Not yet built (honest inspection finding)

The stabilization brief listed **Warehouse** and **Carriers** as completed modules, but
inspection shows **no implementation exists** for either (no controllers, models,
migrations, routes or views — their route stubs in `routes/admin/` are still empty).
They appear as "Building…" on the dashboard module grid. Both need a full build pass.

## Built in this pass (2026-08-18, pending-modules list)

- **Shop** (`/admin/shop/*`): products (multi-image upload, soft delete), hierarchical
  categories, reviews moderation (approve/reject/bulk filters + rating KPIs), coupons
  (percent/fixed, usage limits), shop orders (read + status map), shop analytics
  (revenue/AOV/top products). Tables: `shop_*` (6). Seeder: ShopSeeder.
- **Tools** (`/admin/tools/*`): global search (page + JSON across users/orders/quotes/
  messages/blogs/products/services), system health (DB latency, disk, last 10 log
  errors), read-only DB viewer (information_schema whitelist, sensitive columns
  masked, values truncated), PDF exports via PdfService print-fallback (invoice,
  order summary, financial report), testimonials CRUD with reorder + publish.
  Table: `testimonials`.
- **Compliance** (`/admin/compliance/*`): KYC queue (approve/reject with reason +
  expiry, doc viewers), sanctions screening (metaphone+levenshtein fuzzy match,
  country boost, logged history — verified: "Viktor Zahkarov" → Zakharov match),
  restricted items CRUD, HS codes CRUD, VAT rules (4 schemes), versioned consent
  templates with conditional triggers, GDPR export (JSON download + audit log,
  credential fields stripped). Tables: 8 compliance tables. Seeder: ComplianceSeeder.

Shared infra added: `admin/partials/lazy-table.blade.php` (generic config-driven
responsive lazy table used by all new modules).

## Infra notes

- `dp_per_page()` global helper (app/Support/helpers.php, composer files autoload).
- Blade→JS data must use `@json()`, never `{{ json_encode() }}` (see docs/bug-report.md).
