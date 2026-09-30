@extends('admin.layouts.app')
@section('title', $widget->exists ? 'Edit widget' : 'New widget')

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">{{ $widget->exists ? 'Edit' : 'New' }} widget — {{ $widget->title ?: 'untitled' }}</h3></div>
    <div class="card-body">
        <form method="POST"
              action="{{ $widget->exists ? route('admin.widgets.update', $widget) : route('admin.widgets.store') }}">
            @csrf
            @if ($widget->exists) @method('PUT') @endif

            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" class="form-control" required maxlength="150" value="{{ old('title', $widget->title) }}">
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Type *</label>
                    <select name="type" id="w-type" class="form-control">
                        @foreach ($types as $key => $t)
                            <option value="{{ $key }}" {{ old('type', $widget->type) === $key ? 'selected' : '' }}>{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted" id="w-type-help"></small>
                </div>
                <div class="col-md-4 form-group">
                    <label>Area *</label>
                    <select name="area" class="form-control">
                        @foreach ($areas as $key => $label)
                            <option value="{{ $key }}" {{ old('area', $widget->area) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label>Sort order</label>
                    <input type="number" name="sort" min="0" class="form-control" value="{{ old('sort', $widget->sort ?? 100) }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Scope *</label>
                    <select name="scope" class="form-control">
                        @foreach ($scopes as $key => $label)
                            <option value="{{ $key }}" {{ old('scope', $widget->scope) === $key ? 'selected' : '' }}>{{ $key }} — {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label>Scope key</label>
                    <input type="text" name="scope_key" class="form-control" placeholder="home2/services (page) · template/theme name · * for any"
                           value="{{ old('scope_key', $widget->scope_key) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label>Visibility</label>
                    <select name="auth_mode" class="form-control">
                        <option value="any"  {{ old('auth_mode', old('conditions.auth', 'any')) === 'any' ? 'selected' : '' }}>Everyone</option>
                        <option value="guest" {{ old('auth_mode', 'any') === 'guest' ? 'selected' : '' }}>Guests only</option>
                        <option value="user"  {{ old('auth_mode', 'any') === 'user' ? 'selected' : '' }}>Logged-in only</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Active</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $widget->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('is_active', $widget->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Cache minutes (0 = none)</label>
                    <input type="number" name="cache_minutes" min="0" max="1440" class="form-control" value="{{ old('cache_minutes', $widget->cache_minutes ?? 10) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Starts at</label>
                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($widget->starts_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Ends at</label>
                    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($widget->ends_at)->format('Y-m-d\TH:i')) }}">
                </div>
            </div>

            <hr>
            <h5>Widget configuration</h5>

            {{-- review_widget config --}}
            <div class="w-cfg" data-for="review_widget">
                <div class="row">
                    <div class="col-md-3 form-group"><label>Heading</label>
                        <input type="text" name="config[title]" class="form-control" value="{{ old('config.title', $widget->config['title'] ?? 'What our customers say') }}"></div>
                    <div class="col-md-3 form-group"><label>Limit</label>
                        <input type="number" name="config[limit]" min="1" max="24" class="form-control" value="{{ old('config.limit', $widget->config['limit'] ?? 6) }}"></div>
                    <div class="col-md-3 form-group"><label>Sort</label>
                        <select name="config[sort]" class="form-control">
                            @foreach (['newest', 'oldest', 'rating_high', 'rating_low', 'random', 'featured'] as $s)
                                <option value="{{ $s }}" {{ old('config.sort', $widget->config['sort'] ?? 'newest') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-3 form-group"><label>Layout</label>
                        <select name="config[layout]" class="form-control">
                            @foreach (['grid', 'list', 'cards'] as $l)
                                <option value="{{ $l }}" {{ old('config.layout', $widget->config['layout'] ?? 'grid') === $l ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select></div>
                </div>
                <div class="form-group form-check">
                    <input type="hidden" name="config[verified]" value="0">
                    <input type="checkbox" class="form-check-input" id="w-verified" name="config[verified]" value="1" {{ old('config.verified', $widget->config['verified'] ?? 0) ? 'checked' : '' }}>
                    <label class="form-check-label" for="w-verified">Verified purchases only</label>
                </div>
                <div class="form-group"><label>Minimum rating</label>
                    <select name="config[rating_min]" class="form-control">
                        <option value="0" {{ old('config.rating_min', $widget->config['rating_min'] ?? 0) == 0 ? 'selected' : '' }}>any</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ old('config.rating_min', $widget->config['rating_min'] ?? 0) == $i ? 'selected' : '' }}>{{ $i }}★ and up</option>
                        @endfor
                    </select></div>
            </div>

            {{-- blade block config --}}
            <div class="w-cfg" data-for="blade">
                <div class="form-group"><label>Block *</label>
                    <select name="config[view]" class="form-control">
                        @foreach ($blocks as $key => $label)
                            <option value="{{ $key }}" {{ old('config.view', $widget->config['view'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select></div>
            </div>

            {{-- html config --}}
            <div class="w-cfg" data-for="html">
                <div class="form-group"><label>HTML (sanitized on save — scripts/iframes stripped)</label>
                    <textarea name="config[html]" rows="6" class="form-control" placeholder="<p>Welcome …</p>">{{ old('config.html', $widget->config['html'] ?? '') }}</textarea></div>
            </div>

            <button class="btn btn-primary" type="submit">{{ $widget->exists ? 'Save changes' : 'Create widget' }}</button>
            <a href="{{ route('admin.widgets.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var type = document.getElementById('w-type');
    var cfgs = document.querySelectorAll('.w-cfg');
    function show() {
        cfgs.forEach(function (c) { c.style.display = c.dataset.for === type.value ? '' : 'none'; });
    }
    type.addEventListener('change', show); show();
})();
</script>
@endpush
