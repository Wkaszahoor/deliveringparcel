@extends('admin.layouts.app')

@section('title', 'Edit Page: ' . $dynamicPage->title)
@section('page_title', 'Edit Page')
@section('page_subtitle', 'Editing: ' . $dynamicPage->title)

@section('content')
<div class="container-fluid">

  @if(session('success'))
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
  </div>
  @endif
  @if($errors->any())
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    @foreach($errors->all() as $e)<p class="mb-0">{{ $e }}</p>@endforeach
  </div>
  @endif

  <div class="card card-outline card-warning">
    <div class="card-header">
      <h3 class="card-title"><i class="fa fa-edit mr-2"></i>Edit: {{ $dynamicPage->title }}</h3>
    </div>
    <div class="card-body">
      {{-- NOTE: the update form and the delete form are SIBLINGS (not nested),
           because nested <form> elements are invalid HTML. --}}
      <form action="{{ route('admin.dynamic-pages.update', $dynamicPage) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.dynamic-pages._form', ['page' => $dynamicPage])
        <div class="mt-4">
          <button type="submit" class="btn btn-warning mr-2">
            <i class="fa fa-save mr-1"></i> Update Page
          </button>
          <a href="{{ route('admin.dynamic-pages.index') }}" class="btn btn-secondary mr-2">Cancel</a>
        </div>
      </form>

      @if(!$dynamicPage->is_locked)
      <div class="text-right mt-2">
        <form action="{{ route('admin.dynamic-pages.destroy', $dynamicPage) }}" method="POST"
              onsubmit="return confirm('Delete this page?')" class="d-inline">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-outline-danger">
            <i class="fa fa-trash mr-1"></i> Delete
          </button>
        </form>
      </div>
      @endif
    </div>
  </div>

</div>
@endsection
