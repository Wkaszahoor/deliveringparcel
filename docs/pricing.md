# Pricing Systems (PR-001)

## System 1 — Service catalog (value-added services)

```
ServiceCategory → Service → OrderService / OfferOrderService
```

| Price type | Behaviour | Example |
|---|---|---|
| `fixed` | Flat fee | $5 per photo request |
| `percent` | % of order value | 5% insurance |
| `per_kg` | Per kilogram | $2/kg storage |
| `per_item` | Per item | $1/item handling |
| `per_day` | Daily rate | Warehouse storage |

- Catalog prices are **reference only** — shown on the order form.
- Client picks services at order creation → `OrderService` rows with
  appliedPrice = 0 (no real pricing yet).
- **Admin sets the actual price** per line when placing the offer
  (`offerorderservices`). Offer total = Σ real line prices + base shipping.

## System 2 — Shipping rate matrix (carrier freight)

```
RateZone ←→ RateRule (carrier-service × zone-pair × weight bracket → price)
           + RateSurcharge + RateInsurance + volumetric weight
```

- Powers `/admin/rates/calculator` (itemized quotes; verified working).
- **Standalone estimation/reference tool for admins.**
- NOT called automatically during order creation or offer placement.

## Division of authority

| Action | Where | Effect |
|---|---|---|
| Set catalog reference prices | Admin → Services | Guide shown on order form |
| Set rate matrix | Admin → Rate Engine | Carrier cost reference |
| Place offer with real prices | Admin → Offer | **Actual customer pays** |
| Negotiate/counter | Admin ↔ Client offer flow | Adjusts final lines |
| Final payable | Payments engine (PM) | Recomputed from accepted offer |

**This pass changes no pricing semantics.**
