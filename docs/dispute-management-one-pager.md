# DeliveringParcel — Payment & Dispute Management: One-Pager
*2026-08-26 · compares the LIVE payment system with the agreed target (multi-rail payments + unified dispute/evidence system)*

## 1. What exists today (live, E2E-proven)

| Capability | Status | Where |
|---|---|---|
| Central payment ledger | ✅ | `payments` table — reference, order_id, attempt, method, gateway, amount, status, status_set_by (customer/admin/webhook/system), metadata JSON |
| State machine | ✅ | pending → method_selected → awaiting_payment → processing / awaiting_verification → paid / failed / cancelled; paid → refunded / partially_refunded (illegal transitions throw) |
| Rails | ✅ | Stripe Checkout (webhook + abandoned-session resume/recovery), Bank Transfer (receipt upload → **admin verification — receipt ≠ paid**), COD, Wallet |
| Admin controls | ✅ | Method rules per service context, min/max amounts, forced method per order/product/quote, legacy↔advanced mode |
| Refunds | ✅ | Admin-only, partial/full, back to original rail or to wallet, gateway + bookkeeping entries |
| Audit trail | ✅ | AuditLogger lifecycle snapshots + webhook event log (PM-013/PM-017) |
| Order↔payment separation | ✅ | Order/offer flow reads payment status; sync hooks (offer_status=1 on paid) are compatibility, not truth |
| **Dispute handling** | ❌ **NONE** | No `charge.dispute.*` webhook handler, no disputes table, no evidence pack, no PayPal, no response-deadline tracking |

## 2. The gap, in one sentence

We can *take* payments on three rails but cannot *defend* them: a Stripe chargeback today arrives by email, is fought manually in the Stripe dashboard, and leaves no record in the app — while our Purchase-Assistant evidence trail (request → offer → acceptance → payment → supplier invoice → tracking → delivery) is exactly the evidence a dispute needs, already in our DB.

## 3. Target architecture (extends, does not replace, the current engine)

```
Order → Offer → Accept → PaymentService (existing) ──► rails: Stripe | PayPal | Bank
                                   │
                     payment_disputes (NEW)  ◄── webhooks (charge.dispute.*, PayPal cases) + manual entry
                                   │
                     payment_evidence (NEW)   ◄── auto-built from orders/offerorders/payments/chat/
                                   │              tracking/products + admin uploads
                                   ▼
                     Admin Dispute Console: status, deadline, evidence pack, outcome
```

**New tables (all additive — zero changes to existing tables):**
- `payment_disputes`: payment_id, order_id, provider, provider_dispute_id, type/reason, amount, status (`opened→needs_response→evidence_submitted→under_review→won/lost/closed`), opened_at, **due_at**, submitted_at, closed_at, outcome
- `payment_evidence`: dispute_id, type (offer / customer_acceptance / invoice / payment_receipt / purchase_invoice / tracking / delivery_proof / customer_message / terms_acceptance / other), file_path or inline payload, uploaded_by

**Key decisions already aligned with current code:**
- Dispute is a **separate lifecycle** on top of the payment — order history stays untouched (delivery facts must not be overwritten by `disputed`).
- Stripe webhook controller gains `charge.dispute.created / closed` (signature-verified, idempotent — same pattern as existing handlers); PayPal mirrors later when that rail lands.
- Bank "disputes" = internal claim records (bank recall/complaint), same console, same evidence pack.
- **Evidence pack generator** = one button per dispute that assembles: order details, accepted offer snapshot, acceptance timestamp, payment row, purchase products/invoices, tracking links, delivery confirmation (`order_status=received`), chat transcript, T&C version accepted at signup — exported as a printable page/PDF for Stripe's evidence form.
- Permissions: dispute view/respond separated from refund (finance roles).

## 4. Delivery phases

| Phase | Scope | Effort |
|---|---|---|
| **P1 — Record & track** | migration (2 tables), dispute statuses, Stripe `charge.dispute.*` webhook → auto-open dispute + deadline, admin list/detail, manual open for bank claims | ~1 session |
| **P2 — Evidence engine** | evidence-pack generator pulling existing data + file uploads, printable export, timeline view | ~1 session |
| **P3 — Response loop** | deadline reminders via email system, outcome logging, payment status link (`disputed` flag), metrics (dispute rate vs 0.75% healthy ceiling) | ~1 session |
| **P4 — PayPal rail** | PayPal gateway + case sync into the same console | later |

## 5. Guardrails (what we will NOT do)

- No rewrite of the working Stripe/Checkout resume-refund flow — dispute layer is read-only over payments.
- No customer-facing "open dispute" button yet — disputes arrive via provider webhooks or staff entry.
- Terms & Conditions acceptance evidence (signup + order flows) feeds the evidence pack — T&C redesign is tracked separately and should land **before** relying on it in evidence.
