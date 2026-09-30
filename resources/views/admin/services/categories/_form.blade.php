{{-- Shared service category form fields (create + edit). Expects: $category --}}
<div class="card card-outline card-primary">
    <div class="card-body">
        <div class="form-group">
            <label for="name">Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
            @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}" class="form-control @error('slug') is-invalid @enderror" maxlength="255" pattern="[a-z0-9]+(-[a-z0-9]+)*" title="Lowercase letters, numbers and dashes only. Leave empty to auto-generate.">
            <small class="form-text text-muted">Leave empty to auto-generate from the name.</small>
            @error('slug')<span class="text-danger small">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label for="icon_class">Icon class</label>
            <input type="text" id="icon_class" name="icon_class" value="{{ old('icon_class', $category->icon_class) }}" class="form-control @error('icon_class') is-invalid @enderror" maxlength="100" pattern="[A-Za-z0-9\- ]+" title="Font Awesome classes, e.g. fas fa-shipping-fast">
            <small class="form-text text-muted">Font Awesome class, e.g. <code>fas fa-shipping-fast</code>.</small>
            @error('icon_class')<span class="text-danger small">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
