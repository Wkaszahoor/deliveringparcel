@props(['color' => 'slate'])

@php
$colors = [
    'slate' => 'bg-slate-100 text-slate-700',
    'green' => 'bg-green-100 text-green-700',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'blue' => 'bg-blue-100 text-blue-700',
    'red' => 'bg-red-100 text-red-700',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . ($colors[$color] ?? $colors['slate'])]) }}>
    {{ $slot }}
</span>
