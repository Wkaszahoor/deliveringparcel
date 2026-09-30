@extends('admin.layouts.app')
@section('title', isset($category->id) ? 'Edit Category' : 'New Category')
@section('page_title', isset($category->id) ? 'Edit Category' : 'New Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ isset($category->id) ? route('admin.shop.categories.update', $category->id) : route('admin.shop.categories.store') }}">
                    @csrf
                    @if (isset($category->id)) @method('PUT') @endif
                    <div class="form-group">
                        <label for="sc-name">Name <span class="text-danger">*</span></label>
                        <input id="sc-name" type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control" required maxlength="255">
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="sc-slug">Slug (auto if empty)</label>
                        <input id="sc-slug" type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="form-control" maxlength="255">
                        @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="sc-parent">Parent category</label>
                        <select id="sc-parent" name="parent_id" class="custom-select">
                            <option value="">— (root)</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" {{ old('parent_id', $category->parent_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="sc-active" name="is_active" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="sc-active">Active</label>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.shop.categories.index') }}" class="btn btn-outline-secondary mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary flex-fill">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
