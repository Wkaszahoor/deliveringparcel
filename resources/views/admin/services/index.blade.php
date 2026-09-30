@extends('admin.layouts.app')

@section('title', 'Services')
@section('page_title', 'Services')
@section('page_subtitle', 'Manage shipping services offered on the site')

@section('content')
<div class="dp-toolbar d-flex flex-wrap align-items-center mb-3">
    <form method="GET" action="{{ route('admin.services.index') }}" class="form-inline flex-wrap mr-auto" data-dp-filters>
        <div class="input-group input-group-sm mr-2 mb-2" style="max-width:260px;">
            <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Search title, slug or summary…" aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </div>
        <select name="category_id" class="form-control form-control-sm mr-2 mb-2" style="max-width:170px;" data-dp-filter>
            <option value="">All categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ $filters['category_id'] === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="type" class="form-control form-control-sm mr-2 mb-2" style="max-width:150px;" data-dp-filter>
            <option value="">All types</option>
            @foreach ($types as $key => $label)
                <option value="{{ $key }}" {{ $filters['type'] === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="availability" class="form-control form-control-sm mr-2 mb-2" style="max-width:150px;" data-dp-filter>
            <option value="">All availability</option>
            <option value="1" {{ $filters['availability'] === '1' ? 'selected' : '' }}>Available</option>
            <option value="0" {{ $filters['availability'] === '0' ? 'selected' : '' }}>Unavailable</option>
        </select>
        <a href="{{ route('admin.service-categories.index') }}" class="btn btn-sm btn-outline-secondary mb-2 mr-2"><i class="fas fa-tags"></i> Categories</a>
    </form>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm mb-2"><i class="fas fa-plus"></i> New Service</a>
</div>

<div class="dp-table-wrap">
    <table class="table dp-table dp-card-mobile">
        <thead>
            <tr>
                <th style="width:90px;">Image</th>
                <th>Service</th>
                <th>Category</th>
                <th>Type</th>
                <th>Price</th>
                <th>Availability</th>
                <th>Sort</th>
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
    var thumb = '<img class="dp-lazy dp-thumb" alt="service" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="';

    function buildUrl() {
        var params = new URLSearchParams();
        ['q', 'category_id', 'type', 'availability'].forEach(function (name) {
            var el = document.querySelector('[data-dp-filters] [name="' + name + '"]');
            if (el && el.value !== '') params.set(name, el.value);
        });
        var qs = params.toString();
        return '{{ route('admin.services.data') }}' + (qs ? '?' + qs : '');
    }

    document.querySelectorAll('[data-dp-filter]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });

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
            var imageCell = item.image ? thumb + esc(item.image) + '">' : '<i class="fas fa-box-open fa-2x text-muted"></i>';
            return '<tr>' +
                '<td data-label="Image">' + imageCell + '</td>' +
                '<td data-label="Service"><strong>' + esc(item.title) + '</strong><br><small class="text-muted">/' + esc(item.slug) + '</small></td>' +
                '<td data-label="Category">' + esc(item.category || '—') + '</td>' +
                '<td data-label="Type"><span class="dp-badge badge badge-' + esc(item.type_color) + '">' + esc(item.type_label) + '</span></td>' +
                '<td data-label="Price">' + (item.price === null ? '—' : '$' + item.price) + '</td>' +
                '<td data-label="Availability"><span class="dp-badge badge badge-' + esc(item.avail_color) + '">' + esc(item.avail_label) + '</span></td>' +
                '<td data-label="Sort">' + item.sort + '</td>' +
                '<td data-label="Actions" class="text-right text-nowrap">' +
                    '<a href="' + item.urls.edit + '" class="btn btn-xs btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a> ' +
                    '<a href="#" data-dp-delete data-url="' + item.urls.delete + '" data-title="Delete service?" class="btn btn-xs btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></a>' +
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
