<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ $callout->exists ? 'Edit' : 'New' }} callout</h3></div>
            <div class="card-body">
                <form method="POST"
                      action="{{ $callout->exists ? route('admin.callouts.update', $callout) : route('admin.callouts.store') }}">
                    @csrf
                    @if ($callout->exists) @method('PUT') @endif

                    <div class="form-group">
                        <label for="status_key">Order status / stage key</label>
                        <input type="text" name="status_key" id="status_key" class="form-control @error('status_key') is-invalid @enderror"
                               list="dp-callout-slots" value="{{ old('status_key', $callout->status_key) }}" required>
                        <datalist id="dp-callout-slots">
                            @foreach (\App\Models\OrderCallout::SLOTS as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </datalist>
                        <small class="form-text text-muted">
                            Pick a known stage (legacy order page) or type a custom key — e.g. the raw order status
                            (<code>Order processing</code>, <code>completed</code>) which the new-design order page renders.
                        </small>
                        @error('status_key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="style">Callout color style</label>
                        <select name="style" id="style" class="form-control @error('style') is-invalid @enderror">
                            @foreach (\App\Models\OrderCallout::STYLES as $st)
                            <option value="{{ $st }}" {{ old('style', $callout->style) === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                        @error('style')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="heading">Heading (optional)</label>
                        <input type="text" name="heading" id="heading" class="form-control @error('heading') is-invalid @enderror"
                               value="{{ old('heading', $callout->heading) }}" maxlength="255" placeholder="e.g. Hi there.">
                        @error('heading')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="body">Callout text</label>
                        <textarea name="body" id="body" rows="5" class="form-control @error('body') is-invalid @enderror"
                                  required placeholder="Callout text — each line becomes its own paragraph.">{{ old('body', $callout->body) }}</textarea>
                        <small class="form-text text-muted">Each new line renders as a separate paragraph inside one colored callout box.</small>
                        @error('body')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="sort_order">Sort order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $callout->sort_order) }}" min="0" max="9999">
                            <small class="form-text text-muted">Lower shows first (10, 20, 30…).</small>
                            @error('sort_order')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group col-md-4 pt-4">
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" class="custom-control-input" id="is_enabled" name="is_enabled" value="1"
                                       {{ old('is_enabled', $callout->is_enabled ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_enabled">Enabled (visible to customers)</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ $callout->exists ? 'Save changes' : 'Create callout' }}</button>
                    <a href="{{ route('admin.callouts.index') }}" class="btn btn-default border">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Preview</h3></div>
            <div class="card-body">
                <div class="callout callout-{{ old('style', $callout->style) }}">
                    @if (old('heading', $callout->heading))<h5>{{ old('heading', $callout->heading) }}</h5>@endif
                    @foreach (preg_split('/\r\n|\r|\n/', trim((string) old('body', $callout->body))) as $p)
                    <p>{{ $p }}</p>
                    @endforeach
                </div>
                <small class="text-muted">Static preview (updates after save).</small>
            </div>
        </div>
    </div>
</div>
