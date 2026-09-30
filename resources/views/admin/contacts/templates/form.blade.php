@extends('admin.layouts.app')

@section('title', $template->exists ? 'Edit template' : 'New template')
@section('page_title', $template->exists ? 'Edit: ' . $template->name : 'New reply template')
@section('page_subtitle', 'Placeholders {name} {email} {message} {company} are replaced per contact')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <form method="POST"
                          action="{{ $template->exists ? route('admin.contacts.templates.update', $template->id) : route('admin.contacts.templates.store') }}">
                        @csrf
                        @if ($template->exists)
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $template->name) }}" required maxlength="191">
                            @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror"
                                   value="{{ old('subject', $template->subject) }}" required maxlength="191">
                            @error('subject')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="body">Body <span class="text-danger">*</span></label>
                            <textarea name="body" id="body" rows="12" class="form-control @error('body') is-invalid @enderror"
                                      required maxlength="6000">{{ old('body', $template->body) }}</textarea>
                            @error('body')<span class="text-danger small">{{ $message }}</span>@enderror
                            <small class="text-muted">Available placeholders: <code>{name}</code> <code>{email}</code> <code>{message}</code> <code>{company}</code></small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active (selectable in the reply composer)</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> {{ $template->exists ? 'Update template' : 'Create template' }}</button>
                        <a href="{{ route('admin.contacts.templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
