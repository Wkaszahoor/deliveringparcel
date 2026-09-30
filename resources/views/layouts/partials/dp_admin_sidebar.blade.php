{{-- Shared ADMIN sidebar partial — used by admin/layouts/app.blade.php AND layouts/admin_dashbord_master.blade.php
     so the admin sees the same sidebar on every page (incl. legacy order pages /order/{id}). --}}
<a href="{{ route('admin-dashbord') }}" class="brand-link text-decoration-none">
    <img src="{{ asset('images/deliveringlogo.png') }}" alt="DP" class="brand-image" style="opacity:.8">
    <span class="brand-text font-weight-light">DeliveringParcel</span>
</a>
<div class="sidebar">
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
            <li class="nav-item">
                <a href="{{ route('admin-dashbord') }}" class="nav-link {{ request()->is('admin-dashbord') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                </a>
            </li>
<?php
$dpMenuOrder = [];
try {
    $dpMenuOrderRow = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'admin_menu_order')->value('value');
    $dpMenuOrder = $dpMenuOrderRow ? array_filter(explode(',', $dpMenuOrderRow)) : [];
} catch (\Throwable $dpMenuE) {}
$dpMenuFiles = collect(\Illuminate\Support\Facades\File::glob(resource_path('views/admin/menu/*.blade.php')))
    ->map(function ($f) { return basename($f, '.blade.php'); });
$dpMenuSorted = collect($dpMenuOrder)->filter(function ($k) use ($dpMenuFiles) { return $dpMenuFiles->contains($k); })
    ->merge($dpMenuFiles->reject(function ($k) use ($dpMenuOrder) { return in_array($k, $dpMenuOrder); })->sort()->values());
?>
            @foreach ($dpMenuSorted as $dpMenuKey)
                @include('admin.menu.' . $dpMenuKey)
            @endforeach
        </ul>
    </nav>
</div>
