@extends('layouts.tailwind.app')

@section('title', 'User Details')
@section('page_title', 'User Details')
@section('page_subtitle', $user->name)

{{-- window.DP (infinite scroll + lazy images + confirm-dialog + toast helpers), toastr and
     SweetAlert2 are only loaded by the old AdminLTE layouts; this page relies on
     DP.infiniteScroll for the order-history table, DP.request/DP.toast for the "Send reset
     link" button, and DP.bindConfirms (backs the "Set password" data-dp-confirm button via
     Swal), so all three must be pulled in explicitly here — the new Tailwind layout never
     loads them globally. Pushed via @push('admin_scripts') so they load AFTER the layout's
     jQuery <script> tag (layout yields content, then jQuery, then @stack('admin_scripts')). --}}
@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
</div>

@if (session('success'))
    <x-admin.alert type="success">{{ session('success') }}</x-admin.alert>
@endif
@if (session('error'))
    <x-admin.alert type="error">{{ session('error') }}</x-admin.alert>
@endif

<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <x-admin.card>
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-full bg-brand text-lg font-bold text-white">
                    {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-base font-semibold text-slate-900">{{ $user->name }}</h2>
                    <small class="block truncate text-slate-500">{{ $user->email }}</small>
                </div>
            </div>
            <table class="w-full text-sm">
                <tr><th class="w-2/5 py-1 pr-2 text-left align-top font-medium text-slate-500">Phone</th><td class="py-1">{{ $user->number ?: '—' }}</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Status</th><td class="py-1"><x-admin.badge :color="($user->status ?? 'active') === 'blocked' ? 'red' : 'green'">{{ $statusOptions[$user->status ?? 'active'] ?? ucfirst($user->status ?? 'active') }}</x-admin.badge></td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Roles</th><td class="py-1">@foreach ($roles as $r)<x-admin.badge color="blue" class="mr-1">{{ $r->name }}</x-admin.badge>@endforeach</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Joined</th><td class="py-1">{{ optional($user->created_at)->format('M d, Y') }}</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">User ID</th><td class="py-1">#{{ $user->id }}</td></tr>
            </table>
            <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-dark">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @unless ($isSelf)
                    <button type="button" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-md border border-brand px-3 py-1.5 text-sm font-medium text-brand hover:bg-brand-light" id="dp-reset-link" data-url="{{ route('admin.users.resetLink', $user->id) }}">
                        <i class="fas fa-envelope"></i> Send reset link
                    </button>
                @endunless
            </div>
        </x-admin.card>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <x-admin.card>
                <div class="mb-1 flex h-8 w-8 items-center justify-center rounded-full bg-cyan-100 text-cyan-600"><i class="fas fa-box"></i></div>
                <div class="text-xs text-slate-500">Orders</div>
                <div class="text-lg font-semibold text-slate-900">{{ number_format($ordersCount) }}</div>
            </x-admin.card>
            <x-admin.card>
                <div class="mb-1 flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600"><i class="fas fa-dollar-sign"></i></div>
                <div class="text-xs text-slate-500">Total Spent</div>
                <div class="text-lg font-semibold text-slate-900">${{ number_format($totalSpent, 2) }}</div>
            </x-admin.card>
        </div>

        @unless ($isSelf)
        <x-admin.card class="mt-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-key mr-1 text-slate-400"></i> Set new password</h3>
            <form method="POST" action="{{ route('admin.users.setPassword', $user->id) }}">
                @csrf
                <div class="mb-3">
                    <label for="setpw-password" class="mb-1 block text-xs font-medium text-slate-600">New password</label>
                    <input type="password" id="setpw-password" name="password" autocomplete="new-password" class="block w-full rounded-md border px-3 py-1.5 text-sm {{ $errors->has('password') ? 'border-red-400' : 'border-slate-300' }}" required minlength="8">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mb-3">
                    <label for="setpw-confirm" class="mb-1 block text-xs font-medium text-slate-600">Confirm password</label>
                    <input type="password" id="setpw-confirm" name="password_confirmation" autocomplete="new-password" class="block w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm" required minlength="8">
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-md bg-amber-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-600"
                        data-dp-confirm data-title="Set new password?" data-text="The user will sign in with the new password immediately.">
                    Set password
                </button>
            </form>
        </x-admin.card>
        @endunless
    </div>

    <div class="lg:col-span-2">
        <x-admin.card class="mb-3">
            <div class="flex flex-wrap items-center gap-3">
                <input id="f-q" name="q" class="w-full max-w-[260px] rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Search order id / tracking…">
                <span class="ml-auto text-xs text-slate-500">Loaded on scroll</span>
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Order</th>
                            <th class="py-2 pr-4">Items</th>
                            <th class="py-2 pr-4">Total</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Tracking</th>
                            <th class="py-2 pr-4">Date</th>
                        </tr>
                    </thead>
                    <tbody id="dp-tbody" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </x-admin.card>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
(function () {
    var esc = DP.esc;
    var statusMap = @json($statusMap);
    var base = @json(route('admin.users.orders', $user->id));

    // Bootstrap badge class (from config/admin_orders.php) → Tailwind pill classes.
    // Same mapping convention as admin/ordersm/index.blade.php's badgeClasses() JS helper.
    var badgeClassMap = {
        'bg-secondary': 'bg-slate-100 text-slate-700',
        'bg-primary':   'bg-blue-100 text-blue-700',
        'bg-info':      'bg-cyan-100 text-cyan-700',
        'bg-teal':      'bg-teal-100 text-teal-700',
        'bg-success':   'bg-green-100 text-green-700',
        'bg-danger':    'bg-red-100 text-red-700',
        'bg-indigo':    'bg-indigo-100 text-indigo-700',
        'bg-orange':    'bg-orange-100 text-orange-700',
        'bg-purple':    'bg-purple-100 text-purple-700',
        'bg-warning':   'bg-yellow-100 text-yellow-800'
    };

    function badge(status) {
        var meta = statusMap[status] || { label: status || '—', badge: 'bg-secondary' };
        var cls = badgeClassMap[meta.badge] || badgeClassMap['bg-secondary'];
        return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + cls + '">' + esc(meta.label) + '</span>';
    }

    var sc = DP.infiniteScroll({
        url: base,
        target: '#dp-tbody',
        render: function (o) {
            var track = o.trackingid
                ? '<a href="' + esc(o.trackinglink || '#') + '" target="_blank" rel="noopener" class="text-brand hover:underline">' + esc(o.trackingid) + '</a>'
                : '<span class="text-slate-400">—</span>';
            return '<tr>'
                + '<td data-label="Order" class="py-2 pr-4"><strong class="text-slate-900">#' + esc(o.order_id) + '</strong></td>'
                + '<td data-label="Items" class="py-2 pr-4">' + esc(o.items_count) + '</td>'
                + '<td data-label="Total" class="py-2 pr-4">$' + esc(o.total) + '</td>'
                + '<td data-label="Status" class="py-2 pr-4">' + badge(o.order_status) + '</td>'
                + '<td data-label="Tracking" class="py-2 pr-4">' + track + '</td>'
                + '<td data-label="Date" class="py-2 pr-4 text-slate-500">' + esc(o.created_at) + '</td>'
                + '</tr>';
        }
    });

    var q = document.getElementById('f-q'), t;
    q.addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () {
            var u = new URL(base);
            if (q.value.trim()) u.searchParams.set('q', q.value.trim());
            sc.reload(u.toString());
        }, 350);
    });

    var resetBtn = document.getElementById('dp-reset-link');
    if (resetBtn) resetBtn.addEventListener('click', function () {
        DP.btnLoading(resetBtn, true);
        DP.request(resetBtn.dataset.url, 'POST').then(function (r) { return r.json(); }).then(function (res) {
            DP.btnLoading(resetBtn, false);
            (res.ok ? DP.toast.success : DP.toast.error)(res.message || 'Done');
        }).catch(function () { DP.btnLoading(resetBtn, false); DP.toast.error('Request failed'); });
    });

    DP.bindConfirms();
})();
</script>
@endpush
