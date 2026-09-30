<li class="nav-item">
    <a href="{{ route('admin.quotes.index') }}" class="nav-link {{ request()->is('admin/quotes*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-alt"></i><p>Quotes &amp; Offers</p>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('admin.returns.index') }}" class="nav-link {{ request()->is('admin/returns*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-undo"></i><p>Returns &amp; Claims</p>
    </a>
</li>
