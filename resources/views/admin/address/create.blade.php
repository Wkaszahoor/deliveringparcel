@extends('layouts.tailwind.app')

@section('title', 'Add Shipping Address')
@section('page_title', 'Add Shipping Address')

@section('content')
<x-admin.card class="max-w-xl">
    <form method="POST" action="{{ route('address.store') }}">
        @csrf
        <x-admin.input name="name" label="Name" required />
        <x-admin.input name="number" label="Phone Number" required />
        <x-admin.input name="address1" label="Address" required />
        <x-admin.input name="address2" label="Address Line 2" />
        <x-admin.input name="city" label="City" required />
        <x-admin.input name="state" label="State" required />

        <div class="mb-3">
            <label for="country" class="mb-1 block text-sm font-medium text-slate-700">Country <span class="text-red-500">*</span></label>
            <select name="country" id="country" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('country') ? ' border-red-400' : '' }}">
                @include('admin.address._country-options')
            </select>
            @error('country')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-admin.input name="postalcode" label="Postal Code" required />

        <div class="mt-4 flex gap-2">
            <x-admin.button type="submit">Save Address</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('address.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
