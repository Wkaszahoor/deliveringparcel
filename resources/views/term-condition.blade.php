@extends('layouts.fmaster')

@section('title', 'Terms of Business | Delivering Parcel')

@section('meta_description', 'The legally binding agreement between you, registered Shippers, and Deliveringparcel Limited governing our parcel forwarding and purchase assistance marketplace.')

@section('keywords', 'terms of business, terms and conditions, deliveringparcel terms')

@section('content')

@php
    $dpLegalToc = [
        ['id' => 'introduction', 'label' => '1. Introduction & company information'],
        ['id' => 'marketplace-overview', 'label' => '2. Marketplace overview & our role'],
        ['id' => 'shopper-responsibilities', 'label' => '3. Shopper responsibilities'],
        ['id' => 'shipper-responsibilities', 'label' => '4. Shipper responsibilities'],
        ['id' => 'forwarding-storage', 'label' => '5. Forwarding, storage & abandoned goods'],
        ['id' => 'customs-taxes', 'label' => '6. Customs, taxes & import duties'],
        ['id' => 'dispute-resolution', 'label' => '7. Dispute resolution & moderation'],
        ['id' => 'prohibited-items', 'label' => '8. Prohibited & restricted items'],
        ['id' => 'summary-matrix', 'label' => 'Summary matrix of core responsibilities'],
    ];

    $dpProhibitedItems = [
        'Aerosols, Butane Lighters, and Flammable Liquids/Solids',
        'Ammunition, Firearms, Replicas, Weapons, and Weapon Parts',
        'Counterfeit Goods, Currency, and Postage Stamps',
        'Prescription, Over-the-counter, or Illegal Drugs and Narcotics',
        'E-cigarettes and Battery-powered Vaping Devices',
        'Official Government Documents (Passports, Driving Licenses, Certificates)',
        'Items Purchased on Finance without written export approval',
        'Explosives, Fireworks, Matches, and Radioactive Materials',
        'Precious Metals, Bullion, Currency, and Loose Precious Stones',
        'Pornography, Obscene Material, and Hazardous/Clinical Waste',
    ];
@endphp

