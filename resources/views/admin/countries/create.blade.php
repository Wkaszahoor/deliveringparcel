@extends('layouts.tailwind.app')

@section('title', 'New Country')
@section('page_title', 'Countries')
@section('page_subtitle', 'Create a new country')

@section('content')
<form method="POST" action="{{ route('admin.countries.store') }}" data-dp-form>
    @csrf
    @include('admin.countries._form')
    <div class="mt-4 flex max-w-2xl gap-2">
        <x-admin.button type="submit"><i class="fas fa-save"></i> Create Country</x-admin.button>
        <x-admin.button variant="secondary" tag="a" :href="route('admin.countries.index')">Cancel</x-admin.button>
    </div>
</form>
@endsection

@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush

{{-- This page's submit-validation calls DP.toast.error, which wraps toastr — neither
     window.DP (dp-lazy.js) nor toastr are loaded globally by layouts.tailwind.app, so
     both must be pulled in explicitly here. jQuery itself loads at the bottom of the
     shared layout, after @yield('content'), so this script must live in @push('admin_scripts'). --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
<script>
$(function () {
    $('#iso2, #iso3').on('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
    });
    $('#dial_code').on('input', function () {
        this.value = this.value.replace(/[^+0-9]/g, '');
    });
    $('[data-dp-form]').on('submit', function (e) {
        if (!$('#name').val().trim()) { e.preventDefault(); DP.toast.error('Name is required.'); $('#name').focus(); return false; }
        $(this).find('button[type=submit]').prop('disabled', true);
    });
});
</script>
@endpush
