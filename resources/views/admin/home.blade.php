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
