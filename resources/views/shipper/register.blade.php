@extends('shipper.layouts.app')
@section('title', 'Become a Shipper')

@section('content')

{{-- Page-scoped bolder pass: the shipper portal's own home2 (.h2-*) system
     stays the vocabulary — no dp-* classes imported here, that's the public
     site's namespace. New primitives (decorative blobs, step strip, numbered
     section cards, circular avatar upload) are added because the user
     supplied a concrete visual reference asking for exactly this: a floating
     card over a soft decorative backdrop, a progress indicator, grouped
     sections, and a camera-button photo upload — not present in home2.css
     before this page needed them. Kept the portal's own blue --h2-primary
     rather than the reference's orange, since orange would break consistency
     with every sibling shipper-portal page (dashboard, wallet, assignments)
     that shares this same header and accent. --}}
<style>
.shp-reg-shell { position: relative; padding: 1.5rem 0 3rem; overflow: hidden; }
.shp-reg-blob { position: absolute; border-radius: 50%; z-index: 0; pointer-events: none; }
.shp-reg-blob-1 { width: 280px; height: 280px; top: -110px; right: -70px; background: radial-gradient(circle, var(--h2-primary), transparent 70%); opacity: .16; }
.shp-reg-blob-2 { width: 220px; height: 220px; bottom: -80px; left: -60px; background: radial-gradient(circle, var(--h2-primary-dark), transparent 70%); opacity: .14; }
.shp-reg-blob-3 { width: 150px; height: 150px; top: 38%; left: -80px; background: radial-gradient(circle, var(--h2-primary), transparent 70%); opacity: .1; }
@media (prefers-reduced-motion: no-preference) { .shp-reg-card { animation: shpRegIn .5s cubic-bezier(.16,1,.3,1) both; } }
@keyframes shpRegIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

