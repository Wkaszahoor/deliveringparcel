{{-- Shared country form fields (create + edit). Expects: $country --}}
<x-admin.card class="max-w-2xl">
    <x-admin.input name="name" label="Name" :value="old('name', $country->name)" maxlength="255" required />

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.input name="iso2" label="ISO2" class="uppercase" :value="old('iso2', $country->iso2)" maxlength="2" minlength="2" pattern="[A-Za-z]{2}" title="Exactly 2 letters, e.g. US" :required="empty($country->id)" />
        <x-admin.input name="iso3" label="ISO3" class="uppercase" :value="old('iso3', $country->iso3)" maxlength="3" minlength="3" pattern="[A-Za-z]{3}" title="Exactly 3 letters, e.g. USA" :required="empty($country->id)" />
        <x-admin.input name="dial_code" label="Dial code" :value="old('dial_code', $country->dial_code)" maxlength="10" pattern="\+?[0-9]{1,6}" title="Optional + followed by up to 6 digits, e.g. +1" />
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-center">
        <x-admin.input type="number" name="shipping_rate" label="Base shipping rate (USD)" :value="old('shipping_rate', $country->shipping_rate)" min="0" max="99999999.99" step="0.01" />
        <div class="mb-3 flex items-center gap-2 sm:pt-6">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $country->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-2 focus:ring-brand-light">
            <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
        </div>
    </div>
</x-admin.card>
