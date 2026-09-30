@extends('home2.layouts.app')
@section('title', 'New consolidation request')
@section('meta_description', 'Submit a package consolidation request — our team replies with a custom offer.')

@section('content')
<section class="h2-section alt">
    <div class="h2-container">
        <h1>Package consolidation request</h1>
        <p class="muted">
            Tell us what you are shipping — nothing is charged at this stage.
            Our team reviews the request and sends you an offer.
        </p>

        @if ($isGuest)
            <p class="muted" style="font-size:.9rem">
                New here? No account needed — we create one automatically and email your login details.
                Already registered? <a href="{{ route('login') }}">Log in first</a> so the request lands on your dashboard.
            </p>
        @else
            <p class="muted" style="font-size:.9rem">Submitting as <strong>{{ auth()->user()->name }}</strong> — the request will appear on your dashboard.</p>
        @endif

        <form method="POST" action="{{ route('home2.request2.store') }}" id="req2-form" class="h2-form h2-form-wide">
            @csrf

            {{-- ============ 1 · Route & delivery ============ --}}
            <div class="h2-card" style="padding:1.25rem; margin-bottom:1.25rem">
                <h3 style="font-size:1.05rem">1 · Route &amp; delivery</h3>

                <div class="h2-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:.9rem">
                    <div>
                        <label for="shipfrom">Shipping from *</label>
                        <select id="shipfrom" name="shipfrom" class="form-control" required>
                            <option value="" disabled @if(!old('shipfrom', $prefill['from'])) selected @endif>Select country</option>
                            @include('home2.partials.country-options', ['selected' => old('shipfrom', $prefill['from'])])
                        </select>
                    </div>
                    <div>
                        <label for="shipto">Shipping to *</label>
                        <select id="shipto" name="shipto" class="form-control" required>
                            <option value="" disabled @if(!old('shipto', $prefill['to'])) selected @endif>Select country</option>
                            @include('home2.partials.country-options', ['selected' => old('shipto', $prefill['to'])])
                        </select>
                    </div>
                    <div>
                        <label for="postalcode">Postal code *</label>
                        <input type="text" id="postalcode" name="postalcode" class="form-control" required maxlength="20" value="{{ old('postalcode') }}">
                    </div>
                    <div>
                        <label for="approximate_weight">Approximate weight (kg) *</label>
                        <input type="number" id="approximate_weight" name="approximate_weight" class="form-control" required
                               step="0.1" min="0.1" max="20000" value="{{ old('approximate_weight') }}">
                    </div>
                </div>

                <div style="margin-top:.9rem">
                    <label for="address">Delivery address *</label>
                    <textarea id="address" name="address" class="form-control" required maxlength="500" rows="2">{{ old('address', $prefill['address']) }}</textarea>
                </div>
            </div>

            {{-- ============ 2 · Your details ============ --}}
            <div class="h2-card" style="padding:1.25rem; margin-bottom:1.25rem">
                <h3 style="font-size:1.05rem">2 · Your details</h3>

                <div class="h2-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:.9rem">
                    <div>
                        <label for="name">Full name *</label>
                        <input type="text" id="name" name="name" class="form-control" required maxlength="191" value="{{ $prefill['name'] }}">
                    </div>
                    <div>
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" required maxlength="191"
                               value="{{ $prefill['email'] }}" @if(!$isGuest) readonly @endif>
                    </div>
                    <div>
                        <label for="number">Phone *</label>
                        <input type="tel" id="number" name="number" class="form-control" required maxlength="20" value="{{ $prefill['number'] }}">
                    </div>
                </div>
            </div>

            {{-- ============ 3 · Products ============ --}}
            <div class="h2-card" style="padding:1.25rem; margin-bottom:1.25rem">
                <h3 style="font-size:1.05rem">3 · Products</h3>
                <p class="muted" style="font-size:.9rem">Add every item — link, name, quantity and unit weight.</p>

                <div id="req2-products"></div>
                <button type="button" id="req2-add" class="h2-btn h2-btn-primary" style="margin-top:.5rem">+ Add product</button>
            </div>

            {{-- ============ 4 · Options ============ --}}
            <div class="h2-card" style="padding:1.25rem; margin-bottom:1.25rem">
                <h3 style="font-size:1.05rem">4 · Options</h3>

                <div style="display:flex; flex-wrap:wrap; gap:1rem; margin:.5rem 0">
                    @foreach (['consolidation' => 'Consolidation (combine parcels)', 'customs' => 'Customs handling', 'check' => 'Item check &amp; photos', 'disinfection' => 'Disinfection'] as $key => $label)
                        <label style="display:flex; align-items:center; gap:.4rem; font-weight:400">
                            <input type="checkbox" name="product_services[]" value="{{ $key }}" @if(in_array($key, old('product_services', []))) checked @endif>
                            {!! $label !!}
                        </label>
                    @endforeach
                    <label style="display:flex; align-items:center; gap:.4rem; font-weight:400">
                        <input type="hidden" name="purchase_assistance" value="0">
                        <input type="checkbox" name="purchase_assistance" value="1" @if(old('purchase_assistance')) checked @endif>
                        Purchase assistance (we buy for you)
                    </label>
                </div>

                <label for="product_photo">Photo / product link (optional)</label>
                <input type="url" id="product_photo" name="product_photo" class="form-control" placeholder="https://" maxlength="500" value="{{ old('product_photo') }}">
                <p class="muted" style="font-size:.85rem; margin-top:.35rem">Paste a link — file uploads (incl. JPG) are not accepted on this form.</p>
            </div>

            {{-- ============ Submit ============ --}}
            @if ($turnstileOn)
                <div style="margin-bottom:1rem">
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAABdxU2xNnEwFvrQl"></div>
                </div>
            @endif

            <button type="submit" class="h2-btn h2-btn-primary" style="width:100%">Place request</button>
            <p class="muted" style="font-size:.85rem; margin-top:.6rem; text-align:center">
                You will not pay anything at this stage — an offer arrives first.
            </p>
        </form>
    </div>
</section>

@push('scripts')
@if ($turnstileOn)
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
<script>
(function () {
    var wrap = document.getElementById('req2-products');
    if (!wrap) return;
    var idx = 0;

    function row(i, v) {
        v = v || {};
        var d = document.createElement('div');
        d.className = 'req2-prod';
        d.style.cssText = 'display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)) auto; gap:.6rem; align-items:end; margin-bottom:.6rem';
        d.innerHTML =
            '<div><label>Product link *</label><input type="url" class="form-control" name="addmore[' + i + '][producturl]" required placeholder="https://" maxlength="500"></div>' +
            '<div><label>Name</label><input type="text" class="form-control" name="addmore[' + i + '][productname]" maxlength="191"></div>' +
            '<div><label>Quantity *</label><input type="number" class="form-control" name="addmore[' + i + '][productquantity]" required min="1" max="9999" value="1"></div>' +
            '<div><label>Weight (kg) *</label><input type="number" class="form-control" name="addmore[' + i + '][productweight]" required step="0.1" min="0"></div>' +
            '<div><button type="button" class="h2-btn" style="padding:.45rem .7rem" title="Remove">&times;</button></div>';
        d.querySelector('button').addEventListener('click', function () {
            if (wrap.children.length > 1) d.remove();
        });
        return d;
    }

    wrap.appendChild(row(idx++));
    document.getElementById('req2-add').addEventListener('click', function () {
        wrap.appendChild(row(idx++));
    });
})();
</script>
@endpush
@endsection
