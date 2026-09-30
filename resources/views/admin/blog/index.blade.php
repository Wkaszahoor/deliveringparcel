@extends('admin.layouts.app')

@section('title', 'Blog Posts')
@section('page_title', 'Blog Posts')
@section('page_subtitle', 'Manage news, articles and announcements')

@section('content')
<div class="dp-toolbar d-flex flex-wrap align-items-center mb-3">
    <form method="GET" action="{{ route('admin.blogs.index') }}" class="form-inline flex-wrap mr-auto" data-dp-filters>
        <div class="input-group input-group-sm mr-2 mb-2" style="max-width:280px;">
            <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Search title or slug…" aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </div>
        <select name="category_id" class="form-control form-control-sm mr-2 mb-2" style="max-width:180px;" data-dp-filter>
            <option value="">All categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ (string) $filters['category_id'] === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-control form-control-sm mr-2 mb-2" style="max-width:140px;" data-dp-filter>
            <option value="">All statuses</option>
            <option value="published" {{ $filters['status'] === 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
        </select>
        <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-sm btn-outline-secondary mb-2 mr-2"><i class="fas fa-tags"></i> Categories</a>
    </form>
    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary btn-sm mb-2"><i class="fas fa-plus"></i> New Post</a>
</div>

<div class="dp-table-wrap">
    <table class="table dp-table dp-card-mobile">
        <thead>
            <tr>
                <th style="width:90px;">Cover</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Published</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="dp-rows"></tbody>
    </table>
</div>
@endsection

@push('admin_scripts')
<script>
(function () {
    'use strict';

    var esc = function (s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };
    var thumb = '<img class="dp-lazy dp-thumb" alt="cover" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="';

    function buildUrl() {
        var params = new URLSearchParams();
        ['q', 'category_id', 'status'].forEach(function (name) {
            var el = document.querySelector('[data-dp-filters] [name="' + name + '"]');
            if (el && el.value !== '') params.set(name, el.value);
        });
        var qs = params.toString();
        return '{{ route('admin.blogs.data') }}' + (qs ? '?' + qs : '');
    }

    // auto-submit filters when a select changes
    document.querySelectorAll('[data-dp-filter]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });

    // delete rows appended after page load (delegated confirm)
    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest('[data-dp-delete]');
        if (!btn) return;
        ev.preventDefault();
        Swal.fire({
            title: btn.dataset.title || 'Delete?',
            text: 'This action cannot be undone.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = btn.dataset.url;
            form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name=csrf-token]').content + '">' +
                             '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        });
    });

    DP.infiniteScroll({
        url: buildUrl(),
        target: '#dp-rows',
        render: function (item) {
            var coverCell = item.cover ? thumb + esc(item.cover) + '">' : '<i class="fas fa-image fa-2x text-muted"></i>';
            var badge = item.status === 'published'
                ? '<span class="dp-badge badge badge-success">Published</span>'
                : '<span class="dp-badge badge badge-secondary">Draft</span>';
            return '<tr>' +
                '<td data-label="Cover">' + coverCell + '</td>' +
                '<td data-label="Title"><strong>' + esc(item.title) + '</strong><br><small class="text-muted">/' + esc(item.slug) + '</small></td>' +
                '<td data-label="Category">' + esc(item.category || '—') + '</td>' +
                '<td data-label="Status">' + badge + '</td>' +
                '<td data-label="Published">' + esc(item.published_at || '—') + '</td>' +
                '<td data-label="Actions" class="text-right text-nowrap">' +
                    '<a href="' + item.urls.show + '" class="btn btn-xs btn-outline-info" title="View"><i class="fas fa-eye"></i></a> ' +
                    '<a href="' + item.urls.edit + '" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a> ' +
                    '<a href="#" data-dp-delete data-url="' + item.urls.delete + '" data-title="Delete post?" class="btn btn-xs btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></a>' +
                '</td>' +
            '</tr>';
        }
    });

    @if (session('success'))
        document.addEventListener('DOMContentLoaded', function () { DP.toast.success(@json(session('success'))); });
    @endif
})();
</script>
@endpush
