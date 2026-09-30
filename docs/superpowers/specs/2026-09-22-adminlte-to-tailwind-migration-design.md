# AdminLTE/Bootstrap → Tailwind CSS Migration — Design

**Date:** 2026-09-22
**Status:** Phase 0 complete (foundation shipped and verified); ready for Phase 1 batch-migration planning

## Problem

The admin panel, an older still-live legacy admin layout, and the client dashboard all run on AdminLTE (Bootstrap 4), a dated stack (`bootstrap@^4`, jQuery, Laravel Mix). The site's shipper portal and public pages (`home2`) already moved to hand-rolled custom CSS and are **not** part of this migration. The goal is to replace the AdminLTE/Bootstrap stack with a modern Tailwind CSS design across all three affected layout groups, unifying them into one shared design system.

## Scope

Three current layout groups, all sharing the AdminLTE/Bootstrap asset stack, are in scope:

| Layout | Pages | Status |
|---|---|---|
| `admin.layouts.app` | 165 | Main, current admin panel |
| `layouts.admin_dashbord_master` | 23 | Older admin layout, still live and routed (not dead code) |
| `layouts.client_dashbord_master` | 10 | Client-facing dashboard (My Orders, etc.) |

**Total: ~198 Blade views** across these three layouts, unified into **one shared Tailwind layout + component system** (admin and client dashboard reuse the same design tokens/components; the client dashboard keeps its own nav content but the same visual language).

**Out of scope:**
- Shipper portal (`resources/views/shipper/`) — already custom CSS, untouched.
- Public site (`resources/views/home2/`) — already custom CSS, untouched.
- Any new features or business-logic changes — this is a pure UI re-implementation of existing pages.
- The 3 dead/broken legacy routes identified in the earlier QA pass (`client-place_orders`, `client_chat` GET route, `admin-orderss`) — these will be **deleted**, not migrated, since they already 500 on missing views/methods and have working replacements elsewhere (`clients/orders.blade.php`, the `clients/inbox/` flow).

## Architecture

### Build tooling
- Replace **Laravel Mix** with **Vite** + `@tailwindcss/vite` (Tailwind v4, CSS-first `@theme` config — no `tailwind.config.js` needed for the basics).
- Remove from `package.json`: `bootstrap`, `popper.js`, `laravel-mix`, `sass`, `sass-loader`, `resolve-url-loader`.
- Keep: `jquery` (required by select2, summernote, daterangepicker, DataTables), `axios`, `lodash`.
- Add: `alpinejs`.
- `webpack.mix.js` is removed; add `vite.config.js` + a Vite entry point wired into the new shared layout via `@vite(...)`.

### Interactivity
Bootstrap's JS (modals, dropdowns, collapse) is replaced by **Alpine.js**. Modals/dropdowns/tabs/mobile-nav-toggle become small `x-data`/`x-show`/`@click` attributes directly in Blade markup — no separate JS components or build step needed. This affects the 8 pages currently using `data-toggle="modal"` plus any dropdown/collapse usage in the shared layout chrome (user menu, sub-sidebar collapse, etc).

### Design tokens
- **Primary accent: `#0d6efd`**, matching the public site's existing `--h2-primary` CSS variable — admin and public site stay visually consistent. Defined once as a Tailwind `@theme` color.
- Neutral grays / spacing / radius: Tailwind v4 defaults (slate palette), no custom scale needed.
- **Layout shell:** dark top nav bar (brand mark, top-level section links, user menu) **+ slim contextual sub-sidebar** per section — replacing the current single large AdminLTE left sidebar. Chosen over a faithful AdminLTE re-skin or a light-sidebar SaaS look after a visual review of 3 mockup directions.

