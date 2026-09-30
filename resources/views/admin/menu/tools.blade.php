<li class="nav-item">
    <a href="{{ route('admin.tools.search') }}" class="nav-link {{ request()->is('admin/tools*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-toolbox"></i><p>Tools &amp; PDF</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->is('admin/testimonials*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-quote-right"></i><p>Testimonials</p>
    </a>
</li>
