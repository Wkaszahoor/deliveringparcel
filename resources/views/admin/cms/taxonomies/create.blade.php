@extends('layouts.tailwind.app')

@section('title', 'Add New Term')
@section('page_title', 'Add New Term')
@section('page_subtitle', 'Create a category, tag or service area.')

@section('content')
<div class="flex justify-center">
    <x-admin.card title="New Term" class="w-full max-w-xl">
        <form method="POST" action="{{ route('admin.cms.taxonomies.store') }}">
            @csrf
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Taxonomy</label>
                <select name="taxonomy" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($taxonomyOptions as $tKey => $tLabel)
                    <option value="{{ $tKey }}" {{ old('taxonomy', request('taxonomy', 'category')) === $tKey ? 'selected' : '' }}>{{ $tLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Post Type</label>
                <select name="post_type" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($postTypeOptions as $pKey => $pLabel)
                    <option value="{{ $pKey }}" {{ old('post_type', request('post_type', 'blog_post')) === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.input name="name" label="Name" :value="old('name')" />
            <x-admin.input name="slug" label="Slug" :value="old('slug')" />
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Parent</label>
                <select name="parent_id" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">— none —</option>
                    @foreach ($parents as $pid => $pname)
                    <option value="{{ $pid }}" {{ (string) old('parent_id') === (string) $pid ? 'selected' : '' }}>{{ $pname }}</option>
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
@endsection
