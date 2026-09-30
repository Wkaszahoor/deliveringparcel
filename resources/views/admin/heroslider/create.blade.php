@extends('layouts.tailwind.app')

@section('title', 'New Hero Slide')
@section('page_title', 'Hero Slider')
@section('page_subtitle', 'Create a new slide')

@section('content')
@php
    // default option values straight from the config definitions
    $values = [];
    foreach ($optionFields as $optKey => $optDef) {
        $values[$optKey] = $optDef['default'] ?? '';
    }
@endphp
<form method="POST" action="{{ route('admin.hero-slides.store') }}" enctype="multipart/form-data" data-dp-form>
    @csrf
    @include('admin.heroslider._form')
    <div class="mt-4 flex gap-2">
        <x-admin.button type="submit"><i class="fas fa-save"></i> Create Slide</x-admin.button>
        <x-admin.button variant="secondary" tag="a" :href="route('admin.hero-slides.index')">Cancel</x-admin.button>
    </div>
</form>
@endsection

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@push('admin_scripts')
{{-- window.DP (toast helper) and toastr are only loaded by the old AdminLTE layouts; this page's
     inline script uses DP.toast for validation feedback, so both must be pulled in explicitly
     here. This whole block sits in @push('admin_scripts'), which renders AFTER the layout's
     jQuery <script> tag (jQuery loads near the bottom, after @yield('content')) — an inline
     script placed directly in @section('content') would run before jQuery exists. --}}
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
$(function () {
    // live range value badges
    $('[data-dp-range-output]').on('input change', function () {
        $($(this).data('dp-range-output')).text($(this).val());
    });

    $('#image').on('change', function () {
        var file = this.files && this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) { DP.toast.error('Image must be under 2MB.'); this.value = ''; return; }
        var reader = new FileReader();
        reader.onload = function (e) { $('#image-preview').attr('src', e.target.result); $('#image-preview-wrap').show(); };
        reader.readAsDataURL(file);
    });

    $('[data-dp-form]').on('submit', function (e) {
        if (!$('#title').val().trim()) { e.preventDefault(); DP.toast.error('Title is required.'); $('#title').focus(); return false; }
        if (!$('#image').val()) { e.preventDefault(); DP.toast.error('A slide image is required.'); return false; }
        $(this).find('button[type=submit]').prop('disabled', true);
    });
});
</script>
@endpush
