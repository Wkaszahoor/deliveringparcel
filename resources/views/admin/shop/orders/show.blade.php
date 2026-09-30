@extends('admin.layouts.app')
@section('title', 'Shop Order ' . $order->code)
@section('page_title', 'Shop Order ' . $order->code)
@section('page_subtitle', optional($order->user)->name ?: 'guest customer')

@section('content')
<div class="row mb-3"><div class="col-12">
    <a href="{{ route('admin.shop.orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div></div>
<div class="row">
    <div class="col-12 col-lg-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-header py-2">Details</div>
            <div class="card-body">
                @php $m = $statusMap[$order->status] ?? ['label' => $order->status, 'color' => 'bg-secondary']; @endphp
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted" style="width:40%">Code</th><td>{{ $order->code }}</td></tr>
                    <tr><th class="text-muted">Customer</th><td>{{ optional($order->user)->name ?: '—' }}<br><small class="text-muted">{{ optional($order->user)->email }}</small></td></tr>
                    <tr><th class="text-muted">Status</th><td><span class="dp-badge {{ $m['color'] }} text-white">{{ $m['label'] }}</span></td></tr>
                    <tr><th class="text-muted">Total</th><td>${{ number_format((float) $order->total, 2) }}</td></tr>
                    <tr><th class="text-muted">Paid at</th><td>{{ optional($order->paid_at)->format('M d, Y H:i') ?: '—' }}</td></tr>
                    <tr><th class="text-muted">Created</th><td>{{ optional($order->created_at)->format('M d, Y H:i') }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="dp-table-wrap p-2 p-md-0">
            <table class="table dp-table dp-card-mobile mb-0">
                <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                <tbody>
                    @forelse ($order->items as $item)
                        <tr>
                            <td data-label="Item">{{ $item->name }}</td>
                            <td data-label="Qty">{{ $item->qty }}</td>
                            <td data-label="Price">${{ number_format((float) $item->price, 2) }}</td>
                            <td data-label="Subtotal">${{ number_format((float) $item->price * $item->qty, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PM-015: force the payment gateway for this shop order --}}
        <div class="card shadow-sm mt-3">
            <div class="card-header py-2"><i class="fas fa-credit-card mr-1"></i> Payment method</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="so-forced">Required gateway</label>
                    <select id="so-forced" class="form-control" style="max-width:340px">
                        <option value="">— No forcing —</option>
                        @foreach ($payMethods as $pm)
                            <option value="{{ $pm->code }}" {{ $forcedNow === $pm->code ? 'selected' : '' }}>{{ $pm->name }}</option>
                        @endforeach
                    </select>
                    @if ($order->forced_payment_method_code)
                        <small class="text-muted">This order forces <strong>{{ $order->forced_payment_method_code }}</strong>.</small>
                    @elseif ($forcedNow)
                        <small class="text-muted">Inherited from a forced product: <strong>{{ $forcedNow }}</strong>. Selecting an option here overrides it.</small>
                    @else
                        <small class="text-muted">Normal checkout rules apply. The customer must use the selected gateway.</small>
                    @endif
                </div>
                <button type="button" id="so-forced-save" class="btn btn-primary btn-sm"
                        data-url="{{ route('admin.shop.orders.payment-method', $order->id) }}">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
                <span id="so-forced-msg" class="ml-2 small"></span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
(function () {
    var btn = document.getElementById('so-forced-save');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var sel = document.getElementById('so-forced');
        var msg = document.getElementById('so-forced-msg');
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
            msg.className = 'ml-2 small ' + (d.ok ? 'text-success' : 'text-danger');
            if (d.ok) setTimeout(function () { window.location.reload(); }, 900);
        })
        .catch(function () { msg.textContent = 'Request failed.'; msg.className = 'ml-2 small text-danger'; })
        .finally(function () { btn.disabled = false; });
    });
})();
</script>
@endpush
