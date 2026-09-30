{{-- CMS Blog tag archive — spec SECTION 8. Adapted to home2 custom CSS layout (h2-* classes, no Bootstrap). --}}
@extends('home2.layouts.app')
@section('title', '#' . $tag->name . ' — Blog')
@section('meta_description', \Str::limit(strip_tags('Articles tagged with ' . $tag->name . '.'), 155))

@push('meta')
    <meta name="robots" content="noindex, follow">
    <meta property="og:title" content="#{{ $tag->name }} | DeliveringParcel Blog">
    <meta property="og:description" content="Articles tagged with {{ $tag->name }}.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
@endpush

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <p class="muted small" style="margin:.2rem 0 1rem">
            <a href="{{ url('/') }}" style="text-decoration:none; color:inherit">Home</a> ›
            <a href="{{ route('cms.blog.index') }}" style="text-decoration:none; color:inherit">Blog</a> ›
            <span>#{{ $tag->name }}</span>
        </p>

        <h1 style="font-size:2rem; margin:0 0 1.5rem">Tag: <span style="color:var(--h2-primary)">#{{ $tag->name }}</span></h1>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(270px, 1fr)); gap:1.25rem; margin-bottom:1.5rem">
            @forelse ($posts as $post)
                <article class="h2-card" style="display:flex; flex-direction:column; overflow:hidden">
                    @if ($post->featured_image)
                        <a href="{{ route('cms.blog.show', $post->slug) }}">
                            <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset($post->featured_image) }}"
                                 alt="{{ $post->title }}" loading="lazy"
                                 style="width:100%; height:220px; object-fit:cover"
                                 onerror="this.parentElement.remove()">
                        </a>
                    @else
                        <a href="{{ route('cms.blog.show', $post->slug) }}" style="text-decoration:none">
                            <div style="height:220px; display:flex; align-items:center; justify-content:center; font-size:2.6rem; background:linear-gradient(135deg, var(--h2-border), transparent)">✍️</div>
                        </a>
                    @endif
                    <div class="h2-card-body" style="display:flex; flex-direction:column; gap:.45rem; flex:1">
                        <h3 style="margin:0; font-size:1.15rem">
                            <a href="{{ route('cms.blog.show', $post->slug) }}" style="color:inherit; text-decoration:none">{{ $post->title }}</a>
                        </h3>
                        <p class="muted" style="font-size:.92rem; margin:0">{{ \Str::limit($post->smart_excerpt, 160) }}</p>
                        <p class="muted small" style="margin:auto 0 .3rem">
                            {{ optional($post->published_at)->format('M d, Y') }} ·
                            {{ $post->reading_time }} min read
                        </p>
                        <a class="h2-btn h2-btn-outline" href="{{ route('cms.blog.show', $post->slug) }}" style="text-align:center">Read More</a>
                    </div>
                </article>
            @empty
                <div class="h2-card" style="grid-column:1 / -1; padding:3rem 1.5rem; text-align:center">
                    <div style="font-size:2.6rem">🏷️</div>
                    <h3 style="margin:.6rem 0 .3rem">Nothing tagged “{{ $tag->name }}” yet</h3>
                    <p class="muted" style="margin:0">Articles with this tag will appear here.</p>
                </div>
            @endforelse
        </div>

        @include('home2.partials.pagination', ['paginator' => $posts])
    </div>
</section>
@endsection
