@extends('admin.layouts.app')
@section('title', 'Shop Categories')
@section('page_title', 'Product Categories')
@section('page_subtitle', 'Hierarchical categories')

@section('content')
<div class="dp-toolbar">
    <input id="f-q" class="form-control form-control-sm" placeholder="Search…" style="max-width:240px">
    <a href="{{ route('admin.shop.categories.create') }}" class="btn btn-primary btn-sm ml-auto"><i class="fas fa-plus"></i> New Category</a>
</div>
@include('admin.partials.lazy-table', [
    'id' => 'sc',
    'url' => route('admin.shop.categories.data'),
    'filterIds' => ['f-q'],
    'cols' => [
        ['text', 'Name', 'name'],
        ['text', 'Slug', 'slug'],
        ['text', 'Parent', 'parent'],
        ['text', 'Products', 'products'],
        ['badge2', 'Active', 'active', 'badge-success', 'On', 'Off'],
    ],
    'acts' => [
        ['pen', 'Edit', 'edit'],
        ['trash', 'Delete category', 'delete', 'DELETE', 1, 'danger'],
    ],
])
@endsection
