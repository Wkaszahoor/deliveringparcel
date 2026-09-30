{{-- CMS static page — spec SECTION 8. Extends the dynamic $layout passed by CmsController::pageShow()
     (defaults to home2.layouts.app). Full width, no sidebar. --}}
@extends($layout)
@section('title', $page->meta_title ?: $page->title)
@section('meta_description', \Str::limit(strip_tags($page->meta_description ?: $page->smart_excerpt), 155))

@push('meta')
    <meta name="keywords" content="{{ $page->meta_keywords }}">
    <meta name="robots" content="{{ $page->robots }}">
    <meta property="og:title" content="{{ $page->meta_title ?: $page->title }}">
    <meta property="og:description" content="{{ \Str::limit(strip_tags($page->meta_description ?: $page->smart_excerpt), 200) }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $page->url }}">
    @if ($page->og_image || $page->featured_image)
        @php $pageOgImage = $page->og_image ?: $page->featured_image; @endphp
        <meta property="og:image" content="{{ str_starts_with($pageOgImage, 'http') ? $pageOgImage : asset($pageOgImage) }}">
    @endif
    <link rel="canonical" href="{{ $page->url }}">
@endpush

@section('content')
<section class="h2-section">
    <div class="h2-container">
        @if (($page->meta['show_breadcrumb'] ?? true))
            <p class="muted small" style="margin:.2rem 0 1rem">
                <a href="{{ url('/') }}" style="text-decoration:none; color:inherit">Home</a> ›
                <span>{{ $page->title }}</span>
            </p>
        @endif

        @if (($page->meta['show_title'] ?? true))
            <h1 style="font-size:2rem; margin:0 0 1.25rem">{{ $page->title }}</h1>
        @endif

        @if ($page->featured_image)
            <img src="{{ str_starts_with($page->featured_image, 'http') ? $page->featured_image : asset($page->featured_image) }}"
                 alt="{{ $page->title }}"
                 style="width:100%; max-height:420px; object-fit:cover; border-radius:10px; margin-bottom:1.5rem"
                 onerror="this.remove()">
        @endif

        <div class="h2-article" style="line-height:1.7; max-width:860px">
            {!! \App\Support\HtmlSanitizer::clean($page->content ?? '') !!}
        </div>
    </div>
</section>
@endsection
