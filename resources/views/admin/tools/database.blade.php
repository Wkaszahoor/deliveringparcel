@extends('layouts.tailwind.app')

@section('title', 'Database')
@section('page_title', 'Database Viewer')
@section('page_subtitle', 'Read-only table browser — sensitive columns masked')

@section('content')
    <x-admin.card class="mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <input type="text" id="f-q" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" style="max-width:260px" placeholder="Filter tables…">
            <span class="ml-auto text-xs text-slate-500">{{ $tables->count() }} tables (STRICT read-only)</span>
        </div>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="db-tables">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Table</th>
                        <th class="py-2 pr-4">Rows (approx)</th>
                        <th class="py-2 pr-4">Size</th>
                        <th class="py-2 pr-4 text-right">Browse</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($tables as $t)
                        <tr>
                            <td class="py-2 pr-4"><strong>{{ $t['name'] }}</strong></td>
                            <td class="py-2 pr-4">{{ number_format($t['rows']) }}</td>
                            <td class="py-2 pr-4">{{ $t['size'] }}</td>
                            <td class="py-2 pr-4 text-right"><a href="{{ route('admin.tools.database-browse', $t['name']) }}" class="text-slate-400 hover:text-brand" title="Browse"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

@push('admin_scripts')
    {{-- Plain vanilla JS client-side filter, no jQuery dependency. --}}
    <script>
    (function () {
        var q = document.getElementById('f-q');
        q.addEventListener('input', function () {
            var v = q.value.toLowerCase();
            document.querySelectorAll('#db-tables tbody tr').forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().indexOf(v) > -1 ? '' : 'none';
            });
        });
    })();
    </script>
@endpush
