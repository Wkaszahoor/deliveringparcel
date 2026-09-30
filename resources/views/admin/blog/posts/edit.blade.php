@extends('admin.layouts.app')

@section('title', 'Edit Blog Post')
@section('page_title', 'Edit Blog Post')
@section('page_subtitle', $post->title)

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.blog-posts.update', $post) }}">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" value="{{ old('title', $post->title) }}" class="form-control @error('title') is-invalid @enderror" required>
                        @error('title')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Content (HTML)</label>
                        {{-- Simple textarea — drop a TinyMCE/CKEditor init in the admin_scripts push below if a rich editor is wanted. --}}
                        <textarea name="content" rows="14" class="form-control @error('content') is-invalid @enderror">{{ old('content', $post->content) }}</textarea>
                        @error('content')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Excerpt</label>
                        <textarea name="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $post->excerpt) }}</textarea>
                        @error('excerpt')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-search mr-1"></i> SEO</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>SEO Title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $post->seo_title) }}" class="form-control" maxlength="500">
                    </div>
                    <div class="form-group">
                        <label>SEO Description</label>
                        <textarea name="seo_description" rows="2" class="form-control" maxlength="500">{{ old('seo_description', $post->seo_description) }}</textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label>SEO Keywords</label>
                        <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $post->seo_keywords) }}" class="form-control" maxlength="500" placeholder="comma, separated, keywords">
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
                        <select name="status" class="form-control" id="statusSelect">
                            @foreach(['draft','published','archived'] as $s)
                            <option value="{{ $s }}" {{ old('status', $post->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Published At</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Featured Image URL</label>
                        <input type="url" name="featured_image_url" value="{{ old('featured_image_url', $post->featured_image_url) }}" class="form-control @error('featured_image_url') is-invalid @enderror" placeholder="https://…">
                        @error('featured_image_url')<span class="text-danger small">{{ $message }}</span>@enderror
                        @if($post->featured_image_src)
                        <img src="{{ $post->featured_image_src }}" alt="" class="mt-2" style="max-width:100%;border-radius:6px">
                        @endif
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
                            <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat-{{ $cat->id }}" {{ in_array($cat->id, old('categories', $post->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat-{{ $cat->id }}">{{ $cat->name }}</label>
                        </div>
                        @empty
                        <p class="text-muted mb-0">No categories yet.</p>
                        @endforelse
                    </div>
                    <div class="form-group mb-0">
                        <label>Tags <small class="text-muted">(comma separated)</small></label>
                        <input type="text" name="tags_input" value="{{ old('tags_input', $post->tags->pluck('name')->implode(', ')) }}" class="form-control" placeholder="shipping, guides">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Import Info <small class="text-muted">(read-only)</small></h3></div>
                <div class="card-body py-2">
                    <table class="table table-sm table-borderless mb-0">
                        @if($post->source_url)
                        <tr><th style="width:110px">Source URL</th><td class="text-truncate"><a href="{{ $post->source_url }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($post->source_url, 45) }}</a></td></tr>
                        @endif
                        <tr><th>Imported</th><td>{{ optional($post->imported_at)->format('Y-m-d H:i') ?? '—' }}</td></tr>
                        <tr><th>Batch</th><td><code>{{ $post->import_batch_id ?? '—' }}</code></td></tr>
                        <tr><th>Views</th><td>{{ $post->view_count }}</td></tr>
                        <tr><th>Slug</th><td><code>{{ $post->slug }}</code></td></tr>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save mr-1"></i> Save Changes</button>
            <a href="{{ route('newblog.show', $post->slug) }}" class="btn btn-outline-info btn-block mt-2" target="_blank" rel="noopener"><i class="fas fa-eye mr-1"></i> Preview on /new/blog</a>
            <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-outline-secondary btn-block mt-2">Back to list</a>
        </div>
    </div>
</form>

<form method="POST" action="{{ route('admin.blog-posts.destroy', $post) }}" onsubmit="return confirm('Move this post to trash?')">
    @csrf @method('DELETE')
    <button class="btn btn-outline-danger mt-3"><i class="fas fa-trash mr-1"></i> Move to Trash</button>
</form>
@endsection
