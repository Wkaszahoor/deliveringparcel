# /home2 Frontend Integration (2026-08-18)

## Principles

- The legacy frontend is untouched and fully functional — both coexist.
- No duplicated business logic: /home2 reuses the same models, services,
  classifier, notification system and payment/returns/claims read-models as admin.
- No new JS frameworks: vanilla CSS (`/css/home2.css`) + minimal inline JS.
  Responsive via CSS grid + mobile card-collapse tables (`data-label` pattern).

## Routes (name prefix `home2.`)

| Route | Access | Backing |
|---|---|---|
| GET /home2 | public | hero_slides (active, ordered, per-slide UI options), services, published blog |
| GET /home2/services[?q&category] | public | Service + ServiceCategory, paginated |
| GET /home2/services/{slug} | public | Service detail + related |
| GET /home2/blog[?q] | public | published Blog posts, paginated |
| GET /home2/blog/{slug} | public | post + SEO meta (meta_title/description/keywords) |
| GET+POST /home2/contact | public | Contactus + ContactClassifier (same storage & auto-classification as admin inbox) |
| GET+POST /home2/track-order | public | orders lookup — requires order ref **and** email on file; returns status-level info only |
| GET /home2/dashboard | auth | counters |
| GET /home2/returns · /claims · /quotes · /payments · /notifications | auth | scoped strictly to `auth()->id()` / account email |

## Files

- `app/Http/Controllers/Home2/Home2Controller.php`
- `resources/views/home2/` (layout, 11 pages, pagination partial)
- `css/home2.css` (served from docroot)
- routes appended in `routes/web.php`

## Security

CSRF on all POST forms, server-side validation, `{{ }}` escaping everywhere,
auth-scoped queries only, no admin/internal data exposed (tracking lookup requires
email match; payments view exposes only reference/status/offer total/date).
