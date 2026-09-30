@extends('layouts.tailwind.app')

@section('title', 'Offers / Negotiation')
@section('page_title', 'Offers & Negotiation')
@section('page_subtitle', 'Read-model over offerorders — statuses per app convention')

{{-- window.DP (infinite scroll + esc helpers) is only loaded by the old AdminLTE layouts; this
     page relies on DP.infiniteScroll for its table, so dp-lazy.js must be pulled in explicitly
     here — the new Tailwind layout never loads it globally. Pushed via @push('admin_scripts') so
     it loads AFTER the layout's jQuery <script> tag (layout yields content, then jQuery, then
     @stack('admin_scripts')); an inline script directly in @section('content') would run before
     jQuery/dp-lazy.js exist. --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')
<x-admin.card>
    <div class="mb-4 flex flex-wrap items-end gap-2">
        <div>
            <label for="f-q" class="mb-1 block text-xs font-medium text-slate-600">Search</label>
            <input id="f-q" type="text" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Search user / order ref…" style="max-width:280px">
        </div>
        <div>
            <label for="f-status" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
            <select id="f-status" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:160px">
                <option value="">Status: all</option>
                <option value="0">New</option>
                <option value="1">Accepted</option>
                <option value="2">Rejected</option>
                <option value="3">Counter</option>
            </select>
        </div>
        <x-admin.button tag="a" variant="secondary" :href="route('admin.quotes.index')" class="ml-auto">
            <i class="fas fa-file-alt"></i> Quote inbox
        </x-admin.button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">#</th>
                    <th class="py-2 pr-4">Order</th>
                    <th class="py-2 pr-4">Customer</th>
                    <th class="py-2 pr-4">Total</th>
                    <th class="py-2 pr-4">Products</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Note</th>
                    <th class="py-2 pr-4">Date</th>
                </tr>
            </thead>
            <tbody id="dp-tbody" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
</x-admin.card>
@endsection

@push('admin_scripts')
<script>
(function () {
    var base = @json(route('admin.quotes.offers.data'));
    function url() {
        var u = new URL(base);
        if (document.getElementById('f-q').value.trim()) u.searchParams.set('q', document.getElementById('f-q').value.trim());
        if (document.getElementById('f-status').value) u.searchParams.set('status', document.getElementById('f-status').value);
        return u.toString();
    }
    // Bootstrap badge class (from config/admin_quotes.php offer_status_map) → Tailwind pill
    // classes — same mapping convention as admin/ordersm/index.blade.php's badgeClasses().
    function badgeClasses(bootstrap) {
        var map = {
            'bg-secondary': 'bg-slate-100 text-slate-700',
            'bg-primary':   'bg-blue-100 text-blue-700',
            'bg-info':      'bg-cyan-100 text-cyan-700',
            'bg-success':   'bg-green-100 text-green-700',
            'bg-danger':    'bg-red-100 text-red-700',
            'bg-warning':   'bg-yellow-100 text-yellow-800'
        };
        return map[bootstrap] || map['bg-secondary'];
    }
    var sc = DP.infiniteScroll({
        url: url(), target: '#dp-tbody',
        render: function (o) {
            return '<tr>'
                + '<td data-label="#" class="py-2 pr-4"><strong>#' + DP.esc(o.id) + '</strong></td>'
                + '<td data-label="Order" class="py-2 pr-4">' + DP.esc(o.order_ref || o.order_id) + '</td>'
                + '<td data-label="Customer" class="py-2 pr-4">' + DP.esc(o.user) + '<br><small class="text-slate-500">' + DP.esc(o.email || '') + '</small></td>'
                + '<td data-label="Total" class="py-2 pr-4">$' + DP.esc(o.total) + '</td>'
                + '<td data-label="Products" class="py-2 pr-4">' + DP.esc(o.products) + '</td>'
                + '<td data-label="Status" class="py-2 pr-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + badgeClasses(o.color) + '">' + DP.esc(o.status) + '</span></td>'
                + '<td data-label="Note" class="py-2 pr-4 text-slate-500">' + DP.esc(o.note || '—') + '</td>'
                + '<td data-label="Date" class="py-2 pr-4 text-slate-500">' + DP.esc(o.created_at) + '</td>'
                + '</tr>';
        }
    });
    var q = document.getElementById('f-q'), t;
    q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { sc.reload(url()); }, 350); });
    document.getElementById('f-status').addEventListener('change', function () { sc.reload(url()); });
})();
</script>
@endpush
