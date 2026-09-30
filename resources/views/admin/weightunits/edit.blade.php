@extends('layouts.tailwind.app')

@section('title', 'Edit Weight Unit')
@section('page_title', 'Weight Units')
@section('page_subtitle', 'Edit unit: ' . $unit->name)

@section('content')
<form method="POST" action="{{ route('admin.weight-units.update', $unit) }}" data-dp-form>
    @csrf
    @method('PUT')
    @include('admin.weightunits._form')
    <div class="mt-4 flex max-w-2xl gap-2">
        <x-admin.button type="submit"><i class="fas fa-save"></i> Save Changes</x-admin.button>
        <x-admin.button variant="secondary" tag="a" :href="route('admin.weight-units.index')">Cancel</x-admin.button>
    </div>
</form>
@endsection

@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

{{-- See create.blade.php comment: DP.toast (dp-lazy.js) + toastr must be pushed
     explicitly, and this jQuery script stays in @push('admin_scripts'). --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
$(function () {
    $('[data-dp-form]').on('submit', function (e) {
        if (!$('#name').val().trim()) { e.preventDefault(); DP.toast.error('Name is required.'); $('#name').focus(); return false; }
        if (!$('#symbol').val().trim()) { e.preventDefault(); DP.toast.error('Symbol is required.'); $('#symbol').focus(); return false; }
        $(this).find('button[type=submit]').prop('disabled', true);
    });
});
</script>
@endpush
