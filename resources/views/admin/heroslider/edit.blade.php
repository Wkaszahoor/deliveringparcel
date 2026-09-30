@extends('layouts.tailwind.app')

@section('title', 'Edit Hero Slide')
@section('page_title', 'Hero Slider')
@section('page_subtitle', 'Edit slide: ' . $slide->title)

@section('content')
@php
    $values = $options; // resolvedOptions() from the controller (defaults + stored overrides)
@endphp
<form method="POST" action="{{ route('admin.hero-slides.update', $slide) }}" enctype="multipart/form-data" data-dp-form>
    @csrf
    @method('PUT')
    @include('admin.heroslider._form')
    <div class="mt-4 flex gap-2">
        <x-admin.button type="submit"><i class="fas fa-save"></i> Save Changes</x-admin.button>
        <x-admin.button variant="secondary" tag="a" :href="route('admin.hero-slides.index')">Cancel</x-admin.button>
    </div>
</form>
@endsection

@push('admin_styles')
    <link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

@push('admin_scripts')
{{-- Same rationale as create.blade.php: DP.toast/toastr aren't loaded globally by the Tailwind
     layout, and this block must stay in @push('admin_scripts') so it runs after the layout's
     jQuery <script> tag. --}}
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
$(function () {
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
        $(this).find('button[type=submit]').prop('disabled', true);
    });
});
</script>
@endpush
