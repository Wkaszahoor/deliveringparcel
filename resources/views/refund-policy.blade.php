@extends('layouts.fmaster')

@section('title', 'Refund Policy | Delivering Parcel')

@section('meta_description', 'How refunds work for address-only forwarding and Buy For Me purchase assistance orders, including fee deductions, damaged-goods handling, and the full policy matrix.')

@section('keywords', 'refund policy, deliveringparcel refunds, buy for me refund, forwarding refund')

@section('content')

@php
    $dpLegalToc = [
        ['id' => 'general-principle', 'label' => '1. Non-refundable processing & bank fees'],
        ['id' => 'forwarding-refunds', 'label' => '2. Address-only / forwarding refunds'],
        ['id' => 'buy-for-me-refunds', 'label' => '3. "Buy For Me" refunds'],
        ['id' => 'damaged-goods', 'label' => '4. Defective or damaged goods'],
        ['id' => 'timeline', 'label' => '5. Timeline & communication'],
        ['id' => 'policy-matrix', 'label' => 'Policy matrix summary'],
    ];
@endphp

<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mb-4">Refund Policy</h1>
                <span class="dp-legal-meta-chip"><i class="fa-solid fa-calendar-check text-xs" aria-hidden="true"></i> Effective Date: September 28, 2020</span>
            </div>
        </div>
    </section>

    {{-- ======= Body ======= --}}
    <section class="relative py-12 lg:py-16" style="background:#fff">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-10 lg:gap-14 items-start">

                <nav class="hidden lg:block sticky top-28 self-start" aria-label="Sections">
                    <p class="text-xs font-bold uppercase tracking-wide mb-3 px-3" style="color:var(--dp-ink-soft)">On this page</p>
                    <div class="flex flex-col gap-0.5">
                        @foreach ($dpLegalToc as $item)
                            <a href="#{{ $item['id'] }}" class="dp-legal-toc-link">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </nav>

                <article class="dp-legal-body max-w-3xl">

                    <p>At DeliveringParcel, we strive to make international package forwarding, warehousing, handling, and personal shopping seamless. Because international parcel forwarding involves multiple transaction steps, currency conversions, third-party merchants, and international shipping carriers, all refund requests are strictly governed by the following policy guidelines.</p>

                    <h2 id="general-principle">1. Non-Refundable Processing &amp; Bank Fees (General Principle)</h2>
                    <ul>
                        <li><strong>Gateway &amp; Financial Charges:</strong> Any third-party transaction costs, payment gateway processing fees (e.g., credit/debit card processing fees), or bank international transfer charges incurred during payment collection or refund processing are strictly non-refundable.</li>
                        <li><strong>Unseen Administrative Expenses:</strong> For certain order cancellations or store stockouts, fixed processing administrative charges apply to cover operational handling and platform administrative overhead.</li>
                    </ul>

                    <h2 id="forwarding-refunds">2. Address-Only / Forwarding Service Refunds</h2>
                    <p><em>Where the Shopper purchases the item directly from the online store/website and uses the assigned DeliveringParcel residential address.</em></p>
                    <div class="dp-legal-table-wrap">
                        <table class="dp-legal-table">
                            <thead>
                                <tr><th>Trigger / Scenario</th><th>Refund Status &amp; Deductions</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Merchant Non-Delivery or Stockout:</strong> The store/website fails to ship the package, cancels the order, or runs out of stock prior to delivery to the assigned address.</td>
                                    <td>Full refund of forwarding &amp; shipping fees, minus: (1) a <strong>$5 USD administrative fee</strong> (to cover unseen handling costs), and (2) third-party payment gateway or bank transaction charges.</td>
                                </tr>
                                <tr>
                                    <td><strong>Merchant Delivery Confirmed:</strong> The package arrives safely at the assigned residential forwarding address.</td>
                                    <td>Shipping and service fees become non-refundable once international dispatch instructions are executed by the Shipper.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 id="buy-for-me-refunds">3. "Buy For Me" / Purchase Assistance Refunds</h2>
                    <p><em>Where the Shopper requests DeliveringParcel and a registered Shipper to purchase the item on their behalf.</em></p>

                    <h3>Scenario A: Item Unpurchased (Before Funds Transfer to Shipper)</h3>
                    <ul>
                        <li>If the order request is canceled <strong>before</strong> the Shipper places the order with the merchant and <strong>before</strong> funds are transferred to the Shipper: the Shopper receives a full refund of the item cost and service fees, minus non-refundable bank or payment gateway processing charges.</li>
                    </ul>

                    <h3>Scenario B: Item Sold Out / Canceled (After Funds Transfer to Shipper)</h3>
                    <ul>
                        <li>If DeliveringParcel has already transferred funds to the Shipper to execute the purchase, but the store cancels the order or the item becomes sold out before the Shipper can buy it, the Shopper receives a refund of the item cost and service fees, subject to the deduction of bank transfer fees and payment gateway charges incurred during the transfer and return processes.</li>
                        <li><strong>Estimated Deduction:</strong> Banking and gateway fees typically range from <strong>$20 USD or 4.5% of the total order value</strong> (whichever amount is lower).</li>
                    </ul>

                    <h3>Scenario C: Item Arrives at Shipper's Address &amp; Shopper Rejects Item (Based on Photos)</h3>
                    <ul>
                        <li>If the item is delivered by the store to the Shipper, inspection photos are uploaded to the dashboard, and the Shopper is dissatisfied with the item received and requests a refund: a <strong>partial refund</strong> is issued, consisting of the international shipping cost, subject to a mandatory <strong>$15 USD service fee</strong> deduction.</li>
                        <li><strong>Store Return Handling:</strong> If the Shopper agrees to the fee deductions and terms, DeliveringParcel will instruct the Shipper to return the item to the merchant. The Shopper may be required to pay for return domestic shipping labels and any additional service fees charged by the Shipper for return processing.</li>
                    </ul>

                    <h2 id="damaged-goods">4. Defective or Damaged Goods Post-Dispatch (Moderator Interventions)</h2>
                    <p>Parcel forwarding involves a multi-step logistics chain. To ensure fairness across all parties:</p>
                    <ul>
                        <li><strong>Inspection vs. Transit Defects:</strong> If an item was inspected and verified as undamaged/correct in photos provided by the Shipper prior to international dispatch, but arrives at the final destination defective or damaged, liability will be assessed against the Shipper/Carrier.</li>
                        <li>
                            <strong>Moderator Resolution:</strong> DeliveringParcel acts as an impartial moderator in all post-delivery claims. If the Shipper failed to properly check the item, sent a defective item despite visible flaws, or packaged it negligently, DeliveringParcel as moderator will hold the Shipper accountable and ensure the Shipper compensates the Shopper for the defect or error.
                        </li>
                    </ul>

                    <h2 id="timeline">5. Timeline Notice &amp; Communication Guidelines</h2>
                    <ul>
                        <li><strong>Process Complexity:</strong> Package forwarding and international personal shopping are inherently time-sensitive, complex operations involving multiple time zones, bank clearance delays, merchant return windows, and international customs authorities.</li>
                        <li><strong>Shopper Advisory:</strong> Shoppers are strongly advised to factor in processing lead times, monitor their dashboard updates daily, and maintain clear, prompt communication with the DeliveringParcel team and assigned Shipper.</li>
                    </ul>

                    <h2 id="policy-matrix">Policy Matrix Summary</h2>
                    <div class="dp-legal-table-wrap">
                        <table class="dp-legal-table">
                            <thead>
                                <tr><th>Refund Request Reason</th><th>Applicable Service</th><th>Fee Deductions / Deductible Amounts</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Shipper unable to purchase item (pre-transfer)</td><td>Buy For Me</td><td>Actual bank / payment gateway charges only</td></tr>
                                <tr><td>Store stockout / canceled by merchant (post-transfer)</td><td>Buy For Me</td><td>Bank/gateway charges (lower of $20 USD or 4.5%)</td></tr>
                                <tr><td>Shopper rejects item upon viewing arrival photos</td><td>Buy For Me</td><td>$15 USD service fee + domestic return costs + bank charges; international shipping fee refunded</td></tr>
                                <tr><td>Store stockout / store fails to ship</td><td>Forwarding Only</td><td>$5 USD admin fee + bank/gateway charges; full shipping/forwarding refund</td></tr>
                                <tr><td>Item defective post-dispatch (verified good at photo stage)</td><td>Forwarding / Buy For Me</td><td>Moderator enforcement; Shipper required to compensate Shopper</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="dp-legal-callout">
                        <span class="dp-legal-callout-icon"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></span>
                        <span>Questions about a specific refund? Contact us at <a href="mailto:admin@deliveringparcel.com">admin@deliveringparcel.com</a> or through your dashboard's message system, and reference your order number.</span>
                    </div>

                </article>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}

@endsection
