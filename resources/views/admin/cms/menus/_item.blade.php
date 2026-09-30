{{-- One menu-builder item + its nested children (recursive).
    Expects: $item (CmsMenuItem) and $menu (NavMenu) from parent scope. --}}
<li class="cms-item" data-id="{{ $item->id }}">
    <div x-data="{ editOpen: false }" class="mb-1 rounded-md border border-slate-200">
        <div class="flex items-center gap-2 px-2 py-1.5">
            <span class="cms-handle cursor-grab text-slate-400" title="Drag to reorder / nest"><i class="fas fa-grip-vertical"></i></span>
            <strong class="truncate text-sm text-slate-800">{{ $item->label }}</strong>
            <span class="inline-flex items-center rounded-full border border-slate-200 px-2 py-0.5 text-xs text-slate-600">{{ $item->item_type }}</span>
            @if($item->show_badge && $item->badge_text)
            <span class="inline-flex items-center rounded-full bg-brand px-2 py-0.5 text-xs font-medium text-white">{{ $item->badge_text }}</span>
            @endif
            @if(!$item->is_active)
            <x-admin.badge>hidden</x-admin.badge>
            @endif
            <span class="ml-auto flex items-center gap-2 whitespace-nowrap text-sm">
                <button type="button" @click="editOpen = !editOpen" class="text-slate-400 hover:text-slate-600" title="Edit">
                    <i class="fas fa-chevron-down" :class="{ 'rotate-180': editOpen }"></i>
                </button>
                <a href="#" class="js-remove-item text-red-500 hover:text-red-700" title="Remove"
                   data-url="{{ route('admin.cms.menus.remove-item', [$menu->id, $item->id]) }}"><i class="fas fa-times"></i></a>
            </span>
        </div>
        <div x-show="editOpen" x-cloak class="border-t border-slate-100 px-3 py-2">
            <form method="POST" action="{{ route('admin.cms.menus.edit-item', [$menu->id, $item->id]) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-4">
                    <div>
                        <label class="mb-0.5 block text-xs text-slate-500">Label</label>
                        <input type="text" name="label" value="{{ $item->label }}" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm" required>
                    </div>
                    <div>
                        <label class="mb-0.5 block text-xs text-slate-500">URL <small class="text-slate-400">(blank = auto-resolve)</small></label>
                        <input type="text" name="url" value="{{ $item->url }}" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="mb-0.5 block text-xs text-slate-500">Target</label>
                        <select name="target" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm">
                            <option value="_self" {{ $item->target === '_self' ? 'selected' : '' }}>Same tab</option>
                            <option value="_blank" {{ $item->target === '_blank' ? 'selected' : '' }}>New tab</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-0.5 block text-xs text-slate-500">Icon</label>
                        <input type="text" name="icon" value="{{ $item->icon }}" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm" placeholder="fa-home">
                    </div>
                    <div class="flex items-center pt-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" id="active_{{ $item->id }}" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                            Active
                        </label>
                    </div>
                    <div class="flex items-center pt-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" id="badge_{{ $item->id }}" name="show_badge" value="1" {{ $item->show_badge ? 'checked' : '' }}>
                            Show badge
                        </label>
                    </div>
                    <div class="pt-2">
                        <input type="text" name="badge_text" value="{{ $item->badge_text }}" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm" placeholder="Badge text">
                    </div>
                    <div class="pt-2">
                        <select name="badge_color" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm">
                            @foreach (['bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-info'] as $color)
                            <option value="{{ $color }}" {{ $item->badge_color === $color ? 'selected' : '' }}>{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-right sm:col-span-2 md:col-span-4">
                        <button type="submit" class="mt-1 inline-flex items-center gap-1.5 rounded-md bg-brand px-2.5 py-1 text-xs font-medium text-white hover:bg-brand-dark"><i class="fas fa-save mr-1"></i>Update Item</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <ul class="cms-sortable cms-children ml-4 list-none" style="min-height:6px;">
        @foreach ($item->allChildren as $child)
        @include('admin.cms.menus._item', ['item' => $child])
        @endforeach
    </ul>
</li>
