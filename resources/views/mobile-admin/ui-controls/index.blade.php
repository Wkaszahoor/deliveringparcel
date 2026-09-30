@extends('admin.layouts.app')

@section('title', 'Mobile App · UI Controls')
@section('page_title', 'Mobile App — UI Controls')
@section('page_subtitle', 'Visibility + enabled controls for every screen, section, element and button, separately for admin and client apps. Changes bump the version; apps re-sync automatically.')

@section('content')
@include('mobile-admin.partials.subnav')

@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<div class="row mb-3">
    <div class="col-md-8">
        <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
            <input type="text" class="form-control" id="uiSearch" placeholder="Filter by name or key (screens / sections / elements / actions)…">
        </div>
    </div>
    <div class="col-md-4 text-right d-flex align-items-center justify-content-end">
        <span class="badge badge-info mr-2" style="font-size:13px">Version: <span id="uiVersion">{{ $version }}</span></span>
        <button class="btn btn-outline-secondary btn-sm mr-2" id="expandAllBtn" title="Open every screen and section">
            <i class="fas fa-bars mr-1"></i> Expand All
        </button>
        <button class="btn btn-warning btn-sm" id="bumpBtn" title="Force every app to re-sync its UI settings">
            <i class="fas fa-sync-alt mr-1"></i> Force Sync All Apps
        </button>
    </div>
</div>

