# Request / Offer / Negotiation Flow (RQ-001)

_Traced from live code + schema on 2026-08-19. Frontend redesign must preserve these semantics._

## 1. Actors & artefacts

| Artefact | Table | Notes |
|---|---|---|
| Public quote request | `request_quotes` | Name/email/cargo/from/to/weight/dimensions/detail (all TEXT cols, no user link) |
| Order | `orders` | `order_id` (public ref), `user_id`, `order_status` (TEXT — values in `config/admin_orders.php`), `total` |
| Offer | `offerorders` | `order_id`, `offer_status` (INT), `product_total`, `total` (INT, cents observed), `rejections_note` |
| Offer lines | `offerorderproducts`, `offerorderservices` | Per-line actual prices set by ADMIN |
| Client selections | `orderproducts` (+ OrderService equivalents) | Created at order placement with appliedPrice = 0 |
| Negotiation chat | `order_chats` (`from`, `order_id`, `body`, `read`) | Per-order thread |
| Tracking | `orders.trackingid/trackinglink/companyname` | Set by admin |

## 2. End-to-end flow

```
Client creates order (parcel details + catalog service picks, no pricing yet)
        │  status: "Order Placed"
        ▼
Admin reviews → places OFFER (auto-fills catalog reference prices, sets REAL
serviceValue per line + base shipping)          offerorders + lines created
        ▼
Client views itemized offer (base + each service + total)
        ├── ACCEPT   → offer_status = 1
        ├── REJECT   → offer_status = 2 (+ rejections_note)
        └── COUNTER  → offer_status = 3 → admin adjusts → new offer state
        ▼
On accept → payment initiated against offer total (see payments engine)
        ▼
Payment confirmed → order moves to processing → shipped (tracking set)
        → delivered
```

## 3. State machines (authoritative server-side)

- **Offer:** `offer_status` INT — `0` new, `1` accepted, `2` rejected, `3` counter
  (map in `config/admin_quotes.php`). Only admin/backend mutates.
- **Order:** TEXT `order_status` governed by `config/admin_orders.php`
  (11 statuses + transitions; `OrderStatusService` enforces allowed moves).
- **Client-side rule (RQ-003):** browser may never submit `status`/`price` —
  stripped server-side; Home2 flows derive everything from auth + route models.

## 4. Two pricing systems (PR-001)

1. **Service catalog** (`services`: fixed/percent/per_kg/per_item/per_day) —
   REFERENCE prices shown on the order form; real prices are the admin's
   `offerorderservices` lines.
2. **Shipping rate matrix** (`rate_rules` + `RateCalculator`) — standalone
   estimation tool for admins; NOT auto-applied to offers.
   → Offer total remains admin-authoritative; do not auto-price during this pass.

## 5. Rules the frontend must never change

- Status/transition authority (backend only)
- Amount authority (accepted offer total; PM engine recalculates)
- Negotiation semantics (accept/reject/counter per existing endpoints)
- Order creation point (after accepted offer + payment, unchanged)

## 6. Frontend mapping (home2)

| Step | Route (existing/planned) |
|---|---|
| Request/quote form | legacy `/freequote` (reuse) |
| My quotes | `/home2/quotes` (live) |
| Offers view | admin `/admin/quotes/offers` (live); customer-side listing = RQ-004 (pending) |
| Payment | `/home2/pay/{order}/methods` (PM agent) |
| Tracking | `/home2/track-order` (live) |
