{{-- Shared CMS post create/edit form.
    Expects: postType, postTypes, statuses, parents, categories, tags, serviceAreas, templates, layouts
    and optionally $cmsPost (edit mode). All form field names are dot-free (PHP-safe). --}}
@php
    $isEdit = isset($cmsPost) && $cmsPost !== null;
    $post = $isEdit ? $cmsPost : null;
    $meta = $post ? ($post->meta ?? []) : [];
    $selectedTaxIds = $selectedTaxIds ?? [];
    $robotsOptions = ['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'];
@endphp

<form method="POST"
      action="{{ $isEdit ? route('admin.cms.posts.update', $post) : route('admin.cms.posts.store') }}"
      enctype="multipart/form-data" id="cmsPostForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- ─────────── LEFT COLUMN ─────────── --}}
        <div class="space-y-4 lg:col-span-2">

            @if(!$isEdit)
            {{-- Panel A — Post Type selector (create only) --}}
            <x-admin.card title="Post Type">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @php $iconMap = ['page' => 'fa-file-alt', 'blog_post' => 'fa-blog', 'product' => 'fa-shopping-bag', 'service' => 'fa-cogs']; @endphp
                    @foreach ($postTypes as $typeKey => $typeLabel)
                    <label class="block cursor-pointer rounded-md border p-2 text-center {{ old('post_type', $postType) === $typeKey ? 'border-brand bg-brand-light' : 'border-slate-200' }}">
                        <input type="radio" name="post_type" value="{{ $typeKey }}" class="js-post-type"
                               {{ old('post_type', $postType) === $typeKey ? 'checked' : '' }}>
                        <div class="mt-1">
                            <i class="fas {{ $iconMap[$typeKey] ?? 'fa-file' }} mb-1 block text-lg text-brand"></i>
                            <span class="text-sm font-semibold text-slate-700">{{ $typeLabel }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </x-admin.card>
            @else
            <input type="hidden" name="post_type" value="{{ $postType }}">
            @endif

            {{-- Panel B — Title & Slug --}}
            <x-admin.card title="Title & Slug">
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Title</label>
                    <input type="text" name="title" id="cms-title" value="{{ old('title', $post->title ?? '') }}"
                           class="block w-full rounded-md border px-3 py-2 text-base focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light {{ $errors->has('title') ? 'border-red-400' : 'border-slate-300' }}"
                           placeholder="Enter title…">
                    @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mb-1">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Slug <small class="font-normal text-slate-400">(auto-generated, editable)</small></label>
                    <input type="text" name="slug" id="cms-slug" value="{{ old('slug', $post->slug ?? '') }}"
                           class="block w-full rounded-md border px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light {{ $errors->has('slug') ? 'border-red-400' : 'border-slate-300' }}"
                           placeholder="auto-from-title">
                    @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <small class="text-xs text-slate-500">Preview: <span id="cms-slug-preview">{{ url('/page/' . ($post->slug ?? '')) }}</span></small>
                </div>
            </x-admin.card>

            {{-- Panel C — Content Editor --}}
            <x-admin.card title="Content">
                <textarea name="content" id="cms-content-editor" rows="20" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('content', $post->content ?? '') }}</textarea>
                <small class="text-xs text-slate-500">Visual editor + <b>&lt;/&gt; Code View</b> for raw HTML (WordPress-style). Summernote keeps the HTML in sync on save.</small>
            </x-admin.card>

            {{-- Panel D — Excerpt --}}
            <x-admin.card title="Excerpt">
                <textarea name="excerpt" id="cms-excerpt" rows="3" maxlength="500" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                <small class="text-xs text-slate-500"><span id="cms-excerpt-count">0</span>/500 characters</small>
            </x-admin.card>

            {{-- Panel E — Post-type-specific fields --}}
            <x-admin.card title="Type-Specific Fields">
                    {{-- blog_post --}}
                    <div class="field-blog-post {{ old('post_type', $postType) === 'blog_post' ? '' : 'hidden' }}">
                        <h6 class="mb-2 font-semibold text-slate-700">Blog Post Options</h6>
                        <label class="mb-3 flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" id="allow_comments" name="allow_comments" value="1"
                                   {{ old('allow_comments', $meta['allow_comments'] ?? true) ? 'checked' : '' }}>
                            Allow comments
                        </label>
                        <div class="mb-3">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Canonical URL</label>
                            <input type="text" name="canonical_url" value="{{ old('canonical_url', $meta['canonical_url'] ?? '') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    {{-- product --}}
                    <div class="field-product {{ old('post_type', $postType) === 'product' ? '' : 'hidden' }}">
                        <h6 class="mb-2 font-semibold text-slate-700">Product Options</h6>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Price</label>
                                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $meta['price'] ?? '') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Sale Price</label>
                                <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $meta['sale_price'] ?? '') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Currency</label>
                                <select name="currency" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                    @foreach (['GBP', 'USD', 'EUR'] as $cur)
                                    <option value="{{ $cur }}" {{ old('currency', $meta['currency'] ?? 'GBP') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">SKU</label>
                                <input type="text" name="sku" value="{{ old('sku', $meta['sku'] ?? '') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Stock</label>
                                <input type="number" name="stock" value="{{ old('stock', $meta['stock'] ?? 0) }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div class="flex items-end pb-2">
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="checkbox" id="prod_is_featured" name="is_featured" value="1"
                                           {{ old('is_featured', $meta['is_featured'] ?? false) ? 'checked' : '' }}>
                                    Featured product
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- service --}}
                    <div class="field-service {{ old('post_type', $postType) === 'service' ? '' : 'hidden' }}">
                        <h6 class="mb-2 font-semibold text-slate-700">Service Options</h6>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Icon (Font Awesome class)</label>
                                <input type="text" name="service_icon" value="{{ old('service_icon', $meta['icon'] ?? 'fa-cog') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">CTA Label</label>
                                <input type="text" name="cta_label" value="{{ old('cta_label', $meta['cta_label'] ?? 'Get Quote') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">CTA URL</label>
                                <input type="text" name="cta_url" value="{{ old('cta_url', $meta['cta_url'] ?? '/get-quote') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div class="sm:col-span-2 md:col-span-3">
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="checkbox" id="srv_is_featured" name="is_featured" value="1"
                                           {{ old('is_featured', $meta['is_featured'] ?? false) ? 'checked' : '' }}>
                                    Featured service
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- page --}}
                    <div class="field-page {{ old('post_type', $postType) === 'page' ? '' : 'hidden' }}">
                        <h6 class="mb-2 font-semibold text-slate-700">Page Options</h6>
                        <label class="mb-2 flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" id="show_title" name="show_title" value="1"
                                   {{ old('show_title', $meta['show_title'] ?? true) ? 'checked' : '' }}>
                            Show title on page
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" id="show_breadcrumb" name="show_breadcrumb" value="1"
                                   {{ old('show_breadcrumb', $meta['show_breadcrumb'] ?? true) ? 'checked' : '' }}>
                            Show breadcrumb
                        </label>
                    </div>
            </x-admin.card>
        </div>

        {{-- ─────────── RIGHT SIDEBAR ─────────── --}}
        <div class="space-y-4">

            {{-- Panel F — Publish --}}
            <x-admin.card title="Publish">
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" id="cms-status" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($statuses as $st)
                        <option value="{{ $st }}" {{ old('status', $post->status ?? 'draft') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 hidden" id="cms-published-at-wrap">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Publish At</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($post->published_at) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="js-save-status mb-2 block w-full rounded-md border border-slate-200 px-3 py-2 text-center text-sm font-medium text-slate-600 hover:bg-slate-100" data-status="draft">
                    <i class="fas fa-save mr-1"></i> Save Draft
                </button>
                <button type="submit" class="js-save-status block w-full rounded-md bg-brand px-3 py-2 text-center text-sm font-medium text-white hover:bg-brand-dark" data-status="published">
                    <i class="fas fa-check mr-1"></i> Publish
                </button>
                @if($isEdit)
                <a href="{{ $post->url }}" target="_blank" rel="noopener" class="mt-2 block w-full rounded-md border border-blue-200 px-3 py-2 text-center text-sm font-medium text-blue-600 hover:bg-blue-50">
                    <i class="fas fa-eye mr-1"></i> View on site
                </a>
                @endif
            </x-admin.card>

            {{-- Panel G — Featured Image --}}
            <x-admin.card title="Featured Image">
                <div id="cms-fi-preview" class="mb-2 {{ ($post->featured_image_url ?? old('featured_image')) ? '' : 'hidden' }}">
                    <img src="{{ $post->featured_image_url ?? old('featured_image') }}" alt="" class="w-full rounded-md border border-slate-200">
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Upload file</label>
                    <input type="file" name="featured_image_file" accept="image/*" class="block w-full text-sm">
                    <small class="text-xs text-red-600">@error('featured_image_file'){{ $message }}@enderror</small>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">…or image path / URL</label>
                    <input type="text" name="featured_image" id="cms-featured-image" value="{{ old('featured_image', $post->featured_image ?? '') }}"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" placeholder="uploads/cms/2026/09/uuid.jpg">
                </div>
                <button type="button" class="js-open-media mb-1 block w-full rounded-md border border-blue-200 px-3 py-1.5 text-center text-sm font-medium text-blue-600 hover:bg-blue-50" data-target="cms-featured-image">
                    <i class="far fa-images mr-1"></i> Choose from Media Library
                </button>
                <button type="button" class="js-clear-image block w-full rounded-md border border-slate-200 px-3 py-1.5 text-center text-sm font-medium text-slate-600 hover:bg-slate-100">
                    <i class="fas fa-times mr-1"></i> Clear image
                </button>
            </x-admin.card>

            {{-- Panel H — Taxonomies --}}
            <x-admin.card title="Taxonomies">
                <div class="max-h-[380px] overflow-y-auto">
                    @if($categories->isNotEmpty())
                    <h6 class="font-semibold text-slate-700">Categories</h6>
                    @foreach ($categories as $cat)
                    <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="cat_{{ $cat->id }}" name="category_ids[]" value="{{ $cat->id }}"
                               {{ in_array($cat->id, $selectedTaxIds) || in_array($cat->id, (array) old('category_ids', [])) ? 'checked' : '' }}>
                        {{ $cat->name }}
                    </label>
                    @endforeach
                    @endif

                    @if($tags->isNotEmpty())
                    <h6 class="mt-3 font-semibold text-slate-700">Tags</h6>
                    @foreach ($tags as $tag)
                    <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="tag_{{ $tag->id }}" name="tag_ids[]" value="{{ $tag->id }}"
                               {{ in_array($tag->id, $selectedTaxIds) || in_array($tag->id, (array) old('tag_ids', [])) ? 'checked' : '' }}>
                        {{ $tag->name }}
                    </label>
                    @endforeach
                    @endif

                    @if($serviceAreas->isNotEmpty() && in_array($postType, ['service', 'product', 'blog_post']))
                    <h6 class="mt-3 font-semibold text-slate-700">Service Areas</h6>
                    @foreach ($serviceAreas as $area)
                    <label class="mb-1 flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="area_{{ $area->id }}" name="service_area_ids[]" value="{{ $area->id }}"
                               {{ in_array($area->id, $selectedTaxIds) || in_array($area->id, (array) old('service_area_ids', [])) ? 'checked' : '' }}>
                        {{ $area->name }}
                    </label>
                    @endforeach
                    @endif

                    <hr class="my-3 border-slate-100">
                    <h6 class="font-semibold text-slate-700">Add New Category Inline</h6>
                    <div class="mt-1 flex gap-1.5">
                        <input type="text" id="cms-new-cat-name" class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm" placeholder="Category name…">
                        <button type="button" class="js-add-category shrink-0 rounded-md border border-blue-200 px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50" data-taxonomy="category" data-post-type="{{ $postType }}">Add</button>
                    </div>
                </div>
            </x-admin.card>

            {{-- Panel I — Page Attributes --}}
            <x-admin.card title="Page Attributes">
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Parent {{ in_array($postType, ['page', 'service']) ? '(page/service hierarchy)' : '' }}</label>
                    <select name="parent_id" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="">— none —</option>
                        @foreach ($parents as $pid => $ptitle)
                        <option value="{{ $pid }}" {{ (string) old('parent_id', $post->parent_id ?? '') === (string) $pid ? 'selected' : '' }}>{{ $ptitle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Menu Order</label>
                    <input type="number" name="menu_order" value="{{ old('menu_order', $post->menu_order ?? 0) }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" min="0">
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Template</label>
                    <select name="template" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($templates as $tKey => $tLabel)
                        <option value="{{ $tKey }}" {{ (string) old('template', $post->template ?? '') === (string) $tKey ? 'selected' : '' }}>{{ $tLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Layout</label>
                    <select name="layout" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($layouts as $lKey => $lLabel)
                        <option value="{{ $lKey }}" {{ old('layout', $post->layout ?? 'home2.layouts.app') === $lKey ? 'selected' : '' }}>{{ $lLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="mb-2 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" id="show_in_main_nav" name="show_in_main_nav" value="1"
                           {{ old('show_in_main_nav', $post->show_in_main_nav ?? false) ? 'checked' : '' }}>
                    Show in main navigation
                </label>
                <label class="mb-2 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" id="show_in_footer" name="show_in_footer" value="1"
                           {{ old('show_in_footer', $post->show_in_footer ?? false) ? 'checked' : '' }}>
                    Show in footer
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" id="show_in_header" name="show_in_header" value="1"
                           {{ old('show_in_header', $post->show_in_header ?? false) ? 'checked' : '' }}>
                    Show in header
                </label>
            </x-admin.card>

            {{-- Panel J — SEO --}}
            <x-admin.card title="SEO">
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Meta Title <small class="font-normal text-slate-400">(<span id="cms-mt-count">0</span>/60)</small></label>
                    <input type="text" name="meta_title" id="cms-meta-title" value="{{ old('meta_title', $post->meta_title ?? '') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Meta Description <small class="font-normal text-slate-400">(<span id="cms-md-count">0</span>/160)</small></label>
                    <textarea name="meta_description" id="cms-meta-description" rows="3" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Meta Keywords</label>
                    <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords ?? '') }}" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Robots</label>
                    <select name="robots" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($robotsOptions as $ro)
                        <option value="{{ $ro }}" {{ old('robots', $post->robots ?? 'index,follow') === $ro ? 'selected' : '' }}>{{ $ro }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">OG Image</label>
                    <div class="flex gap-1.5">
                        <input type="text" name="og_image" id="cms-og-image" value="{{ old('og_image', $post->og_image ?? '') }}" class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm">
                        <button type="button" class="js-open-media shrink-0 rounded-md border border-blue-200 px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50" data-target="cms-og-image">Browse</button>
                    </div>
                </div>
                {{-- SERP preview --}}
                <h6 class="font-semibold text-slate-700">Search Preview</h6>
                <div class="rounded-md border border-slate-200 bg-white p-2">
                    <div class="truncate text-xs text-green-700">{{ url('/page') }}/{{ optional($post)->slug ?: 'your-slug' }}</div>
                    <div class="text-blue-700" id="cms-serp-title" style="font-size:1.05rem;">{{ optional($post)->meta_title ?: (optional($post)->title ?: 'Page title') }}</div>
                    <div class="text-sm text-slate-500" id="cms-serp-desc">{{ optional($post)->meta_description ?: (optional($post)->excerpt ?: 'Meta description preview appears here…') }}</div>
                </div>
            </x-admin.card>
        </div>
    </div>
</form>

{{-- Media Library modal — plain JS-toggled overlay (no Bootstrap JS in the new layout). --}}
<div id="cmsMediaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" data-media-modal-close></div>
    <div class="relative w-full max-w-2xl rounded-lg bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Media Library</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-media-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="max-h-[70vh] overflow-y-auto p-4">
            <div id="cmsMediaGrid" class="grid grid-cols-3 gap-2 sm:grid-cols-4">Loading…</div>
        </div>
    </div>
</div>

@push('admin_scripts')
<script>
(function () {
    'use strict';
    var slugDirty = {{ json_encode((bool) old('slug', $post->slug ?? '')) }};

    function slugify(s) {
        return String(s).toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    // Auto-slug + slug preview
    var titleEl = document.getElementById('cms-title');
    var slugEl = document.getElementById('cms-slug');
    if (titleEl && slugEl) {
        titleEl.addEventListener('input', function () {
            if (!slugDirty) slugEl.value = slugify(titleEl.value);
            updateSlugPreview();
            updateSerp();
        });
        slugEl.addEventListener('input', function () { slugDirty = true; updateSlugPreview(); });
    }
    function updateSlugPreview() {
        var prev = document.getElementById('cms-slug-preview');
        if (prev) prev.textContent = '{{ url('/page') }}/' + slugEl.value;
    }

    // Excerpt counter
    var excerptEl = document.getElementById('cms-excerpt');
    var excerptCount = document.getElementById('cms-excerpt-count');
    function updateExcerptCount() {
        if (excerptEl && excerptCount) excerptCount.textContent = excerptEl.value.length;
    }
    if (excerptEl) { excerptEl.addEventListener('input', updateExcerptCount); updateExcerptCount(); }

    // Post type switching
    function applyTypePanels(type) {
        document.querySelectorAll('.field-blog-post,.field-product,.field-service,.field-page').forEach(function (el) {
            el.classList.add('hidden');
        });
        var panel = document.querySelector('.field-' + type.replace('_', '-'));
        if (panel) panel.classList.remove('hidden');
    }
    document.querySelectorAll('.js-post-type').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked) {
                applyTypePanels(radio.value);
                // create mode: reload with type preselected
                window.location = '{{ route('admin.cms.posts.create') }}?type=' + radio.value;
            }
        });
    });
    applyTypePanels('{{ $postType }}');

    // Status → published_at visibility
    var statusEl = document.getElementById('cms-status');
    var pubWrap = document.getElementById('cms-published-at-wrap');
    function togglePubAt() {
        if (statusEl && pubWrap) pubWrap.classList.toggle('hidden', statusEl.value !== 'scheduled');
    }
    if (statusEl) { statusEl.addEventListener('change', togglePubAt); togglePubAt(); }

    // Save Draft / Publish buttons
    document.querySelectorAll('.js-save-status').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (statusEl) statusEl.value = btn.getAttribute('data-status');
        });
    });

    // Counters + SERP preview
    var mtEl = document.getElementById('cms-meta-title');
    var mdEl = document.getElementById('cms-meta-description');
    function updateSerp() {
        var st = document.getElementById('cms-serp-title');
        var sd = document.getElementById('cms-serp-desc');
        var mtCount = document.getElementById('cms-mt-count');
        var mdCount = document.getElementById('cms-md-count');
        if (mtCount && mtEl) mtCount.textContent = mtEl.value.length;
        if (mdCount && mdEl) mdCount.textContent = mdEl.value.length;
        if (st && mtEl) st.textContent = mtEl.value || titleEl.value || 'Page title';
        if (sd && mdEl) sd.textContent = mdEl.value || 'Meta description preview appears here…';
    }
    if (mtEl) mtEl.addEventListener('input', updateSerp);
    if (mdEl) mdEl.addEventListener('input', updateSerp);
    updateSerp();

    // Media library modal
    var mediaLoaded = false;
    var mediaTarget = null;
    var mediaModal = document.getElementById('cmsMediaModal');
    function openMediaModal() { mediaModal.classList.remove('hidden'); mediaModal.classList.add('flex'); }
    function closeMediaModal() { mediaModal.classList.add('hidden'); mediaModal.classList.remove('flex'); }
    document.querySelectorAll('.js-open-media').forEach(function (btn) {
        btn.addEventListener('click', function () {
            mediaTarget = document.getElementById(btn.getAttribute('data-target'));
            openMediaModal();
            if (!mediaLoaded) loadMedia();
        });
    });
    document.querySelectorAll('[data-media-modal-close]').forEach(function (el) {
        el.addEventListener('click', closeMediaModal);
    });
    function loadMedia() {
        fetch('{{ route('admin.cms.media.browse') }}', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                var grid = document.getElementById('cmsMediaGrid');
                grid.innerHTML = '';
                (json.media || []).forEach(function (m) {
                    var col = document.createElement('div');
                    var img = document.createElement('img');
                    img.src = m.url;
                    img.className = 'w-full rounded-md border border-slate-200 object-cover cursor-pointer';
                    img.style.cssText = 'height:90px;object-fit:cover;cursor:pointer;';
                    img.title = m.filename || '';
                    img.addEventListener('click', function () {
                        if (mediaTarget) {
                            mediaTarget.value = m.path;
                            mediaTarget.dispatchEvent(new Event('input'));
                        }
                        closeMediaModal();
                    });
                    col.appendChild(img);
                    grid.appendChild(col);
                });
                if (!grid.children.length) grid.innerHTML = '<div class="col-span-4 text-slate-400">No images uploaded yet.</div>';
                mediaLoaded = true;
            })
            .catch(function () {
                document.getElementById('cmsMediaGrid').innerHTML = '<div class="col-span-4 text-red-600">Failed to load media.</div>';
            });
    }

    // Featured image preview sync
    var fiInput = document.getElementById('cms-featured-image');
    var fiPreview = document.getElementById('cms-fi-preview');
    function updateFiPreview() {
        if (!fiInput || !fiPreview) return;
        var v = fiInput.value.trim();
        if (!v) { fiPreview.classList.add('hidden'); return; }
        var src = v.indexOf('http') === 0 ? v : '{{ asset('') }}' + v.replace(/^\/+/, '');
        fiPreview.classList.remove('hidden');
        fiPreview.querySelector('img').src = src;
    }
    if (fiInput) { fiInput.addEventListener('input', updateFiPreview); }
    document.querySelectorAll('.js-clear-image').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (fiInput) fiInput.value = '';
            updateFiPreview();
        });
    });

    // Inline add category
    document.querySelectorAll('.js-add-category').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var nameEl = document.getElementById('cms-new-cat-name');
            var name = nameEl.value.trim();
            if (!name) return;
            var body = new URLSearchParams();
            body.set('name', name);
            body.set('taxonomy', btn.getAttribute('data-taxonomy'));
            body.set('post_type', btn.getAttribute('data-post-type'));
            body.set('_token', '{{ csrf_token() }}');
            fetch('{{ route('admin.cms.taxonomies.store') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: body
            }).then(function (r) { return r.json().catch(function () { return { ok: r.ok }; }); })
              .then(function (json) {
                  if (json.ok === false) { alert(json.message || 'Failed to add category.'); return; }
                  window.location.reload();
              })
              .catch(function () { alert('Failed to add category.'); });
        });
    });
})();
</script>
@endpush

