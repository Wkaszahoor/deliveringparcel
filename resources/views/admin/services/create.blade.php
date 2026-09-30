@extends('admin.layouts.app')

@section('title', 'New Service')
@section('page_title', 'Services')
@section('page_subtitle', 'Create a new service')

@section('content')
<form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data" data-dp-form>
    @csrf
    @include('admin.services._form')
    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Service</button>
        <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection

@push('admin_styles')
<link rel="stylesheet" href="{{ asset('dashbord/plugins/summernote/summernote-bs4.min.css') }}">
<link rel="stylesheet" href="{{ asset('dashbord/plugins/select2/select2.min.css') }}">
@endpush

@push('admin_scripts')
<script src="{{ asset('dashbord/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('dashbord/plugins/select2/select2.min.js') }}"></script>
<script>
$(function () {
    $('#description-editor').summernote({
        height: 260,
        toolbar: [['style', ['style']], ['font', ['bold', 'italic', 'underline', 'clear']], ['para', ['ul', 'ol', 'paragraph']], ['insert', ['link', 'picture']], ['view', ['fullscreen', 'codeview', 'help']]]
    });
    $('.dp-select2').select2({ width: '100%' });

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
