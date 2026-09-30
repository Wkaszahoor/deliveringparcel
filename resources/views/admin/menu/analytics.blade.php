<li class="nav-item">
    <a href="{{ route('admin.analytics.revenue') }}" class="nav-link {{ request()->is('admin/analytics*') || request()->is('admin/dashbord2') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line"></i><p>Analytics</p>
    </a>
</li>
