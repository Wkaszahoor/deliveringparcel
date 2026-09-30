@extends('layouts.fmaster')

@section('title', 'Shipping Guides & Parcel Forwarding News | Delivering Parcel')
@section('meta_description', 'Latest international shopping tips, parcel forwarding guides, and courier updates from Delivering Parcel.')
@section('keywords', 'parcel forwarding blog, international shipping guides, how to buy from usa, shop uk to worldwide')

@push('meta')
    <meta property="og:title" content="Shipping Guides &amp; Parcel Forwarding News | Delivering Parcel">
    <meta property="og:description" content="Latest international shopping tips, parcel forwarding guides, and courier updates from Delivering Parcel.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
@endpush

@section('content')
<div class="min-h-screen bg-slate-50/70 pt-28 pb-20 sm:pt-32 sm:pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16" data-aos="fade-up">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 mb-4 shadow-xs">
                <i class="fas fa-newspaper text-blue-600 text-xs"></i> Insights &amp; Guides
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                The Delivering Parcel Blog
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto">
                Helpful articles on cross-border shopping, customs duties, international forwarding tips, and global e-commerce guides.
            </p>

            {{-- Search Bar --}}
            <div class="mt-8 max-w-xl mx-auto">
                <form method="GET" action="{{ route('cms.blog.index') }}" class="relative flex items-center">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search guides, shopping tips, countries…"
                           class="w-full pl-11 pr-24 py-3.5 bg-white border border-slate-300 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition">
                    <span class="absolute left-4 text-slate-400 text-sm">
                        <i class="fas fa-magnifying-glass"></i>
                    </span>
                    <button type="submit"
                            class="absolute right-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
                        Search
                    </button>
                </form>
            </div>

            {{-- Category Filter Pills --}}
            @if ($categories->isNotEmpty())
                <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                    <a href="{{ route('cms.blog.index') }}"
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ !request()->filled('q') ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-100' }}">
                        All Articles
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('cms.blog.category', $category->slug) }}"
                           class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 transition">
                            {{ $category->name }} <span class="text-slate-400 font-normal ml-0.5">({{ $category->posts_count }})</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if (request()->filled('q'))
                <p class="text-xs text-slate-500 mt-4">
                    Showing results for “<strong class="text-slate-800">{{ request('q') }}</strong>” ({{ $posts->total() }} articles found) ·
                    <a href="{{ route('cms.blog.index') }}" class="text-blue-600 hover:underline">Clear search</a>
                </p>
            @endif
        </div>

        {{-- Post Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12" data-aos="fade-up">
            @forelse ($posts as $post)
                <article class="group bg-white rounded-3xl border border-slate-200/90 overflow-hidden flex flex-col justify-between shadow-xs hover:shadow-2xl hover:border-blue-200 hover:-translate-y-1.5 transition-all duration-300">
                    <div>
                        {{-- Featured Image --}}
                        @if ($post->featured_image)
                            <a href="{{ route('cms.blog.show', $post->slug) }}" class="block overflow-hidden aspect-video bg-slate-100 relative">
                                <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset($post->featured_image) }}"
                                     alt="{{ $post->title }}" loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     onerror="this.parentElement.style.display='none'">
                            </a>
                        @else
                            <a href="{{ route('cms.blog.show', $post->slug) }}" class="block aspect-video bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-3xl text-slate-400">
                                <i class="fas fa-newspaper"></i>
                            </a>
                        @endif

                        <div class="p-6">
                            {{-- Categories --}}
                            @if ($post->categories->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    @foreach ($post->categories as $cardCategory)
                                        <a href="{{ route('cms.blog.category', $cardCategory->slug) }}"
                                           class="inline-block bg-blue-50 text-blue-700 text-[11px] font-semibold px-2.5 py-0.5 rounded-full border border-blue-100 hover:bg-blue-100 transition">
                                            {{ $cardCategory->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Title & Excerpt --}}
                            <h2 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-snug">
                                <a href="{{ route('cms.blog.show', $post->slug) }}">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            <p class="mt-2.5 text-slate-600 text-sm leading-relaxed line-clamp-3">
                                {{ \Str::limit($post->smart_excerpt, 150) }}
                            </p>
                        </div>
                    </div>

                    {{-- Footer Metadata --}}
                    <div class="px-6 pb-6 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 mt-auto">
                        <span class="flex items-center gap-1.5 font-medium text-slate-600">
                            <i class="fas fa-clock text-slate-400 text-[10px]"></i> {{ $post->reading_time }} min read
                        </span>
                        <span>{{ optional($post->published_at)->format('M d, Y') }}</span>
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-white rounded-3xl border border-slate-200/90 p-12 text-center max-w-md mx-auto shadow-sm my-8">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mb-4">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-1">No articles found</h3>
                    <p class="text-sm text-slate-500 mb-6">No published articles match your search criteria. Check back soon!</p>
                    <a href="{{ route('cms.blog.index') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition">
                        View All Articles
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $posts->links() }}
        </div>

    </div>
</div>
@endsection
