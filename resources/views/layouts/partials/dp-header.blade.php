{{-- ============================================================
     THE one header bar for every logged-in surface:
     admin panel, client/shipper portal, legacy order pages,
     shipper workspace. Same markup, same look, everywhere.

     Optional vars from the host layout:
       $dpHeaderLinks        array: ['label','url','active'] center links (e.g. shipper nav)
       $dpProfileUrl         profile link in the user dropdown
       $dpFeedChatView / $dpFeedTaskView   inbox feed partial names (legacy masters)
       $dpHideToggle         hide the hamburger (layouts without a sidebar)
       $dpBrandUrl           brand target (default /)
============================================================ --}}
<nav class="main-header navbar navbar-expand navbar-white navbar-light dp-main-header">
  <style>
    .dp-main-header { border-bottom: 2px solid transparent;
        border-image: linear-gradient(90deg, #0b5fff, #16a34a) 1; }
    .dp-main-header .dp-header-brand { display: flex; align-items: center; gap: .45rem;
        text-decoration: none; color: #0d2a52; font-weight: 800; font-size: .98rem;
        letter-spacing: .01em; margin-left: .35rem; white-space: nowrap; }
    .dp-main-header .dp-header-brand img { height: 26px; width: auto; }
    .dp-main-header .dp-header-links { display: flex; flex-wrap: nowrap; overflow-x: auto; }
    .dp-main-header .dp-header-links .nav-link { white-space: nowrap; font-weight: 600;
        color: #37474f; border-bottom: 3px solid transparent; padding: .6rem .7rem; }
    .dp-main-header .dp-header-links .nav-link.on,
    .dp-main-header .dp-header-links .nav-link.active { color: #0b5fff; border-bottom-color: #0b5fff; }
    @media (max-width: 575.98px) {
        .dp-main-header .dp-header-brand span { display: none; }
        .dp-main-header .dp-header-links .nav-link { padding: .6rem .45rem; font-size: .85rem; }
    }
  </style>
  <ul class="navbar-nav">
    @if (empty($dpHideToggle))
        <li class="nav-item">
            <a class="nav-link {{ $dpHeaderToggleClass ?? 'dp-sidebar-toggle' }}" href="#" role="button" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    @endif
    <a class="dp-header-brand" href="{{ $dpBrandUrl ?? url('/') }}">
        <img src="{{ asset('images/deliveringlogo.png') }}" alt="DeliveringParcel">
        <span>DeliveringParcel</span>
    </a>
    @if (!empty($dpHeaderLinks))
        <li class="nav-item dp-header-links">
            @foreach ($dpHeaderLinks as $dpL)
                <a class="nav-link {{ $dpL['active'] ?? '' }}" href="{{ $dpL['url'] }}">{{ $dpL['label'] }}</a>
            @endforeach
        </li>
    @endif
  </ul>

  <ul class="navbar-nav ml-auto">
    <?php $dpUser = auth()->user(); ?>
    @if ($dpUser)
        @if (isset($dpFeedChatView, $dpFeedTaskView))
            <?php
            // Capped badge counts — never load all notifications just to badge them.
            $unreadChatCount = $dpUser->unreadNotifications()->where('type', 'App\Notifications\Chatnotification')->count();
            $unreadTaskCount = $dpUser->unreadNotifications()->where('type', 'App\Notifications\TaskNotification')->count();
            $chatFeed = $dpUser->notifications()->where('type', 'App\Notifications\Chatnotification')
                ->orderByRaw('read_at IS NULL DESC, created_at DESC')->take(10)->get();
            $taskFeed = $dpUser->notifications()->where('type', 'App\Notifications\TaskNotification')
                ->orderByRaw('read_at IS NULL DESC, created_at DESC')->take(10)->get();
            ?>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-label="Messages">
                    <i class="far fa-comments"></i>
                    <span class="badge badge-danger navbar-badge">{{ $unreadChatCount > 10 ? '10+' : $unreadChatCount }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ $unreadChatCount }} Unread Messages</span>
                    <div class="dropdown-divider"></div>
                    <div class="dp-feed" data-type="chat" style="max-height:300px;overflow-y:auto;">
                        @include($dpFeedChatView, ['items' => $chatFeed, 'type' => 'chat'])
                    </div>
                    @isset($dpInboxChatUrl)<a href="{{ $dpInboxChatUrl }}" class="dropdown-item dropdown-footer">See All Messages</a>@endisset
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-label="Notifications">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge">{{ $unreadTaskCount > 10 ? '10+' : $unreadTaskCount }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ $unreadTaskCount }} Unread Notifications</span>
                    <div class="dropdown-divider"></div>
                    <div class="dp-feed" data-type="task" style="max-height:300px;overflow-y:auto;">
                        @include($dpFeedTaskView, ['items' => $taskFeed, 'type' => 'task'])
                    </div>
                    @isset($dpInboxTaskUrl)<a href="{{ $dpInboxTaskUrl }}" class="dropdown-item dropdown-footer">See All Notifications</a>@endisset
                </div>
            </li>
        @endif
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-label="Account menu">
                <i class="far fa-user"></i>
                <span class="d-none d-sm-inline ml-1">{{ $dpUser->name }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $dpUser->name }}</span>
                <div class="dropdown-divider"></div>
                @if (!empty($dpProfileUrl))
                    <a class="dropdown-item" href="{{ $dpProfileUrl }}"><i class="fas fa-id-badge mr-2"></i>Profile</a>
                @endif
                <a class="dropdown-item" href="{{ url('/') }}"><i class="fas fa-globe mr-2"></i>Website</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('dp-header-logout').submit();">
                    <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Logout') }}
                </a>
                <form id="dp-header-logout" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </li>
    @else
        @foreach (($dpGuestLinks ?? []) as $dpG)
            <li class="nav-item"><a class="nav-link {{ $dpG['class'] ?? '' }}" href="{{ $dpG['url'] }}">{{ $dpG['label'] }}</a></li>
        @endforeach
    @endif
  </ul>
</nav>
