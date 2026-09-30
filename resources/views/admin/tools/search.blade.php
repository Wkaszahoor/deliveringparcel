@extends('layouts.tailwind.app')

@section('title', 'Global Search')
@section('page_title', 'Global Search')
@section('page_subtitle', 'Search across users, orders, quotes, messages, content')

@section('content')
    <x-admin.card class="mb-4">
        <form method="GET" action="{{ route('admin.tools.search') }}" class="flex flex-wrap items-center gap-2">
            <input name="q" value="{{ $q }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:380px" placeholder="Search everything…" autofocus>
            <x-admin.button type="submit"><i class="fas fa-search"></i> Search</x-admin.button>
            <div class="ml-auto flex gap-2">
                <x-admin.button variant="secondary" tag="a" :href="route('admin.tools.health')"><i class="fas fa-heartbeat"></i> Health</x-admin.button>
                <x-admin.button variant="secondary" tag="a" :href="route('admin.tools.database')"><i class="fas fa-database"></i> DB</x-admin.button>
                <x-admin.button variant="secondary" tag="a" :href="route('admin.tools.pdf')"><i class="fas fa-file-pdf"></i> PDF</x-admin.button>
            </div>
        </form>
    </x-admin.card>

    @if ($q !== '')
        @forelse (array_slice($results, 0, 8, true) as $group => $items)
            <x-admin.card class="mb-3">
                <h3 class="mb-2 text-sm font-semibold text-slate-800">{{ $group }} <x-admin.badge>{{ count($items) }}</x-admin.badge></h3>
                <div class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <a href="{{ $item['url'] }}" class="block py-2 text-sm text-slate-700 hover:text-brand">{{ $item['label'] }}</a>
                    @endforeach
                </div>
            </x-admin.card>
        @empty
            <x-admin.alert type="info">No results for &ldquo;{{ $q }}&rdquo;.</x-admin.alert>
        @endforelse
    @endif
@endsection
