@extends('layouts.tailwind.app')

@section('title', 'Sitemap Settings')
@section('page_title', 'Sitemap Settings')
@section('page_subtitle', 'Control what /sitemap.xml includes — sections, defaults, extra URLs — and ping Google after changes.')

@section('content')
<div class="mb-4 flex justify-end gap-2">
    <x-admin.button variant="secondary" tag="a" :href="url('/sitemap.xml')" target="_blank" rel="noopener">
        <i class="fas fa-eye"></i> View Sitemap
    </x-admin.button>
    <x-admin.button variant="secondary" type="button" id="pingGoogle">
        <i class="fab fa-google"></i> Ping Google
    </x-admin.button>
</div>

<form method="POST" action="{{ route('admin.sitemap.settings.update') }}">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        <div class="lg:col-span-7 space-y-4">
            <x-admin.card title="General">
                <div class="mb-3 flex items-start gap-2">
                    <input type="checkbox" id="sitemap_enabled" name="sitemap_enabled" value="1"
                        {{ old('sitemap_enabled', $values['sitemap.enabled']) === '1' ? 'checked' : '' }}
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                    <label for="sitemap_enabled" class="text-sm text-slate-700"><strong>Sitemap enabled</strong> — when off, /sitemap.xml returns 404</label>
                </div>

                <div class="mb-3">
                    <label for="sitemap_base_url" class="mb-1 block text-sm font-medium text-slate-700">
                        Base URL <span class="text-slate-400">(blank = app URL)</span>
                    </label>
                    <input type="url" id="sitemap_base_url" name="sitemap_base_url"
                        value="{{ old('sitemap_base_url', $values['sitemap.base_url']) }}"
                        placeholder="https://deliveringparcel.com"
                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('sitemap_base_url') ? ' border-red-400' : '' }}">
                    @error('sitemap_base_url')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label for="sitemap_default_priority" class="mb-1 block text-sm font-medium text-slate-700">Default priority (0.1–1.0)</label>
                        <input type="number" step="0.1" min="0.1" max="1" id="sitemap_default_priority" name="sitemap_default_priority"
                            value="{{ old('sitemap_default_priority', $values['sitemap.default_priority']) }}"
                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('sitemap_default_priority') ? ' border-red-400' : '' }}">
                        @error('sitemap_default_priority')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sitemap_default_changefreq" class="mb-1 block text-sm font-medium text-slate-700">Default changefreq</label>
                        <select id="sitemap_default_changefreq" name="sitemap_default_changefreq"
                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                            @foreach (['always','hourly','daily','weekly','monthly','yearly','never'] as $f)
                                <option value="{{ $f }}" {{ old('sitemap_default_changefreq', $values['sitemap.default_changefreq']) === $f ? 'selected' : '' }}>{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sitemap_cache_minutes" class="mb-1 block text-sm font-medium text-slate-700">Cache minutes (0 = no cache)</label>
                        <input type="number" min="0" max="1440" id="sitemap_cache_minutes" name="sitemap_cache_minutes"
                            value="{{ old('sitemap_cache_minutes', $values['sitemap.cache_minutes']) }}"
                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has('sitemap_cache_minutes') ? ' border-red-400' : '' }}">
                        @error('sitemap_cache_minutes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Sections">
                @foreach (['sitemap.include_static' => 'Static pages (home, legal, /blog, /services, track-order…)', 'sitemap.include_blog' => 'Published blog posts', 'sitemap.include_services' => 'Available services', 'sitemap.include_shop' => 'Active shop products'] as $key => $label)
                    @php $formKey = str_replace('.', '_', $key); @endphp
                    <div class="mb-2 flex items-start gap-2 last:mb-0">
                        <input type="checkbox" id="{{ $formKey }}" name="{{ $formKey }}" value="1"
                            {{ old($formKey, $values[$key]) === '1' ? 'checked' : '' }}
                            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                        <label for="{{ $formKey }}" class="text-sm text-slate-700">{{ $label }}</label>
                    </div>
                @endforeach
            </x-admin.card>
        </div>

        <div class="lg:col-span-5 space-y-4">
            <x-admin.card>
                <x-slot:title>Extra URLs <span class="font-normal text-slate-400">(one per line)</span></x-slot:title>
                <textarea name="sitemap_extra_urls" rows="5" placeholder="https://deliveringparcel.com/some-page"
                    class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">{{ old('sitemap_extra_urls', $values['sitemap.extra_urls']) }}</textarea>
            </x-admin.card>

            <x-admin.card>
                <x-slot:title>Excluded URLs <span class="font-normal text-slate-400">(one per line — prefix match)</span></x-slot:title>
                <textarea name="sitemap_exclude_urls" rows="5" placeholder="/some-page-to-hide"
                    class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">{{ old('sitemap_exclude_urls', $values['sitemap.exclude_urls']) }}</textarea>
            </x-admin.card>

            <x-admin.button type="submit" class="w-full justify-center">
                <i class="fas fa-save"></i> Save Sitemap Settings
            </x-admin.button>
            <x-admin.button variant="secondary" type="button" id="regenBtn" class="w-full justify-center">
                <i class="fas fa-sync-alt"></i> Regenerate Now (clear cache)
            </x-admin.button>
        </div>
    </div>
</form>

@push('admin_scripts')
<script>
(function () {
    async function post(url) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        });
        const json = await res.json().catch(() => ({}));
        alert(json.message || (res.ok ? 'Done' : 'Failed'));
    }
    document.getElementById('regenBtn').addEventListener('click', () => post('{{ route('admin.sitemap.settings.regenerate') }}'));
    document.getElementById('pingGoogle').addEventListener('click', () => post('{{ route('admin.sitemap.settings.ping') }}'));
})();
</script>
@endpush
@endsection
