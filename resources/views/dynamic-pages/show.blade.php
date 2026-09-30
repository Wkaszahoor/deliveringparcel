@extends($layout)

{{-- home2 layout renders: <title>@yield('title') | DeliveringParcel</title>
     so the suffix is NOT added here (avoids "…| Delivering Parcel | DeliveringParcel"). --}}
@section('title', $page->meta_title ?: $page->title)

@if($page->meta_description)
@section('meta_description', $page->meta_description)
@endif

@section('content')
<section class="h2-section dynamic-page-show">
  <div class="h2-container" style="padding:2.5rem 1rem">

    <div class="row">
      <div class="col-12 col-lg-9">
        <h1 style="font-weight:700;margin-bottom:1.25rem">
          @if($page->icon)<i class="fa {{ $page->icon }} mr-2 text-primary" aria-hidden="true"></i>@endif
          {{ $page->title }}
        </h1>

        <div class="dynamic-page-content">
          {!! $page->content !!}
        </div>
      </div>
    </div>

  </div>
</section>
@endsection
