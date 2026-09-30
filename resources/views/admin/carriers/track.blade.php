@extends('layouts.tailwind.app')

@section('title', 'Track Shipment')
@section('page_title', 'Aggregated Tracking')
@section('page_subtitle', 'Auto-detects the carrier, falls back to mock data offline')

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-3xl">
        <form method="GET" action="{{ route('admin.carriers.track') }}" class="mb-4 flex flex-wrap items-center gap-2">
            <input name="number" value="{{ $number }}" placeholder="Tracking number…" required
                class="max-w-[320px] rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
            <button type="submit" class="flex items-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-dark"><i class="fas fa-search"></i> Track</button>
        </form>

        @if ($result)
            @if (isset($result['error']))
                <x-admin.alert type="error">{{ $result['error'] }}</x-admin.alert>
            @else
                <x-admin.card>
                    <div class="-mx-4 -mt-4 mb-2 flex items-center border-b border-slate-100 px-4 py-2">
                        <strong class="text-sm text-slate-800">{{ $result['carrier'] }}</strong>
                        <span class="mx-2 text-sm text-slate-400">{{ $result['number'] }}</span>
                        <x-admin.badge class="ml-auto" color="blue">{{ $result['status'] }}</x-admin.badge>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @foreach ($result['events'] as $e)
                            <li class="py-2">
                                <div class="flex justify-between">
                                    <div>
                                        <strong class="text-sm text-slate-800">{{ $e['description'] }}</strong><br>
                                        <small class="text-xs text-slate-400">{{ $e['location'] }}</small>
                                    </div>
                                    <small class="text-xs text-slate-400">{{ $e['at'] }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if (!empty($result['note']))
                        <div class="-mx-4 -mb-4 mt-2 border-t border-slate-100 px-4 py-2 text-xs text-slate-400">{{ $result['note'] }} · detected via {{ $detected['carrier'] }} ({{ $detected['confidence'] }}%)</div>
                    @endif
                </x-admin.card>
            @endif
        @else
            <x-admin.alert type="info">Enter a tracking number to see the aggregated timeline. Unconfigured carriers return deterministic mock events.</x-admin.alert>
        @endif
    </div>
</div>
@endsection
