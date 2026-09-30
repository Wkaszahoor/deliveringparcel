@extends('admin.layouts.app')

@section('title', 'Mobile App · UI Settings')
@section('page_title', 'Mobile App — UI Settings')
@section('page_subtitle', 'App colors, chat bubble colors per sender (admin / client / system) and typography. The preview updates live as you pick.')

@section('content')
@include('mobile-admin.partials.subnav')

@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

@php
    $g = $grouped;
    $colors = $g['app_colors'] ?? [];
    $msg = $g['message_colors'] ?? [];
    $typo = $g['typography'] ?? [];
@endphp

<form method="POST" action="{{ route('mobile.admin.ui.update') }}">
    @csrf
    @method('PUT')
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-colors"><i class="fas fa-palette mr-1"></i> Colors</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-messages"><i class="fas fa-comments mr-1"></i> Messages</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-typo"><i class="fas fa-font mr-1"></i> Typography</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab-colors">
            <div class="card">
                <div class="card-header"><h3 class="card-title">App colors</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Primary Color</label>
                            <div class="d-flex">
                                <input type="color" name="colors[primary_color]" class="form-control form-control-color cpick" id="primary_color" value="{{ $colors['primary_color'] ?? '#2196F3' }}">
                                <input type="text" class="form-control ml-1 hexinput" data-target="primary_color" value="{{ $colors['primary_color'] ?? '#2196F3' }}" maxlength="7" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Secondary Color</label>
                            <div class="d-flex">
                                <input type="color" name="colors[secondary_color]" class="form-control form-control-color cpick" id="secondary_color" value="{{ $colors['secondary_color'] ?? '#FF9800' }}">
                                <input type="text" class="form-control ml-1 hexinput" data-target="secondary_color" value="{{ $colors['secondary_color'] ?? '#FF9800' }}" maxlength="7" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Background Color</label>
                            <div class="d-flex">
                                <input type="color" name="colors[background_color]" class="form-control form-control-color cpick" id="background_color" value="{{ $colors['background_color'] ?? '#FFFFFF' }}">
                                <input type="text" class="form-control ml-1 hexinput" data-target="background_color" value="{{ $colors['background_color'] ?? '#FFFFFF' }}" maxlength="7" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="tab-messages">
            <div class="row">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Message bubble colors <small class="text-muted">(per-sender color coding in chat)</small></h3></div>
                        <div class="card-body">
                            @foreach (['admin' => 'ADMIN', 'client' => 'CLIENT', 'system' => 'SYSTEM'] as $who => $label)
                            <div class="mb-3 pb-3 border-bottom">
                                <h6 class="text-uppercase text-muted">{{ $label }}</h6>
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="mb-0">Bubble Color</label>
                                        <div class="d-flex">
                                            <input type="color" name="message_colors[{{ $who }}][bubble]" class="form-control form-control-color cpick" id="{{ $who }}_bubble" value="{{ $msg[$who.'_bubble_color'] ?? '#1565C0' }}">
                                            <input type="text" class="form-control ml-1 hexinput" data-target="{{ $who }}_bubble" value="{{ $msg[$who.'_bubble_color'] ?? '#1565C0' }}" maxlength="7" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="mb-0">Text Color</label>
                                        <div class="d-flex">
                                            <input type="color" name="message_colors[{{ $who }}][text]" class="form-control form-control-color cpick" id="{{ $who }}_text" value="{{ $msg[$who.'_text_color'] ?? '#FFFFFF' }}">
                                            <input type="text" class="form-control ml-1 hexinput" data-target="{{ $who }}_text" value="{{ $msg[$who.'_text_color'] ?? '#FFFFFF' }}" maxlength="7" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="mb-0">Position</label>
                                        @if ($who === 'system')
                                        <input type="hidden" name="message_colors[system][position]" value="center">
                                        <input type="text" class="form-control" value="Center" readonly>
                                        @else
                                        <select name="message_colors[{{ $who }}][position]" class="form-control posSel" data-who="{{ $who }}">
                                            @foreach (['left','right','center'] as $pos)
                                            <option value="{{ $pos }}" {{ ($msg[$who.'_position'] ?? ($who === 'admin' ? 'right' : 'left')) === $pos ? 'selected' : '' }}>{{ ucfirst($pos) }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card sticky-top" style="top:80px">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-eye mr-1"></i> Live Preview</h3></div>
                        <div class="card-body">
                            <div id="chat-preview" style="background:#f5f5f5;border-radius:8px;padding:16px">
                                <div id="preview-admin" style="display:flex;justify-content:flex-end;margin-bottom:8px">
                                    <div id="admin-bubble" style="background:#1565C0;color:#FFFFFF;padding:8px 12px;border-radius:10px;border-bottom-right-radius:2px;max-width:75%">
                                        Hello! How can I help you?
                                    </div>
                                </div>
                                <div id="preview-client" style="display:flex;justify-content:flex-start;margin-bottom:8px">
                                    <div id="client-bubble" style="background:#E8F5E9;color:#212121;padding:8px 12px;border-radius:10px;border-bottom-left-radius:2px;max-width:75%">
                                        I need help with my order
                                    </div>
                                </div>
                                <div id="preview-system" style="display:flex;justify-content:center">
                                    <div id="system-bubble" style="background:#FFF3E0;color:#E65100;padding:4px 12px;border-radius:10px;font-size:12px">
                                        Order status updated
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mt-2">Roughly how the chat bubbles will look in the mobile app.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="tab-typo">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Typography</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Base font size</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="typography[font_size_base]" value="{{ $typo['font_size_base'] ?? 14 }}" min="8" max="72">
                                <div class="input-group-append"><span class="input-group-text">px</span></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Header size</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="typography[font_size_header]" value="{{ $typo['font_size_header'] ?? 18 }}" min="8" max="72">
                                <div class="input-group-append"><span class="input-group-text">px</span></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Message size</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="typography[font_size_message]" value="{{ $typo['font_size_message'] ?? 14 }}" min="8" max="72">
                                <div class="input-group-append"><span class="input-group-text">px</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Layout</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Screen bottom padding</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="layout[screen_bottom_padding]" value="{{ ($g['layout'] ?? [])['screen_bottom_padding'] ?? 90 }}" min="0" max="200">
                                <div class="input-group-append"><span class="input-group-text">px</span></div>
                            </div>
                            <small class="text-muted">Empty space kept below lists so content never hides behind the bottom tab bar / quick actions.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save All Settings</button>
    </div>
</form>

@push('admin_scripts')
<script>
(function () {
    $('.cpick').on('input', function () {
        $('input.hexinput[data-target="' + this.id + '"]').val(this.value);
        render();
    });
    function render() {
        const set = (who) => {
            const pos = $('select.posSel[data-who="' + who + '"]').val() || (who === 'admin' ? 'right' : (who === 'system' ? 'center' : 'left'));
            const justify = pos === 'right' ? 'flex-end' : (pos === 'center' ? 'center' : 'flex-start');
            $('#preview-' + who).css('justify-content', justify);
            $('#' + who + '-bubble').css({ background: $('#' + who + '_bubble').val(), color: $('#' + who + '_text').val() });
        };
        ['admin', 'client', 'system'].forEach(set);
        $('#chat-preview').css('background', $('#background_color').val());
        $('#chat-preview').css('border', '2px solid ' + $('#primary_color').val());
    }
    $('.posSel').on('change', render);
    render();
})();
</script>
@endpush
@endsection
