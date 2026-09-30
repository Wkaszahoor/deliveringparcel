<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in')</title>
    <meta name="description" content="@yield('meta_description')">
    <link rel="icon" type="image/png" href="{{ url('images/dp.svg') }}">

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ url('dashbord/plugins/fontawesome6-free/css/all.min.css') }}">
    @stack('admin_styles')
</head>
<body class="bg-[#131314] text-slate-800">
@yield('content')
@stack('admin_scripts')
</body>
</html>
