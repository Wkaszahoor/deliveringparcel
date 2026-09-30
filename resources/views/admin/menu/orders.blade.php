{{-- ORDERS & QUOTES sidebar group (auto-included by admin/layouts/app.blade.php) --}}
<li class="nav-item has-treeview {{ request()->is('admin/quotes*') || request()->is('admin/returns*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/quotes*') || request()->is('admin/returns*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-clipboard-list"></i>
        <p>Orders &amp; Quotes <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.quotes.index') }}" class="nav-link {{ request()->is('admin/quotes*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice"></i><p>Quotes &amp; Offers</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.returns.index') }}" class="nav-link {{ request()->is('admin/returns*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-undo-alt"></i><p>Returns &amp; Claims</p>
            </a>
        </li>
    </ul>
</li>
