@props(['title' => null])

{{-- Requires an ancestor element with x-data="{ open: false }" — this component has
     no x-data of its own and references the parent scope's `open` variable. Usage:
     <div x-data="{ open: false }">
         <button @click="open = true">Trigger</button>
         <x-admin.modal title="...">...</x-admin.modal>
     </div> --}}
<div
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-slate-900/50"></div>

    <div x-show="open" x-transition class="relative w-full max-w-md rounded-lg bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
            <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600" aria-label="Close">&times;</button>
        </div>
        <div class="p-4">
            {{ $slot }}
        </div>
    </div>
</div>
