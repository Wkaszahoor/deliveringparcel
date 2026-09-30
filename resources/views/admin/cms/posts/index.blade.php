@extends('layouts.tailwind.app')

@section('title', 'CMS Posts')
@section('page_title', 'CMS Posts')
@section('page_subtitle', 'Pages, blog posts, products and services — one content engine.')

@php
    $currentType = request('type');
    $currentStatus = request('status');
@endphp

@section('content')

{{-- Post type tabs --}}
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.cms.posts.index') }}"
       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium {{ $currentType ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-brand text-white' }}">
        All <span class="rounded-full bg-white/70 px-1.5 text-xs {{ $currentType ? 'bg-slate-200 text-slate-700' : 'text-slate-700' }}">{{ array_sum($counts) }}</span>
    </a>
    @foreach ($postTypes as $typeKey => $typeLabel)
        <a href="{{ route('admin.cms.posts.index', ['type' => $typeKey]) }}"
           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium {{ $currentType === $typeKey ? 'bg-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            {{ $typeLabel }} <span class="rounded-full px-1.5 text-xs {{ $currentType === $typeKey ? 'text-white/90' : 'bg-slate-200 text-slate-700' }}">{{ $counts[$typeKey] ?? 0 }}</span>
        </a>
    @endforeach
</div>

{{-- Status pills + search + Add New --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-1.5">
        @php
            $statusPills = ['' => 'All', 'published' => 'Published', 'draft' => 'Draft', 'trashed' => 'Trashed'];
        @endphp
        @foreach ($statusPills as $statusKey => $statusLabel)
            <a href="{{ route('admin.cms.posts.index', array_filter(['type' => $currentType, 'status' => $statusKey ?: null])) }}"
               class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium {{ ($currentStatus ?: '') === $statusKey ? 'border-brand bg-brand-light text-brand' : 'border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                {{ $statusLabel }}
            </a>
        @endforeach
    </div>
    <form method="GET" action="{{ route('admin.cms.posts.index') }}" class="flex items-end gap-2">
        @if ($currentType)<input type="hidden" name="type" value="{{ $currentType }}">@endif
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title or slug…"
               class="w-56 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <button type="submit" class="inline-flex items-center rounded-md border border-slate-200 px-2.5 py-1.5 text-sm text-slate-600 hover:bg-slate-100">
            <i class="fas fa-search"></i>
        </button>
        <a href="{{ route('admin.cms.posts.create', $currentType ? ['type' => $currentType] : []) }}"
           class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-dark">
            <i class="fas fa-plus"></i> Add New
        </a>
    </form>
</div>

{{-- Bulk action bar --}}
<form method="POST" action="{{ route('admin.cms.posts.bulk') }}" id="cmsBulkForm">
    @csrf
    <x-admin.card class="mb-4">
        <div class="flex flex-wrap items-center gap-2">
            <label class="text-sm font-medium text-slate-700">Bulk action:</label>
            <select name="action" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">— choose —</option>
                <option value="publish">Publish</option>
                <option value="draft">Draft</option>
                <option value="trash">Trash</option>
            </select>
            <button type="submit" class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Apply</button>
        </div>
    </x-admin.card>
</form>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="w-9 py-2"><input type="checkbox" id="cms-check-all" aria-label="Select all"></th>
                    <th class="w-16 py-2 pr-4">Image</th>
                    <th class="py-2 pr-4">Title</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Categories</th>
                    <th class="py-2 pr-4">Updated</th>
                    <th class="py-2 pr-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($posts as $post)
                    <tr class="{{ $post->trashed() ? 'text-slate-400' : '' }}">
                        <td class="py-2 pr-4"><input type="checkbox" name="ids[]" value="{{ $post->id }}" form="cmsBulkForm" class="cms-row-check"></td>
                        <td class="py-2 pr-4">
                            @if ($post->featured_image_url)
                                <img src="{{ $post->featured_image_url }}" alt="" class="h-[50px] w-[50px] rounded object-cover">
                            @else
                                <div class="flex h-[50px] w-[50px] items-center justify-center rounded bg-slate-100">
                                    <i class="far fa-file-image text-slate-400"></i>
                                </div>
                            @endif
                        </td>
                        <td class="py-2 pr-4">
                            <span class="{{ $post->trashed() ? 'line-through' : '' }}"><strong class="text-slate-900">{{ $post->title }}</strong></span>
                            @unless ($post->trashed())
                                <a href="{{ $post->url }}" target="_blank" rel="noopener" class="ml-1 text-slate-400 hover:text-brand" title="View on site"><i class="fas fa-external-link-alt text-xs"></i></a>
                            @endunless
                            <br><small class="text-slate-500">/{{ $post->slug }}</small>
                            <span class="ml-1 inline-flex items-center rounded-full border border-slate-200 px-2 py-0.5 text-xs text-slate-600">{{ $post->post_type === 'blog_post' ? 'blog post' : $post->post_type }}</span>
                        </td>
                        <td class="py-2 pr-4">
                            @if ($post->trashed())
                                <x-admin.badge color="red">trashed</x-admin.badge>
                            @else
                                <x-admin.badge :color="['published' => 'green', 'draft' => 'slate', 'scheduled' => 'blue', 'private' => 'yellow'][$post->status] ?? 'slate'">{{ $post->status }}</x-admin.badge>
                            @endif
                        </td>
                        <td class="py-2 pr-4">
                            @forelse ($post->categories as $cat)
                                <span class="mb-0.5 mr-1 inline-flex items-center rounded-full border border-slate-200 px-2 py-0.5 text-xs text-slate-600">{{ $cat->name }}</span>
                            @empty
                                <span class="text-slate-400">—</span>
                            @endforelse
                        </td>
                        <td class="py-2 pr-4"><small class="text-slate-500">{{ $post->updated_at ? $post->updated_at->format('d M Y H:i') : '—' }}</small></td>
                        <td class="py-2 pr-4 text-right whitespace-nowrap">
                            @if ($post->trashed())
                                <form method="POST" action="{{ route('admin.cms.posts.restore', $post->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded-md border border-green-200 px-2 py-1 text-xs font-medium text-green-600 hover:bg-green-50"><i class="fas fa-undo mr-1"></i>Restore</button>
                                </form>
                                <form method="POST" action="{{ route('admin.cms.posts.force-delete', $post->id) }}" class="js-confirm-delete inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50" data-confirm="Permanently delete this post?"><i class="fas fa-trash"></i></button>
                                </form>
                            @else
                                <a href="{{ route('admin.cms.posts.edit', $post) }}" class="rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"><i class="fas fa-pen mr-1"></i>Edit</a>
                                <form method="POST" action="{{ route('admin.cms.posts.toggle', $post) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100" title="Toggle publish status">
                                        <i class="fas fa-toggle-{{ $post->status === 'published' ? 'on text-green-600' : 'off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.cms.posts.destroy', $post) }}" class="js-confirm-delete inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50" data-confirm="Move this post to trash?"><i class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-400">No posts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</x-admin.card>
@endsection

@push('admin_scripts')
<script>
(function () {
    'use strict';
    // Select-all for bulk checkboxes
    var checkAll = document.getElementById('cms-check-all');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.cms-row-check').forEach(function (cb) {
                cb.checked = checkAll.checked;
            });
        });
    }
    // Confirm before destructive actions
    document.querySelectorAll('.js-confirm-delete button[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!window.confirm(btn.getAttribute('data-confirm'))) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
})();
</script>
@endpush
