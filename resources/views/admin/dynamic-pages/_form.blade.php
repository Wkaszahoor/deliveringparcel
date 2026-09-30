{{-- Shared form partial for create/edit.
     Expects: $page (DynamicPage|null), $sections (array), $layouts (array).
     BS4/AdminLTE markup (NOT Bootstrap 5). --}}
@php $isEdit = !is_null($page); @endphp

<div class="row">

  {{-- Column 1: Core --}}
  <div class="col-12 col-lg-8">

    <div class="form-group">
      <label class="font-weight-bold">Page Title <span class="text-danger">*</span></label>
      <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title', $isEdit ? $page->title : '') }}"
             placeholder="e.g. Careers" required>
      @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Slug <small class="text-muted">(auto from title)</small></label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">/page/</span>
            </div>
            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                   value="{{ old('slug', $isEdit ? $page->slug : '') }}"
                   placeholder="careers" {{ $isEdit ? 'readonly' : '' }}>
          </div>
          @if($isEdit)<small class="text-muted">Slug cannot be changed after creation.</small>@endif
          @error('slug')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Nav Label</label>
          <input type="text" name="nav_label" class="form-control"
                 value="{{ old('nav_label', $isEdit ? $page->nav_label : '') }}"
                 placeholder="Careers (shown in navigation)">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="font-weight-bold">Page Content (HTML)</label>
      <textarea name="content" id="content-editor" class="form-control" rows="15"
                style="font-family:monospace;font-size:13px">{!! old('content', $isEdit ? $page->content : '') !!}</textarea>
      <small class="text-muted">
        Full HTML supported. Use &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, etc.
        For rich editor, install TinyMCE or Quill and target #content-editor.
      </small>
    </div>

  </div>

  {{-- Column 2: Settings --}}
  <div class="col-12 col-lg-4">

    <div class="card bg-light mb-3">
      <div class="card-header font-weight-bold">Settings</div>
      <div class="card-body">

        <div class="form-group">
          <label>Section / Group</label>
          <select name="section" class="form-control">
            @foreach($sections as $s)
            <option value="{{ $s }}"
              {{ old('section', $isEdit ? $page->section : 'pages') === $s ? 'selected' : '' }}>
              {{ ucfirst($s) }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Icon <small class="text-muted">(FontAwesome class)</small></label>
          <input type="text" name="icon" class="form-control"
                 value="{{ old('icon', $isEdit ? $page->icon : '') }}"
                 placeholder="fa-briefcase">
        </div>

        <div class="form-group">
          <label>Layout</label>
          <select name="layout" class="form-control">
            @foreach($layouts as $val => $label)
            <option value="{{ $val }}"
              {{ old('layout', $isEdit ? $page->layout : 'home2.layouts.app') === $val ? 'selected' : '' }}>
              {{ $label }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Sort Order</label>
          <input type="number" name="sort_order" class="form-control" min="0"
                 value="{{ old('sort_order', $isEdit ? $page->sort_order : 0) }}">
        </div>

        <div class="custom-control custom-switch mb-2">
          <input type="checkbox" class="custom-control-input" name="is_active" value="1" id="is_active"
                 {{ old('is_active', $isEdit ? $page->is_active : true) ? 'checked' : '' }}>
          <label class="custom-control-label" for="is_active">Active (visible to public)</label>
        </div>
        <div class="custom-control custom-switch mb-2">
          <input type="checkbox" class="custom-control-input" name="show_in_header" value="1" id="show_in_header"
                 {{ old('show_in_header', $isEdit ? $page->show_in_header : false) ? 'checked' : '' }}>
          <label class="custom-control-label" for="show_in_header">Show in Header Nav</label>
        </div>
        <div class="custom-control custom-switch mb-2">
          <input type="checkbox" class="custom-control-input" name="show_in_main_nav" value="1" id="show_in_main_nav"
                 {{ old('show_in_main_nav', $isEdit ? $page->show_in_main_nav : true) ? 'checked' : '' }}>
          <label class="custom-control-label" for="show_in_main_nav">Show in Main Navigation</label>
        </div>
        <div class="custom-control custom-switch mb-2">
          <input type="checkbox" class="custom-control-input" name="show_in_footer" value="1" id="show_in_footer"
                 {{ old('show_in_footer', $isEdit ? $page->show_in_footer : false) ? 'checked' : '' }}>
          <label class="custom-control-label" for="show_in_footer">Show in Footer</label>
        </div>

      </div>
    </div>

    {{-- SEO --}}
    <div class="card bg-light mb-3">
      <div class="card-header font-weight-bold"><i class="fa fa-search mr-2"></i>SEO Settings</div>
      <div class="card-body">
        <div class="form-group">
          <label>Meta Title</label>
          <input type="text" name="meta_title" class="form-control"
                 value="{{ old('meta_title', $isEdit ? $page->meta_title : '') }}"
                 placeholder="Careers | Delivering Parcel">
        </div>
        <div class="form-group">
          <label>Meta Description</label>
          <textarea name="meta_description" class="form-control" rows="2"
                    placeholder="Max 160 chars for Google">{{ old('meta_description', $isEdit ? $page->meta_description : '') }}</textarea>
        </div>
        <div class="form-group">
          <label>Meta Keywords</label>
          <input type="text" name="meta_keywords" class="form-control"
                 value="{{ old('meta_keywords', $isEdit ? $page->meta_keywords : '') }}"
                 placeholder="careers, jobs, delivery">
        </div>
      </div>
    </div>

    {{-- Advanced --}}
    <div class="card mb-3">
      <div class="card-header">
        <a data-toggle="collapse" href="#advancedSection" role="button" aria-expanded="false" aria-controls="advancedSection" class="text-decoration-none">
          <i class="fa fa-cog mr-2"></i>Advanced (Controller Override)
        </a>
      </div>
      <div class="collapse" id="advancedSection">
        <div class="card-body">
          <div class="form-group">
            <label>Controller Class Override</label>
            <input type="text" name="controller_override" class="form-control"
                   value="{{ old('controller_override', $isEdit ? $page->controller_override : '') }}"
                   placeholder="App\Http\Controllers\CareersController">
            <small class="text-muted">
              Leave blank to use the default content renderer.
              Set to a full class name for pages with custom logic.
            </small>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

@push('admin_scripts')
<script>
// Auto-generate slug from title (only on create)
@if(!$isEdit)
document.addEventListener('DOMContentLoaded', function () {
    var titleEl = document.querySelector('[name="title"]');
    if (!titleEl) { return; }
    titleEl.addEventListener('input', function () {
        var slug = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        var slugEl = document.querySelector('[name="slug"]');
        if (slugEl) { slugEl.value = slug; }
        var navEl = document.querySelector('[name="nav_label"]');
        if (navEl) { navEl.value = this.value; }
    });
});
@endif
</script>
@endpush
