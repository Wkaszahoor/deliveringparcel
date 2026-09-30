{{-- Shared service form fields (create + edit). Expects: $service, $categories, $types --}}
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Service details</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $service->title) }}" class="form-control @error('title') is-invalid @enderror" maxlength="255" required>
                    @error('title')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $service->slug) }}" class="form-control @error('slug') is-invalid @enderror" maxlength="255" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="Lowercase letters, numbers and dashes only. Leave empty to auto-generate.">
                    <small class="form-text text-muted">Leave empty to auto-generate from the title.</small>
                    @error('slug')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="service_category_id">Category</label>
                    <select id="service_category_id" name="service_category_id" class="form-control dp-select2 @error('service_category_id') is-invalid @enderror">
                        <option value="">— None —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) old('service_category_id', $service->service_category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('service_category_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="short_desc">Short description</label>
                    <textarea id="short_desc" name="short_desc" rows="2" maxlength="500" class="form-control @error('short_desc') is-invalid @enderror">{{ old('short_desc', $service->short_desc) }}</textarea>
                    @error('short_desc')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="description-editor">Full description</label>
                    <textarea id="description-editor" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
                    @error('description')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">Pricing &amp; visibility</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="type">Type <span class="text-danger">*</span></label>
                    <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $service->type) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="price">Price (USD)</label>
                    <input type="number" id="price" name="price" value="{{ old('price', $service->price) }}" class="form-control @error('price') is-invalid @enderror" min="0" max="99999999.99" step="0.01">
                    <small class="form-text text-muted">Leave empty for a quote-on-request service.</small>
                    @error('price')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="sort">Sort order</label>
                    <input type="number" id="sort" name="sort" value="{{ old('sort', $service->sort ?? 0) }}" class="form-control @error('sort') is-invalid @enderror" min="0" max="999999" step="1">
                    @error('sort')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_available" name="is_available" value="1" {{ old('is_available', $service->is_available) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_available">Available to customers</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">Image</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <div class="mb-2 text-center" id="image-preview-wrap" @if (empty($service->image)) style="display:none;" @endif>
                        <img id="image-preview" src="{{ $service->imageUrl() }}" alt="service image" class="img-fluid dp-thumb" style="max-height:140px;border-radius:.35rem;">
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,.gif,.svg">
                        <label class="custom-file-label" for="image">Choose image (jpg, png, webp, gif, svg — max 2MB)</label>
                    </div>
                    @error('image')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                @if (!empty($service->image))
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                            <label class="custom-control-label" for="remove_image">Remove current image</label>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
