@extends('layouts.tailwind.app')

@section('title', 'Table: ' . $table)
@section('page_title', 'Table: ' . $table)
@section('page_subtitle', $total . ' rows — read-only, sensitive fields masked')

@section('content')
    <x-admin.card class="mb-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.tools.database') }}" class="rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100"><i class="fas fa-arrow-left"></i> All tables</a>
            <span class="ml-auto text-xs text-slate-500">Page {{ $page }} / {{ $lastPage }}</span>
            @if ($page > 1)<a href="?page={{ $page - 1 }}" class="rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">&lsaquo; Prev</a>@endif
            @if ($page < $lastPage)<a href="?page={{ $page + 1 }}" class="rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">Next &rsaquo;</a>@endif
        </div>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        @foreach ($columns as $c)<th class="py-2 pr-4">{{ $c }}</th>@endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $row)
                        <tr>@foreach ((array) $row as $v)<td class="py-2 pr-4">{{ is_array($v) || is_object($v) ? json_encode($v) : ($v ?? 'NULL') }}</td>@endforeach</tr>
                    @empty
                        <tr><td colspan="{{ count($columns) }}" class="py-6 text-center text-slate-400">No rows.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection
