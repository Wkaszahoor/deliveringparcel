@extends('layouts.tailwind.app')

@section('title', 'Categories & Tags')
@section('page_title', 'Categories & Tags')
@section('page_subtitle', 'Manage categories, tags and service areas for each content type.')

@php
    $groupLabels = [
        'category_blog_post'   => 'Blog Categories',
        'tag_blog_post'        => 'Blog Tags',
        'category_product'     => 'Product Categories',
        'service_area_service' => 'Service Areas',
    ];
    $activeGroup = request('group', array_key_first($groupLabels));
@endphp

@section('content')

{{-- Tabs per taxonomy group --}}
<div class="mb-4 flex flex-wrap gap-2">
    @foreach ($groupLabels as $groupKey => $groupLabel)
    <a href="{{ route('admin.cms.taxonomies.index', ['group' => $groupKey]) }}"
       class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium {{ $activeGroup === $groupKey ? 'bg-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $groupLabel }}</a>
    @endforeach
</div>

@php
    [$activeTaxonomy, $activePostType] = array_pad(explode('_', $activeGroup, 2), 2, null);
    $terms = $taxonomies->get($activeGroup, collect());
@endphp

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    {{-- LEFT: term list --}}
    <div class="lg:col-span-7">
        <x-admin.card :title="$groupLabels[$activeGroup]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4">Slug</th>
                            <th class="w-16 py-2 pr-4">Posts</th>
                            <th class="py-2 pr-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($terms as $term)
                        <tr>
                            <td class="py-2 pr-4">
                                <strong class="text-slate-900">{{ $term->name }}</strong>
                                @if($term->parent_id)
                                <small class="text-slate-500">— child of #{{ $term->parent_id }}</small>
                                @endif
                                <br><small class="text-slate-500">{{ \Str::limit($term->description, 80) }}</small>
                            </td>
                            <td class="py-2 pr-4"><code class="text-xs">{{ $term->slug }}</code></td>
                            <td class="py-2 pr-4"><span class="inline-flex items-center rounded-full border border-slate-200 px-2 py-0.5 text-xs text-slate-600">{{ $term->posts_count ?? $term->post_count }}</span></td>
                            <td class="py-2 pr-4 text-right whitespace-nowrap">
                                <button type="button" class="js-toggle-edit rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" data-target="term-edit-{{ $term->id }}">
                                    <i class="fas fa-pen mr-1"></i>Edit
                                </button>
                                <form method="POST" action="{{ route('admin.cms.taxonomies.destroy', $term) }}" class="js-confirm-delete inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50" data-confirm="Delete term &quot;{{ $term->name }}&quot;?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="hidden" id="term-edit-{{ $term->id }}">
                            <td colspan="4" class="bg-slate-50 py-3">
                                <form method="POST" action="{{ route('admin.cms.taxonomies.update', $term) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="taxonomy" value="{{ $term->taxonomy }}">
                                    <input type="hidden" name="post_type" value="{{ $term->post_type }}">
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                        <div><input type="text" name="name" value="{{ $term->name }}" class="mb-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm" placeholder="Name"></div>
                                        <div><input type="text" name="slug" value="{{ $term->slug }}" class="mb-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm" placeholder="Slug"></div>
                                        <div>
                                            <select name="parent_id" class="mb-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                                                <option value="">— no parent —</option>
                                                @foreach ($terms->where('id', '!=', $term->id) as $potentialParent)
                                                <option value="{{ $potentialParent->id }}" {{ $term->parent_id === $potentialParent->id ? 'selected' : '' }}>{{ $potentialParent->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3"><textarea name="description" rows="2" class="mb-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm" placeholder="Description">{{ $term->description }}</textarea></div>
                                        <div>
                                            <input type="number" name="sort_order" value="{{ $term->sort_order }}" min="0" class="mb-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm" placeholder="Sort">
                                        </div>
                                        <div><button type="submit" class="mb-1 rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-dark">Update</button></div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-6 text-center text-slate-400">No terms in this group yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>

    {{-- RIGHT: add new term --}}
    <div class="lg:col-span-5">
        <x-admin.card>
            <div class="mb-3 flex items-center">
                <h3 class="text-sm font-semibold text-slate-800"><i class="fas fa-plus mr-1 text-slate-400"></i> Add New Term</h3>
                <span class="ml-auto text-xs text-slate-500">&rarr; {{ $groupLabels[$activeGroup] }}</span>
            </div>
            <form method="POST" action="{{ route('admin.cms.taxonomies.store') }}">
                @csrf
                <input type="hidden" name="taxonomy" value="{{ $activeTaxonomy }}">
                <input type="hidden" name="post_type" value="{{ $activePostType }}">
                <x-admin.input name="name" label="Name" :value="old('name')" placeholder="Term name" />
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Slug <small class="font-normal text-slate-400">(optional — generated from name)</small></label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="block w-full rounded-md border px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light {{ $errors->has('slug') ? 'border-red-400' : 'border-slate-300' }}">
                    @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Parent</label>
                    <select name="parent_id" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="">— none —</option>
                        @foreach ($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                    <textarea name="description" rows="3" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
                </div>
                <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', 0)" min="0" />
                <x-admin.button type="submit" class="w-full justify-center"><i class="fas fa-plus"></i> Add Term</x-admin.button>
            </form>
        </x-admin.card>
    </div>
</div>
@endsection

@push('admin_scripts')
<script>
(function () {
    'use strict';
    document.querySelectorAll('.js-toggle-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.getAttribute('data-target'));
            if (target) target.classList.toggle('hidden');
        });
    });
    document.querySelectorAll('.js-confirm-delete button[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!window.confirm(btn.getAttribute('data-confirm'))) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
})();
</script>
@endpush
