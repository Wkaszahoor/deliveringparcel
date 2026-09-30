@extends('layouts.tailwind.app')

@section('title', 'GDPR Export')
@section('page_title', 'GDPR Data Export')
@section('page_subtitle', 'Art. 15 — full user data dump with audit trail')

@section('content')

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
    <div class="lg:col-span-5">
        <x-admin.card title="Find user" class="mb-4">
            <form method="GET" action="{{ route('admin.compliance.gdpr') }}">
                <x-admin.input type="email" name="email" label="Account email" :value="$email" required />
                <x-admin.button type="submit" class="w-full justify-center"><i class="fas fa-search"></i> Look up</x-admin.button>
            </form>
        </x-admin.card>

        <x-admin.card title="Recent exports">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">User</th>
                            <th class="py-2 pr-4">Rows</th>
                            <th class="py-2 pr-4">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($exports as $e)
                            <tr>
                                <td class="py-2 pr-4">{{ optional($e->user)->name ?: '#' . $e->user_id }}</td>
                                <td class="py-2 pr-4">{{ number_format($e->file_rows) }}</td>
                                <td class="py-2 pr-4 text-xs text-slate-500">{{ optional($e->created_at)->format('M d, H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-slate-400">No exports yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>

    <div class="lg:col-span-7">
        <x-admin.card title="Data summary" class="h-full">
            @if (empty($user))
                <p class="mb-0 text-sm text-slate-400">Look up a user by email to preview their data footprint and generate the export.</p>
            @else
                <h5 class="mb-2 text-base font-semibold text-slate-900">{{ $user->name }} <small class="font-normal text-slate-500">{{ $user->email }}</small></h5>
                <table class="w-full text-sm">
                    @foreach ($summary as $entity => $count)
                        <tr><th class="w-1/2 py-1 text-left align-top font-medium text-slate-500">{{ ucwords(str_replace('_', ' ', $entity)) }}</th><td class="py-1">{{ number_format($count) }}</td></tr>
                    @endforeach
                </table>
                {{-- The original AdminLTE anchor used data-dp-confirm (DP.bindConfirms), which
                     builds a POST form with _method=DELETE by default when no data-method is set.
                     That's incompatible with this route, which only accepts GET (see
                     admin.compliance.gdpr.export in route:list) — a pre-existing mismatch in the
                     legacy markup that would have 404'd, not a deliberate design. Preserved as a
                     plain confirm() + GET navigation instead, which keeps the same "confirm before
                     large export" UX without depending on the DELETE-shaped confirm helper. --}}
                <a href="{{ route('admin.compliance.gdpr.export', ['user_id' => $user->id]) }}"
                   id="dp-gdpr-export"
                   class="flex w-full items-center justify-center gap-1.5 rounded-md bg-yellow-500 px-3 py-2 text-sm font-medium text-white hover:bg-yellow-600">
                    <i class="fas fa-download"></i> Generate export (JSON)
                </a>
            @endif
        </x-admin.card>
    </div>
</div>

@endsection

@push('admin_scripts')
<script>
(function () {
    var link = document.getElementById('dp-gdpr-export');
    if (!link) return;
    link.addEventListener('click', function (e) {
        if (!window.confirm('Generate GDPR export?\n\nA full JSON dump will be downloaded and logged.')) {
            e.preventDefault();
        }
    });
})();
</script>
@endpush
