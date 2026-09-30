@extends('layouts.tailwind.app')

@section('title', isset($state->id) ? 'Edit State' : 'New State')
@section('page_title', isset($state->id) ? 'Edit State' : 'New State')
@section('page_subtitle', $state->name ?? '')

@section('content')
<x-admin.card class="max-w-2xl">
    <form method="POST" action="{{ isset($state->id) ? route('admin.geo.states.update', $state->id) : route('admin.geo.states.store') }}">
        @csrf
        @if (isset($state->id)) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-7">
                <x-admin.input name="name" label="Name" :value="$state->name" required maxlength="255" />
            </div>
            <div class="md:col-span-5">
                <x-admin.input name="code" label="Code" :value="$state->code" maxlength="20" />
            </div>
        </div>

        @if ($countries->isNotEmpty())
            <div class="mb-3">
                <label for="country_id" class="mb-1 block text-sm font-medium text-slate-700">Country</label>
                <select id="country_id" name="country_id"
                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="">—</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c->id }}" {{ (string) old('country_id', $state->country_id) === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <x-admin.input name="country_code" label="Country code (2-letter)" :value="$state->country_code" maxlength="8" placeholder="US" />
        @endif

        <div class="mb-4 flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $state->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
            <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
        </div>

        <div class="flex gap-2">
            <x-admin.button variant="secondary" tag="a" :href="route('admin.geo.states.index')">Cancel</x-admin.button>
            <x-admin.button type="submit" class="flex-1 justify-center">Save</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
