@extends('layouts.tailwind.app')

@section('title', 'Rate Calculator')
@section('page_title', 'Rate Calculator')
@section('page_subtitle', 'Test quotes against live zone rules, surcharges and insurance')

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    <div class="lg:col-span-5">
        <x-admin.card title="Quote inputs">
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Origin zone <span class="text-red-500">*</span></label>
                <select id="c-origin" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="">&mdash;</option>
                    @foreach ($zones as $z)<option value="{{ $z->id }}">{{ $z->name }} ({{ $z->code }})</option>@endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Destination zone <span class="text-red-500">*</span></label>
                <select id="c-dest" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="">&mdash;</option>
                    @foreach ($zones as $z)<option value="{{ $z->id }}">{{ $z->name }} ({{ $z->code }})</option>@endforeach
                </select>
            </div>
            <div class="mb-3 grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Actual weight (kg) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" id="c-weight" value="1" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Service</label>
                    <select id="c-service" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                        @foreach (config('admin_rates.service_types') as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3 grid grid-cols-3 gap-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">L (cm)</label>
                    <input type="number" step="0.1" min="0" id="c-l" placeholder="0" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">W (cm)</label>
                    <input type="number" step="0.1" min="0" id="c-w" placeholder="0" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">H (cm)</label>
                    <input type="number" step="0.1" min="0" id="c-h" placeholder="0" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Declared value ($)</label>
                <input type="number" step="0.01" min="0" id="c-declared" value="0" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
            </div>
            <label class="mb-2 flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" id="c-fuel" checked class="rounded border-slate-300 text-brand focus:ring-brand-light">
                Apply fuel surcharges
            </label>
            <label class="mb-4 flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" id="c-ins" checked class="rounded border-slate-300 text-brand focus:ring-brand-light">
                Apply insurance
            </label>
            <x-admin.button type="button" id="c-go" class="w-full justify-center"><i class="fas fa-equals"></i> Calculate</x-admin.button>
        </x-admin.card>
    </div>

    <div class="lg:col-span-7">
        <x-admin.card title="Breakdown">
            <div id="c-out">
                <p class="mb-0 text-sm text-slate-500">Fill the form and press <strong>Calculate</strong> &mdash; the itemized quote appears here.</p>
            </div>
        </x-admin.card>
    </div>
</div>
@endsection

{{-- This page's script uses DP.btnLoading/DP.request/DP.toast/DP.esc, none of which the shared
     Tailwind layout loads globally, so window.DP (dp-lazy.js) + toastr are pulled in here. Pushed
     via @push('admin_scripts') so it runs AFTER the layout's jQuery <script> tag (jQuery isn't
     actually required by this page's own code, but dp-lazy.js's DOMContentLoaded init still needs
     to run once, and keeping all pushed script tags in the stack after the layout's assets avoids
     any ordering surprises). --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
(function () {
    var btn = document.getElementById('c-go');
    var out = document.getElementById('c-out');

    function money(v) { return '$' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    btn.addEventListener('click', function () {
        DP.btnLoading(btn, true);
        var body = {
            origin: document.getElementById('c-origin').value,
            destination: document.getElementById('c-dest').value,
            weight: document.getElementById('c-weight').value,
            service_type: document.getElementById('c-service').value,
            declared_value: document.getElementById('c-declared').value || 0,
            apply_fuel: document.getElementById('c-fuel').checked,
            apply_insurance: document.getElementById('c-ins').checked
        };
        ['l', 'w', 'h'].forEach(function (k) {
            var v = parseFloat(document.getElementById('c-' + k).value);
            if (v > 0) body[k === 'l' ? 'length_cm' : k === 'w' ? 'width_cm' : 'height_cm'] = v;
        });

        if (!body.origin || !body.destination) {
            DP.toast.error('Pick origin and destination zones');
            DP.btnLoading(btn, false);
            return;
        }

        DP.request(@json(route('admin.rates.compute')), 'POST', body)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                DP.btnLoading(btn, false);
                if (!res.ok) {
                    out.innerHTML = '<div class="rounded-md border border-yellow-300 bg-yellow-50 px-3 py-2 text-sm text-yellow-800"><i class="fas fa-exclamation-triangle mr-1"></i>' + DP.esc(res.message || 'No rate found') + '</div>';
                    return;
                }
                var html = '<table class="mb-3 w-full text-left text-sm"><thead><tr class="border-b border-slate-100 text-xs uppercase text-slate-500"><th class="py-2">Item</th><th class="py-2 text-right">Amount</th></tr></thead><tbody class="divide-y divide-slate-100">';
                res.breakdown.forEach(function (b) {
                    html += '<tr><td class="py-2">' + DP.esc(b.label) + '</td><td class="py-2 text-right">' + money(b.amount) + '</td></tr>';
                });
                html += '</tbody></table>';
                html += '<div class="mb-2 flex justify-between border-t border-slate-100 pt-2 text-sm"><strong>Chargeable weight</strong><span>' + res.chargeable_weight + ' kg' + (res.volumetric_weight ? ' (vol. ' + res.volumetric_weight + ' kg)' : '') + '</span></div>';
                if (res.transit) html += '<div class="mb-2 flex justify-between text-sm"><strong>Transit time</strong><span>' + DP.esc(res.transit) + '</span></div>';
                html += '<div class="flex items-center justify-between rounded-md bg-slate-50 p-3"><strong class="text-lg">TOTAL</strong><span class="text-lg font-semibold text-green-600">' + money(res.total) + '</span></div>';
                out.innerHTML = html;
            })
            .catch(function () {
                DP.btnLoading(btn, false);
                DP.toast.error('Request failed');
            });
    });
})();
</script>
@endpush
