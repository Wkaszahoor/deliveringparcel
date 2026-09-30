{{-- SITE CONTENT sidebar group (auto-included by admin/layouts/app.blade.php) --}}
<li class="nav-item has-treeview {{ request()->is('admin/hero-slides*') || request()->is('admin/callouts*') || request()->is('admin/dynamic-pages*') || request()->is('admin/reviews*') || request()->is('admin/testimonials*') || request()->is('admin/tools*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/hero-slides*') || request()->is('admin/callouts*') || request()->is('admin/dynamic-pages*') || request()->is('admin/reviews*') || request()->is('admin/testimonials*') || request()->is('admin/tools*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-photo-video"></i>
        <p>Site Content <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.hero-slides.index') }}" class="nav-link {{ request()->is('admin/hero-slides*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-images"></i><p>Hero Slider</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.callouts.index') }}" class="nav-link {{ request()->is('admin/callouts*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-bullhorn"></i><p>Order Callouts</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.dynamic-pages.index') }}" class="nav-link {{ request()->is('admin/dynamic-pages*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-code"></i><p>Dynamic Pages</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->is('admin/reviews*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-star"></i><p>Reviews</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->is('admin/testimonials*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-quote-right"></i><p>Testimonials</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.image-optimizer.index') }}" class="nav-link {{ request()->is('admin/image-optimizer*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-compress-arrows-alt"></i><p>Image Optimizer</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.tools.search') }}" class="nav-link {{ request()->is('admin/tools*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-toolbox"></i><p>Tools &amp; PDF</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.seo-shield.index') }}" class="nav-link {{ request()->is('admin/seo-shield*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-shield-alt"></i><p>SEO Shield</p>
            </a>
        </li>
    </ul>
</li>
