@extends('home2.layouts.app')
@section('title', 'New shipping request')

@section('content')
<section class="h2-section alt">
    <div class="h2-container">
        <h2>New shipping request</h2>
        <p class="muted">Three quick steps — you can review everything before submitting.</p>

        <form method="POST" action="{{ route('home2.wizard.store') }}" id="wiz" class="h2-form h2-form-wide">
            @csrf

            {{-- progress indicator --}}
            <div style="display:flex;gap:.5rem;margin:1rem 0 1.5rem" id="wiz-steps">
                <span class="h2-badge" data-step="1" style="padding:.4rem .9rem">1 · Route</span>
                <span class="h2-badge" data-step="2" style="padding:.4rem .9rem;opacity:.5">2 · Parcel</span>
                <span class="h2-badge" data-step="3" style="padding:.4rem .9rem;opacity:.5">3 · Review</span>
            </div>

            <div class="wiz-pane" data-pane="1">
                <div class="h2-field-grid">
                    <div>
                        <label>Shipping from *</label>
                        <select name="shipfrom" class="form-control" required>
                            <option value="" disabled @if(!old('shipfrom', $prefill['country'])) selected @endif>Select country</option>
                            @include('home2.partials.country-options', ['selected' => old('shipfrom', $prefill['country'])])
                        </select>
                    </div>
                    <div>
                        <label>Shipping to *</label>
                        <select name="shipto" class="form-control" required>
                            <option value="" disabled @if(!old('shipto')) selected @endif>Select country</option>
                            @include('home2.partials.country-options', ['selected' => old('shipto')])
                        </select>
                    </div>
                    <div>
                        <label>Delivery address *</label>
                        <input type="text" name="address" class="form-control" required value="{{ old('address', $prefill['address']) }}">
                    </div>
                    <div>
                        <label>Postal code *</label>
                        <input type="text" name="postalcode" class="form-control" required value="{{ old('postalcode') }}">
                    </div>
                </div>
            </div>

            <div class="wiz-pane" data-pane="2" style="display:none">
                <div class="h2-field-grid">
                    <div>
                        <label>Approximate weight (kg) *</label>
                        <input type="number" step="0.1" min="0.1" name="approximate_weight" class="form-control" required value="{{ old('approximate_weight') }}">
                    </div>
                    <div>
                        <label>Photo link (optional)</label>
                        <input type="url" name="product_photo" class="form-control" placeholder="https://" value="{{ old('product_photo') }}">
                    </div>
                </div>
                <label>What are we shipping? *</label>
                <textarea name="product_description" rows="4" class="form-control" required minlength="5" placeholder="e.g. 2 cartons of electronics, boxed">{{ old('product_description') }}</textarea>
                <label class="mt-2">Extra services</label>
                <div style="display:flex;flex-wrap:wrap;gap:1rem">
                    @foreach (['Disinfection' => 'disinfection', 'Consolidation' => 'consolidation', 'Customs handling' => 'customs', 'Product check' => 'product_check'] as $label => $key)
                        <label style="display:flex;align-items:center;gap:.4rem;font-weight:400;margin:0">
                            <input type="checkbox" name="product_services[]" value="{{ $key }}"> {{ $label }}
                        </label>
                    @endforeach
                </div>
                <label class="mt-2">Notes</label>
                <textarea name="notes" rows="2" class="form-control" maxlength="2000">{{ old('notes') }}</textarea>
            </div>

            <div class="wiz-pane" data-pane="3" style="display:none">
                <p class="muted">Confirm the summary below, then submit — our team will reply with an offer.</p>
                <ul id="wiz-summary" class="h2-card" style="list-style:none;padding:1rem"></ul>
            </div>

            <div style="display:flex;justify-content:space-between;margin-top:1.5rem">
                <button type="button" class="h2-btn h2-btn-outline" id="wiz-prev" style="visibility:hidden">← Back</button>
                <button type="button" class="h2-btn h2-btn-outline" id="wiz-next">Next →</button>
                <button type="submit" class="h2-btn h2-btn-primary" id="wiz-submit" style="display:none">Submit request</button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var pane = 1, form = document.getElementById('wiz');
    var panes = form.querySelectorAll('.wiz-pane');
    var badges = document.querySelectorAll('#wiz-steps [data-step]');
    function summary() {
        var f = function (n) { return (form.querySelector('[name="' + n + '"]') || {}).value || '—'; };
        var svcs = Array.prototype.map.call(form.querySelectorAll('[name="product_services[]"]:checked'), function (c) { return c.parentElement.textContent.trim(); });
        document.getElementById('wiz-summary').innerHTML =
            '<li><strong>From:</strong> ' + f('shipfrom') + ' → <strong>To:</strong> ' + f('shipto') + '</li>' +
            '<li><strong>Address:</strong> ' + f('address') + ' (' + f('postalcode') + ')</li>' +
            '<li><strong>Weight:</strong> ' + f('approximate_weight') + ' kg</li>' +
            '<li><strong>Contents:</strong> ' + f('product_description') + '</li>' +
            '<li><strong>Services:</strong> ' + (svcs.length ? svcs.join(', ') : 'none') + '</li>';
    }
    function show() {
        panes.forEach(function (p) { p.style.display = +p.dataset.pane === pane ? '' : 'none'; });
        badges.forEach(function (b) { b.style.opacity = (+b.dataset.step <= pane) ? '1' : '.5'; });
        document.getElementById('wiz-prev').style.visibility = pane > 1 ? 'visible' : 'hidden';
        document.getElementById('wiz-next').style.display = pane < 3 ? '' : 'none';
        document.getElementById('wiz-submit').style.display = pane === 3 ? '' : 'none';
        if (pane === 3) summary();
    }
    /* Full constraint validation (minlength, url format, …) — the old code
       only checked "required is empty", letting invalid fields slip onto
       later steps where native submit validation died silently on the
       hidden control ("not focusable"). */
    function firstInvalid(root) {
        var els = root.querySelectorAll('input, select, textarea');
        for (var i = 0; i < els.length; i++) {
            if (!els[i].checkValidity()) return els[i];
        }
        return null;
    }
    document.getElementById('wiz-next').addEventListener('click', function () {
        var bad = firstInvalid(panes[pane - 1]);
        if (bad) { bad.reportValidity(); return; }
        pane = Math.min(3, pane + 1); show();
    });
    document.getElementById('wiz-prev').addEventListener('click', function () { pane = Math.max(1, pane - 1); show(); });
    /* Safety net: never let submit fail silently — jump back to the pane
       holding the first invalid control so it is visible and focusable. */
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            var bad = form.querySelector(':invalid');
            var p = bad.closest('.wiz-pane');
            if (p) { pane = +p.dataset.pane; show(); }
            bad.focus();
            bad.reportValidity();
        }
    });
    show();
})();
</script>
@endpush
