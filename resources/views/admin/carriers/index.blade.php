@extends('layouts.tailwind.app')

@section('title', 'Carriers')
@section('page_title', 'Carrier Integrations')
@section('page_subtitle', '7 adapters — mock mode active offline; secrets via env only')

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
    @php
        // Bootstrap variant -> Tailwind icon-tile background, matching config/admin_carriers.php `color` values.
        $iconBg = [
            'primary' => 'bg-brand',
            'secondary' => 'bg-slate-500',
            'success' => 'bg-green-600',
            'danger' => 'bg-red-600',
            'warning' => 'bg-yellow-500',
            'info' => 'bg-blue-400',
            'dark' => 'bg-slate-800',
        ];
    @endphp
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($cards as $c)
            <x-admin.card class="dp-carrier-card flex h-full flex-col">
                <div class="mb-2 flex items-center">
                    <span class="mr-2 flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg text-white {{ $iconBg[$c['color']] ?? 'bg-slate-500' }}">
                        <i class="fas {{ $c['icon'] }}"></i>
                    </span>
                    <div>
                        <h5 class="mb-0 text-sm font-semibold text-slate-800">{{ $c['name'] }}</h5>
                        <small class="text-xs text-slate-400">{{ $c['code'] }}</small>
                    </div>
                    <x-admin.badge class="ml-auto" :color="$c['configured'] ? 'green' : 'slate'">
                        {{ $c['configured'] ? 'Configured' : 'Not set' }}
                    </x-admin.badge>
                </div>
                <p class="mb-2 text-xs text-slate-500">
                    @foreach ($c['env_keys'] as $key)
                        <code>{{ $key }}</code>@if (!$loop->last), @endif
                    @endforeach
                    <br>Enabled: <strong>{{ $c['enabled'] ? 'yes' : 'no' }}</strong>
                    @if ($c['last_check'])<br>Last check: <span class="dp-last-check">{{ $c['last_check'] }}</span>@endif
                </p>
                <div class="mt-auto flex gap-2">
                    <button type="button" class="dp-ping flex-1 rounded-md border border-brand px-3 py-1.5 text-sm font-medium text-brand hover:bg-brand-light" data-url="{{ $c['urls']['ping'] }}">Test</button>
                    <form method="POST" action="{{ $c['urls']['toggle'] }}">
                        @csrf
                        <button type="submit" class="rounded-md border border-yellow-400 px-3 py-1.5 text-sm font-medium text-yellow-700 hover:bg-yellow-50">{{ $c['enabled'] ? 'Disable' : 'Enable' }}</button>
                    </form>
                </div>
            </x-admin.card>
        @endforeach
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.carriers.parse') }}" class="rounded-md border border-brand px-3 py-1.5 text-sm font-medium text-brand hover:bg-brand-light"><i class="fas fa-barcode"></i> Parse tracking number</a>
        <a href="{{ route('admin.carriers.track') }}" class="rounded-md border border-brand px-3 py-1.5 text-sm font-medium text-brand hover:bg-brand-light"><i class="fas fa-map-marked-alt"></i> Track a shipment</a>
        <a href="{{ route('admin.carriers.history') }}" class="ml-auto rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50"><i class="fas fa-history"></i> Lookup history</a>
    </div>
@endsection

@push('admin_scripts')
{{-- window.DP (btnLoading/toast helpers) and toastr are only loaded by the old AdminLTE layouts;
     this page's ping handler relies on both, so they must be pulled in explicitly here. --}}
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
document.querySelectorAll('.dp-ping').forEach(function (btn) {
    btn.addEventListener('click', function () {
        DP.btnLoading(btn, true);
        fetch(btn.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                DP.btnLoading(btn, false);
                DP.toast.info((res.message || 'Done') + ' (' + (res.mode || '?') + ')');
                var card = btn.closest('.dp-carrier-card');
                var lastCheck = card.querySelector('.dp-last-check');
                if (lastCheck) {
                    lastCheck.textContent = 'just now';
                } else {
                    card.querySelector('p').innerHTML += '<br>Checked just now';
                }
            })
            .catch(function () { DP.btnLoading(btn, false); DP.toast.error('Request failed'); });
    });
});
</script>
@endpush
