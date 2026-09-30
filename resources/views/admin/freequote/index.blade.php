@extends('layouts.tailwind.app')

@section('title', 'Requested Quotes')
@section('page_title', 'Orders & Quotes')

{{-- No Bootstrap CSS is loaded by the Tailwind layout, so the DataTables *-bs4 integration
     builds (which delegate pagination/length/search styling to Bootstrap classes like
     .page-item/.page-link/.form-control) render unstyled. The stock (non-bootstrap) DataTables
     build emits plain .paginate_button/.dataTables_filter markup instead, which is what
     resources/css/vendor-overrides.css already targets. --}}

@section('content')
    <x-admin.card title="Clients Orders" class="mb-6">
        <div class="overflow-x-auto">
            <table id="example1" class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Order ID</th>
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Ship From</th>
                        <th class="py-2 pr-4">Ship To</th>
                        <th class="py-2 pr-4">Net Total</th>
                        <th class="py-2 pr-4">Created</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Actions</th>
                    </tr>
                </thead>
                {{-- Rows are fetched via DataTables server-side AJAX (see @push('admin_scripts')
                     below) — nothing is pre-rendered here, so large order counts no longer ship
                     the whole dataset on page load. --}}
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </x-admin.card>

<x-admin.card>
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-800">Requested Quotes <span class="text-slate-400">({{ $freequote->total() }} total)</span></h3>
    </div>
    <form method="GET" action="{{ route('freequote.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <input type="text" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Name contains…" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="email" value="{{ $filters['email'] ?? '' }}" placeholder="user@example.com" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="country" value="{{ $filters['country'] ?? '' }}" placeholder="Country contains…" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Any field…" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <x-admin.button type="submit"><i class="fas fa-filter"></i> Filter</x-admin.button>
        <x-admin.button variant="secondary" tag="a" :href="route('freequote.index')">Reset</x-admin.button>
    </form>

    @if ($freequote->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">#</th>
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Number</th>
                        <th class="py-2 pr-4">Cargo Type</th>
                        <th class="py-2 pr-4">Country</th>
                        <th class="py-2 pr-4">Destination</th>
                        <th class="py-2 pr-4">Requested</th>
                        <th class="py-2 pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($freequote as $quote)
                        <tr>
                            <td class="py-2 pr-4">{{ $quote->id }}</td>
                            <td class="py-2 pr-4">{{ $quote->name }}</td>
                            <td class="py-2 pr-4">{{ $quote->email }}</td>
                            <td class="py-2 pr-4">{{ $quote->number }}</td>
                            <td class="py-2 pr-4">{{ $quote->cargotype }}</td>
                            <td class="py-2 pr-4">{{ $quote->country }}</td>
                            <td class="py-2 pr-4">{{ $quote->destination }}</td>
                            <td class="py-2 pr-4">{{ $quote->created_at ? \Illuminate\Support\Carbon::parse($quote->created_at)->format('d M Y H:i') : '—' }}</td>
                            <td class="py-2 pr-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('freequote.show', $quote->id) }}" class="text-slate-400 hover:text-brand" title="View"><i class="fas fa-eye"></i></a>
                                    @include('admin.freequote.delete_modal')
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $freequote->links('pagination.tailwind-admin') }}
        </div>
    @else
        <p class="text-center text-slate-400">No quote submissions found.</p>
    @endif
</x-admin.card>

@endsection

@push('admin_scripts')
<script src="{{ url('dashbord/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script>
{{--
    The Clients Orders table above uses an explicit local DataTable() init (rather than the
    generic `data-dp2-datatable` auto-init) because the original page pinned a default sort
    order — column index 6 ("Created at") descending, so most-recent orders show first. The
    auto-init in resources/js/app.js only passes { responsive: true, autoWidth: false } with no
    ordering option, which would silently change the default row order (a real regression, not
    a cosmetic one) if used here. The original's `'order': [6, 'dec']` used an invalid DataTables
    direction ('dec' is not a valid value — only 'asc'/'desc' are); 'desc' is used below to
    preserve the evident intent (newest first) rather than silently dropping the sort.

    This script (and the plugin <script>/<link> tags above) run via @push('admin_scripts') /
    @push('admin_styles') so they load AFTER the layout's jQuery <script> tag — the layout yields
    page content, THEN loads jQuery, THEN renders @stack('admin_scripts'). An inline script placed
    directly in @section('content') would execute before jQuery exists and throw "$ is not defined".

    Converted to true server-side processing (serverSide: true + ajax) — the full `orders` table
    is no longer rendered into the DOM on page load; DataTables now fetches one page of rows at a
    time from freequote.orders.data, which speaks DataTables' standard server-side request/response
    contract (draw/start/length/search/order in, draw/recordsTotal/recordsFiltered/data out). Status
    badges are built client-side from status_label/status_color using the same badgeClasses()
    mapping pattern already used in admin/contacts/index.blade.php for AJAX-rendered badges.
--}}
    $(function () {
        function esc(v) {
            return String(v == null ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function badgeClasses(color) {
            var map = {
                slate: 'bg-slate-100 text-slate-700',
                green: 'bg-green-100 text-green-700',
                blue: 'bg-blue-100 text-blue-700',
                red: 'bg-red-100 text-red-700',
                yellow: 'bg-yellow-100 text-yellow-800',
            };
            return 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + (map[color] || map.slate);
        }

        $('#example1').DataTable({
            responsive: true,
            autoWidth: false,
            info: true,
            ordering: true,
            processing: true,
            serverSide: true,
            order: [[6, 'desc']],
            ajax: {
                url: @json(route('freequote.orders.data')),
                type: 'GET'
            },
            columns: [
                {
                    data: null,
                    render: function (row) {
                        return '<a href="' + esc(row.show_url) + '" class="text-brand hover:underline">' + esc(row.order_id) + '</a>';
                    }
                },
                { data: 'name', render: esc },
                { data: 'email', render: esc },
                { data: 'shipfrom', render: esc },
                { data: 'shipto', render: esc },
                { data: 'total', render: function (v) { return '$' + esc(v); } },
                { data: 'created_at', render: esc },
                {
                    data: null,
                    orderable: false,
                    render: function (row) {
                        return '<span class="' + badgeClasses(row.status_color) + '">' + esc(row.status_label) + '</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (row) {
                        return '<a href="' + esc(row.show_url) + '" class="text-slate-400 hover:text-brand"><i class="fas fa-history"></i></a>';
                    }
                }
            ]
        });
    });
</script>
@endpush
