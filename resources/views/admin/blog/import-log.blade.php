@extends('layouts.tailwind.app')

@section('title', 'Import Log')
@section('page_title', 'Import Log')
@section('page_subtitle', $log->filename)

@section('content')
<div class="mb-4 flex flex-wrap gap-2">
    <x-admin.button tag="a" variant="secondary" :href="route('admin.blog-import.index')">
        <i class="fas fa-arrow-left"></i> Back to Import
    </x-admin.button>
    <x-admin.button tag="a" :href="route('admin.blog-posts.index', ['batch' => $log->batch_id])" class="!bg-green-600 hover:!bg-green-700">
        <i class="fas fa-list"></i> View Imported Posts
    </x-admin.button>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @php
        $statTiles = [
            ['Posts Found', $log->total_found, 'fa-file-alt', 'text-blue-500'],
            ['Imported', $log->total_imported, 'fa-check', 'text-green-500'],
            ['Skipped', $log->total_skipped, 'fa-forward', 'text-slate-500'],
            ['Failed', $log->total_failed, 'fa-exclamation-triangle', 'text-red-500'],
        ];
    @endphp
    @foreach ($statTiles as $tile)
        <x-admin.card>
            <div class="flex items-center gap-2">
                <i class="fas {{ $tile[2] }} {{ $tile[3] }} text-xl"></i>
                <div>
                    <div class="text-lg font-semibold text-slate-900">{{ number_format($tile[1]) }}</div>
                    <div class="text-xs text-slate-500">{{ $tile[0] }}</div>
                </div>
            </div>
        </x-admin.card>
    @endforeach
</div>

<x-admin.card class="mb-6">
    <h3 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-slate-800">
        <i class="fas fa-info-circle"></i> Run Details
    </h3>
    <table class="w-full text-left text-sm">
        <tr class="border-b border-slate-100">
            <th class="w-44 py-1.5 pr-4 font-medium text-slate-600">Batch ID</th>
            <td class="py-1.5"><code>{{ $log->batch_id }}</code></td>
        </tr>
        <tr class="border-b border-slate-100">
            <th class="py-1.5 pr-4 font-medium text-slate-600">File</th>
            <td class="py-1.5">{{ $log->filename }}</td>
        </tr>
        <tr class="border-b border-slate-100">
            <th class="py-1.5 pr-4 font-medium text-slate-600">File type</th>
            <td class="py-1.5"><code>{{ strtoupper($log->file_type) }}</code></td>
        </tr>
        <tr class="border-b border-slate-100">
            <th class="py-1.5 pr-4 font-medium text-slate-600">Status</th>
            <td class="py-1.5">
                <x-admin.badge :color="$log->status === 'completed' ? 'green' : ($log->status === 'partial' ? 'yellow' : ($log->status === 'processing' ? 'blue' : 'red'))">
                    {{ ucfirst($log->status) }}
                </x-admin.badge>
            </td>
        </tr>
        <tr class="border-b border-slate-100">
            <th class="py-1.5 pr-4 font-medium text-slate-600">Started</th>
            <td class="py-1.5">{{ optional($log->started_at)->format('Y-m-d H:i:s') }}</td>
        </tr>
        <tr class="border-b border-slate-100">
            <th class="py-1.5 pr-4 font-medium text-slate-600">Completed</th>
            <td class="py-1.5">{{ optional($log->completed_at)->format('Y-m-d H:i:s') }}</td>
        </tr>
        <tr>
            <th class="py-1.5 pr-4 font-medium text-slate-600">Imported by</th>
            <td class="py-1.5">{{ optional(\App\Models\User::find($log->imported_by))->name ?? '—' }}</td>
        </tr>
    </table>
</x-admin.card>

<x-admin.card>
    <h3 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-slate-800">
        <i class="fas fa-bug"></i> Error Log
    </h3>
    @if (!empty($log->error_log))
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="w-14 py-2 pr-4">#</th>
                        <th class="py-2 pr-4">URL</th>
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach ($log->error_log as $i => $err)
                    <tr class="bg-red-50/60">
                        <td class="py-2 pr-4">{{ $i + 1 }}</td>
                        <td class="py-2 pr-4">
                            <a href="{{ $err['url'] ?? '#' }}" target="_blank" rel="noopener" class="text-brand hover:underline">
                                {{ \Illuminate\Support\Str::limit($err['url'] ?? '—', 60) }}
                            </a>
                        </td>
                        <td class="py-2 pr-4">{{ \Illuminate\Support\Str::limit($err['title'] ?? '—', 50) }}</td>
                        <td class="py-2 pr-4"><code>{{ $err['error'] ?? '' }}</code></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="py-4 text-center text-slate-400">No errors recorded for this import.</p>
    @endif
</x-admin.card>
@endsection
