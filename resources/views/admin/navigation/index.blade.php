@extends('admin.layouts.app')

@section('title', 'Navigation')
@section('page_title', 'Header Navigation')
@section('page_subtitle', 'DB-driven /home2 menu — order, nest and hide links without a deploy')

@section('content')
<div class="dp-toolbar d-flex flex-wrap align-items-center mb-3">
    <form method="GET" action="{{ route('admin.navigation.index') }}" class="form-inline mr-auto" data-dp-filters>
        <input id="f-q" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm mr-2 mb-2" style="max-width:220px" placeholder="Search title, URL or route…" aria-label="Search">
        <select id="f-visibility" name="visibility" class="form-control form-control-sm mr-2 mb-2" style="max-width:150px" data-dp-filter>
            <option value="">All visibility</option>
            @foreach (['everyone', 'guest', 'auth'] as $vis)
                <option value="{{ $vis }}" {{ $filters['visibility'] === $vis ? 'selected' : '' }}>{{ ucfirst($vis) }}</option>
            @endforeach
        </select>
        <select id="f-active" name="active" class="form-control form-control-sm mr-2 mb-2" style="max-width:140px" data-dp-filter>
            <option value="">All status</option>
            <option value="1" {{ $filters['active'] === '1' ? 'selected' : '' }}>Active only</option>
            <option value="0" {{ $filters['active'] === '0' ? 'selected' : '' }}>Inactive only</option>
        </select>
        <button type="submit" class="btn btn-outline-primary btn-sm mb-2"><i class="fas fa-search"></i></button>
    </form>
    <a href="{{ route('admin.navigation.create') }}" class="btn btn-primary btn-sm mb-2"><i class="fas fa-plus"></i> New Menu Item</a>
</div>

@include('admin.partials.lazy-table', [
    'id' => 'nav',
    'url' => route('admin.navigation.data'),
    'filterIds' => ['f-q', 'f-visibility', 'f-active'],
    'cols' => [
        ['text', 'Item', 'title'],
        ['text', 'Shown as', 'label'],
        ['text', 'Destination', 'target'],
        ['badge', 'Visibility', 'visibility', 'vis_badge'],
        ['text', 'Sort', 'sort'],
        ['badge2', 'Status', 'is_active', 'badge-success', 'Active', 'Inactive'],
    ],
    'acts' => [
        ['pen', 'Edit', 'edit'],
        ['power', 'Toggle active', 'toggle', 'POST', 0, 'secondary'],
        ['up', 'Move up', 'up', 'POST', 0, 'secondary'],
        ['down', 'Move down', 'down', 'POST', 0, 'secondary'],
        ['trash', 'Delete menu item', 'delete', 'DELETE', 1, 'danger'],
    ],
])
@endsection

@push('admin_scripts')
<script>
(function () {
    'use strict';

    /* Delegated handler for lazy-loaded rows (DP.bindConfirms only binds
       elements present at DOMContentLoaded). Destructive actions carry
       data-title (confirm flag) and get a SweetAlert; toggle/move act
       immediately. */
    document.addEventListener('click', function (ev) {
        var el = ev.target.closest('[data-dp-confirm]');
        if (!el || !el.dataset.url) return;
        ev.preventDefault();

        var submit = function () {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = el.dataset.url;
            form.innerHTML = '<input type="hidden" name="_token" value="' +
                document.querySelector('meta[name=csrf-token]').content + '">' +
                '<input type="hidden" name="_method" value="' + (el.dataset.method || 'DELETE') + '">';
            document.body.appendChild(form);
            form.submit();
        };

        if (el.dataset.title) {
            Swal.fire({
                title: el.dataset.title,
                text: el.dataset.text || 'This action cannot be undone.',
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, proceed'
            }).then(function (r) { if (r.isConfirmed) submit(); });
        } else {
            submit();
        }
    });
})();
</script>
@endpush