<div id="uiTree">
@foreach ($screens as $screen)
    <div class="card ui-screen mb-2" data-name="{{ strtolower($screen->screen_name . ' ' . $screen->screen_key) }}">
        <div class="card-header d-flex align-items-center flex-wrap ui-card-header" style="gap:10px;cursor:pointer" data-body="#scr-{{ $screen->screen_key }}">
            <i class="fas fa-chevron-right ui-chevron text-muted" style="font-size:12px"></i>
            <i class="fas fa-{{ $screen->icon ?: 'mobile-alt' }} text-primary"></i>
            <strong>{{ $screen->screen_name }}</strong>
            <code class="text-muted">{{ $screen->screen_key }}</code>
            @if($screen->parent_key)<span class="badge badge-secondary">sub of {{ $screen->parent_key }}</span>@endif
            <span class="badge badge-{{ $screen->global_status === 'active' ? 'success' : 'danger' }}">{{ $screen->global_status }}</span>
            <div class="ml-auto d-flex align-items-center" style="gap:14px">
                @include('mobile-admin.ui-controls.toggles', ['level' => 'screens', 'key' => $screen->screen_key, 'row' => $screen])
            </div>
        </div>
        <div class="collapse" id="scr-{{ $screen->screen_key }}">
            <div class="card-body p-0 p-2">
                @foreach (($sections[$screen->screen_key] ?? collect()) as $section)
                <div class="card ui-section mb-2" data-name="{{ strtolower($section->section_name . ' ' . $section->section_key) }}">
                    <div class="card-header d-flex align-items-center flex-wrap ui-card-header" style="gap:8px;cursor:pointer" data-body="#sec-{{ $section->section_key }}">
                        <i class="fas fa-chevron-right ui-chevron text-muted" style="font-size:12px"></i>
                        <i class="fas fa-layer-group text-info"></i>
                        <strong>{{ $section->section_name }}</strong>
                        <code class="text-muted">{{ $section->section_key }}</code>
                        <span class="badge badge-{{ $section->global_status === 'active' ? 'success' : 'danger' }}">{{ $section->global_status }}</span>
                        <div class="ml-auto d-flex align-items-center" style="gap:14px">
                            @include('mobile-admin.ui-controls.toggles', ['level' => 'sections', 'key' => $section->section_key, 'row' => $section])
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info dp-toggle-btn" data-dp-toggle="#els-{{ $section->section_key }}">
                                <i class="fas fa-list-ul mr-1"></i> Elements ({{ ($elements[$section->section_key] ?? collect())->count() }})
                            </button>
                            <button type="button" class="btn btn-outline-purple dp-toggle-btn" style="border-color:#7B1FA2;color:#7B1FA2" data-dp-toggle="#acts-{{ $section->section_key }}">
                                <i class="fas fa-bolt mr-1"></i> Actions ({{ ($actions[$section->section_key] ?? collect())->count() }})
                            </button>
                        </div>
                    </div>

                    <div class="collapse" id="sec-{{ $section->section_key }}">
                        <div class="card-body p-2 bg-light">
                            {{-- Elements panel — opened by the header button --}}
                            <div class="collapse" id="els-{{ $section->section_key }}">
                                <div class="card">
                                    <div class="card-header py-2"><strong><i class="fas fa-list-ul text-info"></i> Elements</strong> — fields shown inside "{{ $section->section_name }}"</div>
                                    <div class="card-body p-2">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead><tr><th>Element</th><th style="width:80px">Type</th><th style="width:190px">Admin V / E</th><th style="width:190px">Client V / E</th></tr></thead>
                                            <tbody>
                                            @foreach (($elements[$section->section_key] ?? collect()) as $el)
                                                <tr class="ui-element" data-name="{{ strtolower($el->element_name . ' ' . $el->element_key) }}">
                                                    <td>{{ $el->element_name }} <code class="text-muted small">{{ $el->element_key }}</code></td>
                                                    <td><span class="badge badge-light">{{ $el->element_type }}</span></td>
                                                    <td>
                                                        @include('mobile-admin.ui-controls.toggles', ['level' => 'elements', 'key' => $el->element_key, 'row' => $el, 'prefix' => 'el_'])
                                                    </td>
                                                    <td>
                                                        @include('mobile-admin.ui-controls.toggles', ['level' => 'elements', 'key' => $el->element_key, 'row' => $el, 'prefix' => 'el_', 'clientFirst' => true])
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions panel — opened by the header button --}}
                            <div class="collapse" id="acts-{{ $section->section_key }}">
                                <div class="card">
                                    <div class="card-header py-2"><strong style="color:#7B1FA2"><i class="fas fa-bolt"></i> Actions</strong> — buttons inside "{{ $section->section_name }}"</div>
                                    <div class="card-body p-2">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead><tr>
                                                <th>Action</th><th style="width:170px">Admin V / E</th><th style="width:170px">Client V / E</th>
                                                <th style="width:220px">Allowed statuses</th><th style="width:80px">Confirm</th>
                                            </tr></thead>
                                            <tbody>
                                            @foreach (($actions[$section->section_key] ?? collect()) as $act)
                                                <tr class="ui-action" data-name="{{ strtolower($act->action_name . ' ' . $act->action_key) }}">
                                                    <td>
                                                        {{ $act->action_name }} <code class="text-muted small">{{ $act->action_key }}</code>
                                                        @if($act->confirmation_required)<span class="badge badge-warning" title="{{ $act->confirmation_message }}">confirm</span>@endif
                                                    </td>
                                                    <td>@include('mobile-admin.ui-controls.toggles', ['level' => 'actions', 'key' => $act->action_key, 'row' => $act, 'prefix' => 'ac_'])</td>
                                                    <td>@include('mobile-admin.ui-controls.toggles', ['level' => 'actions', 'key' => $act->action_key, 'row' => $act, 'prefix' => 'ac_', 'clientFirst' => true])</td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm statuses-input" data-key="{{ $act->action_key }}"
                                                               value="{{ implode(', ', $act->allowed_order_statuses ?? []) }}" placeholder="empty = always show">
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input confirm-toggle" data-key="{{ $act->action_key }}" id="cf_{{ $act->action_key }}" {{ $act->confirmation_required ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="cf_{{ $act->action_key }}"></label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <small class="text-muted">Allowed statuses = comma-separated raw order statuses (e.g. "Offer Placed, Offer Updated"). Empty shows the action always.</small>
                                    </div>
                                </div>
                            </div>

                            <small class="text-muted d-block mt-2"><i class="fas fa-info-circle"></i> Open a section with its header, then use the <b>Elements</b> / <b>Actions</b> buttons to reveal and toggle individual fields and buttons.</small>
                        </div>
                    </div>
                </div>
                @endforeach
                @if (($sections[$screen->screen_key] ?? collect())->isEmpty())
                    <div class="text-muted text-center py-3">No sections configured for this screen.</div>
                @endif
            </div>
        </div>
    </div>
@endforeach
</div>

