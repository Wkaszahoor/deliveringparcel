@extends('layouts.tailwind.app')

@section('title', isset($insurance->id) ? 'Edit Tier' : 'New Tier')
@section('page_title', isset($insurance->id) ? 'Edit Insurance Tier' : 'New Insurance Tier')
@section('page_subtitle', 'Declared-value bracket and flat cost')

@section('content')
<x-admin.card class="mx-auto max-w-2xl">
    <form method="POST" action="{{ isset($insurance->id) ? route('admin.rates.insurances.update', $insurance->id) : route('admin.rates.insurances.store') }}">
        @csrf
        @if (isset($insurance->id)) @method('PUT') @endif

        <div class="mb-3 grid grid-cols-2 gap-3">
            <x-admin.input type="number" name="declared_value_min" label="Declared value min" :value="$insurance->declared_value_min" required step="0.01" min="0" />
            <div>
                <x-admin.input type="number" name="declared_value_max" label="Declared value max" :value="$insurance->declared_value_max" step="0.01" min="0" />
                <p class="-mt-2 mb-3 text-xs text-slate-500">Empty = open-ended.</p>
            </div>
        </div>

        <x-admin.input type="number" name="cost" label="Cost" :value="$insurance->cost" required step="0.01" min="0" />

        <label class="mb-4 flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', $insurance->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand focus:ring-brand-light">
            Active
        </label>

        <div class="flex gap-2">
            <x-admin.button type="submit">Save</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('admin.rates.insurances.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
