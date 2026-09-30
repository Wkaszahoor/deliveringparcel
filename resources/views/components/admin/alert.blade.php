@props(['type' => 'info'])

@php
$styles = [
    'success' => 'border-green-200 bg-green-50 text-green-800',
    'error' => 'border-red-200 bg-red-50 text-red-800',
    'info' => 'border-blue-200 bg-blue-50 text-blue-800',
];
@endphp

<div x-data="{ show: true }" x-show="show" {{ $attributes->merge(['class' => 'mb-4 flex items-start justify-between gap-3 rounded-md border px-4 py-3 text-sm ' . ($styles[$type] ?? $styles['info'])]) }}>
    <div class="min-w-0">{{ $slot }}</div>
    <button @click="show = false" type="button" class="shrink-0 opacity-60 hover:opacity-100" aria-label="Dismiss">&times;</button>
</div>
