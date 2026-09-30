{{-- MOBILE APP sidebar menu (auto-included by admin/layouts/app.blade.php).
    Part of the isolated app/Mobile module — routes live in routes/mobile_web.php. --}}
<li class="nav-item has-treeview {{ request()->is('admin/mobile-app*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/mobile-app*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-mobile-alt"></i>
        <p>Mobile App<i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('mobile.admin.dashboard') }}" class="nav-link {{ request()->routeIs('mobile.admin.dashboard') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i><p>Overview</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('mobile.admin.screens.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.screens*') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i><p>Screen Controls</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('mobile.admin.labels.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.labels*') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i><p>Section Labels</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('mobile.admin.ui.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.ui*') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i><p>UI Settings</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('mobile.admin.features.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.features*') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i><p>Feature Flags</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('mobile.admin.ui-controls.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.ui-controls*') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i><p>UI Controls</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('mobile.admin.notifications.index') }}" class="nav-link {{ request()->routeIs('mobile.admin.notifications*') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i><p>Notifications</p>
            </a>
        </li>
    </ul>
</li>
