@extends('layouts.tailwind.app')

@section('title', 'Settings')
@section('page_title', 'Settings')
@section('page_subtitle', 'Business details, SEO, theme, integrations and admin preferences')

@section('content')
<div x-data="{ tab: '{{ array_key_first($tabs) }}' }" x-init="if (window.location.hash) { const h = window.location.hash.replace('#tab-', ''); if (h) { tab = h; } }">
    <form method="POST" action="{{ route('admin.settings.update') }}" id="dpSettingsForm">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-4 md:flex-row md:items-start">
            <div class="w-full shrink-0 md:w-56">
                <nav class="flex flex-row gap-1 overflow-x-auto rounded-lg border border-slate-200 bg-white p-1.5 shadow-sm md:flex-col md:overflow-visible">
                    @foreach ($tabs as $tabKey => $tab)
                        <a href="#tab-{{ $tabKey }}"
                           @click.prevent="tab = '{{ $tabKey }}'"
                           :class="tab === '{{ $tabKey }}' ? 'bg-brand text-white' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex shrink-0 items-center gap-2 whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition-colors">
                            <i class="fas fa-{{ $tab['icon'] ?? 'circle' }}"></i>{{ $tab['label'] ?? $tab['title'] ?? ucfirst($tabKey) }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="min-w-0 flex-1">
                @foreach ($tabs as $tabKey => $tab)
                    <div x-show="tab === '{{ $tabKey }}'" x-cloak>
                        <x-admin.card :title="($tab['label'] ?? $tab['title'] ?? ucfirst($tabKey))">
                            @foreach ($tab['fields'] ?? [] as $key => $field)
                                @php
                                    $type = $field['type'] ?? 'string';
                                    $value = old($key, $values[$key] ?? ($field['default'] ?? ''));
                                @endphp
                                <div class="mb-4 border-b border-slate-100 pb-4 last:mb-0 last:border-b-0 last:pb-0">
                                    <label for="f-{{ $key }}" class="mb-1 block text-sm font-medium text-slate-700">
                                        {{ $field['label'] ?? $key }}
                                    </label>

                                    @if ($type === 'bool')
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" id="f-{{ $key }}" name="{{ $key }}" value="1"
                                                {{ $value ? 'checked' : '' }}
                                                class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                                            <span class="text-sm text-slate-600">{{ $value ? 'Enabled' : 'Disabled' }}</span>
                                        </div>
                                    @elseif ($type === 'text')
                                        <textarea name="{{ $key }}" id="f-{{ $key }}" rows="3"
                                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has($key) ? ' border-red-400' : '' }}">{{ $value }}</textarea>
                                    @elseif ($type === 'select')
                                        <select name="{{ $key }}" id="f-{{ $key }}"
                                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has($key) ? ' border-red-400' : '' }}">
                                            @foreach (($field['options'] ?? []) as $optKey => $optLabel)
                                                <option value="{{ $optKey }}" {{ (string) $value === (string) $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($type === 'color')
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="{{ $key }}" id="f-{{ $key }}"
                                                class="h-9 w-20 rounded-md border border-slate-300{{ $errors->has($key) ? ' border-red-400' : '' }}"
                                                value="{{ $value }}">
                                            <span class="text-sm text-slate-500">{{ $value }}</span>
                                        </div>
                                    @elseif ($type === 'int')
                                        <input type="number" name="{{ $key }}" id="f-{{ $key }}"
                                            class="block w-40 rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has($key) ? ' border-red-400' : '' }}"
                                            value="{{ $value }}">
                                    @else
                                        <input type="text" name="{{ $key }}" id="f-{{ $key }}"
                                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light{{ $errors->has($key) ? ' border-red-400' : '' }}"
                                            value="{{ $value }}">
                                    @endif

                                    @error($key)
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror

                                    @if (!empty($field['help']))
                                        <p class="mt-1 text-xs text-slate-500">{{ $field['help'] }}</p>
                                    @endif

                                    @if (!empty($field['integration']) && isset($apiStatus[$key]))
                                        <div class="mt-2">
                                            <x-admin.badge :color="$apiStatus[$key]['configured'] ? 'green' : 'slate'" data-dp-integration="{{ $key }}">
                                                <i class="fas fa-{{ $apiStatus[$key]['configured'] ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                                {{ $apiStatus[$key]['configured'] ? 'Configured' : 'Not set' }}
                                            </x-admin.badge>
                                            <span class="ml-1 text-xs text-slate-500">env: {{ implode(', ', $apiStatus[$key]['env_vars']) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <div class="mt-2">
                                <x-admin.button type="submit" name="_tab" value="{{ $tabKey }}">
                                    <i class="fas fa-save mr-1"></i> Save settings
                                </x-admin.button>
                            </div>
                        </x-admin.card>
                    </div>
                @endforeach
            </div>
        </div>
    </form>
</div>
@endsection

@push('admin_scripts')
    <script>
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            document.addEventListener('DOMContentLoaded', function () {
                // refresh integration status chips from the JSON endpoint (presence only)
                var refresh = function () {
                    fetch('{{ route('admin.settings.apiStatus') }}', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
                    })
                        .then(function (res) { return res.json(); })
                        .then(function (json) {
                            (json.integrations || []).forEach(function (integration) {
                                var chip = document.querySelector('[data-dp-integration="' + integration.setting_key + '"]');
                                if (!chip) { return; }
                                chip.className = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ' +
                                    (integration.configured ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600');
                                chip.innerHTML = '<i class="fas fa-' + (integration.configured ? 'check-circle' : 'times-circle') + ' mr-1"></i> ' +
                                    (integration.configured ? 'Configured' : 'Not set');
                            });
                        })
                        .catch(function () { /* chips keep server-rendered state */ });
                };
                refresh();
            });
        })();
    </script>
@endpush
