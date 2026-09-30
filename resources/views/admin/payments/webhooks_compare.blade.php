@extends('layouts.tailwind.app')

@section('title', 'Match Webhooks with Stripe')
@section('page_title', 'Match Webhooks with Stripe')
@section('page_subtitle', 'Latest 25 events in the Stripe account vs our local delivery log')

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
        <a href="{{ route('admin.payments.webhooks') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
            <i class="fas fa-arrow-left"></i> Back to history
        </a>
        <a href="{{ route('admin.payments.webhooks.compare') }}" class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-dark">
            <i class="fas fa-redo"></i> Re-check now
        </a>
        <span class="text-xs text-slate-500">Fetched live from the Stripe API with the configured secret key (test or live mode follows the key).</span>
    </div>

    @if ($error)
        <x-admin.alert type="error">{{ $error }}</x-admin.alert>
    @else
        <x-admin.alert type="info" class="mb-4">
            <strong>{{ $stripeEvents->count() }}</strong> latest event(s) in the Stripe account.
            <span class="ml-2">Events marked <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">missing locally</span> were created in Stripe but never delivered/accepted by this site — investigate the endpoint URL and webhook secret.</span>
        </x-admin.alert>

        <x-admin.card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Stripe event</th>
                            <th class="py-2 pr-4">Type</th>
                            <th class="py-2 pr-4">Created (Stripe)</th>
                            <th class="py-2 pr-4">Local status</th>
                            <th class="py-2 pr-4">Local result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($stripeEvents as $se)
                            @php $found = $local->get($se->id); @endphp
                            <tr>
                                <td class="py-2 pr-4"><code class="text-xs">{{ $se->id }}</code></td>
                                <td class="py-2 pr-4">{{ $se->type }}</td>
                                <td class="py-2 pr-4"><span class="text-xs text-slate-500">{{ \Carbon\Carbon::createFromTimestamp($se->created)->format('M d, Y H:i:s') }}</span></td>
                                <td class="py-2 pr-4">
                                    @if ($found)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $webhookBadgeClasses($found->statusColor()) }}">{{ $found->status }}</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">missing locally</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    @if ($found)
                                        {{ $found->result ?: '—' }}
                                        <a href="{{ route('admin.payments.webhooks.show', $found->id) }}" class="ml-1 text-slate-400 hover:text-brand"><i class="fas fa-eye"></i></a>
                                    @else
                                        <span class="text-slate-400">not received</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-slate-400">No events found in the Stripe account.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @endif
@endsection
