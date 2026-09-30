@extends('layouts.appn')
@section('title','RESET | Recover your Access')
@section('meta_description', 'Reset Page of DeliveringParcel')

@section('content')
<div class="login-box">
  <div class="login-logo">
    <a href="{{route('/')}}"><img src="{{asset('images/deliveringlogo.png')}}" alt="deliveringparcellogo" width="40px" height="28px"> <b>Delivering Parcel</b></a>
  </div>
  <div class="card">
    <div class="card-body login-card-body">
      <h1 class="login-box-msg">Reset your Password</h1>

      @if (session('status'))
      <div class="alert alert-success" role="alert">
        {{ session('status') }}
      </div>
      @endif
      @if (session('error'))
      <div class="alert alert-danger" role="alert">
        {{ session('error') }}
      </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="input-group mb-3">
          <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="email" autofocus>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
          @error('email')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
          @enderror
        </div>

        <div class="row">
          <div class="col-12">
            @include('partials.turnstile', ['page' => 'password_reset'])
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Send Password Reset Link</button>
          </div>
        </div>
      </form>

      <div class="mt-4">
        <div class="d-flex justify-content-center links font-weight-bold">
          <a href="{{ route('login') }}">Back to login</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
