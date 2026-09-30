@extends('home2.layouts.app')
@section('title', 'Category: ' . $category->name)
@section('meta_description', 'Articles in the ' . $category->name . ' category.')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <div style="padding:1.5rem; margin-bottom:1.5rem; border-radius:12px; background:linear-gradient(120deg, var(--h2-primary, #1d4ed8), #0ea5e9); color:#fff">
            <p style="margin:0; opacity:.85; font-size:.85rem; text-transform:uppercase">Category</p>
            <h1 style="margin:.2rem 0 0; font-size:1.6rem">{{ $category->name }}</h1>
            @if ($category->description)
                <p style="margin:.5rem 0 0; opacity:.9">{{ $category->description }}</p>
            @endif
        </div>

        <div style="display:flex; gap:2rem; flex-wrap:wrap; align-items:flex-start">
            <div style="flex:2; min-width:280px">
                <a href="{{ url('blog') }}" class="muted">← All articles</a>

                @forelse ($posts as $post)
                    <div class="h2-card" style="display:flex; flex-direction:column; margin:1.25rem 0 0">
                        @if ($post->featured_image_src)
                            <a href="{{ url('blog/' . $post->slug) }}">
                                <img class="h2-card-img" src="{{ $post->featured_image_src }}" alt="{{ $post->title }}" loading="lazy">
                            </a>
                        @endif
                        <div class="h2-card-body">
                            <span class="muted" style="font-size:.8rem">
                                {{ $post->author_name ?: 'DeliveringParcel' }} ·
                                {{ optional($post->published_at)->format('M d, Y') }} ·
                                {{ $post->view_count }} views
                            </span>
                            <h3 style="margin:.3rem 0"><a href="{{ url('blog/' . $post->slug) }}" style="color:inherit; text-decoration:none">{{ $post->title }}</a></h3>
                            <p class="muted" style="font-size:.92rem; margin:.6rem 0">{{ \Str::limit(strip_tags((string) $post->excerpt), 150) }}</p>
                            <a href="{{ url('blog/' . $post->slug) }}" class="h2-btn h2-btn-outline" style="align-self:flex-start">Read More →</a>
                        </div>
                    </div>
                @empty
                    <p class="muted" style="margin-top:1rem">No articles in this category yet.</p>
                @endforelse

                @include('home2.partials.pagination', ['paginator' => $posts])
            </div>

            <aside style="flex:1; min-width:240px">
                <div class="h2-card">
                    <div class="h2-card-body">
                        <h4 style="margin-top:0">Categories</h4>
                        @forelse ($categories as $cat)
                            <a href="{{ url('blog/category/' . $cat->slug) }}" style="display:block; padding:.25rem 0; {{ $cat->id === $category->id ? 'font-weight:600' : '' }}">
                                {{ $cat->name }} <span class="muted">({{ $cat->posts_count }})</span>
                            </a>
                        @empty
                            <p class="muted mb-0">No categories yet.</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
