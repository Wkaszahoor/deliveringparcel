{{-- PAYMENTS sidebar group (auto-included by admin/layouts/app.blade.php) --}}
<li class="nav-item has-treeview {{ request()->is('admin/payments*') || request()->is('admin/wallets*') || request()->is('admin/payoneer*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/payments*') || request()->is('admin/wallets*') || request()->is('admin/payoneer*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-credit-card"></i>
        <p>Payments <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->is('admin/payments') || request()->is('admin/payments/ledger*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-money-check-alt"></i><p>Payments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.payments.config.index') }}" class="nav-link {{ request()->is('admin/payments/config*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-sliders-h"></i><p>Methods &amp; Service Rules</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.wallets.index') }}" class="nav-link {{ request()->is('admin/wallets*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-wallet"></i><p>Customer Wallets</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.payments.webhooks') }}" class="nav-link {{ request()->is('admin/payments/webhooks*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-plug"></i><p>Stripe Webhooks</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.payoneer.index') }}" class="nav-link {{ request()->is('admin/payoneer*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-hand-holding-usd"></i><p>Payoneer Payments</p>
            </a>
        </li>
    </ul>
</li>
