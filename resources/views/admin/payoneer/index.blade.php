@extends('layouts.tailwind.app')

@section('title', 'Payoneer Payments | Admin')
@section('page_title', 'Payoneer Payments')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <i class="fas fa-link text-brand"></i>
        <span class="text-sm text-slate-500">Link-generation inbox, proof verification and payment settings</span>
    </div>
    <div class="flex items-center gap-2">
        <x-admin.badge :color="$enabled ? 'green' : 'slate'">{{ $enabled ? 'Enabled' : 'Disabled' }}</x-admin.badge>
        <x-admin.badge :color="$proofRequired ? 'blue' : 'slate'">Proof {{ $proofRequired ? 'required' : 'optional' }}</x-admin.badge>
    </div>
</div>

@if (session('success'))
    <x-admin.alert type="success">{{ session('success') }}</x-admin.alert>
@endif
@if (session('error'))
    <x-admin.alert type="error">{{ session('error') }}</x-admin.alert>
@endif

{{-- ============ Settings ============ --}}
<x-admin.card title="Settings" class="mb-6">
    <form method="POST" action="{{ route('admin.payoneer.settings') }}">
        @csrf
        <div class="mb-3 flex items-start gap-2">
            <input type="checkbox" id="pw-enabled" name="payoneer_enabled" value="1" @if ($enabled) checked @endif
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
            <label for="pw-enabled" class="text-sm text-slate-700">Enable Payoneer link payments (shows the Payoneer option on client order pages)</label>
        </div>
        <div class="mb-3 flex items-start gap-2">
            <input type="checkbox" id="pw-proof" name="payoneer_proof_required" value="1" @if ($proofRequired) checked @endif
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
            <label for="pw-proof" class="text-sm text-slate-700">Require payment screenshot proof from the customer</label>
        </div>
        <div class="mb-3">
            <label for="pw-instructions" class="mb-1 block text-sm font-medium text-slate-700">Customer instructions (shown on the Payoneer payment page)</label>
            <textarea id="pw-instructions" name="payoneer_instructions" rows="2" maxlength="2000"
                class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">{{ $instructions }}</textarea>
        </div>
        <x-admin.button type="submit">Save settings</x-admin.button>
    </form>
</x-admin.card>

