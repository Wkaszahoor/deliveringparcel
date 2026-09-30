@extends('layouts.tailwind.app')

@section('title', isset($city->id) ? 'Edit City' : 'New City')
@section('page_title', isset($city->id) ? 'Edit City' : 'New City')
@section('page_subtitle', $city->name ?? '')

@section('content')
<x-admin.card class="max-w-2xl">
    <form method="POST" action="{{ isset($city->id) ? route('admin.geo.cities.update', $city->id) : route('admin.geo.cities.store') }}">
        @csrf
        @if (isset($city->id)) @method('PUT') @endif

        <div class="mb-3">
            <label for="geo_state_id" class="mb-1 block text-sm font-medium text-slate-700">State <span class="text-red-500">*</span></label>
            <select id="geo_state_id" name="geo_state_id" required
                    class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('geo_state_id') ? ' border-red-400' : '' }}">
                <option value="">—</option>
                @foreach ($states as $s)
                    <option value="{{ $s->id }}" {{ (string) old('geo_state_id', $city->geo_state_id) === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            @error('geo_state_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <x-admin.input name="name" label="City name" :value="$city->name" required maxlength="255" />

        <div class="mb-4 flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $city->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
            <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
        </div>

        <div class="flex gap-2">
            <x-admin.button variant="secondary" tag="a" :href="route('admin.geo.cities.index')">Cancel</x-admin.button>
            <x-admin.button type="submit" class="flex-1 justify-center">Save</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
