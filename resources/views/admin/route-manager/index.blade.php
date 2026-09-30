@extends('admin.layouts.app')

@section('title', 'Route Manager')
@section('page_title', 'Route Manager')
@section('page_subtitle', 'Control which version of each page/link is live — legacy vs new design, header/footer/nav visibility, locked routes.')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>
    @foreach($errors->all() as $e)<p class="mb-0">{{ $e }}</p>@endforeach
</div>
@endif

{{-- BULK SWITCH BAR --}}
<div class="card card-outline card-primary mb-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-exchange-alt mr-1"></i> Bulk Switch <small class="text-muted">(skips locked routes)</small></h3></div>
    <div class="card-body">
        <div class="d-flex flex-wrap">
            @foreach(['navigation' => 'Navigation', 'page' => 'Page', 'section' => 'Section'] as $groupKey => $groupLabel)
            <form method="POST" action="{{ route('admin.route-manager.bulk-switch') }}" class="mr-4 mb-2">
                @csrf
                <input type="hidden" name="group" value="{{ $groupKey }}">
                <div class="btn-group">
                    <button type="submit" name="current_version" value="legacy" class="btn btn-warning btn-sm">
                        All {{ $groupLabel }} &rarr; Legacy
                    </button>
                    <button type="submit" name="current_version" value="new" class="btn btn-success btn-sm">
                        All {{ $groupLabel }} &rarr; New Design
                    </button>
                </div>
                <small class="text-muted ml-2">(skips locked routes)</small>
            </form>
            @endforeach

            {{-- 2026-09-02 — Sync Dynamic Pages (pull dynamic_pages rows into this table) --}}
            <form method="POST" action="{{ route('admin.route-manager.sync-dynamic') }}" class="mr-4 mb-2">
                @csrf
                <button type="submit" class="btn btn-outline-info btn-sm"
                        onclick="return confirm('Sync all dynamic pages into Route Manager?')">
                    <i class="fas fa-sync mr-1"></i> Sync Dynamic Pages
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ROUTE GROUPS TABLES --}}
@foreach($groups as $groupName => $settings)
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title text-uppercase font-weight-bold">{{ $groupName }} Routes</h3>
        <div class="card-tools">
            <span class="badge badge-info">{{ $settings->count() }} routes</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Route / Page</th>
                    <th>Active Version</th>
                    <th>Legacy URL</th>
                    <th>New URL</th>
                    <th>Header</th>
                    <th>Footer</th>
                    <th>Main Nav</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($settings as $setting)
                <tr class="{{ $setting->is_locked ? 'table-warning' : '' }}">
                    <td>
                        <strong>{{ $setting->label }}</strong>
                        @if($setting->is_locked)
                            <span class="badge badge-warning ml-1"><i class="fas fa-lock"></i> Locked</span>
                        @endif
                        @if($setting->notes)
                            <br><small class="text-muted">{{ $setting->notes }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-lg {{ $setting->current_version === 'new' ? 'badge-success' : 'badge-secondary' }}">
                            {{ strtoupper($setting->current_version) }}
                        </span>
                    </td>
                    <td>
                        <code class="small">{{ $setting->legacy_url ?? '—' }}</code>
                        @if($setting->legacy_url)
                            <a href="{{ $setting->legacy_url }}" target="_blank" rel="noopener" class="btn btn-xs btn-default ml-1"><i class="fas fa-external-link-alt"></i></a>
                        @endif
                    </td>
                    <td>
                        <code class="small">{{ $setting->new_url ?? '—' }}</code>
                        @if($setting->new_url)
                            <a href="{{ $setting->new_url }}" target="_blank" rel="noopener" class="btn btn-xs btn-default ml-1"><i class="fas fa-external-link-alt"></i></a>
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs toggle-flag-btn {{ $setting->show_in_header ? 'btn-success' : 'btn-default' }}"
                            data-id="{{ $setting->id }}" data-field="show_in_header"
                            data-url="{{ route('admin.route-manager.toggle-flag', $setting) }}">
                            <i class="fas fa-{{ $setting->show_in_header ? 'check' : 'times' }}"></i>
                        </button>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs toggle-flag-btn {{ $setting->show_in_footer ? 'btn-success' : 'btn-default' }}"
                            data-id="{{ $setting->id }}" data-field="show_in_footer"
                            data-url="{{ route('admin.route-manager.toggle-flag', $setting) }}">
                            <i class="fas fa-{{ $setting->show_in_footer ? 'check' : 'times' }}"></i>
                        </button>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs toggle-flag-btn {{ $setting->show_in_main_nav ? 'btn-success' : 'btn-default' }}"
                            data-id="{{ $setting->id }}" data-field="show_in_main_nav"
                            data-url="{{ route('admin.route-manager.toggle-flag', $setting) }}">
                            <i class="fas fa-{{ $setting->show_in_main_nav ? 'check' : 'times' }}"></i>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#editModal{{ $setting->id }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

    {{-- EDIT MODALS for this group's rows (kept outside <tbody> so the HTML stays valid) --}}
    @foreach($settings as $setting)
    <div class="modal fade" id="editModal{{ $setting->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.route-manager.update', $setting) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">Edit: {{ $setting->label }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="label" class="form-control" value="{{ old('label', $setting->label) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Active Version</label>
                            <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                                <label class="btn btn-secondary {{ $setting->current_version === 'legacy' ? 'active' : '' }}">
                                    <input type="radio" name="current_version" value="legacy" {{ $setting->current_version === 'legacy' ? 'checked' : '' }}>
                                    Legacy (Old Design)
                                </label>
                                <label class="btn btn-success {{ $setting->current_version === 'new' ? 'active' : '' }}">
                                    <input type="radio" name="current_version" value="new" {{ $setting->current_version === 'new' ? 'checked' : '' }}>
                                    New Design
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Legacy URL</label>
                                    <input type="text" name="legacy_url" class="form-control" value="{{ old('legacy_url', $setting->legacy_url) }}" placeholder="/old-path">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>New Design URL</label>
                                    <input type="text" name="new_url" class="form-control" value="{{ old('new_url', $setting->new_url) }}" placeholder="/new-path">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_in_header" value="1" id="rm_h_{{ $setting->id }}" {{ $setting->show_in_header ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rm_h_{{ $setting->id }}">Show in Header</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_in_footer" value="1" id="rm_f_{{ $setting->id }}" {{ $setting->show_in_footer ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rm_f_{{ $setting->id }}">Show in Footer</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_in_main_nav" value="1" id="rm_n_{{ $setting->id }}" {{ $setting->show_in_main_nav ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rm_n_{{ $setting->id }}">Show in Main Nav</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label>Admin Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $setting->notes) }}</textarea>
                        </div>
                        @if($setting->is_locked)
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-lock"></i>
                            <strong>This route is locked.</strong> Type <code>I CONFIRM</code> below to allow changes.
                            <input type="text" name="confirm_phrase" class="form-control mt-2" placeholder="Type: I CONFIRM">
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endforeach

@push('admin_scripts')
<script>
// Toggle flag buttons via AJAX — no page reload (vanilla JS, no jQuery dependency).
document.querySelectorAll('.toggle-flag-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var url   = this.dataset.url;
        var field = this.dataset.field;
        var el    = this;
        var csrfEl = document.querySelector('meta[name="csrf-token"]');
        var csrf   = csrfEl ? csrfEl.content : '';
        var fd = new FormData();
        fd.append('_token', csrf);
        fd.append('field', field);
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.value) {
                    el.classList.remove('btn-default');
                    el.classList.add('btn-success');
                    el.innerHTML = '<i class="fas fa-check"></i>';
                } else {
                    el.classList.remove('btn-success');
                    el.classList.add('btn-default');
                    el.innerHTML = '<i class="fas fa-times"></i>';
                }
            })
            .catch(function () { alert('Toggle failed. Please refresh.'); });
    });
});
</script>
@endpush
@endsection
