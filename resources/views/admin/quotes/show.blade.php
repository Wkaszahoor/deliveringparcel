@extends('layouts.tailwind.app')

@section('title', 'Quote #' . $quote->id)
@section('page_title', 'Quote Request #' . $quote->id)
@section('page_subtitle', $quote->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.quotes.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    <div class="lg:col-span-5">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-user mr-1 text-slate-400"></i> Customer</h3>
            <table class="w-full text-sm">
                <tr><td class="w-28 py-1 pr-2 align-top text-slate-500">Name</td><td class="py-1">{{ $quote->name }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Email</td><td class="py-1">{{ $quote->email }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Phone</td><td class="py-1">{{ $quote->number ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Received</td><td class="py-1">{{ $quote->created_at ?: '—' }}</td></tr>
            </table>
        </x-admin.card>
    </div>
    <div class="lg:col-span-7">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-box mr-1 text-slate-400"></i> Shipment details</h3>
            <table class="w-full text-sm">
                <tr><td class="w-32 py-1 pr-2 align-top text-slate-500">Cargo type</td><td class="py-1">{{ $quote->cargotype ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">From</td><td class="py-1">{{ $quote->country ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">To</td><td class="py-1">{{ $quote->destination ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Weight</td><td class="py-1">{{ $quote->weight ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Dimensions (W&times;H)</td><td class="py-1">{{ trim(($quote->width ?: '—') . ' × ' . ($quote->height ?: '—')) }}</td></tr>
            </table>
            @if ($quote->detail)
                <hr class="my-3 border-slate-100">
                <h4 class="mb-1 text-xs font-semibold uppercase text-slate-500">Message</h4>
                <p class="text-sm text-slate-700">{!! nl2br(e($quote->detail)) !!}</p>
            @endif
        </x-admin.card>

        {{-- PM-015: required payment gateway for this quote --}}
        <x-admin.card class="mt-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-credit-card mr-1 text-slate-400"></i> Payment method requirement</h3>
            <div class="mb-3">
                <label for="q-forced" class="mb-1 block text-xs font-medium text-slate-600">Customer must pay with</label>
                <select id="q-forced" class="w-full max-w-sm rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">— No requirement —</option>
                    @foreach ($payMethods as $pm)
                        <option value="{{ $pm->code }}" {{ ($quote->forced_payment_method_code ?? '') === $pm->code ? 'selected' : '' }}>{{ $pm->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Shown here for your reply to the customer, and applied automatically when an order is created from this quote.</p>
            </div>
            <x-admin.button type="button" id="q-forced-save" data-url="{{ route('admin.quotes.payment-method', $quote->id) }}">
                <i class="fas fa-save"></i> Save
            </x-admin.button>
            <span id="q-forced-msg" class="ml-2 text-xs"></span>
        </x-admin.card>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
(function () {
    var btn = document.getElementById('q-forced-save');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var sel = document.getElementById('q-forced');
        var msg = document.getElementById('q-forced-msg');
        btn.disabled = true;
        fetch(btn.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token())
            },
            body: JSON.stringify({ method: sel.value })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            msg.textContent = d.message || (d.ok ? 'Saved.' : 'Failed.');
            msg.className = 'ml-2 text-xs ' + (d.ok ? 'text-green-600' : 'text-red-600');
        })
        .catch(function () { msg.textContent = 'Request failed.'; msg.className = 'ml-2 text-xs text-red-600'; })
        .finally(function () { btn.disabled = false; });
    });
})();
</script>
@endpush
