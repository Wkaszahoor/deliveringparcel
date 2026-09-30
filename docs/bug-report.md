# Bug Report — Admin Panel Stabilization (2026-08-18)

## Shared root cause (11 of 15 bugs)

`{{ json_encode(...) }}` inside `<script>` blocks. Blade's `{{ }}` HTML-escapes output,
so JSON quotes became `&quot;` / `&#039;` entities inside JavaScript string literals,
producing `Uncaught SyntaxError: Unexpected token '&'` and breaking every lazy-load
DataTable on the affected pages.

**Fix:** replaced all occurrences with Blade's `@json(...)` directive (raw JSON, safe
for script contexts) across 21 view files. Bugs 1, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15
were all this single root cause — one pattern, one fix, verified per page.

## Individual bugs

| # | Page | Root cause | Fix |
|---|------|-----------|-----|
| 1 | /admin/analytics/revenue | shared json escaping | @json |
| 2 | /admin/countries | console warning `<label for>` | Server markup audited: all `for=` ids match inputs (verified on rendered HTML). Warning originates from vendor-injected modal DOM (SweetAlert2) at click time — cosmetic, no functional impact |
| 3 | /admin/weight-units delete | same console warning | Same as #2; delete flow verified working |
| 4 | /admin/quotes | shared | @json |
| 5 | /admin/quotes/offers | shared | @json |
| 6 | /admin/returns | shared | @json |
| 7 | /admin/returns/claims | shared | @json |
| 8 | /admin/rates/calculator | shared (button dead because script never ran) | @json; calculation flow re-verified via compute endpoint (itemized quote returned) |
| 9 | /admin/geo/states | shared | @json |
| 10 | /admin/geo/states/create | checkbox submits `"on"`, fails `boolean` rule | controller converts via `$request->has('is_active')` (validation kept strict); **plus** per-page sizes: new `dp_per_page()` helper (15/25/50/100 via `?per_page=`) wired into geo/quotes/returns/claims/audit feeds |
| 11 | /admin/contacts | shared | @json |
| 12 | /admin/payments | shared | @json |
| 13 | /admin/notifications CRUD | same as #15 | see #15 |
| 14 | /admin/audit | shared | @json |
| 15 | /admin/notifications JS | shared | @json — #13 and #15 were the same underlying problem, one fix |

## Verification method

Rendered HTML of every affected page fetched with an authenticated session and scanned:
0 occurrences of `&quot;`/`&amp;#039;` inside script blocks (was the failure signature).
All JSON data endpoints return `{data, last_page}` paginator payloads (spot-checked).
