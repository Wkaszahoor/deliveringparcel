@extends('layouts.fmaster')

@section('title', 'Become a Shipper — Earn with DeliveringParcel')

@section('content')
<section class="container" style="padding:34px 0 60px;">
    <div style="max-width:920px;margin:0 auto;">

        <div class="text-center mb-4">
            <h1 style="font-weight:800;">Become a DeliveringParcel Shipper</h1>
            <p class="text-muted" style="font-size:17px;max-width:720px;margin:0 auto;">
                Earn money by buying and shipping parcels in your country — with escrow-protected payouts
                modeled on the standards you know from Stripe Connect. Customers never see your identity,
                every payment is secured before you spend a cent, and money lands in your wallet the moment a job completes.
            </p>
            <div class="mt-3">
                <a href="{{ route('shipper.register.form') }}" class="btn btn-primary btn-lg mr-2">
                    <i class="fas fa-rocket mr-1"></i> Apply Now
                </a>
                <a href="{{ route('shippers.directory') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-users mr-1"></i> Meet Our Shippers
                </a>
                <a href="{{ route('shipper.guide') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-book-open mr-1"></i> Read the Shipper Guide
                </a>
            </div>
        </div>

        {{-- ================= PERKS ================= --}}
        <h2 class="mb-3" style="font-weight:700;">Perks &amp; Benefits</h2>
        <div class="row">
            @foreach ([
                ['fa-shield-alt', 'Escrow-protected jobs', 'The customer pays before you buy anything. Funds are held by the platform — you never spend out of your own pocket.'],
                ['fa-wallet', '80% payout, released instantly', 'You keep 80% of every job fee. Your share lands in your wallet the moment the job completes — no invoicing, no chasing.'],
                ['fa-lock', '7-day safety hold', 'The remaining 20% is held for 7 days to cover disputes, then released automatically. The same rolling-reserve model used by Stripe Connect and marketplaces worldwide.'],
                ['fa-user-secret', 'Full identity privacy', 'Customers only see your anonymized shipper username, level and rating. Your address is shared at the privacy level you choose.'],
                ['fa-chart-line', 'Level 1→3 career path', 'Higher levels unlock higher-value requests and priority placement in the marketplace.'],
                ['fa-star', 'Ratings & testimonials', 'Build a public reputation. Approved reviews can be published as testimonials with your consent.'],
            ] as $p)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <i class="fas {{ $p[0] }} fa-2x mb-2" style="color:#0d6efd;"></i>
                            <h5 class="mb-1">{{ $p[1] }}</h5>
                            <p class="text-muted mb-0" style="font-size:14px;">{{ $p[2] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ================= PAYOUT MODEL ================= --}}
        <h2 class="mt-4 mb-3" style="font-weight:700;">How Payouts Work</h2>
        <div class="card shadow-sm border-0 mb-2">
            <div class="card-body">
                <table class="table mb-0" style="font-size:15px;">
                    <tbody>
                        <tr><td style="width:60px;"><strong>1</strong></td><td><strong>Customer pays up front.</strong> The full job amount (product cost + your fee) is charged and held by DeliveringParcel in escrow.</td></tr>
                        <tr><td><strong>2</strong></td><td><strong>You complete the job</strong> — purchase (Buy-for-Me) or receive/repack/ship (Ship-for-Me), with photo proof and tracking uploaded at every step.</td></tr>
                        <tr><td><strong>3</strong></td><td><strong>Job completes → 80% to your wallet instantly.</strong> Withdraw to bank transfer or PayPal any time from your wallet page.</td></tr>
                        <tr><td><strong>4</strong></td><td><strong>20% rolling reserve for 7 days.</strong> Covers the dispute window, exactly like Stripe Connect payouts and Amazon/eBay seller reserves. Released automatically — no action needed.</td></tr>
                    </tbody>
                </table>
                <p class="text-muted mb-0" style="font-size:13px;">
                    <i class="fas fa-info-circle mr-1"></i> Payouts are processed to the bank/PayPal account saved in your profile.
                    Keep your KYC verified so payouts are never delayed — this meets international anti-fraud (AML) standards.
                </p>
            </div>
        </div>

        {{-- ================= DUTIES ================= --}}
        <h2 class="mt-4 mb-3" style="font-weight:700;">Your Duties as a Shipper</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card h-100 shadow-sm border-0"><div class="card-body">
                    <h5><i class="fas fa-shopping-basket mr-2 text-primary"></i>Buy-for-Me jobs</h5>
                    <ul class="text-muted mb-0" style="font-size:14px;">
                        <li>Purchase the customer's item locally within the <strong>2-working-day deadline</strong></li>
                        <li>Upload the purchase receipt as proof before shipping</li>
                        <li>Repack securely and dispatch with a trackable carrier</li>
                    </ul>
                </div></div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100 shadow-sm border-0"><div class="card-body">
                    <h5><i class="fas fa-box-open mr-2 text-primary"></i>Ship-for-Me jobs</h5>
                    <ul class="text-muted mb-0" style="font-size:14px;">
                        <li>Confirm the package arrived at your address (photo proof)</li>
                        <li>Repack if needed and ship to the forwarded address provided</li>
                        <li>Upload dispatch receipt + tracking number — the customer sees it once admin verifies</li>
                    </ul>
                </div></div>
            </div>
            <div class="col-md-12">
                <div class="card shadow-sm border-0"><div class="card-body text-muted" style="font-size:14px;">
                    <strong>Every job:</strong> photo proof at each step (private until admin approves), respond to admin messages promptly,
                    ship within your quoted delivery window, and keep your KYC documents valid. Repeated missed deadlines lower your marketplace standing.
                </div></div>
            </div>
        </div>

        {{-- ================= REQUIREMENTS / KYC ================= --}}
        <h2 class="mt-4 mb-3" style="font-weight:700;">Requirements &amp; KYC</h2>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <p class="text-muted">To meet international marketplace and AML standards, every shipper completes verification before their first payout:</p>
                <table class="table mb-3">
                    <thead class="thead-light"><tr><th>Requirement</th><th>Details</th><th>When</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Government ID</strong></td><td>Passport or national ID, all corners visible</td><td>At application</td></tr>
                        <tr><td><strong>Selfie photo</strong></td><td>Clear face photo matching your ID</td><td>At application</td></tr>
                        <tr><td><strong>Address proof</strong></td><td>Utility bill or bank statement (last 3 months)</td><td>At application</td></tr>
                        <tr><td><strong>Payout account</strong></td><td>Bank account or PayPal in your legal name</td><td>Before first payout</td></tr>
                    </tbody>
                </table>
                <p class="text-muted mb-0" style="font-size:14px;">
                    You can apply first and upload documents right after — your marketplace access activates once admin approves your KYC
                    (usually within 24–48 hours). Track everything from your shipper dashboard.
                </p>
            </div>
        </div>

        {{-- ================= LEVELS ================= --}}
        <h2 class="mt-4 mb-3" style="font-weight:700;">Shipper Levels</h2>
        <div class="table-responsive">
            <table class="table table-bordered shadow-sm" style="font-size:14px;">
                <thead class="thead-light"><tr><th>Level</th><th>Unlocked by</th><th>Benefits</th></tr></thead>
                <tbody>
                    <tr><td><strong>Level 1</strong> — Starter</td><td>KYC approval</td><td>Standard requests in your service countries</td></tr>
                    <tr><td><strong>Level 2</strong> — Trusted</td><td>Consistently on-time completions + strong rating</td><td>Higher-value requests, priority marketplace placement</td></tr>
                    <tr><td><strong>Level 3</strong> — Elite</td><td>Sustained performance record</td><td>Top-tier requests, featured in the public shipper directory</td></tr>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('shipper.register.form') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-rocket mr-1"></i> Start Your Application
            </a>
        </div>
    </div>
</section>
@endsection