<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mb-4">Terms of Business</h1>
                <p class="text-sm sm:text-base max-w-2xl" style="color:var(--dp-ink-soft)">The legally binding agreement between you, registered Shippers, and Deliveringparcel Limited.</p>
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

                    <h2 id="introduction">1. Introduction &amp; Company Information</h2>
                    <ol>
                        <li><strong>Operating Entity:</strong> <a href="{{ url('/') }}">www.deliveringparcel.com</a> is operated by Deliveringparcel Limited ("DeliveringParcel", "we", "us", or "our"). We are registered in England and Wales, with our registered office at C/O Cna &amp; Co Accountants, 810 Stockport Road, Manchester, England, M12 4QL.</li>
                        <li><strong>Legal Agreement:</strong> Please read these Terms of Business carefully before registration. By registering as a Member and using our Services, you agree to be bound by these Terms of Business, which form a legally binding agreement between you, registered Shippers, and us.</li>
                        <li><strong>Amendments:</strong> We reserve the right to update or change these Terms of Business from time to time without prior notice. Any changes will not affect ongoing orders or Goods received prior to the publication of the updated Terms on our Site.</li>
                        <li><strong>Registration:</strong> To access the Services, you must become a registered shopper or registered shipper ("Registered Member") by creating an account on the Site.</li>
                        <li><strong>Residential Forwarding Address:</strong> Upon registration, a Shopper is provided with a unique residential address in their desired origin country (e.g., Canada, UK, US). It is the Shopper's sole responsibility to ensure the exact allocated address is provided to the merchant or store at checkout.</li>
                    </ol>

                    <h2 id="marketplace-overview">2. Marketplace Overview &amp; Role of DeliveringParcel</h2>
                    <ol>
                        <li><strong>Marketplace Model:</strong> DeliveringParcel operates an online peer-to-peer forwarding and personal shopping marketplace connecting international buyers ("Shoppers") with verified local forwarders/agents ("Shippers").</li>
                        <li>
                            <strong>Shopper Request &amp; Shipper Quotes:</strong>
                            <ul>
                                <li>Shoppers submit order requests on deliveringparcel.com detailing the desired items from local stores or websites.</li>
                                <li>Registered Shippers submit binding quotes based on local carrier rates and their desired service fee.</li>
                            </ul>
                        </li>
                        <li>
                            <strong>Payment Methods &amp; Financial Structure:</strong>
                            <ul>
                                <li><strong>Purchase Assistance / "Buy For Me":</strong> For requests where the Shipper purchases the item on the Shopper's behalf, all payments must be made via Bank Transfer. Orders will not be placed until bank funds clear.</li>
                                <li><strong>Address-Only / Forwarding Services:</strong> Where the Shopper purchases the item directly from the merchant and only requires a residential shipping address, payments may be made securely via Credit/Debit Card through our secure payment gateway.</li>
                            </ul>
                        </li>
                        <li><strong>Escrow Guarantee &amp; Release of Funds:</strong> DeliveringParcel holds all payments in secure escrow. DeliveringParcel will not release funds to the Shipper until the complete transaction is successfully executed and the item is delivered to the Shopper.</li>
                        <li><strong>Platform Role as Neutral Moderator:</strong> DeliveringParcel acts strictly as a technology platform facilitator, payment escrow manager, and neutral moderator. DeliveringParcel does not directly manufacture, store, inspect, or ship packages, except through its network of registered Shippers and subcontracted couriers.</li>
                    </ol>

                    <h2 id="shopper-responsibilities">3. Shopper Responsibilities &amp; Obligations</h2>
                    <ol>
                        <li>
                            <strong>Accuracy of Request Specifications:</strong> When creating an order request on deliveringparcel.com, the Shopper must submit exact item details, including:
                            <ul>
                                <li>Direct website URL or exact store description</li>
                                <li>Exact size, color, model, style variant, and quantity</li>
                                <li>Weight and dimensions (where specified by the store)</li>
                            </ul>
                        </li>
                        <li>
                            <strong>Item Authenticity &amp; Authentication Disclaimer:</strong>
                            <ul>
                                <li><strong>Shopper Responsibility:</strong> It is the Shopper's sole duty to verify the reputation, authenticity, genuineness, and legitimacy of the online store, retailer, or item prior to placing a request.</li>
                                <li><strong>No Liability for Dissatisfaction:</strong> DeliveringParcel is not responsible if the item received does not meet the Shopper's subjective expectations, provided the Shipper purchased or forwarded the exact URL/specification provided by the Shopper.</li>
                            </ul>
                        </li>
                        <li><strong>Dashboard Notification Requirement:</strong> Upon placing an order directly with a store using a Shipper's assigned residential address, the Shopper must immediately record the order details and tracking information on their deliveringparcel.com dashboard to notify the Shipper.</li>
                        <li>
                            <strong>Legality &amp; Prohibited Items:</strong>
                            <ul>
                                <li>Shoppers are strictly responsible for ensuring that all requested items are legal to purchase in the origin country, legal to export, and legal to import into the destination country.</li>
                                <li>If an item is prohibited, restricted, or seized by customs or law enforcement, the Shopper bears full legal and financial responsibility. Neither DeliveringParcel nor the Shipper shall be liable or responsible for seized, destroyed, or non-delivered prohibited items.</li>
                            </ul>
                        </li>
                    </ol>

                    <h2 id="shipper-responsibilities">4. Shipper Responsibilities &amp; Obligations</h2>
                    <ol>
                        <li><strong>Specification Verification:</strong> The Shipper must carefully check and tally the received physical item against the description, size, color, quantity, and URL specified by the Shopper on the dashboard before completing shipment.</li>
                        <li><strong>Purchase Assistance ("Buy For Me"):</strong> When providing Purchase Assistance, the Shipper acts on behalf of the Shopper and must order the exact item, size, color, and specification detailed in the Shopper's request.</li>
                        <li>
                            <strong>Customs Declarations &amp; Destination Accuracy:</strong>
                            <ul>
                                <li>Shippers are responsible for filling out accurate customs declarations and commercial invoices.</li>
                                <li><strong>Shipper Liability:</strong> The Shipper assumes full responsibility if a package is lost or misdelivered due to shipping to an incorrect address not specified in the dashboard, or due to fraudulent/non-compliant customs declarations made by the Shipper.</li>
                            </ul>
                        </li>
                        <li><strong>Payout Eligibility:</strong> Shippers acknowledge that payout for services rendered will only be released by DeliveringParcel after successful delivery confirmation to the Shopper's final address.</li>
                    </ol>

                    <h2 id="forwarding-storage">5. Forwarding, Storage &amp; Abandoned Goods</h2>
                    <ol>
                        <li><strong>Storage Allowance:</strong> Goods received at the assigned residential address are held for up to 30 days free of charge while awaiting shipping instructions and payment. Daily storage fees apply after 30 days.</li>
                        <li><strong>Abandoned Goods:</strong> Goods stored for more than 90 days (or 60 days without communication or payment of accrued storage fees) will be deemed Abandoned Goods. DeliveringParcel and the Shipper are authorized to dispose of or sell Abandoned Goods to recover unpaid fees.</li>
                        <li><strong>Inspection Rights:</strong> DeliveringParcel, Shippers, and subcontracted couriers reserve the right to open and inspect any package to verify contents, ensure safety, or perform mandatory customs compliance checks.</li>
                    </ol>

                    <h2 id="customs-taxes">6. Customs, Taxes &amp; Import Duties</h2>
                    <ol>
                        <li><strong>DDU Shipping Terms:</strong> All international shipments are dispatched under Delivered Duty Unpaid (DDU) terms unless explicitly agreed otherwise in writing.</li>
                        <li><strong>Import Duties &amp; VAT:</strong> The Shopper is designated as both exporter and importer of record. All import duties, local VAT/GST, handling fees, and customs clearance charges assessed by destination country authorities are the sole responsibility of the Shopper.</li>
                    </ol>

                    <h2 id="dispute-resolution">7. Dispute Resolution &amp; Platform Moderation</h2>
                    <ol>
                        <li><strong>Dispute Reporting:</strong> Any dispute regarding non-delivery, damaged goods, or description discrepancies must be submitted in writing via email or the dashboard within 7 days of scheduled delivery (or within 24 hours for visible package damage).</li>
                        <li><strong>Role as Neutral Moderator:</strong> In the event of a conflict between Shopper and Shipper, DeliveringParcel will act as a neutral moderator. Both parties agree to provide evidence (receipts, tracking numbers, dashboard logs, photos) and accept DeliveringParcel's binding administrative determination regarding escrow releases or refunds.</li>
                    </ol>

                    <h2 id="prohibited-items">8. Prohibited &amp; Restricted Items List</h2>
                    <p>Unless explicitly approved in writing prior to purchase, the following items are strictly prohibited from being handled or shipped through the platform:</p>
                    <ul>
                        @foreach ($dpProhibitedItems as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>

                    <h2 id="summary-matrix">Summary Matrix of Core Responsibilities</h2>
                    <div class="dp-legal-table-wrap">
                        <table class="dp-legal-table">
                            <thead>
                                <tr><th>Feature / Responsibility</th><th>Shopper Duty</th><th>Shipper Duty</th><th>DeliveringParcel Role</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Payment Methods</strong></td><td>Bank Transfer for Purchase Assistance; Card for Forwarding/Address-only</td><td>N/A</td><td>Escrow holder; releases funds only upon final delivery completion</td></tr>
                                <tr><td><strong>Item Specifications</strong></td><td>Provide exact URL, size, color, quantity &amp; weight</td><td>Verify received package matches request details</td><td>Platform host &amp; record keeper</td></tr>
                                <tr><td><strong>Item Authenticity</strong></td><td>Inspect merchant credibility prior to ordering</td><td>N/A (purchases requested link)</td><td>Holds no liability for item authenticity</td></tr>
                                <tr><td><strong>Purchase Assistance</strong></td><td>Advance full item price &amp; service fees via Bank Transfer</td><td>Order exact item specified by Shopper</td><td>Secure escrow payment holding</td></tr>
                                <tr><td><strong>Customs &amp; Compliance</strong></td><td>Pay destination import duties/VAT; verify legal status</td><td>Complete accurate customs declarations</td><td>Enforce compliance policies</td></tr>
                                <tr><td><strong>Disputes &amp; Payouts</strong></td><td>Fully liable for prohibited item customs seizures</td><td>Liable for misdelivery or misdeclaration errors</td><td>Neutral administrative moderator; withholding/releasing funds</td></tr>
                            </tbody>
                        </table>
                    </div>

                </article>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}

@endsection
