{{-- GEO / RATES / UNITS sidebar group (auto-included by admin/layouts/app.blade.php) --}}
<li class="nav-item has-treeview {{ request()->is('admin/rates*') || request()->is('admin/countries*') || request()->is('admin/geo*') || request()->is('admin/weight-units*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/rates*') || request()->is('admin/countries*') || request()->is('admin/geo*') || request()->is('admin/weight-units*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-globe-americas"></i>
        <p>Geo, Rates &amp; Units <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.countries.index') }}" class="nav-link {{ request()->is('admin/countries*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-flag"></i><p>Countries Matrix</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.geo.states.index') }}" class="nav-link {{ request()->is('admin/geo*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-map-marked-alt"></i><p>States &amp; Cities</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.rates.calculator') }}" class="nav-link {{ request()->is('admin/rates*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-calculator"></i><p>Rate Engine</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.weight-units.index') }}" class="nav-link {{ request()->is('admin/weight-units*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-balance-scale"></i><p>Weight Units</p>
            </a>
        </li>
    </ul>
</li>
