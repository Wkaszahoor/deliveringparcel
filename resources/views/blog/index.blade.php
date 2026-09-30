@extends('home2.layouts.app')
@section('title', 'Blog | Delivering Parcel')
@section('meta_description', 'Shipping guides, industry news and parcel-forwarding tips from the Delivering Parcel blog.')

@section('content')
<section class="h2-section">
    <div class="h2-container">

        {{-- Page header --}}
        <div style="padding:2rem 1.5rem; margin-bottom:1.5rem; border-radius:12px; background:linear-gradient(120deg, var(--h2-primary, #1d4ed8), #0ea5e9); color:#fff">
            <h1 style="margin:0 0 .4rem; font-size:2rem">Our Blog</h1>
            <p style="margin:0; opacity:.9">Guides, news and tips about international shipping.</p>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ url('blog') }}" style="display:flex; gap:.6rem; margin-bottom:1.25rem">
            <input name="q" value="{{ request('q') }}" placeholder="Search articles…"
                   style="flex:1; padding:.6rem .8rem; border:1px solid var(--h2-border, #e5e7eb); border-radius:8px">
            <button class="h2-btn h2-btn-primary" type="submit">Search</button>
        </form>

        {{-- Category filter bar — hidden 2026-09-03 (user request: felt awkward).
             Restore by uncommenting; routes + controller still support ?category=. --}}
        {{--
        <div style="display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; margin-bottom:1.5rem">
            <a href="{{ url('blog') }}"
               class="h2-badge"
               style="padding:.4rem .8rem; {{ request('category') ? '' : 'background:var(--h2-primary, #1d4ed8); color:#fff' }}">
                All
            </a>
            @foreach ($categories as $cat)
                <a href="{{ url('blog/category/' . $cat->slug) }}"
                   class="h2-badge"
                   style="padding:.4rem .8rem; {{ request('category') === $cat->slug ? 'background:var(--h2-primary, #1d4ed8); color:#fff' : '' }}">
                    {{ $cat->name }}
                    <span style="opacity:.75; margin-left:.25rem">{{ $cat->posts_count }}</span>
                </a>
            @endforeach
        </div>
        --}}

        {{-- Post cards grid (3-up desktop, 2-up tablet, 1-up mobile — col-md-6 col-lg-4 equivalent) --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:1.5rem">
            @forelse ($posts as $post)
                @php
                    /* Featured image: absolute URL as-is, local path via asset(), else fall back to the imported remote URL. */
                    $img = $post->featured_image;
                    $imgSrc = null;
                    if ($img) {
                        if (str_starts_with($img, 'http')) {
                            $imgSrc = $img;
                        } elseif (file_exists(public_path($img))) {
                            $imgSrc = asset($img);
                        }
                    }
                    if (! $imgSrc && $post->featured_image_url) {
                        $imgSrc = $post->featured_image_url;
                    }
                @endphp
                <div class="h2-card" style="display:flex; flex-direction:column; margin:0; overflow:hidden">
                    @if ($imgSrc)
                        <a href="{{ url('blog/' . $post->slug) }}">
                            <img class="card-img-top"
                                 src="{{ $imgSrc }}"
                                 alt="{{ $post->title }}"
                                 style="width:100%; height:220px; object-fit:cover; display:block"
                                 loading="lazy"
                                 onerror="this.style.display='none'">
                        </a>
                    @endif
                    <div class="h2-card-body" style="display:flex; flex-direction:column; flex:1">
                        {{-- Category badges hidden 2026-09-03 (user request) --}}
                        <h3 style="margin:.2rem 0; font-weight:700">
                            <a href="{{ url('blog/' . $post->slug) }}" style="color:inherit; text-decoration:none">{{ $post->title }}</a>
                        </h3>
                        <p class="muted" style="font-size:.92rem; margin:.5rem 0">
                            {{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 130) }}
                        </p>
                        <span class="muted" style="font-size:.8rem; margin-top:auto">
                            {{ $post->author_name ?: 'DeliveringParcel' }} ·
                            {{ optional($post->published_at)->format('M d, Y') }}
                        </span>
                        <a href="{{ url('blog/' . $post->slug) }}" class="h2-btn h2-btn-outline" style="align-self:flex-start; margin-top:.8rem">Read More →</a>
                    </div>
                </div>
            @empty
                <div style="grid-column:1 / -1">
                    <div class="h2-card">
                        <div class="h2-card-body" style="text-align:center; padding:2.5rem 1.5rem">
                            <h3 style="margin-top:0">No articles found</h3>
                            <p class="muted" style="margin-bottom:0">
                                @if (request('q'))
                                    Nothing matches “{{ request('q') }}”. Try a different search or browse all posts.
                                @else
                                    No articles have been published yet — check back soon.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination (home2 design equivalent of $posts->links()) --}}
        @include('home2.partials.pagination', ['paginator' => $posts])
    </div>
</section>
@endsection
