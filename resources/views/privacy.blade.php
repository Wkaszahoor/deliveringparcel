@extends('layouts.fmaster')

@section('title', 'Privacy Policy | Delivering Parcel')

@section('meta_description', 'How Deliveringparcel Limited collects, uses, discloses and protects your personal data across our parcel forwarding, warehousing, purchasing assistance and reshipping services.')

@section('keywords', 'privacy policy, data protection, GDPR, deliveringparcel privacy')

@section('content')

@php
    $dpLegalToc = [
        ['id' => 'introduction', 'label' => '1. Introduction & data controller'],
        ['id' => 'data-we-collect', 'label' => '2. The personal data we collect'],
        ['id' => 'how-we-use', 'label' => '3. How we use your data'],
        ['id' => 'how-we-share', 'label' => '4. How we share your data'],
        ['id' => 'international-transfers', 'label' => '5. International data transfers'],
        ['id' => 'data-security', 'label' => '6. Data security measures'],
        ['id' => 'data-retention', 'label' => '7. Data retention & storage'],
        ['id' => 'cookies', 'label' => '8. Cookies & tracking'],
        ['id' => 'your-rights', 'label' => '9. Your statutory rights (UK GDPR)'],
        ['id' => 'third-party-links', 'label' => '10. Third-party links'],
        ['id' => 'changes', 'label' => '11. Changes to this policy'],
        ['id' => 'complaints', 'label' => '12. Complaints & supervisory authority'],
    ];
@endphp

