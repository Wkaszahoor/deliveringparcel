# Input Security Policy — Defense in Depth

**Applies to:** DeliveringParcel Laravel 8.83 application (legacy site, `/home2` customer frontend, `/admin` back office).
**Owner:** Security workstream (SE plans). **Status:** enforced — see `docs/security-findings.md` for the audit that accompanies this policy and `docs/security-test-results.md` for regression evidence.

---

## 1. The five layers (every request passes through all of them)

```
Client input
   │
   ▼
[1] TRANSPORT + EDGE      HTTPS only; WAF/robots cannot be relied on; nothing here is trusted
   │
   ▼
[2] FRAMEWORK EDGE        Routing verbs (GET=read-only, POST/PUT/DELETE=mutate), web middleware
   │                       group = CSRF token verification on every state change + session
   │
   ▼
[3] VALIDATION            $request->validate([...]) with explicit rules; allow-list over deny-list;
   │                       honeypot + throttle on anonymous forms; UploadGuard on every file
   │
   ▼
[4] DOMAIN RULES          Server-derived values only (user_id from auth()->id(), status from
   │                       state machine, totals from offer rows); Eloquent with $fillable;
   │                       parameterized Query Builder; ownership scoping on every query
   │
   ▼
[5] OUTPUT                {{ }} escaping for all customer data; {!! !!} ONLY through
                           App\Support\HtmlSanitizer::clean(); @json(...) for Blade->JS data
```

**Rule zero:** no single layer is trusted to be the only one working. `htmlspecialchars` alone is not the strategy; CSRF tokens alone are not authn; validation alone is not authz.

## 2. Layer contracts

### Layer 2 — Verbs and CSRF
- Any route that mutates state MUST be POST/PUT/PATCH/DELETE inside the `web` middleware group (CSRF-verified). No destructive GET. (Exception-free rule; legacy violations are inventoried in findings.)
- Every mutating Blade form carries `@csrf`. Every mutating JS call sends the `X-CSRF-TOKEN` header (AdminLTE `DP.request` helper does).
- Anonymous write endpoints (e.g. `/home2/contact`) are additionally rate-limited (`throttle:5,1`) and honeypot-protected (`company_website` field: filled ⇒ bot ⇒ silently discarded).

### Layer 3 — Validation
- Validate at the controller boundary with an explicit rule array. Never read `$request->input(...)` for storage without a rule.
- `dp_per_page()` (app/Support/helpers.php) is the only accepted pagination-size source (allow-list 15/25/50/100).
- **Uploads:** every file passes `App\Support\UploadGuard::store($file, $module)`:
  - extension allow-list `jpg,jpeg,png,webp,gif` (+`pdf` only for compliance/KYC),
  - real-content MIME sniff via `finfo`, size caps 2048 KB (image) / 5120 KB (pdf),
  - `php,phtml,php3..8,phar,pht,...` blocked **always**, regardless of caller arguments,
  - 32-char random storage name, stored under the web docroot static `uploads/<module>/` tree,
  - SVG never accepted as an upload (scriptable content) — render-side sanitization does not apply to standalone SVG documents.

### Layer 4 — Authorization, state, queries
- Every customer-scoped query filters by `user_id = auth()->id()` (or the account email for pre-registration artifacts like quotes). Route/model binding alone is not authorization.
- The browser never submits `status`, `price`, `amount`, `user_id`, `role`, or payment identifiers that influence money or workflow. Those are derived server-side (see findings §RQ-003).
- Mass assignment: models declare `$fillable`; controllers pass validated arrays (`$request->validate()` output), never `$request->all()`.
- SQL: Eloquent / Query Builder with bindings only. `selectRaw` fragments must be constants; identifiers (table names) may only be interpolated after an allow-list check (see Tools DB viewer).

### Layer 5 — Output
- Customer content (names, emails, phones, addresses, message bodies, order refs) renders via `{{ }}` — always, including inside `@section(...)` values (`@yield` does **not** escape).
- Admin-authored rich text (blog body, service description) renders via `{!! HtmlSanitizer::clean($value) !!}` — allow-list tags/attributes, script/iframe/object/embed and on*/javascript: stripped.
- Blade→JS data uses `@json(...)` (hex-tag-safe `json_encode`), never `{{ json_encode() }}` and never `{!! json_encode() !!}`.
- URL-bearing attributes (`href`) that come from stored data must be scheme-checked (`^https?://`) before rendering a link.

## 3. Endpoint checklist (applied to every form/endpoint)

| Endpoint | Verb | Auth | CSRF | Validation | Rate limit | Ownership scoping | Output escaping |
|---|---|---|---|---|---|---|---|
| `GET /home2` (+services/blog/service-show/blog-show) | GET | public | n/a | slug via route binding, `firstOrFail` + status filter | — | published/available rows only | `{{ }}`; body via HtmlSanitizer |
| `POST /home2/contact` | POST | public | @csrf | name/email/detail rules | `throttle:5,1` + honeypot | n/a (insert) | `{{ }}` in admin Messages |
| `GET+POST /home2/track-order` | POST | public | @csrf | ref/email rules | — | **both** ref AND email must match | `{{ }}`; trackinglink scheme-guarded |
| `GET /home2/dashboard\|returns\|claims\|quotes\|payments\|notifications` | GET | auth | n/a | per-page via dp_per_page | — | `user_id`/email of `auth()->user()` | `{{ }}` |
| `POST /home2/notifications/read-all` | POST | auth | @csrf | none needed | — | operates on `auth()->user()` only | redirect |
| Admin CRUD (blog, shop, services, tools/testimonials, compliance…) | mixed | admin role | @csrf / X-CSRF-TOKEN | explicit rule arrays per module | — | admin scope | `{{ }}` / `nl2br(e(...))` / HtmlSanitizer for rich text |
| Admin uploads (blog cover, product images, testimonial avatar) | POST | admin | @csrf | mimes rule **+ UploadGuard** | — | n/a | static asset path |

**New-endpoint rule:** a merge that adds a form/endpoint must fill every column of this table before review. A gap in any column is a release blocker.

## 4. Regression enforcement

- `tests/Security/security_probes.py <base-url>` — black-box probe pack (XSS, SQLi, forged-state, cross-user, missing-CSRF→419, honeypot, upload rejection, throttle). CI/staging runs it after every deploy; any non-zero exit is a security regression.
- Grep guards (run locally / in CI, must return zero hits):
  - dangerous functions: `grep -rEn "\b(eval|exec|system|passthru|shell_exec|proc_open|popen)\s*\(" app/ routes/ config/ resources/`
  - raw Blade->JS: `grep -rn "json_encode" resources/views/` (only data-flash/@json allowed)
  - unescaped customer output: `grep -rn "{!!" resources/views/` — every remaining hit must be `HtmlSanitizer::clean(...)` or `nl2br(e(...))`.
- Blog/service rich text must never gain a new `{!! $model->field !!}` render; if a new rich-text field appears, wrap it in `HtmlSanitizer::clean()` in the same commit.

## 5. Incident/exception process

Findings, accepted risks and legacy-code exceptions live in `docs/security-findings.md` (with file:line and remediation status). Anything not fixable in-place (legacy controllers outside current workstream scope) is listed there with the recommended fix — it is **not** silently accepted.
