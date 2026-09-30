# Unified Mobile App — Shopper + Shipper in One App (Plan)

**Decision:** ONE mobile app (the client/Expo app) serves both workspaces. Users are managed by role — a shopper sees shopping features, a shipper sees the shipper workspace, and a user with **both roles sees both navigations in the same app**. No second app install.

## Backend contract (LIVE 2026-09-21)

`GET /api/mobile/v1/ui/controls` (Sanctum) now returns a `workspaces` block on top of the existing 4-level UI-control hierarchy:

```json
"workspaces": {
  "shopper": { "enabled": true, "status": "active" },
  "shipper": {
    "enabled": true,
    "status": "active",            // pending | active | suspended | banned
    "kyc_status": "approved",
    "level": 2,
    "service_countries": ["US", "DE"],
    "needs_kyc": false
  },
  "dual_role": true
}
```

- `shopper.enabled` is always true for any logged-in user.
- `shipper.enabled` = the user has a `shipper_profiles` row (pending counts — they can complete KYC in-app).
- `needs_kyc` tells the app to surface the KYC upload flow.
- The shipper API the app calls after enabling the workspace already exists (`/api/shipper/*` — marketplace, quotes, assignments, wallet, chat) and accepts the **same Sanctum token** — no re-login, no second app.

## App-side work (Expo client app — next build)

1. **Role-aware navigation:** read `workspaces` from `/ui/controls` after login. Bottom tabs: Shop (always) + **Shipper** tab when `shipper.enabled`. Both tabs visible when `dual_role`.
2. **Shipper screens:** port the 8 shipper screens (Dashboard, Requests/Marketplace, Quote, Assignments, Assignment detail, Wallet, Profile, KYC) from `deliveringparcel-expoNew/shipper/` into the client app as a "Shipper" stack, pointed at the same `/api/shipper/*` endpoints.
3. **KYC gate:** when `needs_kyc`, the Shipper tab shows the KYC upload screen instead of the marketplace.
4. **Country grid:** shipper profile/countries screens read enabled countries from the admin Countries Matrix (`allow_shipper = 1`).
5. **UI-controls tie-in:** existing screen toggles (Settings → Mobile App) keep working per workspace; add a "Shipper workspace" master toggle to the same panel (backend flag ready).

## What is already server-side

- Role awareness: `workspaces` block (this build).
- Shipper endpoints + proofs streaming: existing `/api/shipper/*`.
- Countries governance: matrix flags control every surface (form, shipper grid, marketplace).
- Admin approvals (KYC, countries changes) unchanged — same admin panel.

The Expo merge is a self-contained app-restructure task: copy the shipper screen stack, add the tab logic, and test dual-role logins (e.g. `saquu1@gmail.com` / `TestPass123!` locally holds both shopper + active shipper).
