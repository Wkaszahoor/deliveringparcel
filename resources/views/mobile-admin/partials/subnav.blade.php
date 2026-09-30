{{-- Mobile App Management sub-nav (shared across all module pages) --}}
<div class="row mb-3">
    <div class="col-12">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="{{ route('mobile.admin.dashboard') }}" class="nav-link {{ request()->routeIs('mobile.admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt mr-1"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('mobile.admin.screens.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.screens*') ? 'active' : '' }}">
                    <i class="fas fa-mobile-screen mr-1"></i> Screens
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('mobile.admin.labels.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.labels*') ? 'active' : '' }}">
                    <i class="fas fa-tags mr-1"></i> Labels
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('mobile.admin.ui.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.ui*') ? 'active' : '' }}">
                    <i class="fas fa-palette mr-1"></i> UI
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('mobile.admin.features.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.features*') ? 'active' : '' }}">
                    <i class="fas fa-flag mr-1"></i> Features
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('mobile.admin.notifications.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.notifications*') ? 'active' : '' }}">
                    <i class="fas fa-bell mr-1"></i> Notifications
                </a>
            </li>
        </ul>
    </div>
</div>
