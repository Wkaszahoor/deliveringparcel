@extends('admin.layouts.app')

@section('title', 'New Blog Post')
@section('page_title', 'New Blog Post')
@section('page_subtitle', 'Create a post for the new-design blog (/new/blog).')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.blog-posts.store') }}">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Post title" required>
                        @error('title')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Content (HTML)</label>
                        {{-- Simple textarea — drop a TinyMCE/CKEditor init in the admin_scripts push below if a rich editor is wanted. --}}
                        <textarea name="content" rows="14" class="form-control @error('content') is-invalid @enderror" placeholder="Post content…">{{ old('content') }}</textarea>
                        @error('content')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Excerpt</label>
                        <textarea name="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror" placeholder="Short summary…">{{ old('excerpt') }}</textarea>
                        @error('excerpt')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-search mr-1"></i> SEO</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>SEO Title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title') }}" class="form-control" maxlength="500">
                    </div>
                    <div class="form-group">
                        <label>SEO Description</label>
                        <textarea name="seo_description" rows="2" class="form-control" maxlength="500">{{ old('seo_description') }}</textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label>SEO Keywords</label>
                        <input type="text" name="seo_keywords" value="{{ old('seo_keywords') }}" class="form-control" maxlength="500" placeholder="comma, separated, keywords">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-cog mr-1"></i> Publish</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            @foreach(['draft','published','archived'] as $s)
                            <option value="{{ $s }}" {{ old('status', 'draft') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Published At</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at') }}" class="form-control">
                        <small class="text-muted">Leave blank for now.</small>
                    </div>
                    <div class="form-group">
                        <label>Featured Image URL</label>
                        <input type="url" name="featured_image_url" value="{{ old('featured_image_url') }}" class="form-control @error('featured_image_url') is-invalid @enderror" placeholder="https://…">
                        @error('featured_image_url')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-tags mr-1"></i> Taxonomies</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Categories</label>
                        @forelse($categories as $cat)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat-{{ $cat->id }}" {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat-{{ $cat->id }}">{{ $cat->name }}</label>
                        </div>
                        @empty
                        <p class="text-muted mb-0">No categories yet.</p>
                        @endforelse
                    </div>
                    <div class="form-group mb-0">
                        <label>Tags <small class="text-muted">(comma separated)</small></label>
                        <input type="text" name="tags_input" value="{{ old('tags_input') }}" class="form-control" placeholder="shipping, guides">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save mr-1"></i> Save Draft</button>
            <button type="submit" class="btn btn-success btn-block mt-2" onclick="this.form.status.value='published'"><i class="fas fa-globe mr-1"></i> Publish</button>
            <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-outline-secondary btn-block mt-2">Cancel</a>
        </div>
    </div>
</form>
@endsection
