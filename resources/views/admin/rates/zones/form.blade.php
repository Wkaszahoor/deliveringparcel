@extends('layouts.tailwind.app')

@section('title', isset($zone->id) ? 'Edit Zone' : 'New Zone')
@section('page_title', isset($zone->id) ? 'Edit Zone' : 'New Zone')
@section('page_subtitle', $zone->name ?? 'Create a shipping zone')

@push('admin_styles')
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/select2/css/select2.min.css') }}">
@endpush

@section('content')
@php $zoneCountryIds = isset($zone->id) ? $zone->countries->pluck('country_id')->all() : []; @endphp
<x-admin.card class="mx-auto max-w-3xl" title="Zone details">
    <form method="POST" action="{{ isset($zone->id) ? route('admin.rates.zones.update', $zone->id) : route('admin.rates.zones.store') }}">
        @csrf
        @if (isset($zone->id)) @method('PUT') @endif

        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-12">
            <div class="sm:col-span-7">
                <x-admin.input name="name" label="Name" :value="$zone->name" required maxlength="255" />
            </div>
            <div class="sm:col-span-5">
                <x-admin.input name="code" label="Code" :value="$zone->code" required maxlength="20" placeholder="e.g. EU-WEST" />
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
            <textarea name="description" id="description" rows="2" maxlength="1000" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('description') ? ' border-red-400' : '' }}">{{ old('description', $zone->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if (\Schema::hasTable('countries'))
        <div class="mb-4">
            <label for="country_ids" class="mb-1 block text-sm font-medium text-slate-700">Assigned countries</label>
            <select name="country_ids[]" id="country_ids" multiple class="dp-select2-multi block w-full rounded-md border border-slate-300 text-sm" style="width:100%">
                @foreach (\App\Models\Country::orderBy('name')->get() as $c)
                    <option value="{{ $c->id }}" {{ in_array($c->id, $zoneCountryIds) ? 'selected' : '' }}>{{ $c->name }} ({{ strtoupper($c->iso2) }})</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Hold Ctrl/Cmd to multi-select, or use the tag picker above.</p>
        </div>
        @endif

        <div class="flex gap-2">
            <x-admin.button type="submit">Save</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('admin.rates.zones.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection

{{-- select2 for the multi-select country picker — plain (non "-bootstrap4") theme only, since the
     Tailwind layout loads no Bootstrap CSS for a bootstrap4 theme to hook into. Pushed so it runs
     after the layout's jQuery <script> tag. --}}
@push('admin_scripts')
<script src="{{ asset('dashbord/plugins/select2/js/select2.full.min.js') }}"></script>
<script>$(function () { $('.dp-select2-multi').select2({ width: '100%' }); });</script>
@endpush
