@extends('layouts.tailwind.app')

@section('title', 'Webhook Event ' . $event->event_id)
@section('page_title', 'Webhook Event')
@section('page_subtitle', $event->event_id)

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

    <div class="mb-4">
        <a href="{{ route('admin.payments.webhooks') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
            <i class="fas fa-arrow-left"></i> Back to history
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card title="Delivery" class="lg:col-span-1">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    <tr><th class="w-2/5 py-2 pr-2 text-left align-top text-xs font-medium uppercase text-slate-500">Event id</th><td class="py-2"><code class="text-xs">{{ $event->event_id }}</code></td></tr>
                    <tr><th class="py-2 pr-2 text-left align-top text-xs font-medium uppercase text-slate-500">Type</th><td class="py-2">{{ $event->type ?: '—' }}</td></tr>
                    <tr><th class="py-2 pr-2 text-left align-top text-xs font-medium uppercase text-slate-500">Status</th><td class="py-2"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $webhookBadgeClasses($event->statusColor()) }}">{{ $event->status }}</span></td></tr>
                    <tr><th class="py-2 pr-2 text-left align-top text-xs font-medium uppercase text-slate-500">Result</th><td class="py-2">{{ $event->result ?: '—' }}</td></tr>
                    <tr><th class="py-2 pr-2 text-left align-top text-xs font-medium uppercase text-slate-500">Payment</th><td class="py-2">@if($event->payment_id) <a href="{{ route('admin.payments.ledger') }}?q={{ $event->payment_id }}" class="text-brand hover:underline">#{{ $event->payment_id }}</a> @else — @endif</td></tr>
                    <tr><th class="py-2 pr-2 text-left align-top text-xs font-medium uppercase text-slate-500">API version</th><td class="py-2">{{ $event->api_version ?: '—' }}</td></tr>
                    <tr><th class="py-2 pr-2 text-left align-top text-xs font-medium uppercase text-slate-500">Received</th><td class="py-2">{{ optional($event->created_at)->format('M d, Y H:i:s') }}</td></tr>
                </tbody>
            </table>
        </x-admin.card>
        <x-admin.card title="Full payload (as delivered)" class="lg:col-span-2">
            <div class="max-h-[70vh] overflow-auto">
                <pre class="text-xs">{!! json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}</pre>
            </div>
        </x-admin.card>
    </div>
@endsection
