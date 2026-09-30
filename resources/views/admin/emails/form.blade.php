<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ $template->exists ? 'Edit' : 'New' }} email template</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $template->exists ? route('admin.emails.update', $template) : route('admin.emails.store') }}">
                    @csrf
                    @if ($template->exists) @method('PUT') @endif

                    <div class="form-group">
                        <label>Key (machine name)</label>
                        <input type="text" name="key" class="form-control @error('key') is-invalid @enderror" list="dp-email-keys"
                               value="{{ old('key', $template->key) }}" required placeholder="welcome_email">
                        <datalist id="dp-email-keys">
                            @foreach (\App\Models\EmailTemplate::KNOWN_KEYS as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </datalist>
                        <small class="form-text text-muted">Must match the key the sending flow uses (list above). Deleting/disabling the row falls back to the built-in email.</small>
                        @error('key')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Admin label</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}" required>
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control @error('category') is-invalid @enderror">
                            @foreach (\App\Models\EmailTemplate::CATEGORIES as $cKey => $cLabel)
                            <option value="{{ $cKey }}" {{ old('category', $template->category) === $cKey ? 'selected' : '' }}>{{ $cLabel }}</option>
                            @endforeach
                        </select>
                        @error('category')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject', $template->subject) }}" required>
                        @error('subject')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Body (HTML allowed; each line = paragraph when no HTML)</label>
                        <textarea name="body" rows="12" class="form-control @error('body') is-invalid @enderror" required>{{ old('body', $template->body) }}</textarea>
                        <small class="form-text text-muted">Placeholders: @verbatim {{name}} {{email}} {{order_id}} {{order_ref}} {{link}} @endverbatim + any fields the flow passes (see help text below).</small>
                        @error('body')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Placeholder help (shown to future admins)</label>
                        <textarea name="placeholder_help" rows="2" class="form-control">{{ old('placeholder_help', $template->placeholder_help) }}</textarea>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="is_enabled" name="is_enabled" value="1" {{ old('is_enabled', $template->is_enabled ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_enabled">Enabled (unchecked = flow uses the built-in email)</label>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ $template->exists ? 'Save changes' : 'Create template' }}</button>
                    <a href="{{ route('admin.emails.index') }}" class="btn btn-default border">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">How sending works</h3></div>
            <div class="card-body small">
                <ol>
                    <li>Master switch (Settings → Email) must be ON.</li>
                    <li>This template must be enabled.</li>
                    <li>Body placeholders are replaced per recipient.</li>
                    <li>Sent inline, or queued on the <code>emails</code> queue when queued sending is ON (drained by the cPanel cron <code>dp:queue-drain</code>).</li>
                    <li>Every attempt is recorded in Email Logs with status + error; failures never break the business flow.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
