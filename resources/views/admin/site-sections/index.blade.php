@extends('layouts.tailwind.app')

@section('title', 'Section Manager')
@section('page_title', 'Section Manager')
@section('page_subtitle', 'Enable / disable Shop, Blog, Services, Contact, Pages sections')

@section('content')

@if (session('success'))
    <x-admin.alert type="success">{{ session('success') }}</x-admin.alert>
@endif
@if ($errors->any())
    <x-admin.alert type="error">
        @foreach ($errors->all() as $e)
            <p class="mb-0">{{ $e }}</p>
        @endforeach
    </x-admin.alert>
@endif

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach ($sections as $s)
        <x-admin.card class="{{ $s->is_enabled ? '!border-brand/30' : '!border-yellow-300' }}">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <i class="fa {{ $s->icon }} text-brand"></i>
                    {{ $s->name }}
                </h3>
                <x-admin.badge :color="$s->is_enabled ? 'green' : 'yellow'">{{ $s->is_enabled ? 'Enabled' : 'Disabled' }}</x-admin.badge>
            </div>

            <form action="{{ route('admin.site-sections.update', $s->id) }}" method="POST">
                @csrf @method('PUT')

                <x-admin.input label="Display Name" name="name" :value="$s->name" />
                <x-admin.input label="Icon (FA class)" name="icon" :value="$s->icon" placeholder="fa-blog" />
                <x-admin.input label="Index Route / URL" name="index_route" :value="$s->index_route" placeholder="/blog" />
                <x-admin.input label="Sort Order" name="sort_order" type="number" :value="$s->sort_order" min="0" />

                <label class="mb-2 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_enabled" value="1" id="en_{{ $s->id }}" @if ($s->is_enabled) checked @endif
                        class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                    Section Enabled
                </label>
                <label class="mb-4 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="show_in_nav" value="1" id="nav_{{ $s->id }}" @if ($s->show_in_nav) checked @endif
                        class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                    Show in Navigation
                </label>

                <x-admin.button type="submit" class="w-full justify-center">
                    <i class="fa fa-save"></i> Save Section
                </x-admin.button>
            </form>
        </x-admin.card>
    @endforeach
</div>

<x-admin.alert type="info" class="mt-4">
    Disabling a section hides it from navigation but does NOT remove its routes.
    To disable routes, use <a href="{{ route('admin.route-manager.index') }}" class="font-medium underline">Route Manager</a>.
</x-admin.alert>

@endsection
