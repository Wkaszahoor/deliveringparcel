# Tailwind Admin Foundation (Phase 0) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the Vite+Tailwind v4+Alpine.js foundation (build tooling, design tokens, shared top-nav+sub-sidebar layout shell, and a core component library), and prove every pattern works by migrating 4 representative admin pages off AdminLTE/Bootstrap.

**Architecture:** New Tailwind-based layout (`layouts.tailwind.app`) and component set are built **alongside** the existing AdminLTE layouts (`admin.layouts.app`, `layouts.admin_dashbord_master`, `layouts.client_dashbord_master`), which are left completely untouched. Only 4 specific pages switch to the new layout in this phase — every other page keeps working exactly as it does today. This is the foundation that later batch-migration plans (Phase 1+) will reuse without new design decisions.

**Tech Stack:** Vite, Tailwind CSS v4 (`@tailwindcss/vite`), Alpine.js, Laravel Blade components. jQuery + select2/summernote/DataTables/SweetAlert2/toastr are kept (only re-themed via CSS), since they're framework-agnostic plugins already in use.

---

## File Structure

**New files:**
- `vite.config.js` — replaces `webpack.mix.js`
- `resources/css/app.css` — Tailwind v4 entry point + brand `@theme` + imports `vendor-overrides.css`
- `resources/css/vendor-overrides.css` — Tailwind-matching skins for DataTables, select2, summernote
- `resources/js/app.js` — Alpine.js bootstrap + auto-init helpers for select2/summernote/DataTables so migrated pages don't repeat init boilerplate
- `app/Support/AdminNav.php` — single source of truth for the new top-nav sections + their sub-sidebar links (PHP array, testable)
- `resources/views/layouts/tailwind/app.blade.php` — new shared shell (top nav + slim sub-sidebar)
- `resources/views/layouts/tailwind/partials/topnav.blade.php`
- `resources/views/layouts/tailwind/partials/subsidebar.blade.php`
- `resources/views/layouts/tailwind/partials/flash.blade.php`
- `resources/views/components/admin/button.blade.php`
- `resources/views/components/admin/card.blade.php`
- `resources/views/components/admin/badge.blade.php`
- `resources/views/components/admin/alert.blade.php`
- `resources/views/components/admin/modal.blade.php`
- `resources/views/components/admin/input.blade.php`
- `resources/views/pagination/tailwind-admin.blade.php` — custom Laravel paginator view
- `tests/Feature/AdminNavTest.php`

**Modified files (the 4 POC pages only):**
- `resources/views/admin/home.blade.php`
- `resources/views/admin/address/index.blade.php`, `create.blade.php`, `edit.blade.php`
- `resources/views/admin/contacts/index.blade.php` *(retargeted during execution — see Task 10 note)*
- `resources/views/admin/freequote/index.blade.php`, `resources/views/admin/freequote/delete_modal.blade.php`
- `package.json`

**Deleted files:**
- `webpack.mix.js`
- `resources/sass/` (entire directory — unused scaffold leftover, confirmed nothing references it)
- `resources/js/bootstrap.js`, `resources/js/components/ExampleComponent.vue` (unused scaffold leftovers)

---

### Task 1: Swap Laravel Mix for Vite + Tailwind v4 + Alpine

**Files:**
- Modify: `package.json`
- Create: `vite.config.js`
- Delete: `webpack.mix.js`, `resources/sass/app.scss`, `resources/sass/_variables.scss`, `resources/js/bootstrap.js`, `resources/js/components/ExampleComponent.vue`

- [ ] **Step 1: Confirm nothing references Mix's output before removing it**

Run: `grep -rn "mix(" resources/views --include="*.blade.php"`
Expected: no output (already confirmed during planning — `resources/js/app.js` and `resources/sass/app.scss` are unused Laravel-scaffold leftovers, never wired into any layout via the `mix()` helper).

- [ ] **Step 2: Update `package.json`**

Replace the `devDependencies` block:

```json
{
    "private": true,
    "scripts": {
        "dev": "vite",
        "build": "vite build"
    },
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "alpinejs": "^3.14.0",
        "axios": "^0.21",
        "jquery": "^3.2",
        "laravel-vite-plugin": "^1.0",
        "lodash": "^4.17.19",
        "tailwindcss": "^4.0.0",
        "vite": "^5.0"
    }
}
```

- [ ] **Step 3: Delete the old Mix/Vue/Sass files**

```bash
rm webpack.mix.js
rm -rf resources/sass
rm resources/js/bootstrap.js
rm -rf resources/js/components
```

- [ ] **Step 4: Create `vite.config.js`**

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

- [ ] **Step 5: Install dependencies**

Run: `npm install`
Expected: installs cleanly, no `bootstrap`/`laravel-mix`/`vue` packages remain in `node_modules/.package-lock.json`.

- [ ] **Step 6: Commit**

```bash
git add package.json package-lock.json vite.config.js
git add -u
git commit -m "build: replace Laravel Mix with Vite + Tailwind v4 + Alpine"
```

(No git repo exists in this project yet — if `git status` reports "not a git repository", skip this commit step and every later one in this plan; just leave the working tree as-is.)

---

### Task 2: Tailwind entry CSS with brand theme

**Files:**
- Create: `resources/css/app.css`
- Create: `resources/css/vendor-overrides.css`

- [ ] **Step 1: Write `resources/css/app.css`**

```css
@import "tailwindcss";
@import "./vendor-overrides.css";

@theme {
    --color-brand: #0d6efd;
    --color-brand-dark: #0a58ca;
    --color-brand-light: #e7f1ff;
}

/* Slim contextual sub-sidebar active-link state */
.dp2-subnav-link[data-active="true"] {
    background-color: var(--color-brand-light);
    color: var(--color-brand-dark);
    font-weight: 600;
}
```

- [ ] **Step 2: Write `resources/css/vendor-overrides.css`**

Tailwind-matching skins for the third-party plugins used by the 4 POC pages (DataTables, select2, summernote). JS behavior of these plugins is unchanged — only their bundled Bootstrap 4 CSS classes are restyled to match the new look.

```css
/* ---- DataTables (loaded via dashbord/plugins/datatables*) ---- */
.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select {
    border: 1px solid #cbd5e1;
    border-radius: 0.375rem;
    padding: 0.25rem 0.5rem;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 0.375rem !important;
    margin: 0 2px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--color-brand) !important;
    border-color: var(--color-brand) !important;
    color: #fff !important;
}
table.dataTable thead th {
    border-bottom: 2px solid #e2e8f0 !important;
    font-weight: 600;
    color: #334155;
}

/* ---- select2 ---- */
.select2-container--default .select2-selection--single {
    border: 1px solid #cbd5e1 !important;
    border-radius: 0.375rem !important;
    height: 2.5rem !important;
    display: flex;
    align-items: center;
    padding-left: 0.5rem;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 2.5rem !important;
}
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: var(--color-brand) !important;
    box-shadow: 0 0 0 3px var(--color-brand-light);
}
.select2-dropdown {
    border-color: #cbd5e1 !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--color-brand) !important;
}

/* ---- summernote ---- */
.note-editor.note-frame {
    border: 1px solid #cbd5e1 !important;
    border-radius: 0.375rem !important;
}
.note-editor.note-frame .note-toolbar {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
.note-editor.note-frame.note-frame-active {
    border-color: var(--color-brand) !important;
    box-shadow: 0 0 0 3px var(--color-brand-light);
}
```

