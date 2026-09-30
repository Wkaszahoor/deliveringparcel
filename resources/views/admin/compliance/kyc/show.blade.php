@extends('layouts.tailwind.app')

@section('title', 'KYC #' . $kyc->id)
@section('page_title', 'KYC Review #' . $kyc->id)
@section('page_subtitle', optional($kyc->user)->name)

@section('content')

<div class="mb-4">
    <x-admin.button tag="a" variant="secondary" :href="route('admin.compliance.kyc.index')">
        <i class="fas fa-arrow-left"></i> Back
    </x-admin.button>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    <div class="lg:col-span-5">
        <x-admin.card title="Applicant">
            @php $m = $statusMap[$kyc->status] ?? []; @endphp
            @php
                $badgeColorMap = [
                    'bg-secondary' => 'slate',
                    'bg-warning'   => 'yellow',
                    'bg-success'   => 'green',
                    'bg-danger'    => 'red',
                ];
            @endphp
            <table class="w-full text-sm">
                <tr><th class="w-2/5 py-1 pr-2 text-left align-top font-medium text-slate-500">User</th><td class="py-1">{{ optional($kyc->user)->name ?: '—' }}</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Email</th><td class="py-1">{{ optional($kyc->user)->email ?: '—' }}</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Document</th><td class="py-1">{{ config('admin_compliance.doc_types.' . $kyc->doc_type, $kyc->doc_type) }}</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Number</th><td class="py-1">{{ $kyc->doc_number ?: '—' }}</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Country</th><td class="py-1">{{ $kyc->doc_country ?: '—' }}</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Status</th><td class="py-1"><x-admin.badge :color="$badgeColorMap[$m['color'] ?? ''] ?? 'slate'">{{ $m['label'] ?? $kyc->status }}</x-admin.badge></td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Expires</th><td class="py-1">{{ optional($kyc->expires_at)->format('M d, Y') ?: '—' }}</td></tr>
                <tr><th class="py-1 pr-2 text-left align-top font-medium text-slate-500">Submitted</th><td class="py-1">{{ optional($kyc->created_at)->format('M d, Y H:i') }}</td></tr>
            </table>

            @if ($kyc->rejection_reason)
                <hr class="my-3 border-slate-100">
                <h6 class="mb-1 text-sm font-semibold text-red-600">Rejection reason</h6>
                <p class="mb-0 text-sm text-slate-700">{!! nl2br(e($kyc->rejection_reason)) !!}</p>
            @endif

            @if ($kyc->status === 'pending')
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <form method="POST" action="{{ route('admin.compliance.kyc.approve', $kyc->id) }}" class="mb-3">
                        @csrf
                        @method('PUT')
                        <label for="kyc-expires" class="mb-1 block text-xs font-medium text-slate-600">Expires at</label>
                        <div class="flex gap-2">
                            <input type="date" id="kyc-expires" name="expires_at" class="flex-1 rounded-md border border-slate-300 px-3 py-1.5 text-sm" value="{{ optional(now()->addYears(2))->format('Y-m-d') }}">
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.compliance.kyc.reject', $kyc->id) }}">
                        @csrf
                        @method('PUT')
                        <textarea name="rejection_reason" rows="2" class="mb-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" placeholder="Reason (required)" required></textarea>
                        <button type="submit" class="flex w-full items-center justify-center gap-1.5 rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </form>
                </div>
            @endif
        </x-admin.card>
    </div>

    <div class="lg:col-span-7">
        <x-admin.card title="Documents">
            <div class="flex flex-wrap gap-2">
                @if (empty($kyc->files))
                    <p class="mb-0 text-sm text-slate-400">No files attached.</p>
                @else
                    @foreach ($kyc->files as $path)
                        <a href="{{ asset($path) }}" target="_blank" rel="noopener">
                            <img src="{{ asset($path) }}" class="h-[140px] w-[140px] rounded-md border border-slate-200 object-cover" alt="doc">
                        </a>
                    @endforeach
                @endif
            </div>
        </x-admin.card>
    </div>
</div>

@endsection
