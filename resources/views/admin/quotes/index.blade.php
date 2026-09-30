@extends('layouts.tailwind.app')

@section('title', 'Quote Requests')
@section('page_title', 'Quote Requests')
@section('page_subtitle', 'Public "get a quote" form submissions')

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
            <input id="f-q" type="text" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Search name/email/route…" style="max-width:280px">
        </div>
        <div>
            <label for="f-from" class="mb-1 block text-xs font-medium text-slate-600">From</label>
            <input id="f-from" type="date" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:160px">
        </div>
        <div>
            <label for="f-to" class="mb-1 block text-xs font-medium text-slate-600">To</label>
            <input id="f-to" type="date" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:160px">
        </div>
        <x-admin.button tag="a" variant="secondary" :href="route('admin.quotes.offers')" class="ml-auto">
            <i class="fas fa-handshake"></i> Offers view
        </x-admin.button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">#</th>
                    <th class="py-2 pr-4">Customer</th>
                    <th class="py-2 pr-4">Cargo</th>
                    <th class="py-2 pr-4">Route</th>
                    <th class="py-2 pr-4">Weight</th>
                    <th class="py-2 pr-4">Received</th>
                    <th class="py-2 pr-4 text-right">Action</th>
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
    var base = @json(route('admin.quotes.data'));
    function url() {
        var u = new URL(base);
        if (document.getElementById('f-q').value.trim()) u.searchParams.set('q', document.getElementById('f-q').value.trim());
        if (document.getElementById('f-from').value) u.searchParams.set('from', document.getElementById('f-from').value);
        if (document.getElementById('f-to').value) u.searchParams.set('to', document.getElementById('f-to').value);
        return u.toString();
    }
    var sc = DP.infiniteScroll({
        url: url(), target: '#dp-tbody',
        render: function (r) {
            return '<tr>'
                + '<td data-label="#" class="py-2 pr-4"><strong>#' + DP.esc(r.id) + '</strong></td>'
                + '<td data-label="Customer" class="py-2 pr-4">' + DP.esc(r.name) + '<br><small class="text-slate-500">' + DP.esc(r.email) + '</small></td>'
                + '<td data-label="Cargo" class="py-2 pr-4">' + DP.esc(r.cargo || '—') + '</td>'
                + '<td data-label="Route" class="py-2 pr-4">' + DP.esc(r.route) + '</td>'
                + '<td data-label="Weight" class="py-2 pr-4">' + DP.esc(r.weight || '—') + '</td>'
                + '<td data-label="Received" class="py-2 pr-4 text-slate-500">' + DP.esc(r.created_at) + '</td>'
                + '<td data-label="Action" class="py-2 pr-4 text-right"><a href="' + r.detail_url + '" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"><i class="fas fa-eye"></i> View</a></td>'
                + '</tr>';
        }
    });
    var q = document.getElementById('f-q'), t;
    q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(function () { sc.reload(url()); }, 350); });
    ['from', 'to'].forEach(function (k) { document.getElementById('f-' + k).addEventListener('change', function () { sc.reload(url()); }); });
})();
</script>
@endpush
