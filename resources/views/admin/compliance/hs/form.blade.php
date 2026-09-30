@extends('layouts.tailwind.app')

@section('title', isset($hs->id) ? 'Edit HS Code' : 'New HS Code')
@section('page_title', isset($hs->id) ? 'Edit HS Code' : 'New HS Code')

@section('content')
<x-admin.card class="mx-auto max-w-xl">
    <form method="POST" action="{{ isset($hs->id) ? route('admin.compliance.hs.update', $hs->id) : route('admin.compliance.hs.store') }}">
        @csrf
        @if (isset($hs->id)) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-x-3 sm:grid-cols-3">
            <div class="sm:col-span-1">
                <x-admin.input name="code" label="Code" :value="$hs->code" required maxlength="10" placeholder="e.g. 4202.12" />
            </div>
            <div class="sm:col-span-2">
                <x-admin.input name="description" label="Description" :value="$hs->description" required maxlength="500" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-x-3 sm:grid-cols-2">
            <x-admin.input name="category" label="Category" :value="$hs->category" maxlength="100" />
            <x-admin.input name="duty_hint" label="Duty hint (%)" type="number" step="0.01" min="0" max="99.99" :value="$hs->duty_hint" />
        </div>

        <div class="flex gap-2">
            <x-admin.button variant="secondary" tag="a" :href="route('admin.compliance.hs.index')">Cancel</x-admin.button>
            <x-admin.button type="submit" class="flex-1 justify-center">Save</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
