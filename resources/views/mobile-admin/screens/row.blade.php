<tr data-key="{{ $screen->screen_key }}" class="{{ $indent ? 'table-light' : '' }}">
    <td>
        @if ($indent)<i class="fas fa-level-up-alt fa-rotate-90 mr-2 text-muted"></i>@endif
        <strong>{{ $screen->screen_name }}</strong>
        @if ($indent)<span class="badge badge-secondary ml-1">sub</span>@endif
    </td>
    <td>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input screen-toggle" id="vis_{{ $screen->screen_key }}" data-key="{{ $screen->screen_key }}" {{ $screen->is_visible ? 'checked' : '' }}>
            <label class="custom-control-label" for="vis_{{ $screen->screen_key }}"></label>
        </div>
    </td>
    <td>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input screen-toggle" id="en_{{ $screen->screen_key }}" data-key="{{ $screen->screen_key }}" {{ $screen->is_enabled ? 'checked' : '' }}>
            <label class="custom-control-label" for="en_{{ $screen->screen_key }}"></label>
        </div>
    </td>
    <td>
        <div class="form-check form-check-inline">
            <input class="form-check-input row-role" type="checkbox" id="vis_{{ $screen->screen_key }}_admin" data-key="{{ $screen->screen_key }}" value="admin" {{ in_array('admin', $screen->visible_to ?? []) ? 'checked' : '' }}>
            <label class="form-check-label" for="vis_{{ $screen->screen_key }}_admin">Admin</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input row-role" type="checkbox" id="vis_{{ $screen->screen_key }}_client" data-key="{{ $screen->screen_key }}" value="client" {{ in_array('client', $screen->visible_to ?? []) ? 'checked' : '' }}>
            <label class="form-check-label" for="vis_{{ $screen->screen_key }}_client">Client</label>
        </div>
    </td>
    <td>
        <span class="badge badge-{{ ($screen->is_visible && $screen->is_enabled) ? 'success' : 'danger' }}">
            {{ ($screen->is_visible && $screen->is_enabled) ? 'Active' : 'Disabled' }}
        </span>
    </td>
    <td>
        @if (in_array($screen->screen_key, ['orders', 'messages']))
        <button type="button" class="btn btn-sm btn-outline-info cfg-btn" data-key="{{ $screen->screen_key }}">
            <i class="fas fa-cog"></i> Config
        </button>
        @else
        <span class="text-muted">—</span>
        @endif
    </td>
</tr>
