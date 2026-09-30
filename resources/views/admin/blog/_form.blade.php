{{-- Shared blog post form fields (used by create + edit). Expects: $blog, $categories --}}
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Content</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $blog->title) }}" class="form-control @error('title') is-invalid @enderror" maxlength="255" required>
                    @error('title')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $blog->slug) }}" class="form-control @error('slug') is-invalid @enderror" maxlength="255" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="Lowercase letters, numbers and dashes only. Leave empty to auto-generate.">
                    <small class="form-text text-muted">Leave empty to auto-generate from the title.</small>
                    @error('slug')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="blog_category_id">Category</label>
                    <select id="blog_category_id" name="blog_category_id" class="form-control dp-select2 @error('blog_category_id') is-invalid @enderror">
                        <option value="">— None —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) old('blog_category_id', $blog->blog_category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('blog_category_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="excerpt">Excerpt</label>
                    <textarea id="excerpt" name="excerpt" rows="2" maxlength="2000" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $blog->excerpt) }}</textarea>
                    @error('excerpt')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="body-editor">Body</label>
                    <textarea id="body-editor" name="body" class="form-control @error('body') is-invalid @enderror">{{ old('body', $blog->body) }}</textarea>
                    @error('body')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">Publishing</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="draft" {{ old('status', $blog->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $blog->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                    @error('status')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="published_at">Published at</label>
                    <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', optional($blog->published_at)->format('Y-m-d\TH:i')) }}" class="form-control @error('published_at') is-invalid @enderror">
                    @error('published_at')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">Cover image</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <div class="mb-2 text-center" id="cover-preview-wrap" @if (empty($blog->cover_image)) style="display:none;" @endif>
                        <img id="cover-preview" src="{{ $blog->coverUrl() }}" alt="cover preview" class="img-fluid dp-thumb" style="max-height:160px;border-radius:.35rem;">
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('cover_image') is-invalid @enderror" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png,.webp,.gif,.svg">
                        <label class="custom-file-label" for="cover_image">Choose image (jpg, png, webp, gif, svg — max 2MB)</label>
                    </div>
                    @error('cover_image')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                @if (!empty($blog->cover_image))
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remove_cover" name="remove_cover" value="1">
                            <label class="custom-control-label" for="remove_cover">Remove current cover</label>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">SEO meta</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="meta_title">Meta title</label>
                    <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}" maxlength="255" class="form-control @error('meta_title') is-invalid @enderror">
                    @error('meta_title')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="meta_description">Meta description</label>
                    <textarea id="meta_description" name="meta_description" rows="2" maxlength="500" class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description', $blog->meta_description) }}</textarea>
                    @error('meta_description')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="meta_keywords">Meta keywords</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $blog->meta_keywords) }}" maxlength="500" class="form-control @error('meta_keywords') is-invalid @enderror">
                    <small class="form-text text-muted">Comma separated.</small>
                    @error('meta_keywords')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
