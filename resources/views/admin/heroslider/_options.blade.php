{{-- Hero slide per-slide options, grouped per config('admin_heroslider.option_fields').
    Expects: $optionFields (key => def), $values (key => current value) --}}
@php
    $groups = ['Layout' => 'Layout', 'Buttons' => 'Buttons', 'Colors' => 'Colors', 'Advanced' => 'Advanced'];
    $truthy = function ($v) { return filter_var($v, FILTER_VALIDATE_BOOLEAN); };
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
@foreach ($groups as $group => $groupLabel)
    @php
        $fields = array_filter($optionFields, function ($def) use ($group) {
            return ($def['group'] ?? '') === $group;
        });
    @endphp
    @if (count($fields))
        <x-admin.card :title="$groupLabel">
            @foreach ($fields as $key => $def)
                @php($value = $values[$key] ?? ($def['default'] ?? ''))
                <div class="mb-3">
                    @if (($def['type'] ?? 'text') !== 'toggle')
                        <label for="option-{{ $key }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $def['label'] ?? $key }}</label>
                    @endif

                    @switch($def['type'] ?? 'text')
                        @case('text')
                            <input type="text" id="option-{{ $key }}" name="options[{{ $key }}]" value="{{ $value }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                            @break
                        @case('number')
                            <input type="number" id="option-{{ $key }}" name="options[{{ $key }}]" value="{{ $value }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light"
                                @if(isset($def['min'])) min="{{ $def['min'] }}" @endif
                                @if(isset($def['max'])) max="{{ $def['max'] }}" @endif
                                @if(isset($def['step'])) step="{{ $def['step'] }}" @endif>
                            @break
                        @case('range')
                            <div class="flex items-center gap-2">
                                <input type="range" id="option-{{ $key }}" name="options[{{ $key }}]" value="{{ $value }}" class="h-2 w-full flex-grow cursor-pointer accent-brand"
                                    data-dp-range-output="#option-{{ $key }}-out"
                                    @if(isset($def['min'])) min="{{ $def['min'] }}" @endif
                                    @if(isset($def['max'])) max="{{ $def['max'] }}" @endif
                                    @if(isset($def['step'])) step="{{ $def['step'] }}" @endif>
                                <span id="option-{{ $key }}-out" class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ $value }}</span>
                            </div>
                            @break
                        @case('select')
                            <select id="option-{{ $key }}" name="options[{{ $key }}]" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                @foreach (($def['options'] ?? []) as $optKey => $optLabel)
                                    <option value="{{ $optKey }}" {{ (string) $value === (string) $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                                @endforeach
                            </select>
                            @break
                        @case('color')
                            <div class="flex items-center gap-2">
                                <input type="color" id="option-{{ $key }}" name="options[{{ $key }}]" value="{{ $value }}" class="h-9 w-16 cursor-pointer rounded-md border border-slate-300 p-1">
                                <code class="text-xs text-slate-500">{{ $value }}</code>
                            </div>
                            @break
                        @case('toggle')
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" id="option-{{ $key }}" name="options[{{ $key }}]" value="1" {{ $truthy($value) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                                {{ $def['label'] ?? $key }}
                            </label>
                            @break
                        @default
                            <input type="text" id="option-{{ $key }}" name="options[{{ $key }}]" value="{{ $value }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    @endswitch

                    @if (!empty($def['hint']))
                        <p class="mt-1 text-xs text-slate-500">{{ $def['hint'] }}</p>
                    @endif
                </div>
            @endforeach
        </x-admin.card>
    @endif
@endforeach
</div>
