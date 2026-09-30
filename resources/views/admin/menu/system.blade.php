<li class="nav-item">
    <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-bell"></i><p>Notifications</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.audit.index') }}" class="nav-link {{ request()->is('admin/audit*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-history"></i><p>Audit Log</p>
    </a>
</li>
