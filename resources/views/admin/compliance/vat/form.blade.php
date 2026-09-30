@extends('layouts.tailwind.app')

@section('title', isset($rule->id) ? 'Edit VAT Rule' : 'New VAT Rule')
@section('page_title', isset($rule->id) ? 'Edit VAT Rule' : 'New VAT Rule')

@section('content')
<x-admin.card class="mx-auto max-w-xl">
    <form method="POST" action="{{ isset($rule->id) ? route('admin.compliance.vat.update', $rule->id) : route('admin.compliance.vat.store') }}">
        @csrf
        @if (isset($rule->id)) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-x-3 sm:grid-cols-2">
            <div class="mb-3">
                <label for="scheme" class="mb-1 block text-sm font-medium text-slate-700">Scheme</label>
                <select id="scheme" name="scheme" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    @foreach (config('admin_compliance.vat_schemes') as $key => $label)
                        <option value="{{ $key }}" {{ old('scheme', $rule->scheme) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.input name="country_code" label="Country code" :value="$rule->country_code" maxlength="8" />
        </div>

        <div class="grid grid-cols-1 gap-x-3 sm:grid-cols-2">
            <x-admin.input name="rate" label="Rate (%)" type="number" step="0.01" min="0" max="100" :value="$rule->rate" required />
            <x-admin.input name="registration_number" label="Registration number" :value="$rule->registration_number" maxlength="50" />
        </div>

        <div class="mb-3">
            <label for="notes" class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
            <textarea id="notes" name="notes" rows="2" maxlength="2000" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">{{ old('notes', $rule->notes) }}</textarea>
        </div>

        <label class="mb-4 flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand focus:ring-brand-light">
            Active
        </label>

        <div class="flex gap-2">
            <x-admin.button variant="secondary" tag="a" :href="route('admin.compliance.vat.index')">Cancel</x-admin.button>
            <x-admin.button type="submit" class="flex-1 justify-center">Save</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
