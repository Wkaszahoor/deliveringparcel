# deliveringparcel-New — Laravel 8 → 12 Upgrade (2026-09-19)

Legacy `deliveringparcel/` untouched (still L8). This app (`deliveringparcel-New/`)
now runs **Laravel 12.69** on PHP 8.2. Pre-upgrade backup:
`C:\laragon\www\dplive\dplive-New-preL12-backup-2026-09-19.zip` (8,072 files).

## What changed

### composer.json
- `laravel/framework ^12.0`, `php ^8.2`, `laravel/sanctum ^4`, `laravel/tinker ^3`,
  `laravel/ui ^4.6`, `spatie/laravel-cookie-consent ^3`, `intervention/image-laravel ^1`
- REMOVED: `fideloper/proxy`, `fruitcake/laravel-cors` (built into L11+), `facade/ignition`,
  `laravel/sail`; PHPUnit 11 + collision 8 in dev.
- KEPT `stripe/stripe-php ^7.78` ON PURPOSE — payment behavior stability; upgrade separately.

### Skeleton (L11+/12 style)
- `bootstrap/app.php` — NEW: routing (web/api/commands), middleware aliases
  (auth/guest/role/shipper.profile/api.*), `api` group + throttle, schedule
  (queue:work every minute, shipper:release-holds 03:10), exceptions dontFlash.
- `bootstrap/providers.php` — the 6 custom providers.
- DELETED: `app/Http/Kernel.php`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php`,
  stock middleware (EncryptCookies/VerifyCsrfToken/TrimStrings/PreventRequestsDuringMaintenance/
  TrustHosts/TrustProxies), `server.php`.
- `app/Providers/RouteServiceProvider.php` — retired to a constants-only shell (`HOME`).
  Rate limiter `api` moved to `AppServiceProvider::boot()`.
- `artisan` + `public/index.php` — L12 front controllers.
- `config/app.php` — providers/aliases now `defaultProviders()`/`defaultAliases()`.

### Code fixes
- `RoleMiddleware` — typed L10+ signature.
- Intervention Image v2→v3 at 3 call sites (Adminorders/Adminorderss/User controllers):
  `ImageManager->read()->fit()` with Gd/Imagick detection and raw-move fallback.
- `home.blade.php` + 1 more view — `"@context"` (schema.org JSON-LD) escaped to `"@@context"`:
  **Laravel 12 added a real `@context` Blade directive**, which previously swallowed the JSON key.
- `public/blog` static dir renamed `blog-old-static` — it shadowed the `/blog` route (403/404).

## Verified
- 109 migrations all `Ran`, zero pending — no DB changes required.
- 804-line route list compiles; API group + dp:/cms:/shipper: commands present.
- Schedule listed (both cron jobs). Tests pass. Tinker reads (users/cms_posts) OK.
- Apache vhost for this app: **http://127.0.0.1:8083** (port vhost, no hosts entry needed).
  `/`, `/blog` (after un-shadowing), `/login`, `/services`, `/home2/*`, `/dashboard` (302 auth) all good.

## Notes / follow-ups
- `/home3` junction cannot route subpages — a subpath prefix needs route-time prefixing this
  app never had. Use the port vhost (8083) or add a `dplive3.test` hosts entry when convenient.
- prod would need PHP ≥ 8.2 (cPanel MultiPHP) — this app is NOT on prod yet.
- Next: run the full manual payment/shipper E2E suite, then consider stripe-php bump.
