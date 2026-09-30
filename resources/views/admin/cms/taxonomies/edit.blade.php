@extends('layouts.tailwind.app')

@section('title', 'Edit Term: ' . ($taxonomy->name ?? ''))
@section('page_title', 'Edit Term: ' . ($taxonomy->name ?? ''))
@section('page_subtitle', 'Update a category, tag or service area.')

@section('content')
<div class="flex justify-center">
    <x-admin.card class="w-full max-w-xl">
        <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-pen mr-1 text-slate-400"></i> {{ $taxonomy->name }}</h3>
        <form method="POST" action="{{ route('admin.cms.taxonomies.update', $taxonomy) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Taxonomy</label>
                <select name="taxonomy" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($taxonomyOptions as $tKey => $tLabel)
                    <option value="{{ $tKey }}" {{ old('taxonomy', $taxonomy->taxonomy) === $tKey ? 'selected' : '' }}>{{ $tLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Post Type</label>
                <select name="post_type" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($postTypeOptions as $pKey => $pLabel)
                    <option value="{{ $pKey }}" {{ old('post_type', $taxonomy->post_type) === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.input name="name" label="Name" :value="old('name', $taxonomy->name)" />
            <x-admin.input name="slug" label="Slug" :value="old('slug', $taxonomy->slug)" />
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Parent</label>
                <select name="parent_id" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">— none —</option>
                    @foreach ($parents as $pid => $pname)
                    <option value="{{ $pid }}" {{ (string) old('parent_id', $taxonomy->parent_id) === (string) $pid ? 'selected' : '' }}>{{ $pname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" rows="3" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('description', $taxonomy->description) }}</textarea>
            </div>
            <x-admin.input name="sort_order" label="Sort Order" type="number" :value="old('sort_order', $taxonomy->sort_order)" min="0" />
            <div class="flex gap-2">
                <x-admin.button type="submit" class="flex-1 justify-center"><i class="fas fa-save"></i> Save Changes</x-admin.button>
                <x-admin.button variant="secondary" tag="a" :href="route('admin.cms.taxonomies.index')" class="flex-1 justify-center">Back to Terms</x-admin.button>
            </div>
        </form>
    </x-admin.card>
</div>
@endsection
