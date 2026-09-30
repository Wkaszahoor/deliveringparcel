@extends('layouts.tailwind.app')

@section('title', 'Blog Import')
@section('page_title', 'Blog Import')
@section('page_subtitle', 'Import WordPress posts (WXR export, atom/RSS feed or sitemap) into the new-design blog.')

@section('content')
@if (session('success'))
    <x-admin.alert type="success">{{ session('success') }}</x-admin.alert>
@endif
@if (session('import_result'))
    @php $r = session('import_result'); @endphp
    <x-admin.alert :type="$r['status'] === 'completed' ? 'success' : ($r['status'] === 'partial' ? 'info' : 'error')">
        <h5 class="mb-1 flex items-center gap-1.5 font-semibold">
            <i class="fas fa-{{ $r['status'] === 'completed' ? 'check-circle' : ($r['status'] === 'partial' ? 'exclamation-triangle' : 'times-circle') }}"></i>
            Import {{ ucfirst($r['status']) }}
        </h5>
        <ul class="mb-2 list-inside list-disc">
            <li>Posts found in file: <strong>{{ $r['found'] }}</strong></li>
            <li>Successfully imported: <strong class="text-green-700">{{ $r['imported'] }}</strong></li>
            <li>Skipped (already exists): <strong>{{ $r['skipped'] }}</strong></li>
            <li>Failed: <strong class="text-red-700">{{ $r['failed'] }}</strong></li>
        </ul>
        <x-admin.button tag="a" variant="primary" :href="route('admin.blog-posts.index', ['batch' => $r['batch_id']])" class="!bg-green-600 hover:!bg-green-700">
            View Imported Posts <i class="fas fa-arrow-right ml-1"></i>
        </x-admin.button>
    </x-admin.alert>
@endif

<x-admin.card class="mb-6">
    <h3 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-slate-800">
        <i class="fas fa-file-import"></i> Import WordPress Blog Posts
    </h3>

    <form method="POST" action="{{ route('admin.blog-import.upload') }}" enctype="multipart/form-data" id="importForm">
        @csrf

        <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            <strong>Supported file formats:</strong>
            <ul class="mb-0 mt-1 list-inside list-disc">
                <li><strong>.atom</strong> — Atom feed / sitemap.atom (WordPress)</li>
                <li><strong>.xml</strong> — WordPress WXR export (Tools → Export All)</li>
                <li><strong>.xml</strong> — Sitemap XML (URLs fetched automatically)</li>
                <li><strong>.rss</strong> — RSS 2.0 feed with content:encoded</li>
            </ul>
            <p class="mb-0 mt-2">
                <strong>Best results:</strong> Use WordPress Admin → Tools → Export → All Content →
                Download Export File. This gives a full WXR file with all content, images, categories and tags.
            </p>
        </div>

        <div class="mb-4">
            <label for="import_file" class="mb-1 block text-sm font-medium text-slate-700">Select Import File <span class="text-red-500">*</span></label>
            <input type="file" id="import_file" name="import_file" accept=".xml,.atom,.rss,.txt" required
                class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
            <p class="mt-1 text-xs text-slate-500">Max 50MB</p>
            @error('import_file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="default_status" class="mb-1 block text-sm font-medium text-slate-700">Import posts as</label>
                <select name="default_status" id="default_status"
                    class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                    <option value="draft">Draft (review before publishing)</option>
                    <option value="published">Published (go live immediately)</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Overwrite existing posts?</label>
                <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="overwrite" value="1" id="overwrite" class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                    Yes — update if already imported
                </label>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Fetch full content from URLs?</label>
                <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="fetch_content" value="1" id="fetch_content" checked class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                    Yes — crawl each post URL (slower, more complete)
                </label>
            </div>
        </div>

        <div id="uploadProgress" style="display:none" class="mb-4">
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full w-full animate-pulse bg-brand"></div>
            </div>
            <p class="mt-2 text-sm text-slate-500"><i class="fas fa-spinner fa-spin"></i> Downloading images and fetching content — please wait. Processing import… this may take several minutes for large files.</p>
        </div>

        <x-admin.button type="submit" id="submitBtn" class="!px-4 !py-2.5 !text-base">
            <i class="fas fa-upload"></i> Start Import
        </x-admin.button>
    </form>
</x-admin.card>

<x-admin.card>
    <h3 class="mb-3 flex items-center gap-1.5 text-sm font-semibold text-slate-800">
        <i class="fas fa-history"></i> Import History
    </h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">File</th>
                    <th class="py-2 pr-4">Type</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Found</th>
                    <th class="py-2 pr-4">Imported</th>
                    <th class="py-2 pr-4">Skipped</th>
                    <th class="py-2 pr-4">Failed</th>
                    <th class="py-2 pr-4">Date</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $log)
                <tr>
                    <td class="py-2 pr-4">{{ $log->filename }}</td>
                    <td class="py-2 pr-4"><code>{{ strtoupper($log->file_type) }}</code></td>
                    <td class="py-2 pr-4">
                        <x-admin.badge :color="$log->status === 'completed' ? 'green' : ($log->status === 'partial' ? 'yellow' : ($log->status === 'processing' ? 'blue' : 'red'))">
                            {{ ucfirst($log->status) }}
                        </x-admin.badge>
                    </td>
                    <td class="py-2 pr-4">{{ $log->total_found }}</td>
                    <td class="py-2 pr-4 font-semibold text-green-700">{{ $log->total_imported }}</td>
                    <td class="py-2 pr-4 text-slate-500">{{ $log->total_skipped }}</td>
                    <td class="py-2 pr-4 text-red-600">{{ $log->total_failed }}</td>
                    <td class="py-2 pr-4">{{ $log->created_at->diffForHumans() }}</td>
                    <td class="py-2 pr-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.blog-import.log', $log) }}" class="rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"><i class="fas fa-eye"></i> Log</a>
                            <a href="{{ route('admin.blog-posts.index', ['batch' => $log->batch_id]) }}" class="rounded-md border border-green-200 px-2 py-1 text-xs font-medium text-green-700 hover:bg-green-50"><i class="fas fa-list"></i> Posts</a>
                            <form method="POST" action="{{ route('admin.blog-import.log.delete', $log) }}" onsubmit="return confirm('Delete this log?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="py-6 text-center text-slate-400">No imports yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="mt-4">
            {{ $logs->links('pagination.tailwind-admin') }}
        </div>
    @endif
</x-admin.card>
@endsection

@push('admin_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('importForm');
    if (form) {
        form.addEventListener('submit', function () {
            document.getElementById('uploadProgress').style.display = 'block';
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Importing…';
        });
    }
});
</script>
@endpush
