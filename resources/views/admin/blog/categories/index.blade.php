@extends('admin.layouts.app')

@section('title', 'Blog Categories')
@section('page_title', 'Blog Categories')
@section('page_subtitle', 'Group posts into categories')

@section('content')
<div class="dp-toolbar d-flex flex-wrap align-items-center mb-3">
    <form method="GET" action="{{ route('admin.blog-categories.index') }}" class="form-inline mr-auto" data-dp-filters>
        <div class="input-group input-group-sm" style="max-width:280px;">
            <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Search name or slug…" aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </div>
    </form>
    <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Category</a>
</div>

<div class="dp-table-wrap">
    <table class="table dp-table dp-card-mobile">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Posts</th>
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
        url: '{{ route('admin.blog-categories.data') }}',
        target: '#dp-rows',
        render: function (item) {
            return '<tr>' +
                '<td data-label="Name"><strong>' + esc(item.name) + '</strong></td>' +
                '<td data-label="Slug"><small class="text-muted">/' + esc(item.slug) + '</small></td>' +
                '<td data-label="Posts"><span class="dp-badge badge badge-light">' + item.posts + '</span></td>' +
                '<td data-label="Actions" class="text-right text-nowrap">' +
                    '<a href="' + item.urls.edit + '" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a> ' +
                    '<a href="#" data-dp-delete data-url="' + item.urls.delete + '" data-title="Delete category?" class="btn btn-xs btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></a>' +
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
