@extends('admin.layouts.app')

@section('title', 'New Service Category')
@section('page_title', 'Service Categories')
@section('page_subtitle', 'Create a new category')

@section('content')
<form method="POST" action="{{ route('admin.service-categories.store') }}" data-dp-form style="max-width:640px;">
    @csrf
    @include('admin.services.categories._form')
    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Category</button>
        <a href="{{ route('admin.service-categories.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection

@push('admin_scripts')
<script>
$(function () {
    $('[data-dp-form]').on('submit', function (e) {
        if (!$('#name').val().trim()) { e.preventDefault(); DP.toast.error('Name is required.'); $('#name').focus(); return false; }
        $(this).find('button[type=submit]').prop('disabled', true);
    });
});
</script>
@endpush
