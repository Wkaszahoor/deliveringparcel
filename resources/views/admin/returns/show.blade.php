@extends('layouts.tailwind.app')

@section('title', 'Return #' . $return->id)
@section('page_title', 'Return Request #' . $return->id)
@section('page_subtitle', $return->reason)

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
    $m = $statusMap[$return->status] ?? [];
    $next = $statusMap[$return->status]['next'] ?? [];
@endphp

<div class="mb-4">
    <a href="{{ route('admin.returns.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    <div class="lg:col-span-5">
        <x-admin.card title="Details">
            <table class="w-full text-sm">
                <tr><td class="w-32 py-1 pr-2 align-top text-slate-500">User</td><td class="py-1">{{ optional($return->user)->name ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Email</td><td class="py-1">{{ optional($return->user)->email ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Order</td><td class="py-1">{{ $return->order_id ? '#' . $return->order_id : '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Reason</td><td class="py-1">{{ $return->reason }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Created</td><td class="py-1">{{ optional($return->created_at)->format('M d, Y H:i') }}</td></tr>
                <tr>
                    <td class="py-1 pr-2 align-top text-slate-500">Status</td>
                    <td class="py-1">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses($m['color'] ?? 'bg-secondary') }}">{{ $m['label'] ?? $return->status }}</span>
                    </td>
                </tr>
            </table>

            @if ($return->description)
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <h4 class="mb-1 text-xs font-semibold uppercase text-slate-500">Description</h4>
                    <p class="text-sm text-slate-700">{!! nl2br(e($return->description)) !!}</p>
                </div>
            @endif
            @if ($return->resolution_note)
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <h4 class="mb-1 text-xs font-semibold uppercase text-slate-500">Resolution note</h4>
                    <p class="text-sm text-slate-700">{!! nl2br(e($return->resolution_note)) !!}</p>
                </div>
            @endif
        </x-admin.card>

        @if ($next)
            <x-admin.card title="Move status" class="mt-4">
                <form method="POST" action="{{ route('admin.returns.status', $return->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600" for="return-status">New status</label>
                        <select id="return-status" name="status" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm" required>
                            @foreach ($next as $n)
                                <option value="{{ $n }}">{{ $statusMap[$n]['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600" for="return-note">Note</label>
                        <textarea id="return-note" name="note" rows="2" maxlength="2000" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm"></textarea>
                    </div>
                    <x-admin.button type="submit" class="w-full justify-center">Update</x-admin.button>
                </form>
            </x-admin.card>
        @endif
    </div>

    <div class="lg:col-span-7">
        <x-admin.card title="Linked claims">
            @if ($return->claims->isEmpty())
                <p class="text-sm text-slate-400">No claims linked.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                                <th class="py-2 pr-4">#</th>
                                <th class="py-2 pr-4">Type</th>
                                <th class="py-2 pr-4">Amount</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($return->claims as $c)
                                @php($cm = config('admin_quotes.claim_statuses.' . $c->status, []))
                                <tr>
                                    <td class="py-2 pr-4">#{{ $c->id }}</td>
                                    <td class="py-2 pr-4">{{ config('admin_quotes.claim_types.' . $c->type, $c->type) }}</td>
                                    <td class="py-2 pr-4">${{ number_format((float) $c->amount_claimed, 2) }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses($cm['color'] ?? 'bg-secondary') }}">{{ $cm['label'] ?? $c->status }}</span>
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        <a href="{{ route('admin.returns.claims-show', $c->id) }}" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    </div>
</div>

@endsection
