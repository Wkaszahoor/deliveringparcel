@extends('layouts.tailwind.app')

@section('title', 'Shipment ' . $shipment->code)
@section('page_title', 'Shipment ' . $shipment->code)
@section('page_subtitle', optional($shipment->user)->name ?: 'customer')

@section('content')

@php
    $m = $statusMap[$shipment->status] ?? [];
    $next = $statusMap[$shipment->status]['next'] ?? [];

    // Bootstrap badge class (from config/admin_warehouse.php's shipment_statuses) → Tailwind
    // pill classes — same mapping convention as admin/warehouse/shipments/index.blade.php's
    // badgeClasses() JS helper.
    $badgeClasses = fn (string $bootstrap) => [
        'bg-secondary' => 'bg-slate-100 text-slate-700',
        'bg-info'      => 'bg-cyan-100 text-cyan-700',
        'bg-primary'   => 'bg-blue-100 text-blue-700',
        'bg-success'   => 'bg-green-100 text-green-700',
        'bg-danger'    => 'bg-red-100 text-red-700',
    ][$bootstrap] ?? 'bg-slate-100 text-slate-700';
@endphp

<div class="mb-4 flex flex-wrap items-center gap-2">
    <a href="{{ route('admin.warehouse.shipments.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    <a href="{{ route('admin.warehouse.shipments.manifest', $shipment->id) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-3 py-1.5 text-sm font-medium text-blue-600 hover:bg-blue-50">
        <i class="fas fa-print"></i> Dispatch manifest
    </a>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    <div class="lg:col-span-4">
        <x-admin.card title="Details" class="mb-4">
            <table class="w-full text-sm">
                <tr><td class="w-2/5 py-1 pr-2 align-top text-slate-500">Code</td><td class="py-1">{{ $shipment->code }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Customer</td><td class="py-1">{{ optional($shipment->user)->name ?: '—' }}<br><small class="text-slate-500">{{ optional($shipment->user)->email }}</small></td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Carrier</td><td class="py-1">{{ $shipment->carrier_name ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Tracking</td><td class="py-1">{{ $shipment->tracking_number ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Status</td><td class="py-1"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses($m['color'] ?? 'bg-secondary') }}">{{ $m['label'] ?? $shipment->status }}</span></td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Dispatched</td><td class="py-1">{{ optional($shipment->dispatched_at)->format('M d, Y H:i') ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Delivered</td><td class="py-1">{{ optional($shipment->delivered_at)->format('M d, Y H:i') ?: '—' }}</td></tr>
            </table>

            @if ($next)
                <form method="POST" action="{{ route('admin.warehouse.shipments.update', $shipment->id) }}" class="mt-4 border-t border-slate-100 pt-4">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">New status</label>
                        <select name="status" required class="block w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                            @foreach ($next as $n)
                                <option value="{{ $n }}">{{ $statusMap[$n]['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 grid grid-cols-2 gap-2">
                        <input type="text" name="carrier_name" value="{{ old('carrier_name', $shipment->carrier_name) }}" placeholder="Carrier"
                               class="block w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                        <input type="text" name="tracking_number" value="{{ old('tracking_number', $shipment->tracking_number) }}" placeholder="Tracking #"
                               class="block w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    </div>
                    <input type="text" name="note" maxlength="500" placeholder="Event note (optional)"
                           class="mb-3 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <x-admin.button type="submit" class="w-full justify-center">Update shipment</x-admin.button>
                </form>
            @else
                <p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-400">Final state — no further transitions.</p>
            @endif
        </x-admin.card>

        <x-admin.card title="Event timeline">
            <ul class="divide-y divide-slate-100">
                @forelse ($shipment->events as $e)
                    <li class="flex items-start justify-between gap-2 py-2 text-sm first:pt-0 last:pb-0">
                        <div>
                            <strong class="text-slate-800">{{ ucfirst(str_replace('_', ' ', $e->status)) }}</strong>
                            @if ($e->note)<br><span class="text-slate-500">{{ $e->note }}</span>@endif
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ optional($e->created_at)->format('M d, H:i') }}</span>
                    </li>
                @empty
                    <li class="py-2 text-sm text-slate-400">No events.</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>

    <div class="lg:col-span-8">
        <x-admin.card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Package</th>
                            <th class="py-2 pr-4">Customer</th>
                            <th class="py-2 pr-4">Bin</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($shipment->packages as $p)
                            <tr>
                                <td class="py-2 pr-4">#{{ $p->id }} {{ $p->expected_tracking ? '(' . $p->expected_tracking . ')' : '' }}</td>
                                <td class="py-2 pr-4">{{ optional($p->user)->name ?: '—' }}</td>
                                <td class="py-2 pr-4">{{ optional($p->bin)->code ?: '—' }}</td>
                                <td class="py-2 pr-4">{{ ucfirst($p->status) }}</td>
                                <td class="py-2 pr-4">{{ optional($p->received_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-slate-400">No packages linked.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>
</div>

@endsection