{{-- Uses the "lite" Summernote build, not -bs4: the -bs4 build's Link/Picture/Video/Help toolbar
     dialogs call jQuery's .modal() (Bootstrap's JS plugin), which this Tailwind layout never
     loads — those buttons would silently throw "t.modal is not a function". The lite build ships
     its own dialog implementation with no Bootstrap JS dependency, and uses the same core
     .note-editor/.note-frame classes already themed in resources/css/vendor-overrides.css. --}}
@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/summernote/summernote-lite.min.css') }}">
<style>
.note-editor { border-radius: .45rem; }
.note-editor .note-toolbar { background: #f8f9fa; }
</style>
@endpush

@push('admin_scripts')
<script src="{{ url('dashbord/plugins/summernote/summernote-lite.min.js') }}"></script>
<script>
(function () {
    var el = document.getElementById('cms-content-editor');
    if (!el || typeof jQuery === 'undefined' || !jQuery.fn.summernote) return;
    var $el = jQuery(el);
    $el.summernote({
        height: 420,
        codeviewFilter: true,
        codeviewIgnoEmpty: false,
        placeholder: 'Write the page content… (use the </> button for the HTML editor)',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['codeview', 'fullscreen', 'help']]
        ],
        styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Georgia', 'Helvetica', 'Segoe UI', 'Tahoma', 'Times New Roman', 'Verdana']
    });
    // Summernote syncs into the textarea on form submit — belt & braces:
    document.querySelector('form')?.addEventListener('submit', function () {
        if ($el.summernote('hasFocus') || $el.summernote('codeview.isActivated')) {
            el.value = $el.summernote('code');
        }
    });
})();
</script>
@endpush
