@extends('layouts.tailwind.app')

@section('title', 'Shipping Addresses')
@section('page_title', 'Shipping Addresses')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Name" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="number" value="{{ $filters['number'] ?? '' }}" placeholder="Number" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="city" value="{{ $filters['city'] ?? '' }}" placeholder="City" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="state" value="{{ $filters['state'] ?? '' }}" placeholder="State" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="country" value="{{ $filters['country'] ?? '' }}" placeholder="Country" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <x-admin.button variant="secondary" tag="a" href="{{ route('address.index') }}">Reset</x-admin.button>
        <x-admin.button type="submit">Filter</x-admin.button>
    </form>
    <x-admin.button tag="a" :href="route('address.create')"><i class="fas fa-plus"></i> Add Address</x-admin.button>
</div>

<x-admin.card>
    <p class="mb-3 text-xs text-slate-500">{{ $address->total() }} total</p>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Name</th>
                    <th class="py-2 pr-4">Number</th>
                    <th class="py-2 pr-4">Address 1</th>
                    <th class="py-2 pr-4">Address 2</th>
                    <th class="py-2 pr-4">City</th>
                    <th class="py-2 pr-4">State/Province</th>
                    <th class="py-2 pr-4">Postal Code</th>
                    <th class="py-2 pr-4">Country</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($address as $a)
                    <tr>
                        <td class="py-2 pr-4">{{ $a->name }}</td>
                        <td class="py-2 pr-4">{{ $a->number }}</td>
                        <td class="py-2 pr-4">{{ $a->address1 }}</td>
                        <td class="py-2 pr-4">{{ $a->address2 ?? '-' }}</td>
                        <td class="py-2 pr-4">{{ $a->city }}</td>
                        <td class="py-2 pr-4">{{ $a->state }}</td>
                        <td class="py-2 pr-4">{{ $a->postalcode }}</td>
                        <td class="py-2 pr-4">{{ $a->country }}</td>
                        <td class="py-2 pr-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('address.edit', $a->id) }}" class="text-brand hover:underline">Edit</a>
                                <form action="{{ route('address.destroy', $a->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this shipping address? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-6 text-center text-slate-400">No addresses found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $address->links('pagination.tailwind-admin') }}
    </div>
</x-admin.card>
@endsection
