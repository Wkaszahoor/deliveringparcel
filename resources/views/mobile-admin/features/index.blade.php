@extends('admin.layouts.app')

@section('title', 'Mobile App · Feature Flags')
@section('page_title', 'Mobile App — Feature Flags & Modes')
@section('page_subtitle', 'Turn mobile features on/off, pick which roles get them, and control maintenance mode + forced app updates.')

@section('content')
@include('mobile-admin.partials.subnav')

@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('mobile.admin.features.update') }}">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-flag mr-1"></i> Feature Flags</h3></div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th style="width:110px">Status</th>
                                <th style="width:190px">Allowed Roles</th>
                                <th style="width:90px">Toggle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($flags as $flag)
                            <tr>
                                <td><strong>{{ $flag->feature_name }}</strong><br><small class="text-muted"><code>{{ $flag->feature_key }}</code></small></td>
                                <td><span class="badge badge-{{ $flag->is_enabled ? 'success' : 'danger' }}">{{ $flag->is_enabled ? 'Enabled' : 'Disabled' }}</span></td>
                                <td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="fr_{{ $flag->feature_key }}_admin" name="flags[{{ $flag->feature_key }}][allowed_roles][]" value="admin" {{ in_array('admin', $flag->allowed_roles ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="fr_{{ $flag->feature_key }}_admin">Admin</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="fr_{{ $flag->feature_key }}_client" name="flags[{{ $flag->feature_key }}][allowed_roles][]" value="client" {{ in_array('client', $flag->allowed_roles ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="fr_{{ $flag->feature_key }}_client">Client</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input feature-toggle" id="flag_{{ $flag->feature_key }}" data-key="{{ $flag->feature_key }}" {{ $flag->is_enabled ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="flag_{{ $flag->feature_key }}"></label>
                                    </div>
                                    {{-- form value used by Save All; the switch above is the instant AJAX toggle --}}
                                    <input type="hidden" name="flags[{{ $flag->feature_key }}][is_enabled]" id="hidden_flag_{{ $flag->feature_key }}" value="{{ $flag->is_enabled ? '1' : '0' }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save All Feature Settings</button>
                    <small class="text-muted d-block mt-1">The switch toggles instantly (AJAX); roles are saved with the button.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-outline {{ $maintenance->active ? 'card-warning' : 'card-default' }}">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-tools mr-1"></i> Maintenance Mode</h3></div>
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="maint_on" name="maintenance[active]" value="1" {{ $maintenance->active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="maint_on"><strong>Active</strong> — all mobile users see the maintenance screen</label>
                    </div>
                    <div class="form-group" id="maint_msg_wrap" style="{{ $maintenance->active ? '' : 'display:none' }}">
                        <label>Maintenance message</label>
                        <textarea class="form-control" name="maintenance[message]" rows="3" maxlength="500">{{ $maintenance->message }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Expected back (shown to users)</label>
                        <input type="text" class="form-control" name="maintenance[expected_back]" value="{{ $maintenance->expected_back }}" placeholder="e.g. Tomorrow 9:00 AM" maxlength="100">
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-cloud-download-alt mr-1"></i> Force App Update</h3></div>
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="fu_on" name="force_update[required]" value="1" {{ $forceUpdate->required ? 'checked' : '' }}>
                        <label class="custom-control-label" for="fu_on"><strong>Required</strong> — app blocks until updated</label>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Min iOS Version</label>
                            <input type="text" class="form-control" name="force_update[min_ios]" value="{{ $forceUpdate->min_ios }}" maxlength="20">
                        </div>
                        <div class="col-6">
                            <label>Min Android Version</label>
                            <input type="text" class="form-control" name="force_update[min_android]" value="{{ $forceUpdate->min_android }}" maxlength="20">
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label>Update message</label>
                        <textarea class="form-control" name="force_update[message]" rows="2" maxlength="500">{{ $forceUpdate->message }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save mr-1"></i> Save All Feature Settings</button>
        </div>
    </div>
</form>

@push('admin_scripts')
<script>
(function () {
    // Instant AJAX single-flag toggle
    $(document).on('change', '.feature-toggle', async function () {
        const key = $(this).data('key');
        try {
            const res = await fetch('{{ url('admin/mobile-app/features') }}/' + key + '/toggle', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (!res.ok || !json.ok) throw new Error(json.message || 'Toggle failed');
            $('#hidden_flag_' + key).val(json.is_enabled ? '1' : '0');
        } catch (e) {
            alert(e.message);
            $(this).prop('checked', !$(this).prop('checked'));
        }
    });

    $('#maint_on').on('change', function () {
        $('#maint_msg_wrap').toggle(this.checked);
    });
})();
</script>
@endpush
@endsection