- [ ] **Step 3: Verify the build compiles**

Run: `npm run build`
Expected: exits 0, produces `public/build/manifest.json` and hashed CSS/JS assets under `public/build/assets/`.

- [ ] **Step 4: Commit**

```bash
git add resources/css/app.css resources/css/vendor-overrides.css
git commit -m "feat: add Tailwind v4 entry CSS with brand theme and vendor overrides"
```

---

### Task 3: Alpine.js entry + plugin auto-init helper

**Files:**
- Create: `resources/js/app.js`

- [ ] **Step 1: Write `resources/js/app.js`**

```js
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * Auto-init helper for jQuery plugins already loaded by the host layout
 * (select2, summernote, DataTables) — migrated pages add a data attribute
 * instead of repeating $(...).plugin() boilerplate in an inline <script>.
 *   <select data-dp2-select2>              -> select2({ width: '100%' })
 *   <textarea data-dp2-summernote>         -> summernote({ height: 280 })
 *   <table data-dp2-datatable>             -> DataTable({ responsive: true })
 */
function dp2InitPlugins() {
    if (window.jQuery) {
        const $ = window.jQuery;
        if ($.fn.select2) {
            $('[data-dp2-select2]').each(function () {
                $(this).select2({ width: '100%' });
            });
        }
        if ($.fn.summernote) {
            $('[data-dp2-summernote]').each(function () {
                $(this).summernote({
                    height: 280,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']],
                    ],
                });
            });
        }
        if ($.fn.DataTable) {
            $('[data-dp2-datatable]').each(function () {
                $(this).DataTable({ responsive: true, autoWidth: false });
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', dp2InitPlugins);
```

- [ ] **Step 2: Verify build still compiles**

Run: `npm run build`
Expected: exits 0.

- [ ] **Step 3: Commit**

```bash
git add resources/js/app.js
git commit -m "feat: add Alpine.js entry and jQuery-plugin auto-init helper"
```

---

### Task 4: `AdminNav` — top-nav + sub-sidebar data source (TDD)

This is the one piece of real PHP logic in this phase — it decides which top-level sections appear in the new top nav and which links appear in each section's slim sub-sidebar. Built from the module list already curated in `resources/views/admin/home.blade.php`.

**Files:**
- Create: `app/Support/AdminNav.php`
- Create: `tests/Feature/AdminNavTest.php`

> **Executed with corrections.** Two bugs were caught during code review and fixed before this task was marked done — the block below reflects the final, correct, actually-implemented state (not the original draft): (1) the "orders" section's `path_prefix` was originally `admin/freequote`, which does not match any real route (the live route is `freequote-inbox`); (2) the 4th section originally pointed at `admin.blogs.create`, a route that doesn't exist — the legacy blog module was retired in favor of a unified CMS, and its create view/route are dead code. Fixed to point at the real, live `admin.contacts.index` route instead, as a "Messages"/contacts section. `activeSectionKey()` was also hardened with segment-boundary matching (was raw substring matching) and its order-dependence documented. **This retargeting is also why Task 10 (below) migrates `admin/contacts/index.blade.php` instead of the originally-planned `admin/blog/create.blade.php`** — the latter is unreachable dead code.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Support\AdminNav;
use Tests\TestCase;

class AdminNavTest extends TestCase
{
    public function test_sections_returns_dashboard_first(): void
    {
        $sections = AdminNav::sections();

        $this->assertSame('dashboard', $sections[0]['key']);
        $this->assertSame('Dashboard', $sections[0]['label']);
    }

    public function test_sections_include_orders_with_links(): void
    {
        $sections = AdminNav::sections();
        $orders = collect($sections)->firstWhere('key', 'orders');

        $this->assertNotNull($orders);
        $this->assertNotEmpty($orders['links']);
        $this->assertSame('Requested Quotes', $orders['links'][0]['label']);
    }

    public function test_active_section_matches_current_request_path(): void
    {
        $active = AdminNav::activeSectionKey('freequote-inbox');

        $this->assertSame('orders', $active);
    }

    public function test_active_section_defaults_to_dashboard_for_unknown_path(): void
    {
        $active = AdminNav::activeSectionKey('admin/something-not-mapped');

        $this->assertSame('dashboard', $active);
    }

    public function test_active_section_returns_full_section_for_known_path(): void
    {
        $section = AdminNav::activeSection('address');

        $this->assertSame('addresses', $section['key']);
        $this->assertSame('Shipping Addresses', $section['label']);
    }

