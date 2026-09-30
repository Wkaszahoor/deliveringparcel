@extends('layouts.tailwind.app')

@section('title', isset($surcharge->id) ? 'Edit Surcharge' : 'New Surcharge')
@section('page_title', isset($surcharge->id) ? 'Edit Surcharge' : 'New Surcharge')
@section('page_subtitle', $surcharge->name ?? 'Add-on charge')

@section('content')
<x-admin.card class="mx-auto max-w-xl">
    <form method="POST" action="{{ isset($surcharge->id) ? route('admin.rates.surcharges.update', $surcharge->id) : route('admin.rates.surcharges.store') }}">
        @csrf
        @if (isset($surcharge->id)) @method('PUT') @endif

        <x-admin.input name="name" label="Name" :value="$surcharge->name" required maxlength="255" />

        <div class="mb-3 grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Type</label>
                <select name="type" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="percentage" {{ old('type', $surcharge->type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ old('type', $surcharge->type) === 'fixed' ? 'selected' : '' }}>Fixed ($)</option>
                </select>
            </div>
            <x-admin.input type="number" name="value" label="Value" :value="$surcharge->value" required step="0.01" min="0" />
        </div>

        <div class="mb-3">
            <label class="mb-1 block text-sm font-medium text-slate-700">Applies to</label>
            <select name="applies_to" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                <option value="all" {{ old('applies_to', $surcharge->applies_to) === 'all' ? 'selected' : '' }}>All quotes</option>
                <option value="fuel" {{ old('applies_to', $surcharge->applies_to) === 'fuel' ? 'selected' : '' }}>Fuel only</option>
                <option value="insurance" {{ old('applies_to', $surcharge->applies_to) === 'insurance' ? 'selected' : '' }}>Insurance only</option>
                <option value="remote" {{ old('applies_to', $surcharge->applies_to) === 'remote' ? 'selected' : '' }}>Remote areas</option>
            </select>
        </div>

        <label class="mb-4 flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', $surcharge->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand focus:ring-brand-light">
            Active
        </label>

        <div class="flex gap-2">
            <x-admin.button type="submit">Save</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('admin.rates.surcharges.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
