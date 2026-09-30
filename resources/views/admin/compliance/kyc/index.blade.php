@extends('layouts.tailwind.app')

@section('title', 'KYC Verification')
@section('page_title', 'KYC Verification Queue')
@section('page_subtitle', 'Identity document review')

{{-- window.DP (infinite scroll helpers) and toastr are only loaded by the old AdminLTE layouts;
     this page relies on DP.infiniteScroll/DP.esc for its table, so both must be pulled in
     explicitly here — the new Tailwind layout never loads them globally. Pushed via
     @push('admin_scripts') so they load AFTER the layout's jQuery <script> tag (layout yields
     content, then jQuery, then @stack('admin_scripts')); an inline script directly in
     @section('content') would run before jQuery/these plugins exist. --}}
@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

<div class="mb-4 grid grid-cols-3 gap-3">
    <x-admin.card>
        <div class="flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100 text-yellow-600"><i class="fas fa-hourglass-half"></i></span>
            <div>
                <div class="text-lg font-semibold text-slate-900">{{ $kpis['pending'] }}</div>
                <div class="text-xs text-slate-500">Pending</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600"><i class="fas fa-check"></i></span>
            <div>
                <div class="text-lg font-semibold text-slate-900">{{ $kpis['approved'] }}</div>
                <div class="text-xs text-slate-500">Approved</div>
            </div>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600"><i class="fas fa-times"></i></span>
            <div>
                <div class="text-lg font-semibold text-slate-900">{{ $kpis['rejected'] }}</div>
                <div class="text-xs text-slate-500">Rejected</div>
            </div>
        </div>
    </x-admin.card>
</div>

<x-admin.card class="mb-4">
    <div class="flex flex-wrap items-center gap-2">
        <input id="f-q" type="text" placeholder="Search user / doc number…" class="w-full max-w-[260px] rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <select id="f-status" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">Status: all</option>
            @foreach ($statusMap as $key => $m)
                <option value="{{ $key }}">{{ $m['label'] }}</option>
            @endforeach
        </select>
        <div class="ml-auto flex gap-2">
            <x-admin.button tag="a" variant="secondary" :href="route('admin.compliance.sanctions')">Sanctions</x-admin.button>
            <x-admin.button tag="a" variant="secondary" :href="route('admin.compliance.gdpr')">GDPR</x-admin.button>
        </div>
    </div>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">User</th>
                    <th class="py-2 pr-4">Document</th>
                    <th class="py-2 pr-4">Country</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Submitted</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="dp-tbody-kyc" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <div class="dp-scroll-sentinel py-3 text-center text-xs text-slate-400"></div>
</x-admin.card>

@endsection

@push('admin_scripts')
<script>
(function () {
    var url = @json(route('admin.compliance.kyc.data'));

    var badgeMap = {
        'bg-secondary': 'bg-slate-100 text-slate-700',
        'bg-warning':   'bg-yellow-100 text-yellow-800',
        'bg-success':   'bg-green-100 text-green-700',
        'bg-danger':    'bg-red-100 text-red-700',
    };
    function badgeClasses(bootstrap) {
        return badgeMap[bootstrap] || badgeMap['bg-secondary'];
    }

    function buildUrl() {
        var u = new URL(url);
        var q = document.getElementById('f-q').value.trim();
        var status = document.getElementById('f-status').value;
        if (q) u.searchParams.set('q', q);
        if (status) u.searchParams.set('status', status);
        return u.toString();
    }

    function render(r) {
        return '<tr>' +
            '<td data-label="User" class="py-2 pr-4">' + DP.esc(r.user) + '<br><small class="text-slate-500">' + DP.esc(r.email) + '</small></td>' +
            '<td data-label="Document" class="py-2 pr-4">' + DP.esc(r.doc) + '</td>' +
            '<td data-label="Country" class="py-2 pr-4">' + DP.esc(r.country) + '</td>' +
            '<td data-label="Status" class="py-2 pr-4"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + badgeClasses(r.color) + '">' + DP.esc(r.status) + '</span></td>' +
            '<td data-label="Submitted" class="py-2 pr-4">' + DP.esc(r.at) + '</td>' +
            '<td data-label="Actions" class="py-2 pr-4 text-right whitespace-nowrap">' +
                '<a href="' + r.urls.show + '" class="inline-flex items-center rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" title="Review"><i class="fas fa-eye"></i></a>' +
            '</td>' +
        '</tr>';
    }

    var sc = DP.infiniteScroll({ url: buildUrl(), target: '#dp-tbody-kyc', render: render });

    var t;
    ['f-q', 'f-status'].forEach(function (id) {
        var el = document.getElementById(id);
        var ev = el.tagName === 'SELECT' ? 'change' : 'input';
        el.addEventListener(ev, function () {
            clearTimeout(t);
            t = setTimeout(function () { sc.reload(buildUrl()); }, 350);
        });
    });
})();
</script>
@endpush
