@props(['variant' => 'primary', 'tag' => 'button', 'href' => null])

@php
$variants = [
    'primary' => 'bg-brand text-white hover:bg-brand-dark',
    'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200',
    'danger' => 'bg-red-600 text-white hover:bg-red-700',
];
$classes = 'inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium transition-colors disabled:opacity-50 ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($tag === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
