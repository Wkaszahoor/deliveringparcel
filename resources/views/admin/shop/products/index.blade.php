@extends('admin.layouts.app')
@section('title', 'Products')
@section('page_title', 'Shop Products')
@section('page_subtitle', 'Catalog with images, stock and pricing')

@section('content')
<div class="dp-toolbar">
    <input id="f-q" class="form-control form-control-sm" placeholder="Search name/SKU…" style="max-width:240px">
    <select id="f-category" class="custom-select custom-select-sm" style="max-width:190px">
        <option value="">Category: all</option>
        @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
    </select>
    <select id="f-active" class="custom-select custom-select-sm" style="max-width:130px">
        <option value="">Status: all</option><option value="1">Active</option><option value="0">Inactive</option>
    </select>
    <div class="ml-auto d-flex gap-2">
        <a href="{{ route('admin.shop.categories.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-tags"></i> Categories</a>
        <a href="{{ route('admin.shop.products.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Product</a>
    </div>
</div>
@include('admin.partials.lazy-table', [
    'id' => 'sp',
    'url' => route('admin.shop.products.data'),
    'filterIds' => ['f-q', 'f-category', 'f-active'],
    'cols' => [
        ['img', 'Image', 'image'],
        ['sub', 'Name', 'name', 'sku'],
        ['text', 'Category', 'category'],
        ['money', 'Price', 'price'],
        ['badge', 'Stock', 'stock', 'stock_c'],
        ['badge2', 'Active', 'active', 'badge-success', 'On', 'Off'],
    ],
    'acts' => [
        ['pen', 'Edit', 'edit'],
        ['trash', 'Delete product', 'delete', 'DELETE', 1, 'danger'],
    ],
])
@endsection
