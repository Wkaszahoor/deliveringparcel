@extends('layouts.tailwind.app')

@section('title', 'Users Management')
@section('page_title', 'Users')
@section('page_subtitle', 'Clients and admins — profile, roles and account access')

{{-- window.DP (infinite scroll + lazy avatar images) is only loaded by the old AdminLTE
     layouts; this page relies on DP.infiniteScroll for its table (which also calls
     DP.lazyImages internally for the .dp-lazy avatar thumbnails), so dp-lazy.js must be
     pulled in explicitly here — the new Tailwind layout never loads it globally. Pushed via
     @push('admin_scripts') so it loads AFTER the layout's jQuery <script> tag (layout yields
     content, then jQuery, then @stack('admin_scripts')). select2 (loaded by the original
     AdminLTE page) is dropped here: neither filter select in the original page's JS ever
     called .select2() or used a dp-select2 class, so it was dead weight, not functionality. --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

@if (session('success'))
    <x-admin.alert type="success">{{ session('success') }}</x-admin.alert>
@endif
@if (session('error'))
    <x-admin.alert type="error">{{ session('error') }}</x-admin.alert>
@endif

{{-- ============ KPI row ============ --}}
<div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <x-admin.card>
        <div class="text-xs text-slate-500">Matching</div>
        <div class="text-lg font-semibold text-slate-900" id="kpiTotal">{{ number_format($stats['total']) }}</div>
    </x-admin.card>
    <x-admin.card>
        <div class="text-xs text-slate-500">All users</div>
        <div class="text-lg font-semibold text-slate-900">{{ number_format($stats['total']) }}</div>
    </x-admin.card>
    <x-admin.card>
        <div class="text-xs text-slate-500">Admins</div>
        <div class="text-lg font-semibold text-slate-900">{{ number_format($stats['admins']) }}</div>
    </x-admin.card>
    <x-admin.card>
        <div class="text-xs text-slate-500">Blocked</div>
        <div class="text-lg font-semibold text-slate-900">{{ number_format($stats['blocked']) }}</div>
    </x-admin.card>
</div>

{{-- ============ Toolbar ============ --}}
<x-admin.card class="mb-4">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fQ">Search</label>
            <input type="search" id="fQ" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm"
                   placeholder="Name, email or phone&hellip;">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fRole">Role</label>
            <select id="fRole" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All roles</option>
                @foreach ($roleOptions as $slug => $label)
                    <option value="{{ $slug }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fStatus">Status</label>
            <select id="fStatus" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600" for="fSort">Sort</label>
            <select id="fSort" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="newest">Newest first</option>
                <option value="oldest">Oldest first</option>
                <option value="name">Name A&ndash;Z</option>
                <option value="orders_desc">Most orders</option>
            </select>
        </div>
    </div>
    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
        <span class="text-sm text-slate-500" id="dpListMeta">Loading&hellip;</span>
        <button type="button" id="btnReset"
                class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100">
            <i class="fas fa-undo"></i> Reset filters
        </button>
    </div>
</x-admin.card>

{{-- ============ Users table ============ --}}
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">User</th>
                    <th class="py-2 pr-4">Email</th>
                    <th class="py-2 pr-4">Phone</th>
                    <th class="py-2 pr-4">Roles</th>
                    <th class="py-2 pr-4">Orders</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Joined</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="tbody" class="divide-y divide-slate-100">
                <tr><td colspan="8" class="py-6 text-center text-slate-400">Loading users&hellip;</td></tr>
            </tbody>
        </table>
    </div>
    <div class="py-3 text-center text-xs text-slate-400" id="scrollHint">Scroll for more&hellip;</div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var DATA_URL  = '{{ route('admin.users.data') }}';
    var SHOW_URL  = '{{ route('admin.users.show', ['id' => ':ID']) }}';
    var EDIT_URL  = '{{ route('admin.users.edit', ['id' => ':ID']) }}';
    var AVATAR_BG = '{{ asset('uploads/profile/') }}/';

    var state = { q: '', role: '', status: '', sort: 'newest' };

    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    function avatarHtml(v) {
        if (v.avatar) {
            var src = /^https?:/.test(v.avatar) ? v.avatar : AVATAR_BG + encodeURIComponent(v.avatar);
            return '<img class="dp-lazy mr-2 inline-block h-9 w-9 rounded-full object-cover align-middle" data-src="' + esc(src) + '" alt="">';
        }
        return '<span class="mr-2 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 align-middle text-sm font-semibold text-slate-600">' + esc((v.name || '?').charAt(0).toUpperCase()) + '</span>';
    }
    function rolesBadges(rolesCsv) {
        if (!rolesCsv) return '<span class="text-slate-400">&mdash;</span>';
        return String(rolesCsv).split(',').map(function (r) {
            var slug = r.trim();
            if (!slug) return '';
            var cls = slug === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700';
            return '<span class="mr-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + cls + '">' + esc(slug) + '</span>';
        }).join(' ');
    }
    function statusBadge(v) {
        if (v.status === 'blocked') return '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Blocked</span>';
        return '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Active</span>';
    }
    function fmtDate(v) {
        if (!v) return '&mdash;';
        return window.moment ? moment(v).format('DD MMM YYYY') : String(v).slice(0, 10);
    }

    function render(v) {
        var show = SHOW_URL.replace(':ID', v.id);
        var edit = EDIT_URL.replace(':ID', v.id);
        return '<tr>' +
            '<td data-label="User" class="py-2 pr-4">' + avatarHtml(v) + '<strong class="align-middle text-slate-900">' + esc(v.name) + '</strong><br><small class="text-slate-500">id ' + v.id + (v.type ? ' &middot; ' + esc(v.type) : '') + '</small></td>' +
            '<td data-label="Email" class="py-2 pr-4">' + esc(v.email) + '</td>' +
            '<td data-label="Phone" class="py-2 pr-4">' + esc(v.number) + '</td>' +
            '<td data-label="Roles" class="py-2 pr-4">' + rolesBadges(v.roles_csv) + '</td>' +
            '<td data-label="Orders" class="py-2 pr-4">' + parseInt(v.orders_count || 0, 10) + '</td>' +
            '<td data-label="Status" class="py-2 pr-4">' + statusBadge(v) + '</td>' +
            '<td data-label="Joined" class="py-2 pr-4">' + fmtDate(v.created_at) + '</td>' +
            '<td data-label="Actions" class="py-2 pr-4 text-right whitespace-nowrap">' +
                '<a href="' + show + '" class="mr-1 inline-flex items-center rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" title="View profile"><i class="fas fa-eye"></i></a>' +
                '<a href="' + edit + '" class="inline-flex items-center rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100" title="Edit"><i class="fas fa-edit"></i></a>' +
            '</td>' +
        '</tr>';
    }

    function buildUrl(page) {
        var p = new URLSearchParams();
        p.set('page', page || 1);
        if (state.q)      p.set('q', state.q);
        if (state.role)   p.set('role', state.role);
        if (state.status) p.set('status', state.status);
        if (state.sort)   p.set('sort', state.sort);
        return DATA_URL + '?' + p.toString();
    }

    function refresh() {
        window.__dpUsersList = DP.infiniteScroll({ url: buildUrl(1), target: '#tbody', render: render });
        fetch(buildUrl(1), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                document.getElementById('kpiTotal').textContent = (json.total || 0).toLocaleString();
                document.getElementById('dpListMeta').textContent =
                    (json.total || 0).toLocaleString() + ' user(s) match — page 1 of ' + (json.last_page || 1);
            })
            .catch(function () {});
    }

    var qTimer = null;
    document.getElementById('fQ').addEventListener('input', function () {
        clearTimeout(qTimer);
        qTimer = setTimeout(function () { state.q = document.getElementById('fQ').value.trim(); refresh(); }, 350);
    });
    ['fRole', 'fStatus', 'fSort'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', function () {
            state.role   = document.getElementById('fRole').value;
            state.status = document.getElementById('fStatus').value;
            state.sort   = document.getElementById('fSort').value;
            refresh();
        });
    });
    document.getElementById('btnReset').addEventListener('click', function () {
        state = { q: '', role: '', status: '', sort: 'newest' };
        document.getElementById('fQ').value = '';
        document.getElementById('fRole').value = '';
        document.getElementById('fStatus').value = '';
        document.getElementById('fSort').value = 'newest';
        refresh();
    });

    refresh();
})();
</script>
@endpush
