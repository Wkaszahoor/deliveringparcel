<li class="nav-item">
    <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->is('admin/reviews*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-comments"></i><p>Reviews</p>
    </a>
</li>
