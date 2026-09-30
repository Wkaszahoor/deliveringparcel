<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My Account') | DeliveringParcel</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ url('dashbord/plugins/fontawesome6-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('dashbord/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashbord/css/dp-admin.css') }}?v=20260919e">
    @stack('portal_styles')
</head>
<body class="hold-transition dp-sidebar-mini layout-fixed layout-navbar-fixed">
@include('common.noscript')
@include('common.a11y')
<div class="wrapper">

    <div class="dp-preloader" id="dpPreloader"><div class="spinner-border text-primary" role="status"></div></div>

    {{-- ============ Navbar ============ --}}
    {{-- THE shared header bar (same on every page) --}}
    @include('layouts.partials.dp-header', [
        'dpProfileUrl'   => route('client_avatar'),
        'dpFeedChatView' => 'clients.inbox._feed',
        'dpFeedTaskView' => 'clients.inbox._feed',
        'dpInboxChatUrl' => route('client.inbox.messages'),
        'dpInboxTaskUrl' => route('client.inbox.notifications'),
    ])

    {{-- ============ Sidebar ============ --}}
    <aside class="main-sidebar dp-sidebar elevation-4">
        @include('layouts.partials.dp_client_sidebar')
    </aside>

    {{-- ============ Content ============ --}}
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="dp-page-title">@yield('page_title')</h1>
                <p class="dp-page-sub text-muted mb-0">@yield('page_subtitle')</p>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div id="dp-flash" data-flash='@json(["success" => session("success"), "error" => session("error"), "info" => session("info")])'></div>
                @if (($errors ?? collect())->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <ul class="mb-0">
                            @foreach (($errors ?? collect())->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer text-sm">
        <strong>DeliveringParcel</strong> &copy; {{ date('Y') }}
    </footer>
</div>

<script src="{{ url('dashbord/plugins/jquery/jquery-3.6.0.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ url('dashbord/js/adminlte.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}?v=20260919e"></script>
@stack('portal_scripts')
</body>
</html>
