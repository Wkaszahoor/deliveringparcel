{{-- Shared CLIENT sidebar partial — used by layouts/portal.blade.php AND layouts/client_dashbord_master.blade.php
     so the client sees the same sidebar on every page (incl. legacy order pages /orders/{id}).
     Shopper + Shipper item order comes from Settings keys shopper_bar_order / shipper_bar_order
     (drag-drop in Admin → Settings → Menu Order). --}}
<a href="{{ url('/') }}" class="brand-link text-decoration-none">
    <img src="{{ asset('images/deliveringlogo.png') }}" alt="DP" class="brand-image" style="opacity:.8">
    <span class="brand-text font-weight-light">DeliveringParcel</span>
</a>
<?php
$dpSavedOrder = function (string $key): array {
    try {
        $row = \Illuminate\Support\Facades\DB::table('settings')->where('key', $key)->value('value');
        return $row ? array_values(array_filter(explode(',', $row))) : [];
    } catch (\Throwable $e) {
        return [];
    }
};
$dpApply = function (array $order, array $keys): array {
    $order = array_values(array_unique(array_merge(
        array_intersect($order, $keys),
        array_diff($keys, $order)
    )));
    return $order;
};

// ---- Shopper section ----
$dpShopperItems = [
    'orders' => [
        'url'    => url('/dashboard'),
        'active' => request()->is('dashboard') || request()->is('orders/*'),
        'icon'   => 'fas fa-box',
        'label'  => 'My Orders',
    ],
    'notifications' => [
        'url'    => route('client.inbox.notifications'),
        'active' => request()->is('client/inbox/notifications') || request()->is('notifications'),
        'icon'   => 'far fa-bell',
        'label'  => 'Notifications',
        'badge'  => ($unreadTaskCount ?? 0) > 0 ? ($unreadTaskCount > 10 ? '10+' : $unreadTaskCount) : null,
        'badge_class' => 'badge-warning',
    ],
    'messages' => [
        'url'    => route('client.inbox.messages'),
        'active' => request()->is('client/inbox/messages') || request()->is('messages'),
        'icon'   => 'far fa-comments',
        'label'  => 'Messages',
        'badge'  => ($unreadChatCount ?? 0) > 0 ? ($unreadChatCount > 10 ? '10+' : $unreadChatCount) : null,
        'badge_class' => 'badge-danger',
    ],
    'password' => [
        'url'    => route('password'),
        'active' => request()->is('password'),
        'icon'   => 'fas fa-key',
        'label'  => 'Change Password',
    ],
];
$dpShowBecomeShipper = true;

// ---- Shipper section ----
$portalShipper = auth()->check()
    ? \App\Models\ShipperProfile::where('user_id', auth()->id())->first()
    : null;
?>
<div class="sidebar">
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
            <li class="nav-header text-uppercase text-xs">Shopper</li>
<?php
foreach ($dpApply($dpSavedOrder('shopper_bar_order'), array_keys($dpShopperItems)) as $dpKey) {
    $dpIt = $dpShopperItems[$dpKey]; ?>
            <li class="nav-item">
                <a href="{{ $dpIt['url'] }}" class="nav-link {{ $dpIt['active'] ? 'active' : '' }}">
                    <i class="nav-icon {{ $dpIt['icon'] }}"></i><p>{{ $dpIt['label'] }} @if(!empty($dpIt['badge']))<span class="badge {{ $dpIt['badge_class'] }} right">{{ $dpIt['badge'] }}</span>@endif</p>
                </a>
            </li>
<?php } ?>
@if ($dpShowBecomeShipper && !$portalShipper)
            <li class="nav-item">
                <a href="{{ route('shipper.register.form') }}" class="nav-link {{ request()->is('become-a-shipper*') || request()->is('shipper-program') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-rocket"></i><p>Become a Shipper</p>
                </a>
            </li>
@endif
@if ($portalShipper)
<?php
$dpShipperItems = [
    'dashboard' => [
        'url'    => url('/shipper/dashboard'),
        'active' => request()->is('shipper/dashboard'),
        'icon'   => 'fas fa-tachometer-alt',
        'label'  => 'Overview',
    ],
    'requests' => [
        'url'    => url('/shipper/requests'),
        'active' => request()->is('shipper/requests*') && !request()->is('shipper/my-quotes'),
        'icon'   => 'fas fa-store',
        'label'  => 'Marketplace',
    ],
    'my-quotes' => [
        'url'    => url('/shipper/my-quotes'),
        'active' => request()->is('shipper/my-quotes'),
        'icon'   => 'fas fa-file-invoice-dollar',
        'label'  => 'My Quotes',
    ],
    'assignments' => [
        'url'    => url('/shipper/assignments'),
        'active' => request()->is('shipper/assignments*'),
        'icon'   => 'fas fa-tasks',
        'label'  => 'Assignments',
    ],
    'wallet' => [
        'url'    => url('/shipper/wallet'),
        'active' => request()->is('shipper/wallet'),
        'icon'   => 'fas fa-wallet',
        'label'  => 'Wallet',
    ],
    'countries' => [
        'url'    => url('/shipper/countries'),
        'active' => request()->is('shipper/countries'),
        'icon'   => 'fas fa-globe',
        'label'  => 'Service Countries',
    ],
];
?>
            <li class="nav-header text-uppercase text-xs">Shipper</li>
<?php
foreach ($dpApply($dpSavedOrder('shipper_bar_order'), array_keys($dpShipperItems)) as $dpKey) {
    $dpIt = $dpShipperItems[$dpKey]; ?>
            <li class="nav-item">
                <a href="{{ $dpIt['url'] }}" class="nav-link {{ $dpIt['active'] ? 'active' : '' }}">
                    <i class="nav-icon {{ $dpIt['icon'] }}"></i><p>{{ $dpIt['label'] }}</p>
                </a>
            </li>
<?php } ?>
@endif
        </ul>
    </nav>
</div>
