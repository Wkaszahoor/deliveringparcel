@extends('layouts.tailwind.app')

@section('title', 'Navigation Menus')
@section('page_title', 'Navigation Menus')
@section('page_subtitle', 'Menu locations rendered by the CMS across the site header and footer.')

@section('content')
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
    @foreach ($menus as $menu)
    <x-admin.card class="flex h-full flex-col">
        <div class="mb-2 flex items-start justify-between">
            <h3 class="text-sm font-semibold text-slate-800"><i class="fas fa-bars mr-1 text-slate-400"></i> {{ $menu->name }}</h3>
            @if(!$menu->is_active)
            <x-admin.badge>inactive</x-admin.badge>
            @endif
        </div>
        <p class="mb-1"><span class="inline-flex items-center rounded-full border border-slate-200 px-2 py-0.5 text-xs text-slate-600">{{ $menu->location }}</span></p>
        <p class="mb-2 flex-1 text-sm text-slate-500">{{ $menu->description }}</p>
        <p class="mb-3 text-sm text-slate-700"><strong>{{ $menu->allItems->count() }}</strong> item(s)</p>
        <a href="{{ route('admin.cms.menus.show', $menu) }}" class="inline-flex items-center justify-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-dark">
            <i class="fas fa-edit mr-1"></i> Edit Menu
        </a>
    </x-admin.card>
    @endforeach
</div>
@endsection
