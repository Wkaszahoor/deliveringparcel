# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Individual online shoppers located outside the US, UK, and Europe who want to buy from retailers that don't ship internationally (Amazon, eBay, Walmart, Apple, Gucci, Hermès, etc.). They sign up for a free virtual address, shop as if they were a local customer, and have purchases forwarded to their real address anywhere in the world.

## Product Purpose

Delivering Parcel is a package forwarding / reshipping service. It gives customers free virtual addresses in the US, UK, and Europe, receives packages on their behalf, optionally consolidates multiple packages into one shipment, and forwards them internationally. Success is a customer successfully shopping from a store that wouldn't otherwise ship to them, at a lower total cost than shipping directly (where that's even possible).

## Positioning

Lowest cost in its category: no membership or monthly fees, tax-free shipping, and 45-day free package storage, versus paid-membership competitors like Shipito, Forward2me, and Shippn.

## Operating Context

- Public marketing site (home, about, contact, pricing/get-a-quote) where prospects learn about the service and start shipping.
- Client dashboard: order/shipment tracking, order history, attachments.
- Admin panel (Laravel + Tailwind): order management, tracking updates, CMS-managed nav, testimonials moderation, contacts, tools.
- Login/registration with email or social sign-in (Google OAuth live; Facebook code-complete but unconfigured; Apple explicitly skipped due to a dependency conflict with the existing Firebase/FCM push integration).
- Trust signals sourced from real Google Places reviews (synced) and manually entered Trustpilot/SiteJabber ratings — no fabricated testimonials.

## Capabilities and Constraints

- Laravel 12 + Blade, mid-migration from Bootstrap/AdminLTE to Tailwind CSS v4 (as of this record, roughly 104 of ~268 admin views migrated; public site partially migrated).
- Vite build pipeline; CSS/JS changes require `npm run build` to take effect (no HMR dev flow currently relied on).
- Self-hosted fonts only — no external Google Fonts CDN, by deliberate project convention.
- Shared compiled `app.css` serves both the admin panel and the public marketing site; admin panel's `--color-brand` and component classes (`.btn`, `.form-control`, `.badge`) must never be altered — public-site design tokens are namespaced `--dp-*` / `.dp-*` to avoid collisions.
- FontAwesome 6 Free bundle only (no `fal`/`fad` Pro-only icon styles).

## Brand Commitments

- Name: Delivering Parcel.
- Palette: navy blue (`--dp-navy #0e2a6b`) + orange accent (`--dp-accent #ff5a1f`) + white/cream (`--dp-paper #fbfaf7`) as the established site-wide triad.
- Typeface: Nunito (self-hosted, "DP Sans" family alias), used across the public marketing site.

## Evidence on Hand

- Real Trustpilot profile linked from site footer/schema.
- Google Places review sync (via Google Places API) available once `GOOGLE_PLACES_API_KEY`/`GOOGLE_PLACES_ID` are configured; Trustpilot/SiteJabber have no accessible free API and are entered manually by an admin.
- No customer testimonials, avatars, or review content should ever be fabricated — this was an explicit past correction on this project.

## Product Principles

- Cost leadership over feature breadth — every pricing/fee claim must stay truthful to "no membership fees, tax-free shipping, 45-day free storage."
- Trust must be earned with real, verifiable signals (real reviews, real ratings) — never simulated social proof.
- The admin panel and public site share infrastructure but must never leak each other's styling; scoping discipline is a hard constraint, not a preference.
- Migration to Tailwind is incremental and ongoing — new work should migrate the page/component it touches rather than leave mixed Bootstrap/Tailwind in place, but shouldn't attempt wholesale migration unprompted.

## Accessibility & Inclusion

No formal compliance target (e.g. WCAG certification) has been set. Build with reasonable accessibility practice (semantic HTML, contrast, focus states) by default, without treating it as a certification requirement.
