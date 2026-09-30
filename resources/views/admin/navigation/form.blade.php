@extends('admin.layouts.app')

@section('title', 'Navigation')
@section('page_title', isset($item->id) && $item->id ? 'Edit Menu Item' : 'New Menu Item')
@section('page_subtitle', 'Route name wins over URL; visibility is presentation only — protected routes keep their guards')

@section('content')
<form method="POST" action="{{ isset($item->id) && $item->id ? route('admin.navigation.update', $item->id) : route('admin.navigation.store') }}">
    @csrf
    @if (isset($item->id) && $item->id)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Menu item</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Title (admin name) <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title', $item->title) }}" class="form-control @error('title') is-invalid @enderror" maxlength="100" required>
                        @error('title')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="label">Displayed label</label>
                        <input type="text" id="label" name="label" value="{{ old('label', $item->label) }}" class="form-control @error('label') is-invalid @enderror" maxlength="100" placeholder="Defaults to the title">
                        @error('label')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="route_name">Route name (preferred)</label>
                        <input type="text" id="route_name" name="route_name" value="{{ old('route_name', $item->route_name) }}" class="form-control @error('route_name') is-invalid @enderror" maxlength="255" pattern="[a-zA-Z0-9._\-]+" placeholder="e.g. shipper.program" title="Letters, numbers, dots, dashes and underscores only">
                        <small class="form-text text-muted">Named route — always check <code>php artisan route:list</code>. Resolved live, so URL changes follow.</small>
                        @error('route_name')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="url">URL (fallback / external)</label>
                        <input type="text" id="url" name="url" value="{{ old('url', $item->url) }}" class="form-control @error('url') is-invalid @enderror" maxlength="500" placeholder="e.g. /home2/services or https://example.com">
                        <small class="form-text text-muted">Used when no route name is set (or the route does not exist). External http(s) links open in the same tab unless “Open in new tab” is checked.</small>
                        @error('url')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="icon_class">Icon text</label>
                        <input type="text" id="icon_class" name="icon_class" value="{{ old('icon_class', $item->icon_class) }}" class="form-control @error('icon_class') is-invalid @enderror" maxlength="100" placeholder="Optional short icon/emoji shown before the label">
                        @error('icon_class')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Placement &amp; visibility</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="parent_id">Parent item</label>
                        <select id="parent_id" name="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                            <option value="">— Top level —</option>
                            @foreach ($parents as $p)
                                <option value="{{ $p->id }}" {{ (string) old('parent_id', $p->id === $item->parent_id ? $p->id : '') === (string) $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Nesting depth is capped at two levels.</small>
                    </div>

                    <div class="form-group">
                        <label for="visibility">Visible to <span class="text-danger">*</span></label>
                        <select id="visibility" name="visibility" class="form-control @error('visibility') is-invalid @enderror" required>
                            <option value="everyone" {{ old('visibility', $item->visibility) === 'everyone' ? 'selected' : '' }}>Everyone</option>
                            <option value="guest" {{ old('visibility', $item->visibility) === 'guest' ? 'selected' : '' }}>Guests only (logged out)</option>
                            <option value="auth" {{ old('visibility', $item->visibility) === 'auth' ? 'selected' : '' }}>Logged-in users only</option>
                        </select>
                        <small class="form-text text-muted">Presentation only — backend auth guards remain authoritative.</small>
                        @error('visibility')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="sort">Sort order</label>
                        <input type="number" id="sort" name="sort" value="{{ old('sort', $item->sort ?? 0) }}" class="form-control @error('sort') is-invalid @enderror" min="0" max="999999" step="1">
                        <small class="form-text text-muted">Lower numbers render first. You can also reorder with the up/down buttons in the list.</small>
                        @error('sort')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active (shown on the site)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="new_tab" name="new_tab" value="1" {{ old('new_tab', $item->new_tab ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="new_tab">Open in a new tab</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">{{ isset($item->id) && $item->id ? 'Update item' : 'Create item' }}</button>
                    <a href="{{ route('admin.navigation.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
