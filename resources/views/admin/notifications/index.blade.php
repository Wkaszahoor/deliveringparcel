@extends('layouts.tailwind.app')

@section('title', 'Notifications')
@section('page_title', 'Notification Center')
@section('page_subtitle', 'Your admin notifications — unread first, lazy loaded')

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-1.5 text-sm text-slate-600">
            <input type="checkbox" id="f-unread" class="rounded border-slate-300 text-brand focus:ring-brand">
            Unread only
        </label>
        <x-admin.badge color="red" id="dp-unread-count">…</x-admin.badge>
        <div class="ml-auto flex gap-2">
            <x-admin.button type="button" variant="secondary" id="dp-read-all"><i class="fas fa-check-double"></i> Mark all read</x-admin.button>
            <x-admin.button tag="a" variant="secondary" :href="route('admin.notifications.preferences')"><i class="fas fa-sliders-h"></i> Preferences</x-admin.button>
        </div>
    </div>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="w-9 py-2"></th>
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4">Message</th>
                        <th class="py-2 pr-4">When</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="dp-tbody" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@push('admin_scripts')
    {{-- window.DP (infinite scroll + toast helpers) is only loaded by the old AdminLTE layouts;
         this page relies on DP.infiniteScroll for its table and DP.toast/DP.request for feedback,
         so both toastr and dp-lazy.js must be pulled in explicitly here. --}}
    <script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
    <script>
    (function () {
        var base = @json(route('admin.notifications.data'));
        var unreadUrl = @json(route('admin.notifications.unread'));

        function refreshCount() {
            fetch(unreadUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    var el = document.getElementById('dp-unread-count');
                    el.textContent = res.count;
                    el.style.display = res.count > 0 ? 'inline-flex' : 'none';
                });
        }
        refreshCount();

        function url() {
            var u = new URL(base);
            if (document.getElementById('f-unread').checked) u.searchParams.set('unread', '1');
            return u.toString();
        }

        function badgeClasses(color) {
            var map = {
                slate: 'bg-slate-100 text-slate-700',
                blue: 'bg-blue-100 text-blue-700',
                brand: 'bg-blue-100 text-blue-700',
            };
            return 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + (map[color] || map.slate);
        }

        var sc = DP.infiniteScroll({
            url: url(), target: '#dp-tbody',
            render: function (n) {
                var msg = n.data && (n.data.message || n.data.title) ? (n.data.message || n.data.title) : JSON.stringify(n.data).slice(0, 120);
                return '<tr class="' + (n.read ? '' : 'font-semibold text-slate-900') + '">'
                    + '<td class="py-2 pr-4">' + (n.read ? '<i class="far fa-envelope-open text-slate-400"></i>' : '<i class="fas fa-envelope text-brand"></i>') + '</td>'
                    + '<td class="py-2 pr-4"><span class="' + badgeClasses('blue') + '">' + DP.esc(n.type) + '</span></td>'
                    + '<td class="py-2 pr-4" style="max-width:420px">' + DP.esc(msg) + '</td>'
                    + '<td class="py-2 pr-4 text-xs text-slate-500">' + DP.esc(n.at) + '</td>'
                    + '<td class="py-2 pr-4">' + (n.read ? '<span class="' + badgeClasses('slate') + '">Read</span>' : '<span class="' + badgeClasses('brand') + '">Unread</span>') + '</td>'
                    + '<td class="py-2 pr-4 text-right">' + (n.read ? '' : '<button class="rounded-md border border-brand px-2 py-1 text-xs font-medium text-brand hover:bg-brand-light dp-mark" data-id="' + n.id + '"><i class="fas fa-check"></i></button>') + '</td>'
                    + '</tr>';
            }
        });

        sc.target = document.getElementById('dp-tbody');
        sc.target.addEventListener('click', function (e) {
            var btn = e.target.closest('.dp-mark');
            if (!btn) return;
            DP.request(@json(route('admin.notifications.read', ':id')).replace(':id', btn.dataset.id), 'POST')
                .then(function () { DP.toast.success('Marked as read'); sc.reload(url()); refreshCount(); });
        });

        document.getElementById('f-unread').addEventListener('change', function () { sc.reload(url()); });
        document.getElementById('dp-read-all').addEventListener('click', function () {
            DP.request(@json(route('admin.notifications.read-all')), 'POST')
                .then(function () { DP.toast.success('All read'); sc.reload(url()); refreshCount(); });
        });
    })();
    </script>
@endpush
