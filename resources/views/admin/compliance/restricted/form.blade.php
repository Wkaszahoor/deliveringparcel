@extends('layouts.tailwind.app')

@section('title', isset($item->id) ? 'Edit Item' : 'New Restricted Item')
@section('page_title', isset($item->id) ? 'Edit Restricted Item' : 'New Restricted Item')

@section('content')
<x-admin.card class="mx-auto max-w-xl">
    <form method="POST" action="{{ isset($item->id) ? route('admin.compliance.restricted.update', $item->id) : route('admin.compliance.restricted.store') }}">
        @csrf
        @if (isset($item->id)) @method('PUT') @endif

        <x-admin.input name="name" label="Item name" :value="$item->name" required maxlength="255" />

        <div class="grid grid-cols-1 gap-x-3 sm:grid-cols-2">
            <x-admin.input name="category" label="Category" :value="$item->category" maxlength="100" />

            <div class="mb-3">
                <label for="severity" class="mb-1 block text-sm font-medium text-slate-700">Severity</label>
                <select id="severity" name="severity" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="prohibited" {{ old('severity', $item->severity) === 'prohibited' ? 'selected' : '' }}>Prohibited</option>
                    <option value="restricted" {{ old('severity', $item->severity) === 'restricted' ? 'selected' : '' }}>Restricted</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="reason" class="mb-1 block text-sm font-medium text-slate-700">Reason</label>
            <textarea id="reason" name="reason" rows="2" maxlength="2000" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">{{ old('reason', $item->reason) }}</textarea>
        </div>

        <label class="mb-4 flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="requires_declaration" value="1" {{ old('requires_declaration', $item->requires_declaration) ? 'checked' : '' }} class="rounded border-slate-300 text-brand focus:ring-brand-light">
            Requires customs declaration
        </label>

        <div class="flex gap-2">
            <x-admin.button variant="secondary" tag="a" :href="route('admin.compliance.restricted.index')">Cancel</x-admin.button>
            <x-admin.button type="submit" class="flex-1 justify-center">Save</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
