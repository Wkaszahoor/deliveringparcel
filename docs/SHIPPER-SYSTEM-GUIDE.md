# SHIPPER SYSTEM — Complete Guide (built 2026-09-02, deliveringparcel-New)

The 3-party marketplace layer on top of the existing 2-party flow.
Parties: **Customer** (existing client), **Admin** (coordinator), **Shipper**
(new role — a person in the destination country who buys/receives/forwards
parcels). Customers and shippers NEVER see each other's identity.

---

## 1. THE FULL FLOW (end to end)

```
CUSTOMER            ADMIN                         SHIPPER
────────            ─────                         ───────
1. Creates order ──► 2. "Generate Shipping
   (existing flow)      Request" 1-click
                        (masked brief auto-built)
                    3. Publish ──────────────────► 4. Sees open requests in
                                                     their countries (no PII)
                                                 5. Submits quote
                       ◄── 6. Reviews quotes
                    7. Selects shipper ─────────► 8. Assignment created,
                       (fee split set:               shipper notified,
                       shipper + platform)           request frozen
                                                 9. [Buy for Me] Purchases
                                                     item (2-day deadline)
                                                10. [Ship for Me] Confirms
                                                     package received
                                                11. Uploads proof photos ──► 12. Reviews & approves
                                                     (private storage)          (+ makes customer-
                                                                                   visible)
Customer submits ◄── 13. Proof approved →
delivery address          customer prompted
(strongly encrypted       for delivery address
at rest)              14. Reviews address, picks
                          forward level
                          (full / no-phone /
                          city+country) ────────► 15. Sees forwarded
                                                      address, ships
                                                16. Submits tracking ──► 17. Verifies & SHARES
                                                                              with customer
Customer sees ◄─────────────────────────────────────────────────────────────
tracking, package
photos
18. Confirms ──────► 19. Releases payment ─────► 20. 80% credited NOW,
    received/delivered    (80/20 split)              20% held 7 days
                                                 21. [Cron 03:10 daily]
                                                     hold auto-releases
22. Rates shipper ◄── moderation ──────────────► rating recalculated,
    (1-5★ + review        (approve / publish          level may progress
    + testimonial         as testimonial w/
    consent)              consent enforcement)
```

Two service types drive slightly different paths:
- **buy_for_me** — shipper purchases the item locally (purchase_receipt proof,
  2-working-day deadline, overdue alerts).
- **ship_for_me** — customer ships to the shipper's address; shipper confirms
  package_received, then repack/forward.

---

## 2. WHAT SHIPPERS CAN DO (every capability)

**Onboarding**
- Public registration at `/become-a-shipper` (guest-capable — creates account
  + client role + shipper_pending role in one step, or attaches to logged-in user)
- Choose service countries (admin-controlled afterwards), services offered,
  residence type, storage capability
- Upload KYC documents (government ID, selfie, address proof, social media,
  consent form) — at registration or later from Profile
- Wait for admin KYC approval → role swaps shipper_pending → shipper

**Marketplace**
- Browse open requests (filtered automatically: their countries, level ≥ required,
  not expired, not frozen, not already quoted)
- Submit ONE quote per request (amount + estimated days + note to admin)
- Withdraw a pending quote; view all own quotes with statuses
- Get notified of new requests in their countries (database/mail)

**Work management**
- See assignments with color-coded status timeline; staged reveal (details
  appear only when relevant)
- Buy-for-Me: mark purchased (deadline shown), upload purchase receipt
- Ship-for-Me: mark package received
- Upload proof photos per type: item_received, before_repack, after_repack,
  dispatch_receipt, damage_report, purchase_receipt (JPG/PNG/PDF ≤5MB)
- See the delivery address ONLY after admin forwards it — masked per
  trust level
- Submit dispatch tracking (carrier, number, URL, ship date, ETA)
- Chat with admin per assignment (admin sees full thread; identity stays hidden)
- Track wallet: available / held / total earned, full transaction ledger
- Request payouts (bank / PayPal / Wise) of the withdrawable balance
- Edit profile: residence type, storage flag, services offered
  (NOT countries, NOT shipper ID — admin-controlled)
- View own rating, ratings count, completed count, level progress

**Available on**: shipper web portal (`/shipper/*`, home2-styled) AND the
third mobile app (`deliveringparcel-expoNew/shipper`, same API).

---

## 3. ADMIN FEATURES (shipper network console)

- **Overview dashboard**: totals by status, pending-KYC counter, active
  assignments, pending proofs/addresses/tracking/payouts, overdue purchase
  alerts, top-rated and low-rating lists
- **All Shippers**: filters (username/email/name, country, level, status, KYC),
  profile page with tabs for KYC docs, assignments, ratings, wallet ledger;
  actions: approve KYC, reject, suspend, reinstate, promote level, wallet debit
