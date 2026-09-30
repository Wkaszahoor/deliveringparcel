@extends('home2.layouts.app')

@section('title', 'Choose a New Password | DeliveringParcel')
@section('meta_description', 'Choose a new password for your DeliveringParcel account.')

@section('content')
<section class="h2-section">
    <div class="h2-container" style="max-width:560px">
        <div style="background:#fff;border:1px solid #e6e9ef;border-radius:14px;padding:2.25rem 2rem;box-shadow:0 10px 30px rgba(15,23,42,.06)">
            <h1 style="font-size:1.6rem;margin:0 0 .35rem">Choose a new password</h1>
            <p class="muted" style="margin:0 0 1.25rem">Confirm your email and set the new password for your account.</p>

            @if (session('error'))
                <div class="h2-alert h2-alert-error">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="h2-alert h2-alert-error">
                    <ul class="mb-0">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <label for="email" style="font-weight:600;display:block;margin-bottom:.35rem">Email address</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                       name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus
                       placeholder="you@example.com"
                       style="width:100%;padding:.65rem .8rem;border:1px solid #cbd3e1;border-radius:8px;margin-bottom:1rem">

                <label for="password" style="font-weight:600;display:block;margin-bottom:.35rem">New password</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                       name="password" required autocomplete="new-password" placeholder="••••••••"
                       style="width:100%;padding:.65rem .8rem;border:1px solid #cbd3e1;border-radius:8px;margin-bottom:1rem">

                <label for="password-confirm" style="font-weight:600;display:block;margin-bottom:.35rem">Confirm new password</label>
                <input id="password-confirm" type="password" class="form-control"
                       name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"
                       style="width:100%;padding:.65rem .8rem;border:1px solid #cbd3e1;border-radius:8px;margin-bottom:1.25rem">

                @include('partials.turnstile', ['page' => 'password_reset'])

                <button type="submit" class="h2-btn h2-btn-primary" style="width:100%;justify-content:center">Reset password</button>
            </form>

            <p class="muted" style="text-align:center;margin:1.25rem 0 0">
                <a href="{{ route('password.request') }}">Request a new reset link</a>
            </p>
        </div>
    </div>
</section>
@endsection
