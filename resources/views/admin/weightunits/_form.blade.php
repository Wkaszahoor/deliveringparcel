{{-- Shared weight unit form fields (create + edit). Expects: $unit --}}
<x-admin.card class="max-w-2xl">
    <x-admin.input name="name" label="Name" :value="old('name', $unit->name)" maxlength="100" required />

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
            <x-admin.input name="symbol" label="Symbol" :value="old('symbol', $unit->symbol)" maxlength="10" required />
            <p class="-mt-2 mb-3 text-xs text-slate-500">e.g. kg, g, lb, oz.</p>
        </div>
        <div>
            <x-admin.input type="number" name="grams" label="Grams" :value="old('grams', $unit->grams)" min="0" max="2147483647" step="1" required />
            <p class="-mt-2 mb-3 text-xs text-slate-500">Weight of one unit in grams (1 lb = 454 g).</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="mb-3 flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $unit->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-2 focus:ring-brand-light">
            <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
        </div>
        <div class="mb-3">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $unit->is_default) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-2 focus:ring-brand-light">
                <label for="is_default" class="text-sm font-medium text-slate-700">Default unit</label>
            </div>
            <p class="mt-1 text-xs text-slate-500">Only one unit can be the default.</p>
        </div>
    </div>
</x-admin.card>