- **Shipping Requests**: CRUD + filters; 1-click generate-from-order
  (auto brief: masked customer code, value range ±30% rounded, services
  checklist, 48h expiry); edit brief; draft → publish (notifies eligible
  shippers); freeze/unfreeze; cancel; admin↔shipper chat; quote comparison
  with one-click Select (sets fees)
- **Assignments**: queue with status filters; proof review (approve + make
  customer-visible / reject with note); address review + forward-level
  selection (forwarded text is written into the assignment chat);
  tracking verify + share; payment release; internal notes
- **Payout requests**: approve (with reference) / reject with reason —
  debits wallet + writes payout_paid transaction
- **Ratings moderation**: approve; publish as testimonial (blocked if
  customer consented "no")
- **Everywhere**: admin panel (web), admin mobile app (4 shipper screens +
  dashboard pending-actions counter)

---

## 4. BUSINESS RULES (enforced in code)

**Identity & privacy**
1. Shippers see only `customer_username` (CUS-XXXXXX) and masked value
   ranges (±30% of real value, rounded to $10) — never names/emails/addresses.
2. Customers never see shipper identity — proof photos are labeled
   "Package Photos from Our Partner".
3. Delivery address reaches the shipper only via admin "Forward" with a
   trust level: `full` (address+phone) / `address_only` / `city_country`.
4. Fee economics are invisible cross-side: shipper never sees platform fee
   or customer total; customer never sees shipper fee.
5. All KYC docs + proofs live on the **private disk**, streamed through
   authenticated routes only.

**Marketplace**
6. A request is visible to a shipper only if: status=open, not frozen, country
   matches their service_countries, required_level ≤ their level, not expired,
   and they have no pending/accepted quote on it.
7. One quote per shipper per request (unique index). Selecting a shipper
   auto-accepts their quote and bulk-rejects all others.
8. Assignment freezes the request (no more quotes) and increments the
   shipper's active-order counter; capacity check (`current < max`) gates
   new quotes.

**Money**
9. On release: shipper_fee is split **80% immediately credited / 20% held**;
   hold auto-releases 7 days after completion (`shipper:release-holds`,
   scheduled daily 03:10, idempotent via locked re-check + nulled timestamp).
10. Wallet mutations ONLY through model methods (creditWallet / holdAmount /
    releaseHold) which always write a ledger row with balance_after.
11. Payouts limited to `balance − pending`; payout approval debits wallet +
    writes payout_paid transaction; payout details stored encrypted.
12. Rating submission auto-recalculates the shipper's average + count
    (model boot) and may trigger level progression.

**Levels**
13. L1 Starter (3 concurrent) → L2 Verified (10 concurrent) at **5 completions
    + ≥4.0 rating + ≥3 ratings** → L3 Elite (999 concurrent) at **25
    completions + ≥4.5 rating + ≥15 ratings**. Admin can promote manually;
    rejection bans.

**Workflow integrity**
14. Buy-for-Me purchase deadline = 2 days; overdue flagged in admin
    overview/performance (isPurchaseOverdue()).
15. Customer's delivery-address form appears ONLY after admin approves
    proofs (order flag `shipper_proof_approved`) and is one-shot.
16. Tracking reaches the customer only when admin presses "Share"
    (`shared_with_customer`), which also flips the order flag.
17. Payment release only from `tracking_shared`/`delivered`; completion
    sets the LEGACY order's `order_status='completed'` so both systems
    stay in lockstep.
18. Ratings only after completion; one rating per assignment (unique);
    testimonial publication respects consent (no/anonymous/first_name/full_name).
19. KYC not approved → shippers can't quote (dashboard/banner explains);
    suspended/banned → force-logged-out of the portal.
20. Multi-role: `role:shipper,shipper_pending` accepted by middleware
    (extended, additive in the New copy only).

**Reference generation**
21. Shipping requests auto-number `DP-SR-0001…`; customers get `CUS-XXXXXX`
    (unambiguous charset, collision-retried); shippers get `SHP-CCC-NNNN`
    from their primary country.

---

## 5. DATABASE (12 tables + extensions)

```
users (extended)                        orders (extended)
├─ customer_username  CUS-A7K2P1        ├─ has_shipper_assignment
├─ shipper_username   SHP-UK-4821       ├─ shipper_proof_approved
└─ username_generated_at                ├─ delivery_address_submitted
                                        ├─ delivery_address_forwarded
roles: shipper / shipper_pending        └─ shipper_tracking_shared
(users_roles pivot)
                                        orderproducts (read by brief generator)

shipper_profiles ──┬─ 1:N ── shipper_kyc_documents
                   ├─ 1:N ── shipper_quotes
                   ├─ 1:N ── shipper_order_assignments ──┬─ 1:N shipper_proofs
                   ├─ 1:N ── shipper_wallet_transactions ├─ 1:1 shipper_delivery_addresses
                   ├─ 1:N ── shipper_ratings             ├─ 1:1 shipper_tracking_details
                   └─ 1:N ── shipper_payout_requests     └─ 1:N shipper_admin_chat
shipping_requests ── 1:N shipper_quotes, 1:1 shipper_order_assignments,
                     1:N shipper_admin_chat
```

