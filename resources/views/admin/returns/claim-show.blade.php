@extends('layouts.tailwind.app')

@section('title', 'Claim #' . $claim->id)
@section('page_title', 'Claim #' . $claim->id)
@section('page_subtitle', config('admin_quotes.claim_types.' . $claim->type, $claim->type))

{{-- data-dp-confirm on the update button below is bound by DP.bindConfirms(), which is only
     loaded by the old AdminLTE layouts and fires the confirm dialog via SweetAlert2 (Swal.fire).
     dp-lazy.js auto-binds on its own DOMContentLoaded listener, so pushing it here is enough —
     both must be pulled in explicitly since the new Tailwind layout never loads them globally.
     Pushed via @push('admin_scripts') so they load AFTER the layout's jQuery <script> tag. --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

@php
    // Bootstrap badge class (from config/admin_quotes.php) → Tailwind pill classes.
    $badgeClasses = fn (string $bootstrap) => [
        'bg-secondary' => 'bg-slate-100 text-slate-700',
        'bg-primary'   => 'bg-blue-100 text-blue-700',
        'bg-info'      => 'bg-cyan-100 text-cyan-700',
        'bg-success'   => 'bg-green-100 text-green-700',
        'bg-danger'    => 'bg-red-100 text-red-700',
        'bg-warning'   => 'bg-yellow-100 text-yellow-800',
    ][$bootstrap] ?? 'bg-slate-100 text-slate-700';
    $m = $statusMap[$claim->status] ?? [];
    $next = $statusMap[$claim->status]['next'] ?? [];
@endphp

<div class="mb-4">
    <a href="{{ route('admin.returns.claims') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    <div class="lg:col-span-5">
        <x-admin.card title="Details">
            <table class="w-full text-sm">
                <tr><td class="w-32 py-1 pr-2 align-top text-slate-500">User</td><td class="py-1">{{ optional($claim->user)->name ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Type</td><td class="py-1">{{ config('admin_quotes.claim_types.' . $claim->type, $claim->type) }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Amount claimed</td><td class="py-1">${{ number_format((float) $claim->amount_claimed, 2) }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Payout</td><td class="py-1">{{ $claim->payout !== null ? '$' . number_format((float) $claim->payout, 2) : '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Created</td><td class="py-1">{{ optional($claim->created_at)->format('M d, Y H:i') }}</td></tr>
                <tr>
                    <td class="py-1 pr-2 align-top text-slate-500">Status</td>
                    <td class="py-1">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses($m['color'] ?? 'bg-secondary') }}">{{ $m['label'] ?? $claim->status }}</span>
                    </td>
                </tr>
            </table>

            @if ($claim->description)
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <h4 class="mb-1 text-xs font-semibold uppercase text-slate-500">Description</h4>
                    <p class="text-sm text-slate-700">{!! nl2br(e($claim->description)) !!}</p>
                </div>
            @endif
        </x-admin.card>

        @if (!empty($claim->evidence))
            <x-admin.card title="Evidence" class="mt-4">
                <div class="flex flex-wrap gap-2">
                    @foreach ($claim->evidence as $path)
                        <a href="{{ asset($path) }}" target="_blank" rel="noopener">
                            <img src="{{ asset($path) }}" class="h-20 w-20 rounded object-cover" alt="evidence">
                        </a>
                    @endforeach
                </div>
            </x-admin.card>
        @endif
    </div>

    <div class="lg:col-span-7">
        @if ($next)
            <x-admin.card title="Update claim">
                <form method="POST" action="{{ route('admin.returns.claims-update', $claim->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600" for="claim-status">New status</label>
                        <select id="claim-status" name="status" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm" required>
                            @foreach ($next as $n)
                                <option value="{{ $n }}">{{ $statusMap[$n]['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600" for="claim-payout">Payout amount ($)</label>
                        <input type="number" step="0.01" min="0" id="claim-payout" name="payout" value="{{ $claim->payout }}" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                    <x-admin.button type="submit" class="w-full justify-center" data-dp-confirm data-title="Update claim?">Update</x-admin.button>
                </form>
            </x-admin.card>
        @else
            <x-admin.alert type="info">This claim is closed.</x-admin.alert>
        @endif
    </div>
</div>

@endsection
