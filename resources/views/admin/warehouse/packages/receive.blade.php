@extends('layouts.tailwind.app')

@section('title', isset($package->id) ? 'Receive Package #' . $package->id : 'Register Package')
@section('page_title', isset($package->id) ? 'Receive Package #' . $package->id : 'Register Inbound Package')
@section('page_subtitle', $package->expected_tracking ?: '')

@section('content')
<x-admin.card class="max-w-3xl">
    <form method="POST" enctype="multipart/form-data"
          action="{{ isset($package->id) ? route('admin.warehouse.packages.receive', $package->id) : route('admin.warehouse.packages.store') }}">
        @csrf
        @if (isset($package->id)) @method('PUT') @endif

        @if (isset($package->id))
            <x-admin.alert type="info" class="mb-4">
                Package #{{ $package->id }} — {{ optional($package->user)->name ?: 'no customer' }}
                ({{ optional($package->user)->email }}) &middot; expected: {{ $package->expected_tracking ?: '—' }}
            </x-admin.alert>

            <div class="mb-3">
                <label for="wp-bin" class="mb-1 block text-sm font-medium text-slate-700">Storage bin <span class="text-red-500">*</span></label>
                <select id="wp-bin" name="storage_bin_id" required
                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="">—</option>
                    @foreach ($bins as $b)
                        <option value="{{ $b->id }}" {{ old('storage_bin_id', $package->storage_bin_id) == $b->id ? 'selected' : '' }}>{{ $b->code }} ({{ $b->zone }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="wp-status" class="mb-1 block text-sm font-medium text-slate-700">Condition</label>
                <select id="wp-status" name="status"
                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="received" {{ old('status') === 'received' ? 'selected' : '' }}>Received OK</option>
                    <option value="damaged" {{ old('status') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="returned" {{ old('status') === 'returned' ? 'selected' : '' }}>Return to sender</option>
                </select>
            </div>
        @else
            <div class="mb-3">
                <label for="wp-user" class="mb-1 block text-sm font-medium text-slate-700">Customer (user id, optional)</label>
                <input id="wp-user" type="number" min="1" name="user_id" value="{{ old('user_id') }}"
                       class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
            </div>
            <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label for="wp-track" class="mb-1 block text-sm font-medium text-slate-700">Expected tracking #</label>
                    <input id="wp-track" type="text" name="expected_tracking" value="{{ old('expected_tracking') }}" maxlength="100"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label for="wp-status2" class="mb-1 block text-sm font-medium text-slate-700">Initial status</label>
                    <select id="wp-status2" name="status"
                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                        <option value="pending">Pending arrival</option>
                        <option value="received">Received now</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="wp-bin2" class="mb-1 block text-sm font-medium text-slate-700">Storage bin (if received now)</label>
                <select id="wp-bin2" name="storage_bin_id"
                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="">—</option>
                    @foreach ($bins as $b)
                        <option value="{{ $b->id }}">{{ $b->code }} ({{ $b->zone }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="wp-photos" class="mb-1 block text-sm font-medium text-slate-700">Condition photos (jpg/png/webp, 2MB)</label>
                <input id="wp-photos" type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp"
                       class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
            </div>
        @endif

        <div class="mb-4">
            <label for="wp-notes" class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
            <textarea id="wp-notes" name="notes" rows="2" maxlength="2000"
                      class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">{{ old('notes', $package->notes) }}</textarea>
        </div>

        <div class="flex gap-2">
            <x-admin.button variant="secondary" tag="a" :href="route('admin.warehouse.packages.index')">Cancel</x-admin.button>
            <x-admin.button type="submit" class="flex-1 justify-center">{{ isset($package->id) ? 'Confirm receipt' : 'Register' }}</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
