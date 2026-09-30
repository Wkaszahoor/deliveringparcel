@extends('layouts.tailwind.auth')

@section('title', 'Register to forward parcel | International Package Forwarding & Reship')
@section('meta_description', 'Get register to the Best mail forwarding service for nomads and expats.')

@section('content')
<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#131314] px-4 py-12">
    <img src="{{ asset('frontend/assets/img/hero-img.svg') }}" alt=""
         class="pointer-events-none absolute -left-24 top-1/2 hidden w-[34rem] -translate-y-1/2 opacity-10 md:block lg:w-[42rem]">
    <img src="{{ asset('frontend/assets/img/hero-img.svg') }}" alt=""
         class="pointer-events-none absolute -right-32 -bottom-24 hidden w-[34rem] rotate-12 opacity-10 md:block lg:w-[42rem]">

    <div class="relative w-full max-w-md rounded-2xl border border-slate-700/60 bg-[#1e1f20] px-8 py-10 shadow-2xl sm:px-10">
        <a href="{{ route('/') }}" class="inline-flex">
            <img src="{{ asset('images/deliveringlogo.png') }}" alt="DeliveringParcel" class="h-10 w-auto">
        </a>

        <h1 class="mt-6 text-3xl font-normal text-white">Create account</h1>
        <p class="mt-2 text-sm text-slate-400">to start shopping and shipping with DeliveringParcel</p>

        <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
            @csrf

            <div>
                <label for="name" class="sr-only">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       placeholder="Full name"
                       class="block w-full rounded-md border bg-transparent px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 {{ $errors->has('name') ? 'border-red-500 focus:border-red-400 focus:ring-red-400' : 'border-slate-600 focus:border-brand focus:ring-brand' }}">
                @error('name')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="sr-only">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                       placeholder="Email"
                       class="block w-full rounded-md border bg-transparent px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 {{ $errors->has('email') ? 'border-red-500 focus:border-red-400 focus:ring-red-400' : 'border-slate-600 focus:border-brand focus:ring-brand' }}">
                @error('email')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="number" class="sr-only">Mobile number</label>
                <input id="number" type="text" name="number" value="{{ old('number') }}" required autocomplete="tel"
                       placeholder="Mobile number"
                       class="block w-full rounded-md border bg-transparent px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 {{ $errors->has('number') ? 'border-red-500 focus:border-red-400 focus:ring-red-400' : 'border-slate-600 focus:border-brand focus:ring-brand' }}">
                @error('number')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="sr-only">Password</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           placeholder="Password"
                           class="block w-full rounded-md border bg-transparent px-4 py-3 pr-10 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 {{ $errors->has('password') ? 'border-red-500 focus:border-red-400 focus:ring-red-400' : 'border-slate-600 focus:border-brand focus:ring-brand' }}">
                    <button type="button" onclick="dpTogglePassword('password', 'dp-password-eye')" aria-label="Show password"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300">
                        <i id="dp-password-eye" class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password-confirm" class="sr-only">Confirm password</label>
                <div class="relative">
                    <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                           placeholder="Confirm password"
                           class="block w-full rounded-md border border-slate-600 bg-transparent px-4 py-3 pr-10 text-sm text-white placeholder-slate-500 focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                    <button type="button" onclick="dpTogglePassword('password-confirm', 'dp-password-confirm-eye')" aria-label="Show password"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300">
                        <i id="dp-password-confirm-eye" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            @error('captcha')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
            @include('partials.turnstile', ['page' => 'register'])

            <label class="flex items-start gap-2 text-xs text-slate-400">
                <input type="checkbox" id="agreeTerms" name="terms" value="agree" required
                       class="mt-0.5 rounded border-slate-600 bg-transparent text-brand focus:ring-brand">
                <span>By clicking the tick mark, I am agreeing to the rules, regulations and business terms of DeliveringParcel.</span>
            </label>

            <button type="submit"
                    class="w-full rounded-full bg-brand px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-[#1e1f20]">
                Create account
            </button>
        </form>

        @if (config('services.google.client_id') || config('services.facebook.client_id'))
            <div class="my-5 flex items-center gap-3 text-xs text-slate-500">
                <span class="h-px flex-1 bg-slate-700"></span>
                OR
                <span class="h-px flex-1 bg-slate-700"></span>
            </div>

            <div class="space-y-3">
                @if (config('services.google.client_id'))
                    <a href="{{ route('auth.google.redirect') }}"
                       class="flex w-full items-center justify-center gap-2 rounded-md border border-slate-600 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-brand">
                        <svg class="h-4 w-4" viewBox="0 0 48 48" aria-hidden="true">
                            <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.6-6 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.1 8 3l5.7-5.7C34.6 6 29.6 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.2-.1-2.4-.4-3.5z"/>
                            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.6 19 13 24 13c3.1 0 5.9 1.1 8 3l5.7-5.7C34.6 6 29.6 4 24 4c-7.4 0-13.8 4.1-17.1 10.7z"/>
                            <path fill="#4CAF50" d="M24 44c5.5 0 10.4-1.9 14.3-5.1l-6.6-5.4C29.6 35.4 26.9 36 24 36c-5.3 0-9.7-3.4-11.3-8.1l-6.6 5.1C9.9 39.7 16.4 44 24 44z"/>
                            <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.2 4.2-4.1 5.5l6.6 5.4C41.6 35.6 44 30.2 44 24c0-1.2-.1-2.4-.4-3.5z"/>
                        </svg>
                        Sign up with Google
                    </a>
                @endif

                @if (config('services.facebook.client_id'))
                    <a href="{{ route('auth.facebook.redirect') }}"
                       class="flex w-full items-center justify-center gap-2 rounded-md border border-slate-600 bg-[#1877F2] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#166FE5] focus:outline-none focus:ring-2 focus:ring-brand">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.99 3.66 9.13 8.44 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.45h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99C18.34 21.13 22 16.99 22 12z"/>
                        </svg>
                        Sign up with Facebook
                    </a>
                @endif
            </div>
        @endif

        @if (Route::has('login'))
            <p class="mt-6 text-center text-sm text-slate-500">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-brand-light hover:underline">Sign in</a>
            </p>
        @endif
    </div>
</div>

<script>
    function dpTogglePassword(fieldId, eyeId) {
        var field = document.getElementById(fieldId);
        var eye = document.getElementById(eyeId);
        var isHidden = field.type === 'password';
        field.type = isHidden ? 'text' : 'password';
        eye.classList.toggle('fa-eye', !isHidden);
        eye.classList.toggle('fa-eye-slash', isHidden);
    }
</script>
@endsection
