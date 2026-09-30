@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title)
        <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
        </div>
    @endif
    <div class="p-4">
        {{ $slot }}
    </div>
</div>
