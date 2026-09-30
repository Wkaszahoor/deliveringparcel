@extends('layouts.tailwind.app')

@section('title', 'Notification Preferences')
@section('page_title', 'Notification Preferences')
@section('page_subtitle', 'Per-type toggles for in-app and email channels')

@section('content')
    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Event type</th>
                        <th class="py-2 pr-4 text-center">In-app</th>
                        <th class="py-2 pr-4 text-center">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach ($matrix as $key => $m)
                    <tr>
                        <td class="py-2 pr-4">{{ $m['label'] }}</td>
                        <td class="py-2 pr-4 text-center">
                            <input type="checkbox" class="dp-pref h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand" id="p-inapp-{{ $key }}" data-channel="in_app" data-type="{{ $key }}" {{ $m['in_app'] ? 'checked' : '' }}>
                        </td>
                        <td class="py-2 pr-4 text-center">
                            <input type="checkbox" class="dp-pref h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand" id="p-email-{{ $key }}" data-channel="email" data-type="{{ $key }}" {{ $m['email'] ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@push('admin_scripts')
    {{-- window.DP (DP.request/DP.toast) is only loaded by the old AdminLTE layouts; this page's
         inline script relies on both, so toastr and dp-lazy.js must be pulled in explicitly here. --}}
    <script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
    <script>
    document.querySelectorAll('.dp-pref').forEach(function (el) {
        el.addEventListener('change', function () {
            DP.request(@json(route('admin.notifications.pref')), 'POST', {
                channel: el.dataset.channel,
                type: el.dataset.type,
                is_enabled: el.checked
            }).then(function (r) { return r.json(); }).then(function (res) {
                (res.ok ? DP.toast.success : DP.toast.error)(res.ok ? 'Saved' : 'Failed');
            });
        });
    });
    </script>
@endpush
