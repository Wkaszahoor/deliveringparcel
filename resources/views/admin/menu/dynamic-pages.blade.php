{{-- DYNAMIC PAGES manager sidebar entry (auto-included by admin/layouts/app.blade.php).
     Route::has() guard: the menu glob loads this partial on EVERY admin page even
     before routes/admin/dynamic-pages.php is required in web.php — without the
     guard route() would throw and 500 the whole admin panel. --}}
@if (\Illuminate\Support\Facades\Route::has('admin.dynamic-pages.index'))
<li class="nav-item {{ request()->is('admin/dynamic-pages*') ? 'menu-open' : '' }}">
    <a href="{{ route('admin.dynamic-pages.index') }}" class="nav-link {{ request()->is('admin/dynamic-pages*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-alt"></i><p>Dynamic Pages</p>
    </a>
</li>
@endif
