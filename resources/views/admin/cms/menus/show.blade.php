@extends('layouts.tailwind.app')

@section('title', 'Menu: ' . $menu->name)
@section('page_title', 'Menu: ' . $menu->name)
@section('page_subtitle', 'Drag to reorder, drag onto an item to nest. Location: ' . $menu->location)

@push('admin_scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endpush

@section('content')

<div class="mb-3 flex items-center justify-between">
    <a href="{{ route('admin.cms.menus.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100"><i class="fas fa-arrow-left mr-1"></i> All Menus</a>
    <div class="flex items-center gap-2">
        <span id="cmsSaveState" class="text-sm text-slate-500"></span>
        <button type="button" class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-dark" id="cmsSaveMenu"><i class="fas fa-save mr-1"></i> Save Menu</button>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    {{-- ── LEFT PANEL: Add Menu Items ── --}}
    <div class="lg:col-span-1">
        <x-admin.card title="Add Menu Items" class="!p-0" x-data="{ tab: 'pages' }">
            <div class="divide-y divide-slate-100">
                @php
                    $tabs = [
                        'pages' => 'Pages',
                        'blogs' => 'Blog Posts',
                        'products' => 'Products',
                        'services' => 'Services',
                        'cats' => 'Categories',
                        'tags' => 'Tags',
                        'custom' => 'Custom Link',
                    ];
                @endphp
                @foreach ($tabs as $tabKey => $tabLabel)
                <div>
                    <button type="button" @click="tab = (tab === '{{ $tabKey }}' ? null : '{{ $tabKey }}')" class="block w-full px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                        {{ $tabLabel }} <i class="fas fa-chevron-down float-right mt-0.5 text-xs text-slate-400" :class="{ 'rotate-180': tab === '{{ $tabKey }}' }"></i>
                    </button>
                    <div x-show="tab === '{{ $tabKey }}'" x-cloak class="max-h-[220px] overflow-y-auto px-3 pb-2">
                        @if ($tabKey === 'pages')
                            @forelse ($pages as $page)
                            <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" class="cms-pick" data-type="cms_post" data-id="{{ $page->id }}" data-label="{{ $page->title }}">
                                {{ $page->title }}
                            </label>
                            @empty
                            <span class="text-sm text-slate-400">No published pages.</span>
                            @endforelse
                        @elseif ($tabKey === 'blogs')
                            @forelse ($blogs as $blog)
                            <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" class="cms-pick" data-type="cms_post" data-id="{{ $blog->id }}" data-label="{{ $blog->title }}">
                                {{ $blog->title }}
                            </label>
                            @empty
                            <span class="text-sm text-slate-400">No published blog posts.</span>
                            @endforelse
                        @elseif ($tabKey === 'products')
                            @forelse ($products as $product)
                            <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" class="cms-pick" data-type="cms_post" data-id="{{ $product->id }}" data-label="{{ $product->title }}">
                                {{ $product->title }}
                            </label>
                            @empty
                            <span class="text-sm text-slate-400">No published products.</span>
                            @endforelse
                        @elseif ($tabKey === 'services')
                            @forelse ($services as $service)
                            <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" class="cms-pick" data-type="cms_post" data-id="{{ $service->id }}" data-label="{{ $service->title }}">
                                {{ $service->title }}
                            </label>
                            @empty
                            <span class="text-sm text-slate-400">No published services.</span>
                            @endforelse
                        @elseif ($tabKey === 'cats')
                            @forelse ($categories as $cat)
                            <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" class="cms-pick" data-type="taxonomy" data-id="{{ $cat->id }}" data-label="{{ $cat->name }}">
                                {{ $cat->name }} <small class="text-slate-400">({{ $cat->post_type }})</small>
                            </label>
                            @empty
                            <span class="text-sm text-slate-400">No categories.</span>
                            @endforelse
                        @elseif ($tabKey === 'tags')
                            @forelse ($tags as $tag)
                            <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" class="cms-pick" data-type="taxonomy" data-id="{{ $tag->id }}" data-label="{{ $tag->name }}">
                                {{ $tag->name }}
                            </label>
                            @empty
                            <span class="text-sm text-slate-400">No tags.</span>
                            @endforelse
                        @elseif ($tabKey === 'custom')
                            <form method="POST" action="{{ route('admin.cms.menus.add-item', $menu) }}" class="py-1">
                                @csrf
                                <input type="hidden" name="item_type" value="custom">
                                <input type="hidden" name="target" value="_self">
                                <div class="mb-2">
                                    <label class="mb-0.5 block text-xs text-slate-500">Label</label>
                                    <input type="text" name="label" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm" required>
                                </div>
                                <div class="mb-2">
                                    <label class="mb-0.5 block text-xs text-slate-500">URL</label>
                                    <input type="text" name="url" class="block w-full rounded-md border border-slate-300 px-2 py-1 text-sm" placeholder="/some-path or https://…">
                                </div>
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-brand px-2.5 py-1 text-xs font-medium text-white hover:bg-brand-dark">Add to Menu</button>
                            </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="border-t border-slate-100 p-3">
                <button type="button" class="block w-full rounded-md bg-brand px-3 py-1.5 text-center text-sm font-medium text-white hover:bg-brand-dark" id="cmsAddPicked">
                    <i class="fas fa-plus mr-1"></i> Add to Menu
                </button>
            </div>
        </x-admin.card>
    </div>

    {{-- ── RIGHT PANEL: Menu Structure ── --}}
    <div class="lg:col-span-2">
        <x-admin.card>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800"><i class="fas fa-bars mr-1 text-slate-400"></i> Menu Structure</h3>
                <span class="inline-flex items-center rounded-full border border-slate-200 px-2 py-0.5 text-xs text-slate-600">{{ $items->count() }} top-level</span>
            </div>
            <ul class="cms-sortable list-none" id="cmsMenuTree" style="min-height:60px;">
                @foreach ($items as $item)
                    @include('admin.cms.menus._item', ['item' => $item])
                @endforeach
            </ul>
            @if($items->isEmpty())
            <p class="mb-0 text-center text-slate-400">This menu is empty — add items from the left panel.</p>
            @endif
            <div class="mt-3 border-t border-slate-100 pt-3 text-right">
                <button type="button" class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-dark" id="cmsSaveMenuBottom"><i class="fas fa-save mr-1"></i> Save Menu</button>
            </div>
        </x-admin.card>
    </div>
</div>

@endsection

@push('admin_scripts')
<script>
(function () {
    'use strict';
    var csrf = '{{ csrf_token() }}';
    var menuId = {{ $menu->id }};

    // Sortable: one instance per nesting level
    function initSortables() {
        document.querySelectorAll('.cms-sortable').forEach(function (el) {
            if (el.sortable) return;
            el.sortable = Sortable.create(el, {
                group: 'cmsMenu',
                handle: '.cms-handle',
                animation: 150,
                onAdd: initSortables,
                onSort: function () {}
            });
        });
    }
    initSortables();

    // Collect tree → POST reorder JSON
    function collectItems(list, parentId) {
        var out = [];
        Array.prototype.forEach.call(list.children, function (li) {
            if (!li.classList.contains('cms-item')) return;
            out.push({ id: parseInt(li.getAttribute('data-id'), 10), sort_order: out.length + 1, parent_id: parentId });
            var childList = li.querySelector(':scope > .cms-children');
            if (childList) {
                out = out.concat(collectItems(childList, parseInt(li.getAttribute('data-id'), 10)));
            }
        });
        return out;
    }
    function saveMenu() {
        var tree = document.getElementById('cmsMenuTree');
        var payload = collectItems(tree, null);
        var state = document.getElementById('cmsSaveState');
        if (state) state.textContent = 'Saving…';
        fetch({{ json_encode(route('admin.cms.menus.reorder', $menu)) }}, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: payload })
        }).then(function (r) { return r.json(); })
          .then(function (json) {
              if (state) state.textContent = json.ok ? 'Saved (' + payload.length + ' items)' : 'Save failed';
          })
          .catch(function () { if (state) state.textContent = 'Save failed'; });
    }
    var saveTop = document.getElementById('cmsSaveMenu');
    var saveBottom = document.getElementById('cmsSaveMenuBottom');
    if (saveTop) saveTop.addEventListener('click', saveMenu);
    if (saveBottom) saveBottom.addEventListener('click', saveMenu);

    // Add picked items from left panel
    var addBtn = document.getElementById('cmsAddPicked');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            var picked = document.querySelectorAll('.cms-pick:checked');
            if (!picked.length) { alert('Nothing selected.'); return; }
            var queue = Array.prototype.slice.call(picked);
            addBtn.disabled = true;
            addNext();
            function addNext() {
                var el = queue.shift();
                if (!el) { window.location.reload(); return; }
                var body = new URLSearchParams();
                body.set('label', el.getAttribute('data-label'));
                body.set('item_type', el.getAttribute('data-type'));
                body.set('object_id', el.getAttribute('data-id'));
                body.set('target', '_self');
                body.set('_token', csrf);
                fetch({{ json_encode(route('admin.cms.menus.add-item', $menu)) }}, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: body
                }).then(addNext).catch(function () { alert('Failed to add ' + el.getAttribute('data-label')); window.location.reload(); });
            }
        });
    }

    // Delete item buttons (inside item partials)
    document.querySelectorAll('.js-remove-item').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!window.confirm('Remove this item (and its children) from the menu?')) return;
            fetch(btn.getAttribute('data-url'), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            }).then(function () { window.location.reload(); });
        });
    });
})();
</script>
@endpush