@push('admin_scripts')
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';
    const base = '{{ url("admin/mobile-app/ui-controls") }}';

    async function put(path, body) {
        const res = await fetch(base + path, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(json.message || 'Save failed');
        return json;
    }

    /* ── Collapse handling (custom delegation) ─────────────────────
       Bootstrap's data-toggle listens on document; inline
       stopPropagation() used to swallow those clicks, which is why the
       Elements/Actions panels never opened. We drive everything here
       instead and simply ignore clicks that land on row controls. */
    function refreshChevrons() {
        $('.ui-card-header').each(function () {
            const body = $($(this).data('body'));
            $(this).find('.ui-chevron')
                .toggleClass('fa-chevron-right', !body.hasClass('show'))
                .toggleClass('fa-chevron-down', body.hasClass('show'));
        });
    }

    $(document).on('click', '.dp-toggle-btn', function (e) {
        e.stopPropagation();
        var target = $($(this).data('dp-toggle'));
        target.toggleClass('show');
        // Auto-open the parent section/screen so the panel is visible.
        target.closest('.collapse').addClass('show');
        refreshChevrons();
    });

    $(document).on('click', '.ui-card-header', function (e) {
        if ($(e.target).closest('.ui-toggle, .custom-control, .custom-switch, input, select, button, label, .btn-group, .statuses-input').length) return;
        $($(this).data('body')).toggleClass('show');
        refreshChevrons();
    });

    $('#expandAllBtn').on('click', function () {
        $('#uiTree .collapse').not('[id^="els-"], [id^="acts-"]').addClass('show');
        refreshChevrons();
    });

    // V/E toggles — save instantly per level
    $(document).on('change', '.ui-toggle', async function () {
        const el = $(this);
        const level = el.data('level'), key = el.data('key'), field = el.data('field');
        try {
            const r = await put('/' + level + '/' + encodeURIComponent(key), { [field]: el.is(':checked') ? 1 : 0 });
            $('#uiVersion').text(r.version || $('#uiVersion').text());
            toast((r.message || 'Saved'), true);
        } catch (e) { toast(e.message, false); el.prop('checked', !el.is(':checked')); }
    });

    // Allowed statuses (comma list) — save on change
    $(document).on('change', '.statuses-input', async function () {
        const key = $(this).data('key');
        const list = $(this).val().split(',').map(s => s.trim()).filter(Boolean);
        try { await put('/actions/' + encodeURIComponent(key), { allowed_order_statuses: list }); toast('Statuses saved', true); }
        catch (e) { toast(e.message, false); }
    });

    // Confirmation toggle for actions
    $(document).on('change', '.confirm-toggle', async function () {
        const key = $(this).data('key');
        try { await put('/actions/' + encodeURIComponent(key), { confirmation_required: $(this).is(':checked') ? 1 : 0 }); toast('Saved', true); }
        catch (e) { toast(e.message, false); }
    });

    // Force version bump
    $('#bumpBtn').on('click', async function () {
        try {
            const res = await fetch(base + '/bump-version', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
            const json = await res.json();
            $('#uiVersion').text(json.version);
            toast('Version bumped to ' + json.version + ' — apps will re-sync.', true);
        } catch (e) { toast(e.message, false); }
    });

    // Live search — hide non-matching blocks
    $('#uiSearch').on('input', function () {
        const q = this.value.toLowerCase().trim();
        if (!q) { $('.ui-screen, .ui-section, .ui-element, .ui-action').show(); refreshChevrons(); return; }
        $('.ui-element, .ui-action').each(function () { $(this).toggle($(this).data('name').indexOf(q) >= 0); });
        $('.ui-section').each(function () {
            const self = $(this).data('name').indexOf(q) >= 0;
            const hasKids = $(this).find('.ui-element:visible, .ui-action:visible').length > 0;
            $(this).toggle(self || hasKids);
            if (!self && hasKids) $(this).find('.collapse').addClass('show');
        });
        $('.ui-screen').each(function () {
            const self = $(this).data('name').indexOf(q) >= 0;
            const hasKids = $(this).find('.ui-section:visible, .ui-element:visible, .ui-action:visible').length > 0;
            $(this).toggle(self || hasKids);
            if (!self && hasKids) $(this).find('> .collapse').addClass('show');
        });
        refreshChevrons();
    });

    let toastTimer = null;
    function toast(msg, ok) {
        let t = $('#uiToast');
        if (!t.length) { t = $('<div id="uiToast" class="alert position-fixed" style="bottom:20px;right:20px;z-index:3000"></div>').appendTo('body'); }
        t.attr('class', 'alert position-fixed alert-' + (ok ? 'success' : 'danger')).css({ bottom: 20, right: 20, zIndex: 3000 }).text(msg).show();
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => t.fadeOut(), 2200);
    }
})();
</script>
@endpush
@endsection
