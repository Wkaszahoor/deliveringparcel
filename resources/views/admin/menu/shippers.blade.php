<li class="nav-item has-treeview {{ request()->is('admin/shippers*') || request()->is('admin/shipping-requests*') || request()->is('admin/shipper-assignments*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/shippers*') || request()->is('admin/shipping-requests*') || request()->is('admin/shipper-assignments*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-shipping-fast"></i>
        <p>Shippers <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.shippers.overview') }}" class="nav-link {{ request()->is('admin/shippers/overview') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i><p>Overview</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.shippers.index') }}" class="nav-link {{ request()->routeIs('admin.shippers.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i><p>All Shippers</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.shippers.pending-kyc') }}" class="nav-link {{ request()->routeIs('admin.shippers.pending-kyc') ? 'active' : '' }}">
                <i class="nav-icon fas fa-id-card"></i><p>Pending KYC</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.shippers.country-requests') }}" class="nav-link {{ request()->routeIs('admin.shippers.country-requests*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-globe"></i><p>Country Requests</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.shipping-requests.index') }}" class="nav-link {{ request()->is('admin/shipping-requests*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-clipboard-list"></i><p>Shipping Requests</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.shipper-assignments.index') }}" class="nav-link {{ request()->is('admin/shipper-assignments*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tasks"></i><p>Assignments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.shippers.payout-requests') }}" class="nav-link {{ request()->routeIs('admin.shippers.payout-requests') ? 'active' : '' }}">
                <i class="nav-icon fas fa-money-bill-wave"></i><p>Payout Requests</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.shippers.performance') }}" class="nav-link {{ request()->routeIs('admin.shippers.performance') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-bar"></i><p>Performance</p>
            </a>
        </li>
    </ul>
</li>