<div class="dp-home">

    {{-- ======= Page header ======= --}}
    <section class="relative pt-32 pb-14 lg:pt-40 lg:pb-16" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="dp-legal-header-card dp-hero-in dp-hero-in-1 relative overflow-hidden bg-white rounded-2xl px-6 py-8 sm:px-10 sm:py-10">
                <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl mb-4">Privacy Policy</h1>
                <div class="flex flex-wrap gap-2">
                    <span class="dp-legal-meta-chip"><i class="fa-solid fa-calendar-check text-xs" aria-hidden="true"></i> Effective Date: September 28, 2020</span>
                    <span class="dp-legal-meta-chip"><i class="fa-solid fa-arrows-rotate text-xs" aria-hidden="true"></i> Last Updated: September 28, 2020</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ======= Body ======= --}}
    <section class="relative py-12 lg:py-16" style="background:#fff">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-10 lg:gap-14 items-start">

                {{-- On-page navigation --}}
                <nav class="hidden lg:block sticky top-28 self-start" aria-label="Sections">
                    <p class="text-xs font-bold uppercase tracking-wide mb-3 px-3" style="color:var(--dp-ink-soft)">On this page</p>
                    <div class="flex flex-col gap-0.5">
                        @foreach ($dpLegalToc as $item)
                            <a href="#{{ $item['id'] }}" class="dp-legal-toc-link">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </nav>

                {{-- Article --}}
                <article class="dp-legal-body max-w-3xl">

                    <h2 id="introduction">1. Introduction &amp; Data Controller Information</h2>
                    <p>This Privacy Policy explains how Deliveringparcel Limited ("DeliveringParcel", "we", "us", or "our") collects, uses, discloses, and protects your personal data when you visit our website at <a href="{{ url('/') }}">www.deliveringparcel.com</a>, use our platform, or engage our parcel forwarding, warehousing, purchasing assistance, handling, and reshipping services (collectively, the "Services").</p>
                    <p>Deliveringparcel Limited is a company incorporated under the laws of England and Wales with its registered office at C/O Cna &amp; Co Accountants, 810 Stockport Road, Manchester, England, M12 4QL.</p>
                    <p>For the purposes of the UK General Data Protection Regulation (UK GDPR) and the UK Data Protection Act 2018, Deliveringparcel Limited acts as the <strong>Data Controller</strong> in respect of personal data collected through our platform and operations.</p>
                    <p>If you have any questions about this Privacy Policy or our data protection practices, please contact our Data Protection Officer / Compliance Lead at:</p>
                    <ul>
                        <li><strong>Company Name:</strong> Deliveringparcel Limited</li>
                        <li><strong>Registered Address:</strong> C/O Cna &amp; Co Accountants, 810 Stockport Road, Manchester, England, M12 4QL, United Kingdom</li>
                        <li><strong>Email:</strong> <a href="mailto:admin@deliveringparcel.com">admin@deliveringparcel.com</a></li>
                        <li><strong>Website:</strong> <a href="{{ url('/') }}">www.deliveringparcel.com</a></li>
                    </ul>

                    <h2 id="data-we-collect">2. The Personal Data We Collect</h2>
                    <p>We collect personal data directly from you when you register an account, create or fulfill an order request, use our warehousing or forwarding services, or communicate with us. We may also collect data automatically when you interact with our platform.</p>

                    <h3>A. Data Provided Directly by You</h3>
                    <ul>
                        <li><strong>Identity &amp; Contact Data:</strong> Full name, title, residential street address, country of residence, email address, telephone number, and government-issued identification documents (e.g., passport or driving license copies) required for identity verification, anti-fraud screening, and customs clearance.</li>
                        <li><strong>Shopper Order Data:</strong> Product URLs, product descriptions, quantities, sizes, colors, estimated weights, values, merchant invoices, and seller details provided when creating an order request or booking purchase assistance.</li>
                        <li><strong>Recipient &amp; Delivery Data:</strong> Final destination recipient name, delivery street address, postal code, city, country, and contact phone number for international parcel forwarding.</li>
                        <li><strong>Shipper Registration Data:</strong> For registered local forwarders ("Shippers"), local residential/warehouse addresses, proof of address, national identification, bank account details, and local shipping carrier capabilities.</li>
                        <li><strong>Financial &amp; Payment Data:</strong> Payment method preferences, bank transfer details for "Buy For Me" purchase assistance orders, transaction records, billing addresses, and VAT or local sales tax registration numbers (for business accounts). Debit and credit card payments processed for address-only forwarding are tokenized and processed directly by our secure third-party payment merchant; card numbers are never stored on our servers.</li>
                        <li><strong>Communications Data:</strong> Messages sent between Shoppers and Shippers via the dashboard messaging system, customer support tickets, emails, and dispute resolution records.</li>
                    </ul>

                    <h3>B. Data Collected Automatically</h3>
                    <ul>
                        <li><strong>Technical &amp; Usage Data:</strong> Internet Protocol (IP) address, browser type and version, time zone setting, browser plug-in types, operating system, platform, device identifiers, referral URLs, page interaction details, and session logs.</li>
                        <li><strong>Cookies &amp; Tracking Data:</strong> Information collected through cookies, web beacons, and analytical tags (see Section 8 below).</li>
                    </ul>

                    <h2 id="how-we-use">3. How We Use Your Personal Data &amp; Legal Bases for Processing</h2>
                    <p>We process your personal data in strict compliance with UK GDPR requirements. The table below outlines the purposes for which we process your data and the lawful basis relied upon:</p>
                    <div class="dp-legal-table-wrap">
                        <table class="dp-legal-table">
                            <thead>
                                <tr><th>Purpose / Activity</th><th>Categories of Personal Data</th><th>Lawful Basis for Processing</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Account Registration &amp; Identity Setup:</strong> Creating your account and allocating unique residential forwarding addresses.</td><td>Identity, Contact, Account Credentials</td><td>Performance of a Contract with you.</td></tr>
                                <tr><td><strong>Service Execution:</strong> Facilitating parcel forwarding, warehousing, consolidation, handling, and reshipping.</td><td>Identity, Contact, Order Data, Recipient Data</td><td>Performance of a Contract with you.</td></tr>
                                <tr><td><strong>Purchase Assistance ("Buy For Me"):</strong> Processing bank payments and placing merchant orders on your behalf.</td><td>Identity, Contact, Order Data, Financial Data</td><td>Performance of a Contract with you.</td></tr>
                                <tr><td><strong>Payment Processing &amp; Escrow Management:</strong> Managing card transactions, holding funds, and releasing payouts upon delivery completion.</td><td>Financial Data, Transaction Data, Order Data</td><td>Performance of a Contract and Legitimate Interests (preventing fraud and securing transactions).</td></tr>
                                <tr><td><strong>Customs Clearance &amp; Regulatory Compliance:</strong> Generating air waybills, export/import declarations, tariff filings, and tax reporting.</td><td>Identity, Order Data, Recipient Data, Financial Data</td><td>Legal Obligation (customs laws, tax rules, export compliance).</td></tr>
                                <tr><td><strong>Dispute Resolution &amp; Platform Moderation:</strong> Mediating disputes between Shoppers and Shippers regarding non-delivery, damaged goods, or specification errors.</td><td>Order Data, Communications Data, Delivery Evidence</td><td>Legitimate Interests (operating a safe, moderated marketplace).</td></tr>
                                <tr><td><strong>Fraud Prevention &amp; Security:</strong> Verifying user identities, screening against prohibited items, and preventing illegal activity.</td><td>Identity, Contact, Technical Data, Order Data</td><td>Legal Obligation and Legitimate Interests (protecting our platform and users).</td></tr>
                                <tr><td><strong>Service Communications:</strong> Sending transaction notifications, tracking updates, and system alerts via dashboard/email.</td><td>Contact Data, Communications Data</td><td>Performance of a Contract.</td></tr>
                                <tr><td><strong>Marketing &amp; Promotional Updates:</strong> Sending news about new geographic address locations, promotions, or features (where permitted).</td><td>Contact Data</td><td>Consent or Legitimate Interests (soft opt-in for existing customers).</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 id="how-we-share">4. How We Share &amp; Disclose Your Personal Data</h2>
                    <p>We do not sell, rent, or trade your personal data to third parties for marketing purposes. We share your personal data only as strictly necessary to operate our parcel forwarding marketplace, fulfill services, comply with legal obligations, or protect our platform:</p>
                    <ol>
                        <li><strong>Between Registered Shoppers and Shippers:</strong> When a Shopper accepts a Shipper's quote, necessary order details, recipient names, items, and destination address details are shared with the assigned Shipper to enable order placement, physical package handling, and international forwarding.</li>
                        <li><strong>Subcontracted Couriers &amp; Logistics Partners:</strong> We share delivery address details, contact phone numbers, weight/dimension specs, and commercial invoices with established global couriers (e.g., DHL, FedEx, UPS, local postal services) to execute transport, tracking, and final delivery.</li>
                        <li><strong>Third-Party Payment Processors:</strong> Payment transactions are processed securely through nominated payment merchants and banking partners. Financial data is transmitted directly to payment gateways using encrypted secure socket layer (SSL) protocols.</li>
                        <li><strong>Customs, Regulatory &amp; Law Enforcement Authorities:</strong> We may disclose identity documents, invoice details, and parcel contents to border control agencies, customs officials, tax bodies, and police authorities where legally required, or to comply with mandatory import/export declarations.</li>
                        <li><strong>Professional Advisers &amp; Technology Service Providers:</strong> IT infrastructure hosts, cloud storage providers, software developers, legal advisers, accountants (including Cna &amp; Co Accountants), and auditors under strict duties of confidentiality.</li>
                    </ol>

                    <h2 id="international-transfers">5. International Data Transfers</h2>
                    <p>DeliveringParcel operates an international parcel forwarding network connecting users across multiple countries (such as Canada, the UK, the US, EU nations, Australia, and the UAE). Consequently, your personal data may be transferred to, stored, or processed outside the United Kingdom and the European Economic Area (EEA).</p>
                    <p>Whenever we transfer your personal data outside the UK/EEA, we ensure a similar degree of protection is afforded to it by implementing at least one of the following safeguards:</p>
                    <ul>
                        <li><strong>Adequacy Decisions:</strong> Transferring data to countries officially deemed by the UK Government / European Commission to provide an adequate level of data protection.</li>
                        <li><strong>Standard Contractual Clauses (SCCs) / UK International Data Transfer Agreement (IDTA):</strong> Implementing approved standard contractual clauses with third-party service providers, independent Shippers, or overseas partners to protect data processing.</li>
                        <li><strong>Contractual Necessity:</strong> Transfers directly necessary to execute your international shipment or order request in accordance with our Terms of Business.</li>
                    </ul>

                    <h2 id="data-security">6. Data Security Measures</h2>
                    <p>We implement robust technical and organizational security measures to protect your personal data against unauthorized access, loss, alteration, destruction, or disclosure:</p>
                    <ul>
                        <li><strong>Encryption:</strong> Data transmitted between your browser and our platform is encrypted using Secure Sockets Layer (SSL/TLS) technology.</li>
                        <li><strong>Escrow &amp; Payment Security:</strong> Credit and debit card data is handled exclusively by compliant payment gateways using tokenization. Bank details for purchase assistance are handled via secure banking systems.</li>
                        <li><strong>Access Control:</strong> Access to personal data is restricted to authorized employees, registered Shippers, and contractors who require access to fulfill specific operational duties.</li>
                        <li><strong>Physical &amp; Warehouse Security:</strong> Parcels stored at assigned residential or warehouse locations are held securely during the 30-day free storage and handling periods.</li>
                    </ul>

                    <h2 id="data-retention">7. Data Retention &amp; Storage Policy</h2>
                    <p>We retain your personal data only for as long as necessary to fulfil the purposes for which it was collected, including satisfying legal, accounting, tax, customs, or reporting obligations.</p>
                    <ul>
                        <li><strong>Active Account Data:</strong> Retained for the duration of your active account membership on deliveringparcel.com.</li>
                        <li><strong>Transaction, Order &amp; Customs Records:</strong> Retained for 6 years following transaction completion to satisfy UK tax laws, customs auditing, and statutory financial recordkeeping obligations.</li>
                        <li><strong>Dispute &amp; Moderation Records:</strong> Retained for 3 years following dispute resolution to safeguard against repeated fraud or legal claims.</li>
                        <li><strong>Abandoned Package Records:</strong> Retained for 1 year following package disposal or settlement.</li>
                        <li><strong>Account Closure Requests:</strong> If you request account closure, your account will be deactivated, and personal data removed or anonymized within 30 days, except for records required to be kept by law.</li>
                    </ul>

                    <h2 id="cookies">8. Cookies &amp; Tracking Technologies</h2>
                    <p>Our website uses cookies and similar tracking technologies to distinguish you from other users, optimize platform functionality, and analyze web traffic.</p>
                    <ul>
                        <li><strong>Essential Cookies:</strong> Necessary for account login, dashboard navigation, and secure order requests.</li>
                        <li><strong>Analytical/Performance Cookies:</strong> Allow us to recognize and count visitors and analyze website traffic patterns to improve user experience.</li>
                        <li><strong>Functionality Cookies:</strong> Used to remember your preferences (such as selected language, currency, or origin country addresses).</li>
                    </ul>
                    <p>You can configure your browser to block or alert you about cookies. However, disabling essential cookies may prevent parts of our platform and forwarding services from functioning properly.</p>

                    <h2 id="your-rights">9. Your Statutory Data Protection Rights (UK GDPR)</h2>
                    <p>Under UK data protection law, you possess the following rights regarding your personal data:</p>
                    <ul>
                        <li><strong>Right of Access:</strong> Request a copy of the personal data we hold about you (a "Data Subject Access Request").</li>
                        <li><strong>Right to Rectification:</strong> Request correction of inaccurate or incomplete personal data held on your profile or dashboard.</li>
                        <li><strong>Right to Erasure ("Right to be Forgotten"):</strong> Request deletion of your personal data where there is no legal reason for us to continue processing it (subject to statutory customs/tax retention rules).</li>
                        <li><strong>Right to Restrict Processing:</strong> Request suspension of processing your personal data in certain scenarios (e.g., while verifying accuracy or resolving a dispute).</li>
                        <li><strong>Right to Data Portability:</strong> Request the transfer of your structured personal data to you or a third party in a standard machine-readable format.</li>
                        <li><strong>Right to Object:</strong> Object to processing based on legitimate interests or direct marketing.</li>
                        <li><strong>Right to Withdraw Consent:</strong> Withdraw consent at any time where processing relies on your consent.</li>
                    </ul>
                    <div class="dp-legal-callout">
                        <span class="dp-legal-callout-icon"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></span>
                        <span>To exercise any of these rights, please submit a written request to <a href="mailto:sales@deliveringparcel.com">sales@deliveringparcel.com</a>. We respond to all legitimate requests within one calendar month.</span>
                    </div>

                    <h2 id="third-party-links">10. Third-Party Links &amp; Retailer Websites</h2>
                    <p>Our platform and blog contain links to third-party Canadian and international retail stores, online shopping centers, merchant checkouts, and courier tracking tools. Clicking on those links or visiting external websites may allow third parties to collect or share data about you. We do not control these third-party websites and are not responsible for their privacy statements, return policies, or data handling practices. We encourage you to read the privacy notice of every retailer website you visit.</p>

                    <h2 id="changes">11. Changes to This Privacy Policy</h2>
                    <p>We may update this Privacy Policy periodically to reflect changes in our legal obligations, operational practices, or service enhancements. Any updates will be published on this page with a revised "Effective Date" at the top. We encourage you to review this Privacy Policy regularly to remain informed about how we safeguard your data.</p>

                    <h2 id="complaints">12. Complaints &amp; Supervisory Authority</h2>
                    <p>If you have concerns about our handling of your personal data, we ask that you contact us first at <a href="mailto:sales@deliveringparcel.com">sales@deliveringparcel.com</a> so we can resolve the issue directly.</p>
                    <p>You also have the right to lodge a formal complaint at any time with the UK data protection supervisory authority:</p>
                    <ul>
                        <li><strong>Information Commissioner's Office (ICO)</strong></li>
                        <li><strong>Website:</strong> <a href="https://ico.org.uk" target="_blank" rel="noopener noreferrer">ico.org.uk</a></li>
                        <li><strong>Helpline:</strong> +44 303 123 1113</li>
                        <li><strong>Address:</strong> Wycliffe House, Water Lane, Wilmslow, Cheshire, SK9 5AF, United Kingdom</li>
                    </ul>

                </article>
            </div>
        </div>
    </section>

</div>{{-- /.dp-home --}}

@endsection
