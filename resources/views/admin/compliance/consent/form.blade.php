@extends('layouts.tailwind.app')

@section('title', isset($template->id) ? 'Edit Consent Template' : 'New Consent Template')
@section('page_title', isset($template->id) ? 'Edit Consent Template' : 'New Consent Template')

@section('content')
<x-admin.card class="mx-auto max-w-3xl">
    <form method="POST" action="{{ isset($template->id) ? route('admin.compliance.consent.update', $template->id) : route('admin.compliance.consent.store') }}">
        @csrf
        @if (isset($template->id)) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-x-3 sm:grid-cols-12">
            <div class="sm:col-span-7">
                <x-admin.input name="name" label="Name" :value="$template->name" required maxlength="255" />
            </div>
            <div class="sm:col-span-5">
                <x-admin.input name="slug" label="Slug" :value="$template->slug" required maxlength="255" />
            </div>
        </div>

        <div class="mb-3">
            <label for="body" class="mb-1 block text-sm font-medium text-slate-700">Body <span class="text-red-500">*</span></label>
            <textarea id="body" name="body" rows="8" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('body') ? ' border-red-400' : '' }}">{{ old('body', $template->body) }}</textarea>
            @error('body')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-x-3 sm:grid-cols-2">
            <div class="mb-3">
                <label for="trigger_type" class="mb-1 block text-sm font-medium text-slate-700">Trigger</label>
                <select id="trigger_type" name="trigger_type" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    @foreach (config('admin_compliance.consent_triggers') as $key => $label)
                        <option value="{{ $key }}" {{ old('trigger_type', $template->trigger_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.input name="trigger_value" label="Trigger value (country/route/cargo)" :value="$template->trigger_value" maxlength="100" />
        </div>

        <div class="mb-4 flex gap-6">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-brand focus:ring-brand-light">
                Active
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="requires_signature" value="1" {{ old('requires_signature', $template->requires_signature) ? 'checked' : '' }} class="rounded border-slate-300 text-brand focus:ring-brand-light">
                Requires signature
            </label>
        </div>

        <div class="flex gap-2">
            <x-admin.button variant="secondary" tag="a" :href="route('admin.compliance.consent.index')">Cancel</x-admin.button>
            <x-admin.button type="submit" class="flex-1 justify-center">Save</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
