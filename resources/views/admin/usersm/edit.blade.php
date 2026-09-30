@extends('layouts.tailwind.app')

@section('title', 'Edit User')
@section('page_title', 'Edit User')
@section('page_subtitle', $user->name)

@section('content')
<div class="mx-auto max-w-xl">
    @if (session('error'))
        <x-admin.alert type="error">{{ session('error') }}</x-admin.alert>
    @endif

    <x-admin.card title="Profile details">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <x-admin.input label="Name" name="name" :value="$user->name" required maxlength="255" />

            <x-admin.input label="Phone" name="number" :value="$user->number" maxlength="50" />

            <div class="mb-3">
                <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select name="status" id="status"
                        class="block w-full rounded-md border px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light {{ $errors->has('status') ? 'border-red-400' : 'border-slate-300' }}"
                        @if ($isSelf) disabled @endif>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $user->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if ($isSelf)
                    <p class="mt-1 text-xs text-slate-500">You cannot change your own status.</p>
                @endif
                @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('admin.users.show', $user->id) }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">
                    Cancel
                </a>
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-md bg-brand px-3 py-2 text-sm font-medium text-white hover:bg-brand-dark">
                    Save changes
                </button>
            </div>
        </form>
    </x-admin.card>
</div>
@endsection
