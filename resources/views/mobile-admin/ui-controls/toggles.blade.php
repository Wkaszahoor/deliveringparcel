{{-- 2-toggle pair (V + E) for one audience. clientFirst=true renders the
     client pair; default renders the admin pair. Used at every level. --}}
@php
    $p = ($prefix ?? '') . ($clientFirst ?? false ? 'c_' : 'a_');
    $aud = ($clientFirst ?? false) ? 'client' : 'admin';
    $vField = $aud . '_visible';
    $eField = $aud . '_enabled';
@endphp
<div class="d-flex align-items-center" style="gap:12px">
    <span class="text-muted small" style="width:34px">{{ strtoupper(substr($aud, 0, 1)) }}V</span>
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input ui-toggle" id="{{ $p }}v_{{ md5($key) }}"
               data-level="{{ $level }}" data-key="{{ $key }}" data-field="{{ $vField }}" {{ $row->$vField ? 'checked' : '' }}>
        <label class="custom-control-label" for="{{ $p }}v_{{ md5($key) }}"></label>
    </div>
    <span class="text-muted small" style="width:34px">{{ strtoupper(substr($aud, 0, 1)) }}E</span>
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input ui-toggle" id="{{ $p }}e_{{ md5($key) }}"
               data-level="{{ $level }}" data-key="{{ $key }}" data-field="{{ $eField }}" {{ $row->$eField ? 'checked' : '' }}>
        <label class="custom-control-label" for="{{ $p }}e_{{ md5($key) }}"></label>
    </div>
</div>