.shp-reg-card { position: relative; z-index: 1; max-width: 760px; margin: 0 auto; background: #fff; border-radius: 20px; box-shadow: 0 30px 70px -35px rgba(13,42,82,.35); border: 1px solid var(--h2-border); padding: 2.1rem 1.75rem 2.3rem; }
@media (min-width: 640px) { .shp-reg-card { padding: 2.4rem 2.4rem 2.6rem; } }

.shp-reg-head { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: .5rem; }
.shp-reg-head h2 { margin: 0 0 .4rem; font-size: 1.7rem; }
.shp-reg-head p { margin: 0; color: var(--h2-muted); font-size: .92rem; max-width: 38ch; }
.shp-reg-links { display: flex; gap: .5rem; font-size: .8rem; font-weight: 700; flex-wrap: wrap; }
.shp-reg-links a { padding: .4rem .8rem; border-radius: 999px; background: var(--h2-bg); white-space: nowrap; }
.shp-reg-links a:hover { background: #e7f0ff; }

.shp-steps { display: flex; align-items: flex-start; justify-content: space-between; margin: 1.9rem 0 .5rem; position: relative; }
.shp-steps::before { content: ''; position: absolute; left: 17px; right: 17px; top: 16px; height: 2px; background: var(--h2-border); z-index: 0; }
.shp-step { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: .4rem; flex: 1; }
.shp-step-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .82rem; background: #fff; border: 2px solid var(--h2-border); color: var(--h2-muted); }
.shp-step.is-on .shp-step-dot { background: var(--h2-primary); border-color: var(--h2-primary); color: #fff; }
.shp-step-label { font-size: .68rem; font-weight: 700; color: var(--h2-muted); text-align: center; line-height: 1.25; }
.shp-step.is-on .shp-step-label { color: var(--h2-text); }

.shp-section { border: 1px solid var(--h2-border); border-radius: 16px; padding: 1.35rem 1.4rem 1.5rem; margin-top: 1.6rem; background: var(--h2-bg); }
.shp-section-head { display: flex; align-items: center; gap: .65rem; margin-bottom: .9rem; }
.shp-section-num { width: 28px; height: 28px; border-radius: 50%; background: var(--h2-primary); color: #fff; font-weight: 800; font-size: .8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.shp-section-head h4 { margin: 0; font-size: 1.02rem; }
.shp-section-note { margin: -.4rem 0 .7rem 2.5rem; color: var(--h2-muted); font-size: .82rem; }
.shp-section .h2-form-wide-inner { background: #fff; border-radius: 12px; padding: 1rem 1.1rem .4rem; }

.shp-country-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(180px,1fr)); gap: .5rem; max-height: 230px; overflow: auto; padding: .2rem; }
.shp-country-grid label { display: flex; align-items: center; gap: .5rem; border: 1px solid var(--h2-border); border-radius: 10px; padding: .45rem .65rem; margin: 0 !important; font-weight: 500 !important; cursor: pointer; background: #fff; transition: background-color .15s ease, border-color .15s ease; }
.shp-country-grid label:has(input:checked) { background: #e7f0ff; border-color: var(--h2-primary); color: var(--h2-primary-dark); font-weight: 700 !important; }
.shp-country-grid input { width: auto !important; }

.shp-service-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: .6rem; }
.shp-service-grid label { border: 1px solid var(--h2-border); border-radius: 12px; padding: .75rem .9rem; margin: 0 !important; font-weight: 500 !important; cursor: pointer; background: #fff; transition: background-color .15s ease, border-color .15s ease; }
.shp-service-grid label:has(input:checked) { background: #e7f0ff; border-color: var(--h2-primary); font-weight: 700 !important; }

.shp-avatar-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.1rem; }
.shp-avatar-btn { position: relative; width: 60px; height: 60px; border-radius: 50%; background: var(--h2-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; cursor: pointer; box-shadow: 0 10px 22px -8px rgba(13,110,253,.6); border: 3px solid #fff; outline: 2px solid var(--h2-border); flex-shrink: 0; transition: transform .15s ease, outline-color .15s ease; }
.shp-avatar-btn:hover { transform: translateY(-2px) scale(1.04); }
.shp-avatar-btn:has(:focus-visible) { outline-color: var(--h2-primary); outline-offset: 2px; }
.shp-avatar-input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
.shp-avatar-copy strong { display: block; font-size: .88rem; }
.shp-avatar-copy span { display: block; font-size: .76rem; color: var(--h2-primary); font-weight: 700; margin-top: 2px; }

@media (max-width: 480px) {
    .shp-steps { margin: 1.6rem 0 .4rem; }
    .shp-steps::before { left: 14px; right: 14px; top: 13px; }
    .shp-step-dot { width: 26px; height: 26px; font-size: .72rem; }
    .shp-step-label { font-size: .6rem; }
}

.shp-submit-wrap { margin-top: 1.9rem; }
.shp-submit { width: 100%; box-shadow: 0 16px 36px -16px rgba(13,110,253,.55); transition: transform .15s ease, box-shadow .15s ease; }
.shp-submit:hover { transform: translateY(-1px); box-shadow: 0 20px 42px -16px rgba(13,110,253,.65); }
</style>

<div class="shp-reg-shell">
    <div class="shp-reg-blob shp-reg-blob-1" aria-hidden="true"></div>
    <div class="shp-reg-blob shp-reg-blob-2" aria-hidden="true"></div>
    <div class="shp-reg-blob shp-reg-blob-3" aria-hidden="true"></div>

    <div class="shp-reg-card">

        <div class="shp-reg-head">
            <div>
                <h2>Shipper Application</h2>
                <p>Earn by buying and shipping parcels in your country. Customers never see your identity; everything runs through the platform.</p>
            </div>
            <div class="shp-reg-links">
                <a href="{{ route('shipper.program') }}">Perks &amp; payout model</a>
                <a href="{{ route('shippers.directory') }}">Shipper directory</a>
            </div>
        </div>

        {{-- Purely presentational — the form below stays a single scrolling
             page (matches the rest of this portal's forms, no JS step-gating
             added), but the sequence itself is real information the
             applicant needs, so the numbered strip earns its keep. --}}
        <div class="shp-steps" aria-hidden="true">
            @foreach ([
                ['n' => 1, 'label' => 'Account'],
                ['n' => 2, 'label' => 'Countries'],
                ['n' => 3, 'label' => 'Services'],
                ['n' => 4, 'label' => 'Property'],
                ['n' => 5, 'label' => 'Verification'],
            ] as $s)
                <div class="shp-step is-on">
                    <span class="shp-step-dot">{{ $s['n'] }}</span>
                    <span class="shp-step-label">{{ $s['label'] }}</span>
                </div>
            @endforeach
        </div>

        <form action="{{ route('shipper.register') }}" method="POST" enctype="multipart/form-data" class="h2-form h2-form-wide" style="max-width:none;margin:0;border:0;padding:0">
            @csrf
            @if($errors->any())
                <div class="h2-alert h2-alert-error">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            {{-- ========== 1. Account ========== --}}
            <div class="shp-section">
                <div class="shp-section-head">
                    <span class="shp-section-num">1</span>
                    <h4>Your account</h4>
                </div>
                <div class="h2-form-wide-inner">
                @if($user)
                    <div class="h2-alert" style="background:#e6f4ea;color:#137333;margin-top:0">
                        <i class="fas fa-user-check" style="margin-right:6px"></i>
                        Applying with your existing account: <b>{{ $user->name }}</b> ({{ $user->email }}).
                        No new password needed — your shipper workspace opens inside this same account
                        as soon as admin approves your application.
                    </div>
                @else
                    <label>Full name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    <label>Password</label>
                    <input type="password" name="password" required minlength="8">
                    <label>Confirm password</label>
                    <input type="password" name="password_confirmation" required>
                @endif
                </div>
            </div>

            {{-- ========== 2. Countries (checkbox grid) ========== --}}
            <div class="shp-section">
                <div class="shp-section-head">
                    <span class="shp-section-num">2</span>
                    <h4>Countries you can operate in</h4>
                </div>
                <p class="shp-section-note">Tick every country where you can buy / receive and ship parcels.</p>
                <div class="shp-country-grid">
                    @foreach($countries as $c)
                        <label>
                            <input type="checkbox" name="service_countries[]" value="{{ $c->iso2 }}"
                                {{ in_array($c->iso2, (array) old('service_countries')) ? 'checked' : '' }}>
                            {{ $c->name }} <span style="color:var(--h2-muted)">( {{ $c->iso2 }} )</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ========== 3. Services ========== --}}
            <div class="shp-section">
                <div class="shp-section-head">
                    <span class="shp-section-num">3</span>
                    <h4>Services you offer</h4>
                </div>
                <div class="shp-service-grid">
                    @foreach(['buy_for_me' => 'Buy for Me (purchase items locally)', 'ship_for_me' => 'Ship for Me (receive & forward packages)', 'storage' => 'Short-term storage', 'personal_shopper' => 'Personal shopper', 'luxury' => 'Luxury goods handling'] as $k => $lbl)
                        <label>
                            <input type="checkbox" name="services_offered[]" value="{{ $k }}" {{ in_array($k, (array) old('services_offered')) ? 'checked' : '' }}> {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ========== 4. Property ========== --}}
            <div class="shp-section">
                <div class="shp-section-head">
                    <span class="shp-section-num">4</span>
                    <h4>Your property</h4>
                </div>
                <div class="h2-form-wide-inner">
                    <label>Residence type</label>
                    <select name="residence_type">
                        @foreach(['house','apartment','villa','office'] as $rt)
                            <option value="{{ $rt }}" {{ old('residence_type') === $rt ? 'selected' : '' }}>{{ ucfirst($rt) }}</option>
                        @endforeach
                    </select>
                    <label style="margin-top:12px"><input type="checkbox" name="has_storage" value="1" {{ old('has_storage') ? 'checked' : '' }}> I have storage space for packages</label>
                </div>
            </div>

            {{-- ========== 5. KYC ========== --}}
            <div class="shp-section">
                <div class="shp-section-head">
                    <span class="shp-section-num">5</span>
                    <h4>Identity verification <span style="color:var(--h2-muted);font-weight:400;font-size:13px">(optional now — required before approval)</span></h4>
                </div>
                <div class="h2-form-wide-inner">
                    {{-- Selfie gets the camera-button treatment — the one KYC
                         document that's actually a photo of the applicant,
                         same role the reference's avatar-upload button plays. --}}
                    <div class="shp-avatar-row">
                        <label class="shp-avatar-btn" aria-label="Upload selfie photo">
                            <i class="fas fa-camera" aria-hidden="true"></i>
                            <input class="shp-avatar-input" type="file" name="selfie_photo" accept=".jpg,.jpeg,.png,.pdf"
                                   onchange="document.getElementById('shp-selfie-name').textContent = this.files[0] ? this.files[0].name : 'No file chosen';">
                        </label>
                        <div class="shp-avatar-copy">
                            <strong>Selfie photo</strong>
                            <span id="shp-selfie-name">No file chosen</span>
                        </div>
                    </div>

                    <label>Government ID (JPG/PNG/PDF)</label>
                    <input type="file" name="government_id" accept=".jpg,.jpeg,.png,.pdf">
                    <label>Address proof</label>
                    <input type="file" name="address_proof" accept=".jpg,.jpeg,.png,.pdf">
                </div>
            </div>

            <div class="shp-submit-wrap">
                <button class="h2-btn h2-btn-primary shp-submit">Submit Application</button>
                <p style="text-align:center;color:var(--h2-muted);font-size:13px;margin-top:.75rem">
                    By applying you agree to ship via admin-coordinated assignments only — never contact customers directly.
                    <a href="{{ route('shipper.program') }}">How payouts and KYC work</a>.
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