{{-- ============ Requests list ============ --}}
<x-admin.card>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-800"><i class="fas fa-inbox mr-1"></i> Payment requests</h3>
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('admin.payoneer.index') }}"
                class="rounded-md px-2.5 py-1 text-xs font-medium {{ is_null($filter) ? 'bg-brand text-white' : 'border border-slate-300 text-slate-600 hover:bg-slate-50' }}">All</a>
            @foreach (['requested' => 'Waiting link', 'link_sent' => 'Link sent', 'proof_submitted' => 'Proof submitted', 'marked_paid' => 'Marked paid', 'verified' => 'Verified'] as $value => $label)
                <a href="{{ route('admin.payoneer.index', ['status' => $value]) }}"
                    class="rounded-md px-2.5 py-1 text-xs font-medium {{ $filter === $value ? 'bg-brand text-white' : 'border border-slate-300 text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">#</th>
                    <th class="py-2 pr-4">Order</th>
                    <th class="py-2 pr-4">Customer</th>
                    <th class="py-2 pr-4">Amount</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Proof</th>
                    <th class="py-2 pr-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($requests as $r)
                <tr>
                    <td class="py-2 pr-4">{{ $r->id }}</td>
                    <td class="py-2 pr-4">
                        <a href="{{ url('/order/' . $r->order_id) }}" class="text-brand hover:underline">#{{ $r->order ? $r->order->order_id : $r->order_id }}</a>
                        <div class="text-xs text-slate-400">{{ optional($r->requested_at)->format('d M, h:i A') }}</div>
                    </td>
                    <td class="py-2 pr-4">
                        {{ optional($r->user)->name }}
                        <div class="text-xs text-slate-400">{{ optional($r->user)->email }}</div>
                    </td>
                    <td class="py-2 pr-4">{{ $r->currency }} {{ number_format($r->amount, 2) }}</td>
                    <td class="py-2 pr-4">
                        @if ($r->status === 'requested')
                            <x-admin.badge color="yellow">Waiting link</x-admin.badge>
                        @elseif ($r->status === 'link_sent')
                            <x-admin.badge color="blue">Link sent</x-admin.badge>
                        @elseif ($r->status === 'proof_submitted')
                            <x-admin.badge color="blue">Proof submitted</x-admin.badge>
                        @elseif ($r->status === 'marked_paid')
                            <x-admin.badge color="blue">Marked paid</x-admin.badge>
                        @elseif ($r->status === 'verified')
                            <x-admin.badge color="green">Verified</x-admin.badge>
                        @elseif ($r->status === 'rejected')
                            <x-admin.badge color="red">Rejected</x-admin.badge>
                        @else
                            <x-admin.badge color="slate">Cancelled</x-admin.badge>
                        @endif
                        @if ($r->reject_reason)
                            <div class="mt-0.5 text-xs text-red-600">{{ $r->reject_reason }}</div>
                        @endif
                    </td>
                    <td class="py-2 pr-4">
                        @if ($r->payment && $r->payment->proof_path)
                            <a href="{{ route('admin.payments.proof', $r->payment) }}" target="_blank"
                                class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                <i class="fas fa-file-download"></i> View
                            </a>
                        @else
                            <span class="text-slate-400">&mdash;</span>
                        @endif
                    </td>
                    <td class="py-2 pr-4" style="min-width:320px;">
                        {{-- ACTION: generate + send link --}}
                        @if ($r->status === 'requested')
                            <form method="POST" action="{{ route('admin.payoneer.link', $r) }}" class="flex flex-wrap items-center gap-1.5">
                                @csrf
                                <input type="url" name="link" placeholder="Paste Payoneer payment link" required
                                    class="min-w-[200px] rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                <x-admin.button type="submit" class="!px-2.5 !py-1 !text-xs"><i class="fas fa-link"></i> Send link</x-admin.button>
                                <button type="submit" formaction="{{ route('admin.payoneer.cancel', $r) }}"
                                    class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Decline</button>
                            </form>

                        {{-- ACTION: verify / reject proof --}}
                        @elseif (in_array($r->status, ['proof_submitted', 'marked_paid']))
                            <form method="POST" action="{{ route('admin.payoneer.verify', $r) }}" class="mb-1 flex flex-wrap items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="approve" value="1">
                                <input type="text" name="note" placeholder="Note (optional)"
                                    class="min-w-[140px] rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                <x-admin.button type="submit" class="!px-2.5 !py-1 !text-xs">
                                    <i class="fas fa-check"></i> {{ $r->status === 'proof_submitted' ? 'Verify receipt' : 'Mark received' }}
                                </x-admin.button>
                            </form>
                            <form method="POST" action="{{ route('admin.payoneer.verify', $r) }}" class="flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="approve" value="0">
                                <input type="text" name="note" placeholder="Rejection reason"
                                    class="min-w-[140px] rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                <button type="submit" class="rounded-md bg-red-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-700">Reject</button>
                            </form>

                        {{-- ACTION: revoke a sent link --}}
                        @elseif ($r->status === 'link_sent')
                            <form method="POST" action="{{ route('admin.payoneer.cancel', $r) }}" onsubmit="return confirm('Cancel this link? The customer will be freed to choose another payment method.');">
                                @csrf
                                <button type="submit" class="rounded-md border border-red-300 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50">
                                    <i class="fas fa-unlink"></i> Revoke link
                                </button>
                            </form>

                        @else
                            <span class="text-slate-400">&mdash;</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-6 text-center text-slate-400">No Payoneer payment requests yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($requests->hasPages())
        <div class="mt-4">
            {{ $requests->links('pagination.tailwind-admin') }}
        </div>
    @endif
</x-admin.card>

<div class="mt-4 flex items-start gap-1.5 text-xs text-slate-500">
    <i class="fas fa-info-circle mt-0.5"></i>
    <span>
        Payoneer does not expose a link-generation API for business accounts — generate links in your Payoneer dashboard (Request a Payment) and paste them above.
        Verified payments use the same engine as bank transfers, so the order, offer and wallet stay perfectly synced.
    </span>
</div>
@endsection