    public function test_active_section_falls_back_to_dashboard_for_unknown_path(): void
    {
        $section = AdminNav::activeSection('admin/something-not-mapped');

        $this->assertSame('dashboard', $section['key']);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=AdminNavTest`
Expected: FAIL — `Class "App\Support\AdminNav" not found`.

- [ ] **Step 3: Write `app/Support/AdminNav.php`**

```php
<?php

namespace App\Support;

/**
 * Single source of truth for the new Tailwind admin's top nav + slim
 * sub-sidebar. Phase 1+ batch-migration plans extend $sections as each
 * module gets migrated off AdminLTE — this is a plain array, not a DB
 * table, since the structure only changes when a developer migrates a
 * page, not at runtime.
 */
class AdminNav
{
    /**
     * @return array<int, array{key: string, label: string, icon: string, path_prefix: string, links: array<int, array{label: string, route: string}>}>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'fa-tachometer-alt',
                'path_prefix' => 'admin-dashbord',
                'links' => [],
            ],
            [
                'key' => 'orders',
                'label' => 'Orders & Quotes',
                'icon' => 'fa-clipboard-list',
                'path_prefix' => 'freequote-inbox',
                'links' => [
                    ['label' => 'Requested Quotes', 'route' => 'freequote.index'],
                ],
            ],
            [
                'key' => 'addresses',
                'label' => 'Shipping Addresses',
                'icon' => 'fa-map-marker-alt',
                'path_prefix' => 'address',
                'links' => [
                    ['label' => 'All Addresses', 'route' => 'address.index'],
                    ['label' => 'Add Address', 'route' => 'address.create'],
                ],
            ],
            [
                'key' => 'contacts',
                'label' => 'Messages',
                'icon' => 'fa-envelope',
                'path_prefix' => 'admin/contacts',
                'links' => [
                    ['label' => 'Contact Inbox', 'route' => 'admin.contacts.index'],
                ],
            ],
        ];
    }

    /**
     * Which section key should be highlighted as active for a given request path.
     * Matches at a path-segment boundary (not a raw substring), so e.g. 'address'
     * won't wrongly match a future 'address-verification' route. Sections are
     * checked in declaration order and the first match wins — if you add a new
     * section whose path_prefix is a broader/shorter prefix of an existing one
     * (e.g. a generic 'admin' section), declare it AFTER the more specific ones
     * or it will steal their matches.
     */
    public static function activeSectionKey(string $requestPath): string
    {
        $requestPath = ltrim($requestPath, '/');

        foreach (self::sections() as $section) {
            if ($section['path_prefix'] === '') {
                continue;
            }
            $prefix = $section['path_prefix'];
            if ($requestPath === $prefix || str_starts_with($requestPath, $prefix . '/') || str_starts_with($requestPath, $prefix . '?')) {
                return $section['key'];
            }
        }

        return 'dashboard';
    }

    /** The full section array for the currently active section, or the dashboard section if none matches. */
    public static function activeSection(string $requestPath): array
    {
        $key = self::activeSectionKey($requestPath);

        return collect(self::sections())->firstWhere('key', $key) ?? self::sections()[0];
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --filter=AdminNavTest`
Expected: PASS (6 tests, 10 assertions).

- [ ] **Step 5: Commit** — skipped (no git repo in this project).

---

### Task 5: New shared layout shell (top nav + slim sub-sidebar)

**Files:**
- Create: `resources/views/layouts/tailwind/partials/topnav.blade.php`
- Create: `resources/views/layouts/tailwind/partials/subsidebar.blade.php`
- Create: `resources/views/layouts/tailwind/partials/flash.blade.php`
- Create: `resources/views/layouts/tailwind/app.blade.php`

- [ ] **Step 1: Write `resources/views/layouts/tailwind/partials/topnav.blade.php`**

```blade
{{-- New shared top nav — dark bar, brand mark, top-level sections, user menu. --}}
<nav class="bg-slate-900 text-slate-200" x-data="{ mobileOpen: false }">
    <div class="mx-auto flex h-14 max-w-[1600px] items-center gap-4 px-4">
        <a href="{{ route('admin-dashbord') }}" class="flex items-center gap-2 font-bold text-white">
            <img src="{{ asset('images/deliveringlogo.png') }}" alt="DP" class="h-6 w-auto">
            <span class="hidden sm:inline">DeliveringParcel</span>
        </a>

        <button
            class="ml-auto rounded p-2 text-slate-300 hover:bg-slate-800 sm:hidden"
            @click="mobileOpen = !mobileOpen"
            aria-label="Toggle navigation"
        >
            <i class="fas fa-bars"></i>
        </button>

        <div class="hidden flex-1 items-center gap-1 overflow-x-auto sm:flex">
            @foreach (\App\Support\AdminNav::sections() as $section)
                <a
                    href="{{ $section['links'][0]['route'] ?? null ? route($section['links'][0]['route']) : route('admin-dashbord') }}"
                    class="whitespace-nowrap rounded px-3 py-2 text-sm font-medium {{ $activeSectionKey === $section['key'] ? 'bg-brand text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    <i class="fas {{ $section['icon'] }} mr-1.5"></i>{{ $section['label'] }}
                </a>
            @endforeach
        </div>

        <div class="ml-auto hidden items-center gap-3 sm:flex" x-data="{ userOpen: false }">
            <div class="relative">
                <button @click="userOpen = !userOpen" @click.outside="userOpen = false" class="flex items-center gap-1.5 rounded px-2 py-1.5 text-sm text-slate-200 hover:bg-slate-800">
                    <i class="far fa-user"></i>
                    <span>{{ auth()->user()->name ?? 'Account' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="userOpen" x-transition x-cloak class="absolute right-0 z-50 mt-2 w-44 rounded-md border border-slate-200 bg-white py-1 text-sm text-slate-700 shadow-lg">
                    <a href="{{ url('/') }}" class="block px-3 py-2 hover:bg-slate-50"><i class="fas fa-globe mr-2 text-slate-400"></i>Website</a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <a href="{{ route('logout') }}" class="block px-3 py-2 hover:bg-slate-50"
                       onclick="event.preventDefault(); document.getElementById('dp2-logout').submit();">
                        <i class="fas fa-sign-out-alt mr-2 text-slate-400"></i>Logout
                    </a>
                    <form id="dp2-logout" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile section list --}}
    <div x-show="mobileOpen" x-cloak class="border-t border-slate-800 sm:hidden">
        @foreach (\App\Support\AdminNav::sections() as $section)
            <a
                href="{{ $section['links'][0]['route'] ?? null ? route($section['links'][0]['route']) : route('admin-dashbord') }}"
                class="block px-4 py-2.5 text-sm {{ $activeSectionKey === $section['key'] ? 'bg-brand text-white' : 'text-slate-300' }}"
            >
                <i class="fas {{ $section['icon'] }} mr-2"></i>{{ $section['label'] }}
            </a>
        @endforeach
    </div>
</nav>
```

- [ ] **Step 2: Write `resources/views/layouts/tailwind/partials/subsidebar.blade.php`**

```blade
{{-- Slim contextual sub-sidebar for the active top-nav section. Hidden entirely when the
     active section has no sub-links (e.g. Dashboard). --}}
@if (!empty($activeSection['links']))
    <aside class="hidden w-56 shrink-0 border-r border-slate-200 bg-white md:block">
        <nav class="sticky top-14 p-3">
            <p class="mb-2 px-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                {{ $activeSection['label'] }}
            </p>
            @foreach ($activeSection['links'] as $link)
                <a
                    href="{{ route($link['route']) }}"
                    data-active="{{ request()->routeIs($link['route']) ? 'true' : 'false' }}"
                    class="dp2-subnav-link mb-0.5 block rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-50"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </aside>
@endif
```

- [ ] **Step 3: Write `resources/views/layouts/tailwind/partials/flash.blade.php`**

```blade
@if (session('success'))
    <x-admin.alert type="success">{{ session('success') }}</x-admin.alert>
@endif
@if (session('error'))
    <x-admin.alert type="error">{{ session('error') }}</x-admin.alert>
@endif
@if (($errors ?? collect())->any())
    <x-admin.alert type="error">
        <ul class="list-disc space-y-0.5 pl-4">
            @foreach (($errors ?? collect())->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </x-admin.alert>
@endif
```

- [ ] **Step 4: Write `resources/views/layouts/tailwind/app.blade.php`**

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | Deliveringparcel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Third-party plugin assets stay as raw <link> tags, exactly like the AdminLTE
         layouts — only their bundled CSS gets overridden by vendor-overrides.css above. --}}
    <link rel="stylesheet" href="{{ url('dashbord/plugins/fontawesome6-free/css/all.min.css') }}">
    @stack('admin_styles')
</head>
<body class="bg-slate-50 text-slate-800">
@php
    $activeSectionKey = \App\Support\AdminNav::activeSectionKey(request()->path());
    $activeSection = \App\Support\AdminNav::activeSection(request()->path());
@endphp

@include('layouts.tailwind.partials.topnav')

<div class="flex min-h-[calc(100vh-3.5rem)]">
    @include('layouts.tailwind.partials.subsidebar')

    <main class="min-w-0 flex-1 p-4 md:p-6">
        <div class="mb-4">
            <h1 class="text-xl font-semibold text-slate-900">@yield('page_title')</h1>
            @hasSection('page_subtitle')
                <p class="mt-0.5 text-sm text-slate-500">@yield('page_subtitle')</p>
            @endif
        </div>

        @include('layouts.tailwind.partials.flash')

        @yield('content')
    </main>
</div>

<script src="{{ url('dashbord/plugins/jquery/jquery-3.6.0.min.js') }}"></script>
@stack('admin_scripts')
</body>
</html>
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/tailwind
git commit -m "feat: add new Tailwind admin layout shell (top nav + slim sub-sidebar)"
```

---

### Task 6: Core Blade component library

**Files:**
- Create: `resources/views/components/admin/button.blade.php`
- Create: `resources/views/components/admin/card.blade.php`
- Create: `resources/views/components/admin/badge.blade.php`
- Create: `resources/views/components/admin/alert.blade.php`
- Create: `resources/views/components/admin/modal.blade.php`
- Create: `resources/views/components/admin/input.blade.php`

- [ ] **Step 1: Write `resources/views/components/admin/button.blade.php`**

```blade
@props(['variant' => 'primary', 'tag' => 'button', 'href' => null])

@php
$variants = [
    'primary' => 'bg-brand text-white hover:bg-brand-dark',
    'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200',
    'danger' => 'bg-red-600 text-white hover:bg-red-700',
];
$classes = 'inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium transition-colors disabled:opacity-50 ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($tag === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
```

- [ ] **Step 2: Write `resources/views/components/admin/card.blade.php`**

```blade
@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title)
        <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
        </div>
    @endif
    <div class="p-4">
        {{ $slot }}
    </div>
</div>
```

- [ ] **Step 3: Write `resources/views/components/admin/badge.blade.php`**

```blade
@props(['color' => 'slate'])

@php
$colors = [
    'slate' => 'bg-slate-100 text-slate-700',
    'green' => 'bg-green-100 text-green-700',
    'yellow' => 'bg-yellow-100 text-yellow-800',
    'blue' => 'bg-blue-100 text-blue-700',
    'red' => 'bg-red-100 text-red-700',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . ($colors[$color] ?? $colors['slate'])]) }}>
    {{ $slot }}
</span>
```

- [ ] **Step 4: Write `resources/views/components/admin/alert.blade.php`**

```blade
@props(['type' => 'info'])

@php
$styles = [
    'success' => 'border-green-200 bg-green-50 text-green-800',
    'error' => 'border-red-200 bg-red-50 text-red-800',
    'info' => 'border-blue-200 bg-blue-50 text-blue-800',
];
@endphp

<div x-data="{ show: true }" x-show="show" {{ $attributes->merge(['class' => 'mb-4 flex items-start justify-between gap-3 rounded-md border px-4 py-3 text-sm ' . ($styles[$type] ?? $styles['info'])]) }}>
    <div class="min-w-0">{{ $slot }}</div>
    <button @click="show = false" type="button" class="shrink-0 opacity-60 hover:opacity-100" aria-label="Dismiss">&times;</button>
</div>
```

- [ ] **Step 5: Write `resources/views/components/admin/modal.blade.php`**

Alpine-based modal, replacing Bootstrap's `data-toggle="modal"` pattern. Usage: wrap the trigger in `x-data="{ open: false }"`, trigger with `@click="open = true"`, place `<x-admin.modal>` as a sibling inside the same `x-data` scope.

```blade
@props(['title' => null])

<div
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-slate-900/50"></div>

    <div x-show="open" x-transition class="relative w-full max-w-md rounded-lg bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
            <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600" aria-label="Close">&times;</button>
        </div>
        <div class="p-4">
            {{ $slot }}
        </div>
    </div>
</div>
```

- [ ] **Step 6: Write `resources/views/components/admin/input.blade.php`**

```blade
@props(['label' => null, 'name', 'value' => null, 'type' => 'text', 'required' => false])

<div class="mb-3">
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">
            {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light' . ($errors->has($name) ? ' border-red-400' : '')]) }}
    >
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
```

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/admin
git commit -m "feat: add core Tailwind admin component library"
```

---

### Task 7: Custom Tailwind pagination view

**Files:**
- Create: `resources/views/pagination/tailwind-admin.blade.php`

- [ ] **Step 1: Write the pagination view**

```blade
@if ($paginator->hasPages())
    <nav class="flex items-center justify-between" role="navigation" aria-label="Pagination">
        <div class="flex flex-1 items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm text-slate-300">&laquo; Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100">&laquo; Prev</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-1.5 text-sm text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100">Next &raquo;</a>
            @else
                <span class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm text-slate-300">Next &raquo;</span>
            @endif
        </div>
    </nav>
@endif
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/pagination/tailwind-admin.blade.php
git commit -m "feat: add Tailwind-styled pagination view"
```

---

### Task 8: Migrate the Dashboard page

**Files:**
- Modify: `resources/views/admin/home.blade.php`

> **Executed with a fix.** The module list below was originally trimmed to 5 entries during initial drafting; code review caught this as a real navigational regression (this route is live and the old page had 22 working module quick-links, none of which had any other entry point after the top-nav was reduced to 4 sections for Phase 0). Fixed by restoring the full original 22-module list, unchanged from before migration — only styling changed. The block below reflects the corrected, actually-implemented version.

- [ ] **Step 1: Rewrite `resources/views/admin/home.blade.php`**

```blade
@extends('layouts.tailwind.app')

@section('title', 'Admin Home')
@section('page_title', 'Admin Dashboard')
@section('page_subtitle', 'New responsive admin — modules activate automatically as they come online')

@section('content')
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @php
        $kpis = [
            ['Total Orders', \App\Models\Orders::count(), 'fa-box'],
            ['Users', \App\Models\User::count(), 'fa-users'],
            ['Offer Orders', \App\Models\Offerorder::count(), 'fa-file-invoice-dollar'],
            ['Messages', DB::table('contactuses')->count(), 'fa-envelope'],
        ];
    @endphp
    @foreach ($kpis as $k)
        <x-admin.card>
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-light text-brand">
                    <i class="fas {{ $k[2] }}"></i>
                </span>
                <div>
                    <div class="text-xs text-slate-500">{{ $k[0] }}</div>
                    <div class="text-lg font-semibold text-slate-900">{{ number_format((float) $k[1]) }}</div>
                </div>
            </div>
        </x-admin.card>
    @endforeach
</div>

<h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Modules</h2>
<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
    @php
        $modules = [
            ['Payoneer', 'fa-link', 'admin.payoneer.index', 'Payoneer payment links: requests, link inbox, proof verification'],
            ['Orders', 'fa-box', 'admin.orders.index', 'Orders management with status state machine, bulk actions, tracking'],
            ['Users', 'fa-users', 'admin.users.index', 'User management, roles, password resets'],
            ['Analytics', 'fa-chart-line', 'admin.analytics.revenue', 'Revenue, demand & RFM segmentation'],
            ['CMS · All Content', 'fa-newspaper', 'admin.cms.posts.index', 'One system: blog posts, pages, services, products, taxonomies, menus'],
            ['CMS · Blog Import', 'fa-file-import', 'admin.blog-import.index', 'WordPress/RSS/sitemap import straight into CMS posts'],
            ['CMS · Site Sections', 'fa-layer-group', 'admin.site-sections.index', 'Homepage sections order + visibility; add new sections here'],
            ['Hero Slider', 'fa-images', 'admin.hero-slides.index', 'Homepage slides with 20+ UI options'],
            ['Messages', 'fa-envelope', 'admin.contacts.index', 'Contact inbox with auto-classification & reply templates'],
            ['Payments', 'fa-credit-card', 'admin.payments.index', 'Payment ledger + refunds'],
            ['Settings', 'fa-cogs', 'admin.settings.edit', 'Business, SEO, theme & preferences'],
            ['Countries', 'fa-globe-americas', 'admin.countries.index', 'Country management'],
            ['Weight Units', 'fa-balance-scale', 'admin.weight-units.index', 'Units with default handling'],
            ['CMS · Products', 'fa-shopping-cart', 'admin.cms.posts.index', 'Products under CMS (redirects with ?type=product)'],
            ['Warehouse', 'fa-warehouse', 'admin.warehouse.packages.index', 'Receiving, bins, shipments, manifests'],
            ['Carriers', 'fa-truck', 'admin.carriers.index', 'DHL/FedEx/UPS + tracking tools'],
            ['Rate Engine', 'fa-calculator', 'admin.rates.zones.index', 'Zones, rate matrix, calculator'],
            ['Compliance', 'fa-shield-alt', 'admin.compliance.kyc.index', 'KYC, sanctions, HS codes, GDPR'],
            ['Notifications', 'fa-bell', 'admin.notifications.index', 'Notification center + audit log'],
            ['Quotes', 'fa-file-alt', 'admin.quotes.index', 'Quote inbox + offers negotiation view'],
            ['Returns', 'fa-undo', 'admin.returns.index', 'Returns & claims processing'],
            ['Tools', 'fa-toolbox', 'admin.tools.search', 'Global search, health, DB viewer, PDF'],
        ];
    @endphp
    @foreach ($modules as $m)
        @php $live = \Illuminate\Support\Facades\Route::has($m[2]); @endphp
        <x-admin.card :class="$live ? null : 'opacity-60'">
            <div class="mb-2 flex items-center gap-2">
                <i class="fas {{ $m[1] }} {{ $live ? 'text-brand' : 'text-slate-400' }}"></i>
                <span class="font-medium text-slate-800">{{ $m[0] }}</span>
            </div>
            <p class="mb-3 text-xs text-slate-500">{{ $m[3] }}</p>
            @if ($live)
                <x-admin.button tag="a" :href="route($m[2])" class="w-full justify-center">Open</x-admin.button>
            @else
                <span class="block w-full rounded-md bg-slate-100 px-3 py-2 text-center text-sm text-slate-400">Building…</span>
            @endif
        </x-admin.card>
    @endforeach
</div>
@endsection
```

- [ ] **Step 2: Build assets and verify in browser**

Run: `npm run build`
Then visit `http://127.0.0.1:8000/admin-dashbord` as the admin test user (`admin@deliveringparcel.com` / `TestPass123!`).
Expected: top nav renders with brand-blue active section, KPI tiles and module cards render with the new component styles, no console errors, no broken images.

- [ ] **Step 3: Run the route smoke check**

Run: `curl -s -o /dev/null -w "%{http_code}\n" -b /tmp/dp_admin.txt http://127.0.0.1:8000/admin-dashbord`
Expected: `200`

- [ ] **Step 4: Commit** — skipped (no git repo in this project).

---

### Task 9: Migrate the Address CRUD (list + create + edit)

**Files:**
- Modify: `resources/views/admin/address/index.blade.php`
- Modify: `resources/views/admin/address/create.blade.php`
- Modify: `resources/views/admin/address/edit.blade.php`

- [ ] **Step 1: Rewrite `resources/views/admin/address/index.blade.php`**

```blade
@extends('layouts.tailwind.app')

@section('title', 'Shipping Addresses')
@section('page_title', 'Shipping Addresses')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Name" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="number" value="{{ $filters['number'] ?? '' }}" placeholder="Number" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="city" value="{{ $filters['city'] ?? '' }}" placeholder="City" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="state" value="{{ $filters['state'] ?? '' }}" placeholder="State" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="country" value="{{ $filters['country'] ?? '' }}" placeholder="Country" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <x-admin.button variant="secondary" tag="a" href="{{ route('address.index') }}">Reset</x-admin.button>
        <x-admin.button type="submit">Filter</x-admin.button>
    </form>
    <x-admin.button tag="a" :href="route('address.create')"><i class="fas fa-plus"></i> Add Address</x-admin.button>
</div>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                    <th class="py-2 pr-4">Name</th>
                    <th class="py-2 pr-4">Number</th>
                    <th class="py-2 pr-4">City</th>
                    <th class="py-2 pr-4">Country</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($address as $a)
                    <tr>
                        <td class="py-2 pr-4">{{ $a->name }}</td>
                        <td class="py-2 pr-4">{{ $a->number }}</td>
                        <td class="py-2 pr-4">{{ $a->city }}</td>
                        <td class="py-2 pr-4">{{ $a->country }}</td>
                        <td class="py-2 pr-4">
                            <a href="{{ route('address.edit', $a->id) }}" class="text-brand hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-400">No addresses found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $address->links('pagination.tailwind-admin') }}
    </div>
</x-admin.card>
@endsection
```

- [ ] **Step 2: Rewrite `resources/views/admin/address/create.blade.php`**

```blade
@extends('layouts.tailwind.app')

@section('title', 'Add Shipping Address')
@section('page_title', 'Add Shipping Address')

@section('content')
<x-admin.card class="max-w-xl">
    <form method="POST" action="{{ route('address.store') }}">
        @csrf
        <x-admin.input name="name" label="Name" required />
        <x-admin.input name="number" label="Phone Number" required />
        <x-admin.input name="address1" label="Address" required />
        <x-admin.input name="city" label="City" required />
        <x-admin.input name="state" label="State" required />
        <x-admin.input name="country" label="Country" required />
        <x-admin.input name="postalcode" label="Postal Code" required />

        <div class="mt-4 flex gap-2">
            <x-admin.button type="submit">Save Address</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('address.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
```

- [ ] **Step 3: Rewrite `resources/views/admin/address/edit.blade.php`**

```blade
@extends('layouts.tailwind.app')

@section('title', 'Edit Shipping Address')
@section('page_title', 'Edit Shipping Address')

@section('content')
@php $a = $address->first(); @endphp
<x-admin.card class="max-w-xl">
    <form method="POST" action="{{ route('address.update', $a->id) }}">
        @csrf
        @method('PUT')
        <x-admin.input name="name" label="Name" :value="$a->name" required />
        <x-admin.input name="number" label="Phone Number" :value="$a->number" required />
        <x-admin.input name="address1" label="Address" :value="$a->address1" required />
        <x-admin.input name="city" label="City" :value="$a->city" required />
        <x-admin.input name="state" label="State" :value="$a->state" required />
        <x-admin.input name="country" label="Country" :value="$a->country" required />
        <x-admin.input name="postalcode" label="Postal Code" :value="$a->postalcode" required />

        <div class="mt-4 flex gap-2">
            <x-admin.button type="submit">Update Address</x-admin.button>
            <x-admin.button variant="secondary" tag="a" :href="route('address.index')">Cancel</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
```

- [ ] **Step 4: Verify in browser**

Visit `/address`, `/address/create`, submit the create form with a test address, then edit it via `/address/{id}/edit`.
Expected: list renders, pagination links use the new Tailwind style, create/edit forms show validation errors from `<x-admin.input>` when required fields are left blank (submit empty first to confirm), success flash message renders via `<x-admin.alert>` after a successful save.

- [ ] **Step 5: Run the route smoke check**

Run: `curl -s -o /dev/null -w "%{http_code}\n" -b /tmp/dp_admin.txt http://127.0.0.1:8000/address`
Expected: `200`

- [ ] **Step 6: Commit**

```bash
git add resources/views/admin/address
git commit -m "feat: migrate address CRUD pages to the new Tailwind layout"
```

---

### Task 10: Migrate Contact Messages Index (select2 filters + AJAX list + KPI cards)

> **Retargeted during execution.** The plan originally targeted `resources/views/admin/blog/create.blade.php` to prove select2+summernote re-theming together. During Task 4's review it was discovered that page is **dead code** — `admin/blogs` (and `admin/services`, which uses the same pattern) now just 301-redirects into a unified CMS (`admin.cms.posts.*`), and the old blog-specific create route/view are unreachable. The only live page using summernote is the unified CMS post form (`resources/views/admin/cms/posts/_form.blade.php`, 636 lines) — too large and out of scope for a Phase 0 proof-of-concept; it belongs in the Phase 1+ CMS batch instead. select2's Tailwind re-theme (written and code-reviewed in Task 2) is proven here instead, on a real, live, self-contained page. Summernote's CSS override (also already written and reviewed in Task 2) will get its live-page proof when the Phase 1+ CMS batch migrates `admin/cms/posts/_form.blade.php` — no action needed here.

**Files:**
- Modify: `resources/views/admin/contacts/index.blade.php`

This page (`admin.contacts.index`, `Admin\Contacts\ContactController@index`) shows KPI tiles, a select2+daterangepicker filter bar, a bulk-action toolbar, and a table whose rows are rendered entirely client-side by an inline `<script>` (AJAX-loaded via `DP.infiniteScroll`, calling `admin.contacts.data`). This is a pure UI/markup change — every element `id` the script selects by (`dpFilterQ`, `dpFilterCategory`, `dpFilterStatus`, `dpFilterRange`, `dpDateFrom`, `dpDateTo`, `dpApplyFilters`, `dpResetFilters`, `dpCheckAll`, `dpSelectedCount`, `tbody`, `[data-dp-bulk]`, `.dp-row-check`) must be kept exactly as-is so the script keeps working unmodified — only the CSS classes around them change, plus one important fix described in Step 2.

- [ ] **Step 1: Rewrite the page markup (KPIs, filter bar, toolbar, table shell)**

Replace `resources/views/admin/contacts/index.blade.php` with:

```blade
@extends('layouts.tailwind.app')

@section('title', 'Messages')
@section('page_title', 'Contact Messages')
@section('page_subtitle', 'Classify, read and reply to customer messages')

@push('admin_styles')
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashbord/plugins/daterangepicker/daterangepicker.css') }}">
@endpush

@section('content')
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @php
            $kpiTiles = [
                ['Unread (new)', $kpis['unread'], 'fa-envelope', 'text-red-500'],
                ['Replied', $kpis['replied'], 'fa-reply', 'text-green-500'],
                ['Read', $kpis['read'], 'fa-envelope-open', 'text-blue-500'],
                ['Archived', $kpis['archived'], 'fa-archive', 'text-slate-500'],
                ['Total messages', $kpis['total'], 'fa-inbox', 'text-brand'],
            ];
        @endphp
        @foreach ($kpiTiles as $tile)
            <x-admin.card>
                <div class="flex items-center gap-2">
                    <i class="fas {{ $tile[2] }} {{ $tile[3] }}"></i>
                    <div>
                        <div class="text-lg font-semibold text-slate-900">{{ number_format($tile[1]) }}</div>
                        <div class="text-xs text-slate-500">{{ $tile[0] }}</div>
                    </div>
                </div>
            </x-admin.card>
        @endforeach
    </div>

    <x-admin.card class="mb-4">
        <form id="dpContactFilters" class="flex flex-wrap items-end gap-2" onsubmit="return false;">
            <div>
                <label for="dpFilterQ" class="mb-1 block text-xs font-medium text-slate-600">Search</label>
                <input type="text" id="dpFilterQ" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Name, email or message...">
            </div>
            <div>
                <label for="dpFilterCategory" class="mb-1 block text-xs font-medium text-slate-600">Category</label>
                <select id="dpFilterCategory" class="dp-select2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dpFilterStatus" class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                <select id="dpFilterStatus" class="dp-select2 rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dpFilterRange" class="mb-1 block text-xs font-medium text-slate-600">Date range</label>
                <input type="text" id="dpFilterRange" readonly placeholder="From — To" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <input type="hidden" id="dpDateFrom">
                <input type="hidden" id="dpDateTo">
            </div>
            <div class="ml-auto flex gap-2">
                <x-admin.button type="button" id="dpApplyFilters"><i class="fas fa-filter"></i> Apply</x-admin.button>
                <x-admin.button type="button" variant="secondary" id="dpResetFilters"><i class="fas fa-undo"></i> Reset</x-admin.button>
            </div>
        </form>
    </x-admin.card>

    <div class="mb-3 flex flex-wrap items-center gap-3 rounded-md bg-slate-50 px-3 py-2">
        <label class="flex items-center gap-1.5 text-sm text-slate-600">
            <input type="checkbox" id="dpCheckAll"> Select all
        </label>
        <span class="text-sm text-slate-500"><span id="dpSelectedCount">0</span> selected</span>
        <button type="button" class="rounded-md border border-blue-200 px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50" data-dp-bulk="mark_read"><i class="fas fa-envelope-open"></i> Mark as read</button>
        <button type="button" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100" data-dp-bulk="archive"><i class="fas fa-archive"></i> Archive</button>
        <a href="{{ route('admin.contacts.templates.index') }}" class="ml-auto rounded-md border border-brand px-2.5 py-1 text-xs font-medium text-brand hover:bg-brand-light"><i class="fas fa-file-alt"></i> Reply templates</a>
    </div>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="w-9 py-2"></th>
                        <th class="py-2 pr-4">Customer</th>
                        <th class="py-2 pr-4">Message</th>
                        <th class="py-2 pr-4">Category</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Received</th>
                        <th class="py-2 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody" class="divide-y divide-slate-100">
                    <tr class="dp-loading-row"><td colspan="7" class="py-6 text-center text-slate-400"><i class="fas fa-spinner fa-spin"></i> Loading messages...</td></tr>
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection
```

- [ ] **Step 2: Update the inline `render()` script's badge markup (important — not optional)**

The `@push('admin_scripts')` block's `render(row)` function builds each row's HTML as a raw string, and today hardcodes Bootstrap classes directly into it: `'<span class="dp-badge badge badge-' + esc(row.category_color) + '">'` and the same pattern for `status_color`. `row.category_color`/`row.status_color` come from the backend as Bootstrap color names (`secondary`, `success`, `info`, `danger`, `warning`, `primary` — confirmed via `app/Models/Contactus.php`'s `categoryColor()`/`statusColor()`, which read `config('admin_contacts.categories.*.color')`/`config('admin_contacts.statuses.*.color')`). Since this page no longer loads Bootstrap's CSS, `badge badge-danger` etc. would render as plain unstyled text with no pill/color. Add a small color-mapping helper to the script and use it instead. Keep everything else in the `@push('admin_scripts')` block exactly as-is (same URLs, same event wiring, same `DP.infiniteScroll` call, same select2/daterangepicker init) — only change the `render()` function's two badge lines:

```js
function badgeClasses(bootstrapColor) {
    var map = {
        secondary: 'bg-slate-100 text-slate-700',
        success: 'bg-green-100 text-green-700',
        info: 'bg-blue-100 text-blue-700',
        danger: 'bg-red-100 text-red-700',
        warning: 'bg-yellow-100 text-yellow-800',
        primary: 'bg-blue-100 text-blue-700',
    };
    return 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' + (map[bootstrapColor] || map.secondary);
}
```

Add this function next to `esc()` in the script, then change these two lines inside `render(row)`:

```js
html += '<td data-label="Category"><span class="' + badgeClasses(row.category_color) + '">' + esc(row.category_label) + '</span></td>';
html += '<td data-label="Status"><span class="' + badgeClasses(row.status_color) + '">' + esc(row.status_label) + '</span>' + (row.replied ? ' <i class="fas fa-reply text-green-600" title="Has reply"></i>' : '') + '</td>';
```

(Was: `'<td data-label="Category"><span class="dp-badge badge badge-' + esc(row.category_color) + '">' + esc(row.category_label) + '</span></td>'` and the equivalent status line with `text-success` swapped for `text-green-600`.) Also update the "View" action button's classes from `'<a href="' + esc(row.show_url) + '" class="btn btn-sm btn-outline-primary">'` to `'<a href="' + esc(row.show_url) + '" class="rounded-md border border-brand px-2 py-1 text-xs font-medium text-brand hover:bg-brand-light">'`. Leave every other line of the script (the `dataUrl`/`bulkUrl` setup, `filters()`, `loadList()`, the `DOMContentLoaded` listener, select2/daterangepicker init, bulk-action `fetch()` call) completely unchanged — this is a markup-only migration, no behavior changes.

Note: `daterangepicker` is a third-party plugin not covered by `resources/css/vendor-overrides.css` (Task 2 only covered DataTables/select2/summernote, since those were the only ones the original 4 POC pages needed). Leave it with its own default (un-re-themed) appearance for this task — it's a floating widget, not part of the page's static layout, so a temporary style mismatch there is low-impact and can be addressed in the Phase 1+ batch that next touches a daterangepicker-using page.

- [ ] **Step 3: Verify in browser**

Log in as the admin test user and visit `/admin/contacts`. Expected: 5 KPI cards render with the new component style, the category/status select2 dropdowns render with the new bordered/rounded look (not native browser selects), typing a search term and clicking Apply reloads the AJAX table, category/status badges in the loaded rows render as colored pills (not plain unstyled text — this confirms Step 2's fix worked), the "Select all" checkbox and bulk action buttons work, and the "Reply templates" link navigates correctly.

- [ ] **Step 4: Run the route smoke check**

Run: `curl -s -o /dev/null -w "%{http_code}\n" -b /tmp/dp_admin.txt http://127.0.0.1:8000/admin/contacts`
Expected: `200`

- [ ] **Step 5: Commit** — skipped (no git repo in this project).

---

### Task 11: Migrate Freequote Index (DataTables + Alpine modal + badges)

**Files:**
- Modify: `resources/views/admin/freequote/index.blade.php`
- Modify: `resources/views/admin/freequote/delete_modal.blade.php`

- [ ] **Step 1: Rewrite `resources/views/admin/freequote/delete_modal.blade.php`**

Convert from a standalone Bootstrap modal (toggled by `data-toggle="modal"` targeting an ID) to an Alpine-scoped component included next to its trigger button.

```blade
<div x-data="{ open: false }" class="inline">
    <button type="button" @click="open = true" class="text-red-600 hover:text-red-800" title="Delete">
        <i class="fas fa-trash"></i>
    </button>

    <x-admin.modal title="Delete Confirmation">
        <p class="mb-4 text-sm text-slate-600">Are you sure you want to delete this quote request?</p>
        <form action="{{ route('freequote.destroy', $quote->id) }}" method="POST" class="flex justify-end gap-2">
            @csrf
            @method('DELETE')
            <x-admin.button variant="secondary" type="button" @click="open = false">No</x-admin.button>
            <x-admin.button variant="danger" type="submit">Yes, delete</x-admin.button>
        </form>
    </x-admin.modal>
</div>
```

- [ ] **Step 2: Rewrite `resources/views/admin/freequote/index.blade.php`**

```blade
@extends('layouts.tailwind.app')

@section('title', 'Requested Quotes')
@section('page_title', 'Orders & Quotes')

@section('content')
@if ($order->count() > 0)
    <x-admin.card title="Clients Orders" class="mb-6">
        <div class="overflow-x-auto">
            <table data-dp2-datatable class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Order ID</th>
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Ship From</th>
                        <th class="py-2 pr-4">Ship To</th>
                        <th class="py-2 pr-4">Net Total</th>
                        <th class="py-2 pr-4">Created</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($order as $add)
                        <tr>
                            <td class="py-2 pr-4"><a href="{{ route('order', $add->id) }}" class="text-brand hover:underline">{{ $add->order_id }}</a></td>
                            <td class="py-2 pr-4">{{ $add->name }}</td>
                            <td class="py-2 pr-4">{{ $add->email }}</td>
                            <td class="py-2 pr-4">{{ $add->shipfrom }}</td>
                            <td class="py-2 pr-4">{{ $add->shipto }}</td>
                            <td class="py-2 pr-4">${{ $add->total }}</td>
                            <td class="py-2 pr-4">{{ $add->created_at }}</td>
                            <td class="py-2 pr-4">
                                @if ($add->active_tab == 3)
                                    <x-admin.badge color="yellow">{{ $add->order_status }}</x-admin.badge>
                                @elseif ($add->active_tab == 4)
                                    <x-admin.badge color="green">{{ $add->order_status }}</x-admin.badge>
                                @elseif ($add->order_status == 'Offer Accepted' || $add->order_status == 'Offer Placed')
                                    <x-admin.badge color="blue">{{ $add->order_status }}</x-admin.badge>
                                @elseif ($add->order_status == 'Order placed')
                                    <x-admin.badge color="slate">{{ $add->order_status }}</x-admin.badge>
                                @elseif ($add->order_status != '')
                                    <x-admin.badge color="red">{{ $add->order_status }}</x-admin.badge>
                                @else
                                    <x-admin.badge color="red">Request Placed</x-admin.badge>
                                @endif
                            </td>
                            <td class="py-2 pr-4"><a href="{{ route('order', $add->id) }}" class="text-slate-400 hover:text-brand"><i class="fas fa-history"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
@else
    <p class="text-center text-slate-400">No Record Found</p>
@endif

<x-admin.card>
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-800">Requested Quotes <span class="text-slate-400">({{ $freequote->total() }} total)</span></h3>
    </div>
    <form method="GET" action="{{ route('freequote.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <input type="text" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Name contains…" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="email" value="{{ $filters['email'] ?? '' }}" placeholder="user@example.com" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="country" value="{{ $filters['country'] ?? '' }}" placeholder="Country contains…" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Any field…" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
        <x-admin.button type="submit"><i class="fas fa-filter"></i> Filter</x-admin.button>
        <x-admin.button variant="secondary" tag="a" :href="route('freequote.index')">Reset</x-admin.button>
    </form>

    @if ($freequote->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">#</th>
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Number</th>
                        <th class="py-2 pr-4">Cargo Type</th>
                        <th class="py-2 pr-4">Country</th>
                        <th class="py-2 pr-4">Destination</th>
                        <th class="py-2 pr-4">Requested</th>
                        <th class="py-2 pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($freequote as $quote)
                        <tr>
                            <td class="py-2 pr-4">{{ $quote->id }}</td>
                            <td class="py-2 pr-4">{{ $quote->name }}</td>
                            <td class="py-2 pr-4">{{ $quote->email }}</td>
                            <td class="py-2 pr-4">{{ $quote->number }}</td>
                            <td class="py-2 pr-4">{{ $quote->cargotype }}</td>
                            <td class="py-2 pr-4">{{ $quote->country }}</td>
                            <td class="py-2 pr-4">{{ $quote->destination }}</td>
                            <td class="py-2 pr-4">{{ $quote->created_at ? \Illuminate\Support\Carbon::parse($quote->created_at)->format('d M Y H:i') : '—' }}</td>
                            <td class="py-2 pr-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('freequote.show', $quote->id) }}" class="text-slate-400 hover:text-brand" title="View"><i class="fas fa-eye"></i></a>
                                    @include('admin.freequote.delete_modal')
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $freequote->links('pagination.tailwind-admin') }}
        </div>
    @else
        <p class="text-center text-slate-400">No quote submissions found.</p>
    @endif
</x-admin.card>
@endsection
```

Note: the client-side `#example1` DataTable init script from the old page is dropped since the `data-dp2-datatable` attribute now handles it via `resources/js/app.js`'s auto-init (Task 3) — same `DataTable()` call, same plugin, just centralized instead of repeated per-page.

- [ ] **Step 3: Verify in browser**

Visit `/admin/freequote`. Expected: Clients Orders table is DataTable-powered (search/sort/pagination controls appear, styled to match the new palette from `vendor-overrides.css`), status badges render with the new pill styling, clicking the trash icon opens the Alpine modal (fades in, backdrop click closes it, "No" closes it, "Yes, delete" submits the delete form), Requested Quotes section's server-side filter form and Tailwind pagination both work.

- [ ] **Step 4: Run the route smoke check**

Run: `curl -s -o /dev/null -w "%{http_code}\n" -b /tmp/dp_admin.txt http://127.0.0.1:8000/admin/freequote`
Expected: `200`

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/freequote
git commit -m "feat: migrate freequote index to the new Tailwind layout with Alpine modal"
```

---

### Task 12: Full verification pass

**Files:** none (verification only)

- [ ] **Step 1: Run the full test suite**

Run: `php artisan test`
Expected: all tests pass, including the 4 new `AdminNavTest` tests from Task 4.

- [ ] **Step 2: Production build**

Run: `npm run build`
Expected: exits 0, no Tailwind/Vite warnings about unresolved `@apply` or missing content paths.

- [ ] **Step 3: Re-run the route smoke check across all 4 migrated pages plus their sibling unmigrated pages**

```bash
jar=/tmp/dp_admin.txt
for path in admin-dashbord address address/create admin/contacts admin/freequote admin-orders admin/users; do
  code=$(curl -s -o /dev/null -w "%{http_code}" -b "$jar" "http://127.0.0.1:8000/$path")
  echo "$path -> $code"
done
```

Expected: the 4 migrated pages return `200` with no entries added to `storage/logs/laravel.log`; `admin-orders` and `admin/users` (unmigrated, still AdminLTE) also still return `200`, confirming the old layouts were not broken by this phase's changes.

- [ ] **Step 4: Confirm no other page was affected**

Run: `git diff --stat` (or, if there's no git repo, `find resources/views -newer docs/superpowers/plans/2026-09-22-tailwind-admin-foundation.md -iname "*.blade.php"`)
Expected: only the files listed in this plan's File Structure section were touched — every other Blade file among the ~194 remaining AdminLTE pages is untouched.

- [ ] **Step 5: Update the design doc's status**

Modify `docs/superpowers/specs/2026-09-22-adminlte-to-tailwind-migration-design.md`: change the `**Status:**` line to note Phase 0 is complete and ready for Phase 1 batch-migration planning.

- [ ] **Step 6: Commit**

```bash
git add docs/superpowers/specs/2026-09-22-adminlte-to-tailwind-migration-design.md
git commit -m "docs: mark Phase 0 foundation complete"
```
