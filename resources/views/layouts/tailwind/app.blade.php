<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | Deliveringparcel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Third-party plugin assets stay as raw <link> tags, exactly like the AdminLTE
         layouts — only their bundled CSS gets overridden by vendor-overrides.css above. --}}
    <link rel="stylesheet" href="{{ url('dashbord/plugins/fontawesome6-free/css/all.min.css') }}">
    @stack('admin_styles')
</head>
<body class="bg-slate-50 text-slate-800">
@php
    $activeSectionKey = \App\Support\AdminNav::activeSectionKey(request()->path());
    $activeSection = \App\Support\AdminNav::activeSection(request()->path());
@endphp

@include('layouts.tailwind.partials.topnav')

<div class="flex min-h-[calc(100vh-3.5rem)]">
    @include('layouts.tailwind.partials.subsidebar')

    <main class="min-w-0 flex-1 p-4 md:p-6">
        <div class="mb-4">
            <h1 class="text-xl font-semibold text-slate-900">@yield('page_title')</h1>
            @hasSection('page_subtitle')
                <p class="mt-0.5 text-sm text-slate-500">@yield('page_subtitle')</p>
            @endif
        </div>

        @include('layouts.tailwind.partials.flash')

        @yield('content')
    </main>
</div>

<script src="{{ url('dashbord/plugins/jquery/jquery-3.6.0.min.js') }}"></script>
@stack('admin_scripts')
</body>
</html>
