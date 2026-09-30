@extends('admin.layouts.app')

@section('title', 'Add New Page')
@section('page_title', 'Add New Page')
@section('page_subtitle', 'Create a new public page (e.g. Careers, FAQ, About Us)')

@section('content')
<div class="container-fluid">

  @if($errors->any())
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    @foreach($errors->all() as $e)<p class="mb-0">{{ $e }}</p>@endforeach
  </div>
  @endif

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fa fa-plus mr-2"></i>New Page</h3>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.dynamic-pages.store') }}" method="POST">
        @csrf
        @include('admin.dynamic-pages._form', ['page' => null])
        <div class="mt-4">
          <button type="submit" class="btn btn-primary mr-2">
            <i class="fa fa-save mr-1"></i> Create Page
          </button>
          <a href="{{ route('admin.dynamic-pages.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection
