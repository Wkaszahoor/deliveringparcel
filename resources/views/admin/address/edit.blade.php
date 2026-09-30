@extends('layouts.tailwind.app')

@section('title', 'Edit Shipping Address')
@section('page_title', 'Edit Shipping Address')

@section('content')
@php $a = $address->first(); @endphp
<x-admin.card class="max-w-xl">
    <form method="POST" action="{{ route('address.update', $a->id) }}">
        @csrf
        @method('PUT')
        <x-admin.input name="name" label="Name" :value="$a->name" required />
        <x-admin.input name="number" label="Phone Number" :value="$a->number" required />
        <x-admin.input name="address1" label="Address" :value="$a->address1" required />
        <x-admin.input name="address2" label="Address Line 2" :value="$a->address2" />
        <x-admin.input name="city" label="City" :value="$a->city" required />
        <x-admin.input name="state" label="State" :value="$a->state" required />

        <div class="mb-3">
            <label for="country" class="mb-1 block text-sm font-medium text-slate-700">Country <span class="text-red-500">*</span></label>
            <select name="country" id="country" required class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('country') ? ' border-red-400' : '' }}">
                @include('admin.address._country-options', ['selected' => $a->country])
            </select>
            @error('country')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-admin.input name="postalcode" label="Postal Code" :value="$a->postalcode" required />

        <div class="mt-4 flex gap-2">
            <x-admin.button type="submit">Update Address</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('address.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
