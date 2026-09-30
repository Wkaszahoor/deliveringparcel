@extends('layouts.tailwind.app')

@section('title', 'New Weight Unit')
@section('page_title', 'Weight Units')
@section('page_subtitle', 'Create a new unit')

@section('content')
<form method="POST" action="{{ route('admin.weight-units.store') }}" data-dp-form>
    @csrf
    @include('admin.weightunits._form')
    <div class="mt-4 flex max-w-2xl gap-2">
        <x-admin.button type="submit"><i class="fas fa-save"></i> Create Unit</x-admin.button>
        <x-admin.button variant="secondary" tag="a" :href="route('admin.weight-units.index')">Cancel</x-admin.button>
    </div>
</form>
@endsection

@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

{{-- DP.toast.error (dp-lazy.js -> toastr) is not loaded by layouts.tailwind.app, so
     both are pushed explicitly; this jQuery script stays in @push('admin_scripts')
     since jQuery loads at the bottom of the layout, after @yield('content'). --}}
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
