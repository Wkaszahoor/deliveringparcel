{{-- SETTINGS sidebar group (auto-included by admin/layouts/app.blade.php) --}}
<li class="nav-item has-treeview {{ request()->is('admin/settings*') || request()->is('admin/sitemap-settings*') || request()->is('admin/route-manager*') || request()->is('admin/menu-order*') || request()->is('admin/audit*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/settings*') || request()->is('admin/sitemap-settings*') || request()->is('admin/route-manager*') || request()->is('admin/menu-order*') || request()->is('admin/audit*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cogs"></i>
        <p>Settings <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-sliders-h"></i><p>General Settings</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.sitemap.settings') }}" class="nav-link {{ request()->is('admin/sitemap-settings*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-sitemap"></i><p>Sitemap Settings</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.route-manager.index') }}" class="nav-link {{ request()->is('admin/route-manager*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-route"></i><p>Route Manager</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.menu-order.index') }}" class="nav-link {{ request()->is('admin/menu-order*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-sort-amount-down"></i><p>Menu Order</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.audit.index') }}" class="nav-link {{ request()->is('admin/audit*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-history"></i><p>Audit Log</p>
            </a>
        </li>
    </ul>
</li>
