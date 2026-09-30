@extends('layouts.tailwind.app')

@section('title', isset($testimonial->id) ? 'Edit Testimonial' : 'New Testimonial')
@section('page_title', isset($testimonial->id) ? 'Edit Testimonial' : 'New Testimonial')

@section('content')
<x-admin.card class="max-w-2xl">
    <form method="POST" enctype="multipart/form-data" action="{{ isset($testimonial->id) ? route('admin.testimonials.update', $testimonial->id) : route('admin.testimonials.store') }}">
        @csrf
        @if (isset($testimonial->id)) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
            <x-admin.input name="user_name" label="Customer name" :value="$testimonial->user_name" required maxlength="255" />
            <x-admin.input name="role_or_company" label="Role / company" :value="$testimonial->role_or_company" maxlength="255" />
        </div>
        <x-admin.input name="country" label="Country" :value="$testimonial->country" maxlength="100" placeholder="e.g. United Kingdom" />

        <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
            <div class="mb-3">
                <label for="source" class="mb-1 block text-sm font-medium text-slate-700">Source</label>
                <select id="source" name="source" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    @foreach (['manual' => 'Manual (typed by admin)', 'google' => 'Google', 'trustpilot' => 'Trustpilot', 'sitejabber' => 'SiteJabber'] as $val => $label)
                        <option value="{{ $val }}" {{ old('source', $testimonial->source ?? 'manual') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.input name="source_url" label="Source URL" type="url" :value="$testimonial->source_url" placeholder="Link to the original review (optional)" />
        </div>

        <div class="mb-3">
            <label for="content" class="mb-1 block text-sm font-medium text-slate-700">Quote <span class="text-red-500">*</span></label>
            <textarea id="content" name="content" rows="4" required maxlength="3000" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('content') ? ' border-red-400' : '' }}">{{ old('content', $testimonial->content) }}</textarea>
            @error('content')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-3">
            <div class="mb-3">
                <label for="rating" class="mb-1 block text-sm font-medium text-slate-700">Rating (1-5)</label>
                <select id="rating" name="rating" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="">&mdash;</option>
                    @foreach ([5, 4, 3, 2, 1] as $r)
                        <option value="{{ $r }}" {{ old('rating', $testimonial->rating) == $r ? 'selected' : '' }}>{{ str_repeat('★', $r) }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.input type="number" name="sort" label="Sort order" min="0" :value="old('sort', $testimonial->sort ?? 0)" />
            <div class="mb-3 flex items-end pb-2">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $testimonial->is_published) ? 'checked' : '' }} class="rounded border-slate-300 text-brand focus:ring-brand-light">
                    Published
                </label>
            </div>
        </div>

        <div class="mb-3">
            <label for="avatar" class="mb-1 block text-sm font-medium text-slate-700">Avatar (jpg/png/webp, 2MB)</label>
            <input id="avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
            @error('avatar')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            @if ($testimonial->user_avatar)
                <img src="{{ asset($testimonial->user_avatar) }}" class="mt-2 h-16 w-16 rounded-full object-cover" alt="">
            @endif
        </div>

        <div class="mt-4 flex gap-2">
            <x-admin.button type="submit">Save</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('admin.testimonials.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
