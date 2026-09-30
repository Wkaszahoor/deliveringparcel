@extends('layouts.tailwind.app')

@section('title', 'Parse Tracking Number')
@section('page_title', 'Tracking Number Parser')
@section('page_subtitle', 'Detect the carrier from a tracking number shape')

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-2xl">
        <x-admin.card>
            <div class="mb-3">
                <label for="cp-num" class="mb-1 block text-sm font-medium text-slate-700">Tracking number</label>
                <input id="cp-num" type="text" placeholder="e.g. 1Z999AA10123456784" maxlength="100"
                    class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
            </div>
            <button id="cp-go" class="flex w-full items-center justify-center gap-1.5 rounded-md bg-brand px-3 py-2 text-sm font-medium text-white hover:bg-brand-dark"><i class="fas fa-search"></i> Detect</button>
            <div id="cp-out" class="mt-3"></div>
        </x-admin.card>
    </div>
</div>
@endsection

@push('admin_scripts')
{{-- window.DP (btnLoading/toast/request/esc helpers) and toastr are only loaded by the old AdminLTE
     layouts; this page's click handler relies on all of them, so they must be pulled in explicitly here. --}}
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
(function () {
    var btn = document.getElementById('cp-go'), out = document.getElementById('cp-out');
    btn.addEventListener('click', function () {
        var num = document.getElementById('cp-num').value.trim();
        if (!num) { DP.toast.error('Enter a number'); return; }
        DP.btnLoading(btn, true);
        DP.request({{ json_encode(route('admin.carriers.parse.post')) }}, 'POST', { number: num })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                DP.btnLoading(btn, false);
                var html = '';
                (res.matches || []).forEach(function (m) {
                    html += '<div class="mb-1 flex items-center justify-between rounded-md border border-slate-200 p-2">'
                        + '<strong class="text-sm text-slate-800">' + DP.esc(m.carrier) + '</strong>'
                        + '<div class="h-4 w-[140px] overflow-hidden rounded-full bg-slate-100">'
                        + '<div class="flex h-4 items-center justify-center bg-brand text-[10px] leading-none text-white" style="width:' + m.confidence + '%">' + m.confidence + '%</div></div></div>';
                });
                out.innerHTML = html || '<div class="text-sm text-slate-400">No matches.</div>';
            })
            .catch(function () { DP.btnLoading(btn, false); DP.toast.error('Failed'); });
    });
})();
</script>
@endpush
