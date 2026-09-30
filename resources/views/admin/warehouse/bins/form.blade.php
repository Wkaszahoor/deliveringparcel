@extends('layouts.tailwind.app')

@section('title', isset($bin->id) ? 'Edit Bin' : 'New Bin')
@section('page_title', isset($bin->id) ? 'Edit Bin' : 'New Bin')

@section('content')
<x-admin.card class="max-w-2xl">
    <form method="POST" action="{{ isset($bin->id) ? route('admin.warehouse.bins.update', $bin->id) : route('admin.warehouse.bins.store') }}">
        @csrf
        @if (isset($bin->id)) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-5">
                <label for="wb-code" class="mb-1 block text-sm font-medium text-slate-700">Code <span class="text-red-500">*</span></label>
                <input id="wb-code" type="text" name="code" value="{{ old('code', $bin->code) }}" required maxlength="30" placeholder="A-01-1"
                       class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('code') ? ' border-red-400' : '' }}">
                @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-4">
                <label for="wb-zone" class="mb-1 block text-sm font-medium text-slate-700">Zone</label>
                <input id="wb-zone" type="text" name="zone" value="{{ old('zone', $bin->zone) }}" maxlength="50"
                       class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
            </div>
            <div class="md:col-span-3">
                <label for="wb-cap" class="mb-1 block text-sm font-medium text-slate-700">Capacity <span class="text-red-500">*</span></label>
                <input id="wb-cap" type="number" min="1" name="capacity" value="{{ old('capacity', $bin->capacity) }}" required
                       class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('capacity') ? ' border-red-400' : '' }}">
                @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-3">
            <label for="wb-notes" class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
            <textarea id="wb-notes" name="notes" rows="2" maxlength="2000"
                      class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">{{ old('notes', $bin->notes) }}</textarea>
        </div>

        <div class="mt-3 mb-4 flex items-center gap-2">
            <input type="checkbox" id="wb-active" name="is_active" value="1"
                   {{ old('is_active', $bin->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
            <label for="wb-active" class="text-sm font-medium text-slate-700">Active</label>
        </div>

        <div class="flex gap-2">
            <x-admin.button variant="secondary" tag="a" :href="route('admin.warehouse.bins.index')">Cancel</x-admin.button>
            <x-admin.button type="submit" class="flex-1 justify-center">Save</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
