@extends('layouts.tailwind.app')

@section('title', 'Lookup History')
@section('page_title', 'Carrier Lookup History')

@section('content')
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Number</th>
                    <th class="py-2 pr-4">Carrier</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($lookups as $l)
                    <tr>
                        <td class="py-2 pr-4">{{ $l->number }}</td>
                        <td class="py-2 pr-4">{{ strtoupper($l->carrier_code ?: '—') }}</td>
                        <td class="py-2 pr-4">{{ $l->result_status ?: '—' }}</td>
                        <td class="py-2 pr-4 text-xs text-slate-400">{{ optional($l->created_at)->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-slate-400">No lookups yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
@endsection
