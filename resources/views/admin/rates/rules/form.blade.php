@extends('layouts.tailwind.app')

@section('title', isset($rule->id) ? 'Edit Rule' : 'New Rule')
@section('page_title', isset($rule->id) ? 'Edit Rate Rule' : 'New Rate Rule')
@section('page_subtitle', 'Route, weight bracket and pricing')

@section('content')
<x-admin.card class="mx-auto max-w-4xl" title="Rule details">
    <form method="POST" action="{{ isset($rule->id) ? route('admin.rates.rules.update', $rule->id) : route('admin.rates.rules.store') }}">
        @csrf
        @if (isset($rule->id)) @method('PUT') @endif

        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Origin zone <span class="text-red-500">*</span></label>
                <select name="origin_zone_id" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('origin_zone_id') ? ' border-red-400' : '' }}">
                    <option value="">&mdash;</option>
                    @foreach ($zones as $z)
                        <option value="{{ $z->id }}" {{ old('origin_zone_id', $rule->origin_zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }} ({{ $z->code }})</option>
                    @endforeach
                </select>
                @error('origin_zone_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Destination zone <span class="text-red-500">*</span></label>
                <select name="destination_zone_id" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('destination_zone_id') ? ' border-red-400' : '' }}">
                    <option value="">&mdash;</option>
                    @foreach ($zones as $z)
                        <option value="{{ $z->id }}" {{ old('destination_zone_id', $rule->destination_zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }} ({{ $z->code }})</option>
                    @endforeach
                </select>
                @error('destination_zone_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Service type <span class="text-red-500">*</span></label>
                <select name="service_type" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('service_type') ? ' border-red-400' : '' }}">
                    @foreach (config('admin_rates.service_types') as $k => $v)
                        <option value="{{ $k }}" {{ old('service_type', $rule->service_type) === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                @error('service_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <x-admin.input type="number" name="weight_min" label="Weight min (kg)" :value="$rule->weight_min" required step="0.01" min="0" />
            <x-admin.input type="number" name="weight_max" label="Weight max (kg)" :value="$rule->weight_max" required step="0.01" min="0" />
            <x-admin.input type="number" name="base_price" label="Base price" :value="$rule->base_price" required step="0.01" min="0" />
            <x-admin.input type="number" name="per_kg_price" label="Per kg price" :value="$rule->per_kg_price" required step="0.01" min="0" />
        </div>

        <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <x-admin.input type="number" name="transit_days_min" label="Transit min (days)" :value="$rule->transit_days_min" min="0" />
            <x-admin.input type="number" name="transit_days_max" label="Transit max (days)" :value="$rule->transit_days_max" min="0" />
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Priority</label>
                <input type="number" name="priority" min="0" value="{{ old('priority', $rule->priority ?? 0) }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                <p class="mt-1 text-xs text-slate-500">Higher wins on conflicts.</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Active</label>
                <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand focus:ring-brand-light">
                    Enabled
                </label>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <x-admin.input type="date" name="valid_from" label="Valid from" :value="old('valid_from', optional($rule->valid_from)->format('Y-m-d'))" />
            <x-admin.input type="date" name="valid_to" label="Valid to" :value="old('valid_to', optional($rule->valid_to)->format('Y-m-d'))" />
        </div>

        <div class="flex gap-2">
            <x-admin.button type="submit">Save</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('admin.rates.rules.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
