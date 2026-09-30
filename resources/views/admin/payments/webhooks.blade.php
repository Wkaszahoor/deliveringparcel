@extends('layouts.tailwind.app')

@section('title', 'Stripe Webhook History')
@section('page_title', 'Stripe Webhook History')
@section('page_subtitle', 'Every delivery we received — with outcome and full payload')

@section('content')
    @php
        // WebhookEvent::statusColor() returns a Bootstrap color suffix (success/secondary/info/danger);
        // map it to the Tailwind badge classes used across the migrated admin pages.
        $webhookBadgeClasses = fn (string $bootstrap) => [
            'secondary' => 'bg-slate-100 text-slate-700',
            'success'   => 'bg-green-100 text-green-700',
            'info'      => 'bg-blue-100 text-blue-700',
            'danger'    => 'bg-red-100 text-red-700',
            'warning'   => 'bg-yellow-100 text-yellow-800',
        ][$bootstrap] ?? 'bg-slate-100 text-slate-700';
    @endphp

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.payments.webhooks.compare') }}" class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-dark">
            <i class="fas fa-exchange-alt"></i> Match with Stripe account
        </a>
        <a href="{{ route('admin.payments.webhooks') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
            <i class="fas fa-redo"></i> Refresh
        </a>
        <span class="text-xs text-slate-500">Local history is written for EVERY delivery: processed, ignored, signature failures and errors.</span>
    </div>

    <x-admin.card class="mb-4">
        <form class="flex flex-wrap items-end gap-2" method="GET" action="{{ route('admin.payments.webhooks') }}">
            <div>
                <label for="wh-type" class="mb-1 block text-xs font-medium text-slate-600">Event type contains</label>
                <input id="wh-type" type="text" name="type" value="{{ request('type') }}" placeholder="payment_intent.succeeded"
                    class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
            </div>
            <div>
                <label for="wh-status" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select id="wh-status" name="status" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.button type="submit"><i class="fas fa-filter"></i> Filter</x-admin.button>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Event</th>
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Result</th>
                        <th class="py-2 pr-4">Payment</th>
                        <th class="py-2 pr-4">Received</th>
                        <th class="py-2 pr-4 text-right">Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($events as $e)
                        <tr>
                            <td class="py-2 pr-4"><code class="text-xs">{{ $e->event_id }}</code></td>
                            <td class="py-2 pr-4">{{ $e->type ?: '—' }}</td>
                            <td class="py-2 pr-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $webhookBadgeClasses($e->statusColor()) }}">{{ $e->status }}</span>
                            </td>
                            <td class="py-2 pr-4">{{ $e->result ?: '—' }}</td>
                            <td class="py-2 pr-4">
                                @if ($e->payment_id)
                                    <a href="{{ route('admin.payments.ledger') }}?q={{ $e->payment_id }}" class="text-brand hover:underline">#{{ $e->payment_id }}</a>
                                @else
                                    <span class="text-slate-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4"><span class="text-xs text-slate-500">{{ optional($e->created_at)->format('M d, Y H:i:s') }}</span></td>
                            <td class="py-2 pr-4 text-right">
                                <a href="{{ route('admin.payments.webhooks.show', $e->id) }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-slate-400">No webhook deliveries recorded yet. Deliveries appear here the moment Stripe calls the endpoint.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($events->hasPages())
            <div class="mt-4">
                {{ $events->links('pagination.tailwind-admin') }}
            </div>
        @endif
    </x-admin.card>
@endsection
