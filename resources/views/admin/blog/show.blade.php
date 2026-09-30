@extends('admin.layouts.app')

@section('title', 'View Blog Post')
@section('page_title', 'Blog Posts')
@section('page_subtitle', 'View post')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ $blog->title }}</h3>
            </div>
            <div class="card-body">
                @if ($blog->cover_image)
                    <img src="{{ $blog->coverUrl() }}" alt="{{ $blog->title }}" class="img-fluid mb-3" style="max-height:320px;border-radius:.35rem;">
                @endif
                <p class="text-muted mb-1"><strong>Slug:</strong> /{{ $blog->slug }}</p>
                @if ($blog->excerpt)
                    <p class="font-italic">{{ $blog->excerpt }}</p>
                @endif
                <hr>
                <div class="post-body">{!! \App\Support\HtmlSanitizer::clean($blog->body) !!}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">Details</h3></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Category:</strong> {{ optional($blog->category)->name ?? '—' }}</li>
                <li class="list-group-item">
                    <strong>Status:</strong>
                    @if ($blog->status === 'published')
                        <span class="dp-badge badge badge-success">Published</span>
                    @else
                        <span class="dp-badge badge badge-secondary">Draft</span>
                    @endif
                </li>
                <li class="list-group-item"><strong>Published at:</strong> {{ optional($blog->published_at)->format('M d, Y H:i') ?? '—' }}</li>
                <li class="list-group-item"><strong>Created:</strong> {{ $blog->created_at->format('M d, Y H:i') }}</li>
                <li class="list-group-item"><strong>Updated:</strong> {{ $blog->updated_at->format('M d, Y H:i') }}</li>
            </ul>
        </div>
        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">SEO meta</h3></div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Meta title:</strong> {{ $blog->meta_title ?? '—' }}</li>
                <li class="list-group-item"><strong>Meta description:</strong> {{ $blog->meta_description ?? '—' }}</li>
                <li class="list-group-item"><strong>Meta keywords:</strong> {{ $blog->meta_keywords ?? '—' }}</li>
            </ul>
        </div>
        <div class="mt-3">
            <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-primary"><i class="fas fa-pen"></i> Edit</a>
            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">Back to list</a>
        </div>
    </div>
</div>
@endsection