### Component library
Built once in Phase 0, reused across every migrated page:
- Buttons, cards / stat-tiles
- Data tables — Tailwind-styled wrapper around **DataTables.js** (the plugin's JS logic is kept as-is; only its Bootstrap 4 CSS theme is replaced with a Tailwind-matching skin)
- Form inputs, selects (incl. a Tailwind-themed **select2**), textareas, checkboxes/radios, validation error states
- Badges, alerts (toastr / SweetAlert2 kept, re-themed to match, not replaced)
- Pagination, breadcrumbs
- Alpine-based modals, dropdowns, tabs
- Rich text: **summernote** kept, re-themed
- Date range picker: **daterangepicker** kept, re-themed
- Charts: **Chart.js** kept as-is (already framework-agnostic)

Third-party jQuery plugins (select2, summernote, daterangepicker, DataTables, Chart.js, toastr, SweetAlert2) are **kept** — only their bundled CSS themes are swapped for Tailwind-matching overrides. Their JS behavior does not change.

## Rollout Plan

This project is too large for a single implementation plan (~198 pages) and is decomposed into phases:

### Phase 0 — Foundation *(this design's implementation plan)*
- Vite + Tailwind v4 + Alpine.js setup
- Design tokens (`@theme` colors, spacing)
- New shared layout shell (top nav + slim sub-sidebar), built to serve all 3 layout groups
- Full component library (above)
- Proven end-to-end on a small representative page set:
  - Dashboard / home page
  - One simple CRUD flow (list + create + edit) — e.g. Address module
  - One page exercising DataTables + select2 + summernote together
  - One page with a modal (Alpine-based)

Phase 0 validates every pattern (layout, components, third-party plugin re-theming, Alpine interactivity) before mass rollout begins.

### Phase 1+ — Batch migrations *(separate specs/plans, written after Phase 0 ships)*
Remaining ~190 pages, grouped by module, roughly in this order:
1. Orders / Shipping-requests / Quotes
2. Users / Clients / Shippers (admin-side shipper management)
3. CMS / Blog / Site-sections / Menu / Navigation
4. Settings / lookup tables (Countries, Rates, Carriers, Weightunits, Warehouse)
5. Remaining tools / compliance / analytics / misc modules

Each batch is migrated fully before starting the next (for internal build sequencing — not a live rollout order, see below).

### Cutover — Big-bang
Old AdminLTE views and assets stay live and untouched throughout Phases 0–N. New Tailwind views are built in parallel (new Blade files or refactored copies) and only swap into the live routes once **all** batches are complete and verified. Nothing user-facing changes until the single final cutover, at which point:
- Old AdminLTE-based Blade files are deleted
- New Tailwind Blade files take over the existing route names/paths
- Old AdminLTE public assets (`public/dashbord/...` AdminLTE CSS/JS, Bootstrap plugin bundles no longer needed) are removed

## Verification

No existing automated test coverage exists for these pages (confirmed: only the Laravel scaffold's 2 default example tests exist in the whole app). Verification per batch is manual:
- Visual check of each migrated page against its AdminLTE original
- Functional check of forms, tables, and third-party widgets (select2, summernote, daterangepicker, DataTables, modals)
- Re-run of the route-smoke-test harness (built during the earlier QA pass) across all affected routes to confirm 200/expected-redirect status and catch missed views or broken links, before the batch is considered done

## Open items for Phase 1+ specs
Each batch migration will get its own short spec/plan when it starts (reusing this document's design system — no new design decisions expected, just applying the established patterns to each module's pages).

## Phase 0 Retrospective

Phase 0 shipped the Tailwind foundation (build config, base CSS/JS, `AdminNav`, the `layouts.tailwind.app` shell, shared components, the Tailwind pagination view, and tests) and migrated five pages (dashboard, address index/create/edit, contacts index, freequote index/delete-modal) as a proof of the pattern, leaving the rest of the admin area on the old AdminLTE/Bootstrap layouts.

The single biggest lesson from execution: nearly every task in this phase, once it got an independent code-quality review, turned up at least one real, confirmed regression or bug relative to either the plan's own draft spec or the actual original pre-migration page. Examples found and fixed during the phase:
- Dropped table columns/fields and dropped row actions in both the dashboard and address pages versus the original AdminLTE views.
- Dead/stale routes discovered while building out `AdminNav`.
- A select2 theme mismatch that silently defeated the Task 2 re-theme CSS (the CSS loaded and applied, but the wrong select2 theme class meant it never visually took effect).
- A DataTables script-ordering bug in the freequote page — the init script ran before jQuery/DataTables were available — combined with the DataTables plugin JS/CSS assets never being loaded at all in the new layout chain. This would have thrown a JS console error and left the table completely non-functional on first paint, despite the Blade markup matching the spec byte-for-byte.

The practical implication for Phase 1+: every batch-migration task MUST (a) read the actual current live file before rewriting it — never trust a spec draft as ground truth, since drafts were repeatedly found to be missing fields/actions/routes present in the real page — and (b) get a code-quality review pass that reasons about actual runtime/browser behavior (script load order, third-party plugin asset availability, JS execution timing relative to DOM/library readiness), not just a static Blade-vs-spec text diff. The worst bug found in this phase (the freequote DataTables breakage) was invisible to a pure text diff — the markup was correct — and would only have been caught by someone reasoning through what a browser actually does when it parses and executes the page.

### Final holistic review (post-Task-12) — two more instances of the same bug class

A whole-phase holistic review (looking at all 4 migrated pages together, after every individual task had already passed its own review) found two further "layout no longer provides what this page's vendor JS assumes" bugs — the same category as the freequote DataTables issue, just missed by the per-task reviews because each only looked at its own page in isolation:

1. **Contacts inbox never loaded any rows.** `admin/contacts/index.blade.php` calls `window.DP.infiniteScroll(...)` and `DP.toast.*`, but `window.DP` (`public/dashbord/js/dp-lazy.js`) and `toastr` are only loaded by the old AdminLTE layouts — the new `layouts.tailwind.app` never loads them, and the page didn't `@push` them either. The guard (`if (typeof DP === 'undefined') return;`) failed silently — no console error, just a permanently empty table stuck on "Loading messages…". Fixed by pushing `toastr.min.js`/`toastr.min.css` and `dashbord/js/dp-lazy.js` from the page itself, and fixing a call to `DP.toast.warning` (not a method `dp-lazy.js` actually defines) to `DP.toast.info`.
2. **Freequote's DataTables used the Bootstrap-4 integration build with no Bootstrap loaded.** The `-bs4` DataTables/Responsive files delegate pagination/length/search layout to Bootstrap classes (`.page-item`, `.page-link`, `.form-control`) that are never styled in a Bootstrap-free page — this is the exact same class of bug as the select2 `theme: 'bootstrap4'` issue caught earlier in the phase, just on a different plugin. Fixed by switching to the stock (non-bootstrap) DataTables/Responsive builds, whose plain `.paginate_button`/`.dataTables_filter` markup already matched what `vendor-overrides.css` was written against, and adding wrapper-layout + responsive-details CSS for that plain markup.

Both fixes were verified live (authenticated curl): jQuery loads before `dp-lazy.js`/`toastr` on `/admin/contacts`, `/admin/contacts/data` returns real rows, and `/freequote-inbox` now loads zero `-bs4` DataTables assets.

**Lesson for Phase 1+, sharpened further:** per-task review is not enough — every third-party JS global/plugin a migrated page's inline script touches (`window.DP`, `toastr`, `moment`, any `$.fn.*` plugin, or a Bootstrap-dependent vendor build) must be checked against what the *new* layout actually loads, not assumed from what the *old* layout provided. A recommended process addition: before considering any batch "done," grep each migrated page's inline scripts for every unqualified global/plugin call and confirm the corresponding `<script>`/`<link>` is present in that page's own `@push` or the shared layout — plus at least one authenticated live/browser check per page, since curl-visible HTTP 200s and correct markup do not prove the page's JS actually runs.
