@extends('layouts.tailwind.app')

@section('title', 'System Health')
@section('page_title', 'System Health')
@section('page_subtitle', 'Runtime environment and recent errors')

@section('content')
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fas fa-database {{ $health['db_ok'] ? 'text-green-500' : 'text-red-500' }}"></i>
                <div>
                    <div class="text-sm font-semibold text-slate-900">MySQL {{ $health['db_ok'] ? $health['db_version'] : 'DOWN' }}</div>
                    <div class="text-xs text-slate-500">{{ $health['db_latency'] ? $health['db_latency'] . ' ms' : '—' }}</div>
                </div>
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fab fa-php text-blue-500"></i>
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ $health['php'] }}</div>
                    <div class="text-xs text-slate-500">PHP</div>
                </div>
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fas fa-layer-group text-brand"></i>
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ $health['laravel'] }}</div>
                    <div class="text-xs text-slate-500">Laravel</div>
                </div>
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fas fa-hdd {{ ($health['disk_pct'] ?? 0) > 85 ? 'text-red-500' : 'text-yellow-500' }}"></i>
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ $health['disk_pct'] ?? '—' }}% used</div>
                    <div class="text-xs text-slate-500">{{ $health['disk_free_gb'] ?? '?' }} GB free</div>
                </div>
            </div>
        </x-admin.card>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <x-admin.card title="Environment">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-slate-100">
                        <tr><th class="py-2 pr-4 text-left font-medium text-slate-500">App env</th><td class="py-2">{{ $health['env'] }}</td></tr>
                        <tr><th class="py-2 pr-4 text-left font-medium text-slate-500">Cache</th><td class="py-2">{{ $health['cache'] }}</td></tr>
                        <tr><th class="py-2 pr-4 text-left font-medium text-slate-500">Queue</th><td class="py-2">{{ $health['queue'] }}</td></tr>
                        <tr><th class="py-2 pr-4 text-left font-medium text-slate-500">Session</th><td class="py-2">{{ $health['session'] }}</td></tr>
                        <tr><th class="py-2 pr-4 text-left font-medium text-slate-500">Server time</th><td class="py-2">{{ $health['server_time'] }}</td></tr>
                    </tbody>
                </table>
            </x-admin.card>
        </div>
        <div class="lg:col-span-8">
            <x-admin.card title="Last log entries (from laravel.log)">
                @if (empty($logErrors))
                    <p class="text-sm text-slate-400">No recent errors found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                                    <th class="py-2 pr-4">When</th>
                                    <th class="py-2 pr-4">Level</th>
                                    <th class="py-2 pr-4">Message</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($logErrors as $e)
                                    <tr>
                                        <td class="py-2 pr-4 text-slate-500">{{ $e['at'] }}</td>
                                        <td class="py-2 pr-4"><x-admin.badge :color="$e['level'] === 'error' ? 'red' : 'yellow'">{{ $e['level'] }}</x-admin.badge></td>
                                        <td class="py-2 pr-4">{{ $e['msg'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>
        </div>
    </div>
@endsection

@push('admin_scripts')
    {{-- Plain vanilla JS, no jQuery dependency — safe either way, but pushed after
         jQuery like every other page script for consistency with the layout's load order. --}}
    <script>setTimeout(function () { location.reload(); }, 30000);</script>
@endpush
