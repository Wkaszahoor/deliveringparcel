@extends('admin.layouts.app')

@section('title', 'Mobile App · Screen Controls')
@section('page_title', 'Mobile App — Screen Controls')
@section('page_subtitle', 'Show/hide and enable/disable each mobile screen, choose who sees it, and configure visible elements per screen.')

@section('content')
@include('mobile-admin.partials.subnav')

@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-mobile-screen mr-1"></i> Screen Visibility Controls</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" id="bulkSave"><i class="fas fa-save mr-1"></i> Save All</button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:250px">Screen</th>
                            <th style="width:100px">Visible</th>
                            <th style="width:100px">Enabled</th>
                            <th style="width:180px">Visible To</th>
                            <th style="width:130px">Status</th>
                            <th style="width:110px">Config</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($main as $screen)
                        @include('mobile-admin.screens.row', ['screen' => $screen, 'indent' => false])
                        @foreach ($screen->sub_screens as $sub)
                        @include('mobile-admin.screens.row', ['screen' => $sub, 'indent' => true])
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <small class="text-muted"><b>Visible</b> = tab/screen appears in the app. <b>Enabled</b> = opening it is allowed. Sub-screens (indented) follow their parent tab. Changes auto-save.</small>
            </div>
        </div>
    </div>

    {{-- Config panels --}}
    <div class="col-12" id="configPanels" style="display:none">
        <div class="card" id="config-orders" style="display:none">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i> Orders screen — visible elements &amp; buttons</h3></div>
            <div class="card-body">
                @php $ordersScreen = $main->firstWhere('screen_key', 'orders'); @endphp
                <div class="row">
                    <div class="col-md-6">
                        <h6>Visible elements</h6>
                        @foreach (\App\Mobile\Controllers\Admin\MobileScreenController::ELEMENT_OPTIONS as $opt)
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input cfg" id="el_{{ $opt }}" data-block="visible_elements" data-opt="{{ $opt }}"
                                   {{ ($ordersScreen->config['visible_elements'][$opt] ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="el_{{ $opt }}">{{ ucwords(str_replace('_',' ',$opt)) }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <h6>Visible buttons</h6>
                        @foreach (\App\Mobile\Controllers\Admin\MobileScreenController::BUTTON_OPTIONS as $opt)
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input cfg" id="btn_{{ $opt }}" data-block="visible_buttons" data-opt="{{ $opt }}"
                                   {{ ($ordersScreen->config['visible_buttons'][$opt] ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="btn_{{ $opt }}">{{ ucwords(str_replace('_',' ',$opt)) }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn btn-primary mt-3" id="saveOrdersConfig"><i class="fas fa-save mr-1"></i> Save orders config</button>
            </div>
        </div>

        <div class="card" id="config-messages" style="display:none">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-comment-dots mr-1"></i> Messages screen — bubble colors per sender</h3></div>
            <div class="card-body">
                <p class="text-muted">These colors control how chat bubbles look in the app for each sender type — the per-user color coding.</p>
                @foreach (['admin' => 'Admin messages', 'client' => 'Client messages', 'system' => 'System messages'] as $who => $label)
                <div class="row align-items-center mb-2">
                    <div class="col-md-3"><strong>{{ $label }}</strong></div>
                    <div class="col-md-3">
                        <label class="mb-0">Bubble</label>
                        <div class="d-flex">
                            <input type="color" class="form-control form-control-color msgc" id="{{ $who }}_bubble" value="{{ $ui[$who.'_bubble_color'] ?? '#1565C0' }}">
                            <input type="text" class="form-control hex-input ml-1" data-target="{{ $who }}_bubble" value="{{ $ui[$who.'_bubble_color'] ?? '#1565C0' }}" maxlength="7">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-0">Text</label>
                        <div class="d-flex">
                            <input type="color" class="form-control form-control-color msgc" id="{{ $who }}_text" value="{{ $ui[$who.'_text_color'] ?? '#FFFFFF' }}">
                            <input type="text" class="form-control hex-input ml-1" data-target="{{ $who }}_text" value="{{ $ui[$who.'_text_color'] ?? '#FFFFFF' }}" maxlength="7">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-0">Position</label>
                        <select class="form-control msgpos" id="{{ $who }}_pos">
                            @foreach (['left','right','center'] as $pos)
                            <option value="{{ $pos }}" {{ ($ui[$who.'_position'] ?? '') === $pos ? 'selected' : '' }}>{{ ucfirst($pos) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endforeach
                <div class="d-flex flex-column align-items-center mt-2 mb-3 p-2 bg-light rounded" style="gap:6px" id="bubblePreview">
                    <span id="prev_admin" class="badge p-2">Admin: Your offer is ready</span>
                    <span id="prev_client" class="badge p-2">Client: Thanks, checking now</span>
                    <span id="prev_system" class="badge p-2">System: Payment received</span>
                </div>
                <button type="button" class="btn btn-primary" id="saveMessagesConfig"><i class="fas fa-save mr-1"></i> Save messages config</button>
            </div>
        </div>
    </div>
</div>

<div class="toast position-fixed" id="scrToast" style="bottom:20px;right:20px;z-index:2000">
    <div class="toast-header"><strong class="mr-auto">Mobile App</strong><button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button></div>
    <div class="toast-body" id="scrToastBody"></div>
</div>

@push('admin_scripts')
<script>
(function () {
    const toast = $('#scrToast');
    function showToast(msg, ok) {
        $('#scrToastBody').text(msg).toggleClass('text-danger', !ok);
        toast.toast({ delay: 2500 }).toast('show');
    }

    async function put(url, body) {
        const res = await fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Save failed');
        return json;
    }

    function rowPayload(key) {
        const to = [];
        if ($('#vis_' + key + '_admin').is(':checked')) to.push('admin');
        if ($('#vis_' + key + '_client').is(':checked')) to.push('client');
        return {
            screen_key: key,
            is_visible: $('#vis_' + key).is(':checked'),
            is_enabled: $('#en_' + key).is(':checked'),
            visible_to: to,
        };
    }

    $(document).on('change', '.screen-toggle, .row-role', async function () {
        const key = $(this).data('key');
        try { const r = await put('{{ url('admin/mobile-app/screens') }}/' + key, rowPayload(key)); showToast(r.message || 'Saved', true); }
        catch (e) { showToast(e.message, false); }
    });

    $('#bulkSave').on('click', async function () {
        const screens = [];
        document.querySelectorAll('.screen-toggle[data-key]').forEach(el => {
            const key = el.dataset.key;
            if (!screens.find(s => s.screen_key === key)) screens.push(rowPayload(key));
        });
        try { const r = await put('{{ route('mobile.admin.screens.bulk') }}', { screens }); showToast(r.message || 'Saved', true); }
        catch (e) { showToast(e.message, false); }
    });

    $('.cfg-btn').on('click', function () {
        const key = $(this).data('key');
        $('#configPanels').show();
        $('#configPanels .card').hide();
        $('#config-' + key).show();
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    });

    $('#saveOrdersConfig').on('click', async function () {
        const config = { visible_elements: [], visible_buttons: [] };
        document.querySelectorAll('.cfg').forEach(el => {
            if (el.checked) config[el.dataset.block].push(el.dataset.opt);
        });
        try { const r = await put('{{ route('mobile.admin.screens.config', 'orders') }}', { config }); showToast(r.message || 'Saved', true); }
        catch (e) { showToast(e.message, false); }
    });

    $('.hex-input').on('input', function () { $('#' + this.dataset.target).val(this.value); });
    $('.msgc').on('input', refreshPreview);
    $('.msgpos').on('change', refreshPreview);
    function refreshPreview() {
        $('#prev_admin').css({ background: $('#admin_bubble').val(), color: $('#admin_text').val(), 'border-radius': '12px' });
        $('#prev_client').css({ background: $('#client_bubble').val(), color: $('#client_text').val(), 'border-radius': '12px' });
        $('#prev_system').css({ background: $('#system_bubble').val(), color: $('#system_text').val(), 'border-radius': '12px' });
    }
    refreshPreview();

    $('#saveMessagesConfig').on('click', async function () {
        const ui = {
            admin_bubble_color: $('#admin_bubble').val(), admin_text_color: $('#admin_text').val(), admin_position: $('#admin_pos').val(),
            client_bubble_color: $('#client_bubble').val(), client_text_color: $('#client_text').val(), client_position: $('#client_pos').val(),
            system_bubble_color: $('#system_bubble').val(), system_text_color: $('#system_text').val(), system_position: $('#system_pos').val(),
        };
        try { const r = await put('{{ route('mobile.admin.screens.config', 'messages') }}', { ui }); showToast(r.message || 'Saved', true); }
        catch (e) { showToast(e.message, false); }
    });
})();
</script>
@endpush
@endsection