**shipper_profiles** — user_id (unique), level 1-3, status(pending/active/
suspended/banned), service_countries JSON, services_offered JSON,
residence_type, has_storage, max_concurrent_orders, current_active_orders,
wallet_balance/wallet_pending/total_earned decimal(12,2), rating decimal(3,2),
total_ratings, total_completed, kyc_status(pending/approved/rejected),
payout_method, payout_details_encrypted, social_links JSON, reference_1/2 JSON,
verified_at, suspended_at, suspension_reason, soft-deletes.
IDX: (status,level), kyc_status.

**shipping_requests** — order_id, reference UNIQUE (DP-SR-XXXX),
customer_username, service_type(buy_for_me/ship_for_me), country_required,
brief_text LONGTEXT, product_details JSON, contact_email/phone (admin-filled),
address_snippet (city+country only), value_range_min/max, status(draft/open/
frozen/assigned/active/proof_pending/proof_approved/address_pending/dispatched/
completed/cancelled), is_frozen+frozen_at/by, assigned_shipper_profile_id,
assigned_at, required_level, expires_at, created_by, admin_internal_notes,
soft-deletes. IDX: (country_required,status,is_frozen), (order_id),
(status,required_level).

**shipper_quotes** — request_id FK(cascade), shipper_profile_id,
quoted_amount, estimated_days, notes (admin-only), status(pending/accepted/
rejected/withdrawn), admin_response, responded_at.
UNIQUE(request_id, shipper_profile_id). IDX (request_id,status).

**shipper_order_assignments** — order_id, request_id, shipper_profile_id,
**shipper_fee / platform_fee / total_charged** (the three-way money split),
status (17 values: assigned→accepted→purchasing→purchased→awaiting_package→
package_received→proof_uploaded→proof_approved→address_received→
address_forwarded→dispatched→tracking_added→tracking_shared→delivered→
completed, plus disputed/cancelled), purchase_deadline, purchased_at,
package_received_at, dispatched_at, completed_at, wallet_credit_amount,
wallet_hold_amount, hold_release_at, admin_notes.
IDX (order_id), (shipper_profile_id,status), (status).

**shipper_proofs** — assignment_id FK(cascade), shipper_profile_id,
proof_type(6 enum), file_path (PRIVATE), mime_type, file_size,
admin_approved, customer_visible, admin_approved_at/by, admin_notes,
shipper_notes. IDX (assignment_id, admin_approved, customer_visible).

**shipper_delivery_addresses** — order_id, assignment_id,
submitted_by_user_id, full address block (recipient, lines, city, state,
postal, country, phone, email, instructions), admin_reviewed + by/notes,
forwarded_to_shipper + at/by, **forward_level(full/address_only/city_country)**.

**shipper_tracking_details** — assignment_id FK(cascade), order_id, carrier,
tracking_number, tracking_url, ship_date, estimated_delivery,
admin_reviewed, shared_with_customer + shared_at/by, shipper_notes, admin_notes.

**shipper_wallet_transactions** — shipper_profile_id, type(credit/debit/hold/
hold_release/payout_request/payout_paid/refund/bonus), amount,
balance_after, order_id, assignment_id, reference, status(pending/completed/
failed/cancelled), note, processed_by. IDX (profile, type, created_at).

**shipper_payout_requests** — shipper_profile_id, amount, method(bank/paypal/
wise), payment_details (encrypted), status(pending/processing/paid/rejected/
on_hold), admin_notes, processed_by/at. IDX (profile,status).

**shipper_ratings** — assignment_id UNIQUE, order_id, shipper_profile_id,
rated_by_user_id, overall_rating 1-5, communication/speed/value/condition
ratings (optional), review_text, consent_testimonial(no/anonymous/
first_name_only/full_name), admin_approved, published_as_testimonial,
admin_approved_at.

**shipper_admin_chat** — assignment_id / request_id / order_id (nullable),
from_type(admin/shipper), from_id, message, attachment_path (private),
is_read + read_at. IDX (assignment_id,is_read), (request_id,is_read).

**shipper_kyc_documents** — shipper_profile_id FK(cascade), document_type(5
enum), file_path (private), original_filename, status, reviewed_by, notes, at.

**Key design choices**
- Money splits frozen on the assignment row at selection time (audit-safe;
  later wallet changes never rewrite history).
- Every trust/visibility decision is a boolean+timestamp pair (approved_at/by,
  forwarded_at/by, shared_at/by) — full "who did what when" audit without
  a separate audit table.
- Order-level boolean flags let the legacy order page and API cheaply gate
  UI without joining shipper tables unless needed.
- JSON columns for flexible shipper attributes; hard enums for anything that
  drives status logic.
