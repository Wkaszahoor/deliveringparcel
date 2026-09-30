@extends('layouts.tailwind.app')

@section('title', 'Sanctions Screening')
@section('page_title', 'Sanctions Screening')
@section('page_subtitle', 'Fuzzy-match against the local restricted list (offline)')

@section('content')

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    <div class="lg:col-span-5">
        <x-admin.card title="Screen a name" class="mb-4">
            <form method="POST" action="{{ route('admin.compliance.sanctions.screen') }}">
                @csrf
                <x-admin.input name="name" label="Full name" :value="$name" required maxlength="255" />
                <x-admin.input name="country" label="Country code (optional boost)" maxlength="8" placeholder="e.g. RU" />
                <x-admin.button type="submit" class="w-full justify-center"><i class="fas fa-search"></i> Screen</x-admin.button>
            </form>
        </x-admin.card>

        <x-admin.card title="Recent screenings">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4">Matches</th>
                            <th class="py-2 pr-4">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($history as $h)
                            <tr>
                                <td class="py-2 pr-4">{{ $h->input_name }}</td>
                                <td class="py-2 pr-4"><x-admin.badge :color="$h->match_count ? 'red' : 'green'">{{ $h->match_count }}</x-admin.badge></td>
                                <td class="py-2 pr-4 text-xs text-slate-500">{{ optional($h->created_at)->format('M d, H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-slate-400">No screenings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>

    <div class="lg:col-span-7">
        <x-admin.card title="Result" class="h-full">
            @if (empty($result))
                <p class="mb-0 text-sm text-slate-400">Enter a name to screen against the sanctions list.</p>
            @elseif ($result['count'] === 0)
                <x-admin.alert type="success"><i class="fas fa-check-circle mr-1"></i> No matches found for &ldquo;{{ $name }}&rdquo;.</x-admin.alert>
            @else
                <x-admin.alert type="error"><i class="fas fa-exclamation-triangle mr-1"></i> {{ $result['count'] }} potential match(es) for &ldquo;{{ $name }}&rdquo;:</x-admin.alert>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">List</th>
                                <th class="py-2 pr-4">Country</th>
                                <th class="py-2 pr-4">Confidence</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($result['matches'] as $m)
                                <tr>
                                    <td class="py-2 pr-4"><strong>{{ $m['name'] }}</strong></td>
                                    <td class="py-2 pr-4">{{ $m['list'] }}</td>
                                    <td class="py-2 pr-4">{{ $m['country'] ?: '—' }}</td>
                                    <td class="py-2 pr-4">
                                        <div class="h-4 min-w-[90px] overflow-hidden rounded-full bg-slate-100">
                                            <div class="flex h-full items-center justify-center text-[10px] font-medium text-white {{ $m['score'] >= 85 ? 'bg-red-500' : 'bg-yellow-500' }}" style="width:{{ $m['score'] }}%">{{ $m['score'] }}%</div>
                                        </div>
                                    </td>
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
