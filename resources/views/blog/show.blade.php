@extends('home2.layouts.app')
@section('title', $post->seo_title ?: $post->title)
@section('meta_description', $post->seo_description ?: \Str::limit(strip_tags((string) ($post->excerpt ?: $post->content)), 150))

@push('meta')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->seo_title ?: $post->title }}">
    <meta property="og:description" content="{{ $post->seo_description ?: \Str::limit(strip_tags((string) ($post->excerpt ?: $post->content)), 150) }}">
    @if ($post->featured_image_src)
        <meta property="og:image" content="{{ $post->featured_image_src }}">
    @endif
    <link rel="canonical" href="{{ url('blog/' . $post->slug) }}">
    @if ($post->seo_keywords)
        <meta name="keywords" content="{{ $post->seo_keywords }}">
    @endif
@endpush

@section('content')
<article class="h2-article h2-section">
    <div class="h2-container" style="max-width:860px">
        <a href="{{ url('blog') }}" class="muted">← All articles</a>

        <h1 style="font-size:2rem; margin:.6rem 0">{{ $post->title }}</h1>
        <p class="h2-article-meta muted">
            {{ $post->author_name ?: 'DeliveringParcel' }} ·
            {{ optional($post->published_at)->format('F d, Y') }} ·
            {{ $post->view_count }} views
            {{-- category links hidden 2026-09-03 (user request) --}}
        </p>

        @if ($post->featured_image_src)
            <img class="h2-cover" src="{{ $post->featured_image_src }}" alt="{{ $post->title }}" loading="lazy">
        @endif

        <div style="line-height:1.7">
            {!! $post->content !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <div style="margin-top:2rem">
                @foreach ($post->tags as $tag)
                    <a class="h2-badge" href="{{ url('blog/tag/' . $tag->slug) }}" style="margin-right:.4rem">#{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($post->author_name)
            <div class="h2-card" style="margin-top:2rem">
                <div class="h2-card-body">
                    <strong>{{ $post->author_name }}</strong>
                    <p class="muted mb-0" style="font-size:.9rem">Author</p>
                </div>
            </div>
        @endif

        {{-- Prev / Next --}}
        <div style="display:flex; justify-content:space-between; gap:1rem; margin-top:2rem; flex-wrap:wrap">
            @if ($prev)
                <a class="h2-btn h2-btn-outline" href="{{ url('blog/' . $prev->slug) }}">← {{ \Str::limit($prev->title, 40) }}</a>
            @else
                <span></span>
            @endif
            @if ($next)
                <a class="h2-btn h2-btn-outline" href="{{ url('blog/' . $next->slug) }}">{{ \Str::limit($next->title, 40) }} →</a>
            @endif
        </div>

        @if ($related->isNotEmpty())
            <h2 style="margin-top:2.5rem">Related articles</h2>
            <div class="h2-grid">
                @foreach ($related as $p)
                    <a class="h2-card" href="{{ url('blog/' . $p->slug) }}">
                        @if ($p->featured_image_src)
                            <img class="h2-card-img" src="{{ $p->featured_image_src }}" alt="{{ $p->title }}" loading="lazy">
                        @endif
                        <div class="h2-card-body">
                            <h3 style="margin:0 0 .3rem; font-size:1.05rem">{{ $p->title }}</h3>
                            <span class="muted" style="font-size:.82rem">{{ optional($p->published_at)->format('M d, Y') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</article>
@endsection
