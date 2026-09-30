{{-- Route Manager: header navigation partial (standalone — not yet wired into any layout). --}}
<nav class="main-nav">
    {{-- Dynamic links from Route Manager ($routeLinks auto-injected by RouteManagerServiceProvider) --}}
    @if(!empty($routeLinks))

        @if(!empty($routeLinks['blog']['show_in_header']))
            <a href="{{ $routeLinks['blog']['url'] }}"
               class="nav-link {{ $routeLinks['blog']['version'] === 'new' ? 'nav-new' : 'nav-legacy' }}">
                Blog
                @if(config('app.debug') && $routeLinks['blog']['version'] === 'new')
                    <span class="badge badge-success badge-sm">NEW</span>
                @endif
            </a>
        @endif

        @if(!empty($routeLinks['services']['show_in_header']))
            <a href="{{ $routeLinks['services']['url'] }}" class="nav-link">Services</a>
        @endif

        @if(!empty($routeLinks['get_quote']['show_in_header']))
            <a href="{{ $routeLinks['get_quote']['url'] }}" class="btn btn-primary nav-cta">Get a Quote</a>
        @endif

        @if(!empty($routeLinks['login']['show_in_header']))
            @guest
                <a href="{{ $routeLinks['login']['url'] }}" class="nav-link">Login</a>
            @endguest
        @endif

        @if(!empty($routeLinks['register']['show_in_header']))
            @guest
                <a href="{{ $routeLinks['register']['url'] }}" class="btn btn-outline-primary nav-link">Register</a>
            @endguest
        @endif

        @auth
            {{-- Client panel link — always uses the active version from Route Manager --}}
            <a href="{{ $routeLinks['client_panel']['url'] ?? '/dashboard' }}" class="nav-link">My Orders</a>
        @endauth

    @else
        {{-- FALLBACK: hardcoded legacy — shown when table not yet migrated / composer empty --}}
        <a href="/blog" class="nav-link">Blog</a>
        <a href="/services" class="nav-link">Services</a>
        <a href="/get-quote" class="btn btn-primary">Get a Quote</a>
        @guest
            <a href="/login" class="nav-link">Login</a>
            <a href="/register" class="btn btn-outline-primary">Register</a>
        @endguest
        @auth
            <a href="/dashboard" class="nav-link">My Orders</a>
        @endauth
    @endif
</nav>
