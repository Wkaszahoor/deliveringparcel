@extends('layouts.fmaster')

@section('title', $post->meta_title ?: $post->title . ' | Delivering Parcel')
@section('meta_description', \Str::limit(strip_tags($post->meta_description ?: $post->smart_excerpt), 155))

@push('meta')
    @php $ogImage = $post->og_image ?: $post->featured_image; @endphp
    <meta name="keywords" content="{{ $post->meta_keywords }}">
    <meta name="robots" content="{{ $post->robots }}">
    <meta property="og:title" content="{{ $post->meta_title ?: $post->title }}">
    <meta property="og:description" content="{{ \Str::limit(strip_tags($post->meta_description ?: $post->smart_excerpt), 200) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $post->url }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ str_starts_with($ogImage, 'http') ? $ogImage : asset($ogImage) }}">
    @endif
    <link rel="canonical" href="{{ $post->meta['canonical_url'] ?? $post->url }}">
@endpush

@push('scripts')
    <script type="application/ld+json">
    {!! json_encode([
        '@context'            => 'https://schema.org',
        '@type'               => 'Article',
        'headline'            => $post->title,
        'description'         => \Str::limit(strip_tags($post->meta_description ?: $post->smart_excerpt), 200),
        'datePublished'       => optional($post->published_at)->toIso8601String(),
        'dateModified'        => optional($post->updated_at)->toIso8601String(),
        'author'              => ['@type' => 'Person', 'name' => optional($post->author)->name ?? 'DeliveringParcel Team'],
        'publisher'           => ['@type' => 'Organization', 'name' => 'DeliveringParcel'],
        'mainEntityOfPage'    => $post->url,
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
<div class="dp-home">

    {{-- ======= Article header ======= --}}
    <header class="relative pt-32 pb-10 lg:pt-40 lg:pb-14" style="background:var(--dp-paper)">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">

                <nav class="dp-hero-in dp-hero-in-1 flex items-center gap-2 text-xs font-semibold mb-6" style="color:var(--dp-ink-soft)" aria-label="Breadcrumb">
                    <a href="{{ url('/') }}" class="hover:text-[var(--dp-accent-dark)] transition">Home</a>
                    <i class="fas fa-chevron-right text-[9px] opacity-50" aria-hidden="true"></i>
                    <a href="{{ route('cms.blog.index') }}" class="hover:text-[var(--dp-accent-dark)] transition">Blog</a>
                    <i class="fas fa-chevron-right text-[9px] opacity-50" aria-hidden="true"></i>
                    <span class="truncate max-w-xs" style="color:var(--dp-ink)">{{ $post->title }}</span>
                </nav>

                @if ($post->categories->isNotEmpty())
                    <div class="dp-hero-in dp-hero-in-2 flex flex-wrap gap-2 mb-5">
                        @foreach ($post->categories as $category)
                            <a href="{{ route('cms.blog.category', $category->slug) }}" class="dp-legal-meta-chip hover:opacity-80 transition">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <h1 class="dp-hero-in dp-hero-in-3 text-4xl sm:text-5xl lg:text-6xl mb-6">{{ $post->title }}</h1>

                <div class="dp-hero-in dp-hero-in-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm pt-5" style="color:var(--dp-ink-soft); border-top:1px solid var(--dp-line)">
                    <span class="inline-flex items-center gap-1.5 font-semibold" style="color:var(--dp-ink)">
                        <i class="fas fa-user-circle" aria-hidden="true"></i> {{ optional($post->author)->name ?? 'DeliveringParcel Team' }}
                    </span>
                    <span aria-hidden="true">&middot;</span>
                    <span>{{ optional($post->published_at)->format('F d, Y') }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-clock" aria-hidden="true"></i> {{ $post->reading_time }} min read</span>
                </div>
            </div>
        </div>
    </header>

    {{-- ======= Featured image ======= --}}
    @if ($post->featured_image_url)
        <div class="container mx-auto px-4 mb-12 lg:mb-16">
            <div class="dp-hero-in dp-hero-in-5 max-w-5xl mx-auto rounded-2xl overflow-hidden bg-white" style="border:1px solid var(--dp-line); box-shadow:0 24px 60px -34px rgba(20,23,26,.35)">
                <img src="{{ $post->featured_image_url }}"
                     alt="{{ $post->title }}"
                     class="w-full max-h-[520px] object-cover">
            </div>
        </div>
    @endif

    {{-- ======= Body ======= --}}
    <section class="pb-16 lg:pb-24" style="background:#fff">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">

                <article class="dp-legal-body">
                    {!! \App\Support\HtmlSanitizer::clean($post->content ?? '') !!}
                </article>

                {{-- Tags --}}
                @if ($post->tags->isNotEmpty())
                    <div class="mt-10 pt-6 flex flex-wrap items-center gap-2" style="border-top:1px solid var(--dp-line)">
                        <span class="text-xs font-bold uppercase tracking-wider mr-1" style="color:var(--dp-ink-soft)">Tags:</span>
                        @foreach ($post->tags as $tag)
                            <a href="{{ route('cms.blog.tag', $tag->slug) }}"
                               class="inline-block text-xs font-semibold px-3 py-1 rounded-full transition"
                               style="background:var(--dp-paper); color:var(--dp-ink-soft); border:1px solid var(--dp-line)"
                               onmouseover="this.style.color='var(--dp-ink)'" onmouseout="this.style.color='var(--dp-ink-soft)'">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Share Bar --}}
                @php
                    $shareUrl = urlencode($post->url);
                    $shareTitle = urlencode($post->title);
                @endphp
                <div class="mt-8 pt-6 flex flex-wrap items-center justify-between gap-4" style="border-top:1px solid var(--dp-line)">
                    <span class="text-xs font-bold uppercase tracking-wider" style="color:var(--dp-ink-soft)">Share Article:</span>
                    <div class="flex items-center gap-2">
                        <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener"
                           aria-label="Share on X"
                           class="w-9 h-9 rounded-xl flex items-center justify-center text-xs transition hover:text-white"
                           style="background:var(--dp-paper); color:var(--dp-ink)"
                           onmouseover="this.style.background='var(--dp-ink)'" onmouseout="this.style.background='var(--dp-paper)'">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener"
                           aria-label="Share on Facebook"
                           class="w-9 h-9 rounded-xl flex items-center justify-center text-xs transition hover:text-white"
                           style="background:var(--dp-accent-soft); color:var(--dp-accent-dark)"
                           onmouseover="this.style.background='var(--dp-accent)';this.style.color='var(--dp-ink)'" onmouseout="this.style.background='var(--dp-accent-soft)';this.style.color='var(--dp-accent-dark)'">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener"
                           aria-label="Share on LinkedIn"
                           class="w-9 h-9 rounded-xl flex items-center justify-center text-xs transition hover:text-white"
                           style="background:var(--dp-accent-soft); color:var(--dp-accent-dark)"
                           onmouseover="this.style.background='var(--dp-accent)';this.style.color='var(--dp-ink)'" onmouseout="this.style.background='var(--dp-accent-soft)';this.style.color='var(--dp-accent-dark)'">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ======= Next / Prev Post Links ======= --}}
    @if ($prevPost || $nextPost)
        <section class="pb-16 lg:pb-24" style="background:#fff">
            <div class="container mx-auto px-4">
                <div class="max-w-3xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if ($prevPost)
                        <a href="{{ route('cms.blog.show', $prevPost->slug) }}"
                           class="dp-card group p-5 flex flex-col hover:shadow-lg transition-shadow">
                            <span class="text-xs font-semibold mb-1 flex items-center gap-1.5" style="color:var(--dp-ink-soft)">
                                <i class="fas fa-arrow-left text-[10px]" aria-hidden="true"></i> Previous Article
                            </span>
                            <span class="text-sm font-bold transition-colors group-hover:text-[var(--dp-accent-dark)]" style="color:var(--dp-ink)">
                                {{ $prevPost->title }}
                            </span>
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if ($nextPost)
                        <a href="{{ route('cms.blog.show', $nextPost->slug) }}"
                           class="dp-card group p-5 flex flex-col text-right hover:shadow-lg transition-shadow">
                            <span class="text-xs font-semibold mb-1 flex items-center justify-end gap-1.5" style="color:var(--dp-ink-soft)">
                                Next Article <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
                            </span>
                            <span class="text-sm font-bold transition-colors group-hover:text-[var(--dp-accent-dark)]" style="color:var(--dp-ink)">
                                {{ $nextPost->title }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ======= Related articles ======= --}}
    @if ($related->isNotEmpty())
        <section class="pt-16 pb-20 lg:pt-20 lg:pb-28" style="background:var(--dp-paper)">
            <div class="container mx-auto px-4">
                <div class="max-w-5xl mx-auto">
                    <h2 class="text-2xl sm:text-3xl mb-8">Related articles</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        @foreach ($related as $r)
                            <a href="{{ route('cms.blog.show', $r->slug) }}"
                               class="dp-card flex flex-col overflow-hidden group hover:shadow-lg transition-shadow">
                                @if ($r->featured_image_url)
                                    <div class="overflow-hidden aspect-video bg-slate-100">
                                        <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             src="{{ $r->featured_image_url }}" alt="{{ $r->title }}" loading="lazy">
                                    </div>
                                @endif
                                <div class="p-5 flex flex-col flex-1">
                                    <span class="text-xs font-semibold" style="color:var(--dp-ink-soft)">
                                        {{ optional($r->published_at)->format('M d, Y') }}
                                    </span>
                                    <h3 class="dp-display text-base font-bold mt-1 mb-2 leading-snug transition-colors" style="color:var(--dp-ink)">
                                        {{ $r->title }}
                                    </h3>
                                    <span class="mt-auto inline-flex items-center text-xs font-semibold" style="color:var(--dp-accent-dark)">
                                        Read article <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

</div>{{-- /.dp-home --}}
@endsection
