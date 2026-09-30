@extends('layouts.tailwind.app')

@section('title', 'Order #' . $order->order_id)
@section('page_title', 'Order #' . $order->order_id)
@section('page_subtitle', 'Placed ' . optional($order->created_at)->format('d M Y, H:i') . ' — last update ' . optional($order->updated_at)->format('d M Y, H:i'))

{{-- window.DP (lazy images + confirm-dialog + toast helpers), toastr and SweetAlert2 are only
     loaded by the old AdminLTE layouts; this page relies on DP.toast, DP.bindConfirms (backs the
     data-dp-confirm archive/restore buttons via Swal) and DP.lazyImages (avatar/product/chat
     thumbnails), plus Swal.fire directly for status transitions — so all three must be pulled in
     explicitly here. Pushed via @push('admin_scripts') so they load AFTER the layout's jQuery
     <script> tag (layout yields content, then jQuery, then @stack('admin_scripts')). --}}
@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/plugins/toastr/toastr.min.css') }}">
@endpush
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
@endpush

@section('content')

@php
    $currentKey = (string) $order->order_status;

    // Bootstrap badge class (from config/admin_orders.php) → Tailwind pill classes.
    // Same mapping as admin/ordersm/partials/status-badge.blade.php and
    // admin/ordersm/index.blade.php's $badgeClasses / badgeClasses() helpers.
    $badgeClasses = fn (string $bootstrap) => [
        'bg-secondary' => 'bg-slate-100 text-slate-700',
        'bg-primary'   => 'bg-blue-100 text-blue-700',
        'bg-info'      => 'bg-cyan-100 text-cyan-700',
        'bg-teal'      => 'bg-teal-100 text-teal-700',
        'bg-success'   => 'bg-green-100 text-green-700',
        'bg-danger'    => 'bg-red-100 text-red-700',
        'bg-indigo'    => 'bg-indigo-100 text-indigo-700',
        'bg-orange'    => 'bg-orange-100 text-orange-700',
        'bg-purple'    => 'bg-purple-100 text-purple-700',
        'bg-warning'   => 'bg-yellow-100 text-yellow-800',
    ][$bootstrap] ?? 'bg-slate-100 text-slate-700';
@endphp

{{-- ============ Header ============ --}}
<x-admin.card class="mb-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @include('admin.ordersm.partials.status-badge', ['status' => $order->order_status])
            @if ($order->archived_at)
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                    <i class="fas fa-archive"></i> Archived {{ optional($order->archived_at)->format('d M Y') }}
                </span>
            @endif
            <span class="text-slate-500">Total: <strong class="text-slate-900">${{ number_format((float) $order->total, 2) }}</strong></span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('admin/orders') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">
                <i class="fas fa-arrow-left"></i> Back to orders
            </a>
            @if ($order->archived_at)
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
                        data-dp-confirm data-url="{{ route('admin.orders.archive', $order->id) }}" data-method="PUT"
                        data-title="Restore this order?">
                    <i class="fas fa-box-open"></i> Restore from archive
                </button>
            @elseif ($statusMeta['archivable'])
                <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-md bg-amber-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-600"
                        data-dp-confirm data-url="{{ route('admin.orders.archive', $order->id) }}" data-method="PUT"
                        data-title="Archive this order?">
                    <i class="fas fa-archive"></i> Archive
                </button>
            @endif
        </div>
    </div>
</x-admin.card>

{{-- ============ Timeline + allowed transitions ============ --}}
<x-admin.card class="mb-4">
    <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-tasks mr-1 text-slate-400"></i> Status timeline</h3>

    <div class="mb-3 flex flex-wrap items-center gap-1 overflow-x-auto pb-1">
        @foreach ($timeline as $stepKey)
            @php($stepMeta = $statusMap[$stepKey])
            @php($isCurrent = $currentKey === $stepKey)
            @php($isDone = $statusMeta['step'] >= 0 && $stepMeta['step'] >= 0 && $statusMeta['step'] > $stepMeta['step'])
            <div class="flex items-center">
                <div class="px-2 text-center">
                    <span class="mx-auto flex h-8 w-8 items-center justify-center rounded-full border-2 {{ $isCurrent ? 'border-blue-400 text-blue-500' : ($isDone ? 'border-green-400 text-green-500' : 'border-slate-200 text-slate-300') }}">
                        <i class="fas {{ $isCurrent ? 'fa-spinner' : ($isDone ? 'fa-check' : 'fa-circle text-[6px]') }}"></i>
                    </span>
                    <div class="mt-1 max-w-[110px] text-xs {{ $isCurrent ? 'font-semibold text-blue-600' : ($isDone ? 'text-green-600' : 'text-slate-400') }}">{{ $stepMeta['label'] }}</div>
                </div>
                @if (! $loop->last)
                    <i class="fas fa-chevron-right mx-1 text-slate-300"></i>
                @endif
            </div>
        @endforeach
    </div>

    <p class="mb-3 text-sm text-slate-500"><strong class="text-slate-700">{{ $statusMeta['label'] }}:</strong> {{ $statusMeta['description'] }}</p>

    <div class="border-t border-slate-100 pt-3">
        <span class="mb-1.5 block text-xs text-slate-500">Allowed next states (state machine enforced):</span>
        @if (count($nextStatuses))
            <div class="flex flex-wrap gap-2">
                @foreach ($nextStatuses as $nextKey)
                    @php($nextMeta = $statusMap[$nextKey])
                    <button type="button" class="dp-transition inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium hover:opacity-80 {{ $badgeClasses($nextMeta['badge']) }}"
                            data-status="{{ $nextKey }}">
                        <i class="fas fa-arrow-right"></i> Move to {{ $nextMeta['label'] }}
                    </button>
                @endforeach
            </div>
        @else
            <span class="text-sm text-slate-400">No further transitions — this is a final state.</span>
        @endif
    </div>
</x-admin.card>

{{-- ============ Customer + shipping ============ --}}
<div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-user mr-1 text-slate-400"></i> Customer</h3>
        @if ($user)
            <div class="mb-3 flex items-center gap-2">
                @if ($user->avatar)
                    <img class="dp-lazy h-11 w-11 rounded-full object-cover"
                         data-src="{{ Str::startsWith($user->avatar, 'http') ? $user->avatar : asset('uploads/profile/' . $user->avatar) }}" alt="avatar">
                @endif
                <div>
                    <strong class="block text-slate-900">{{ $user->name }}</strong>
                    <small class="text-slate-500">{{ $user->type ? ucfirst($user->type) : 'Client' }}</small>
                </div>
            </div>
            <table class="w-full text-sm">
                <tr><td class="w-24 py-1 pr-2 align-top text-slate-500">Email</td><td class="py-1">{{ $user->email }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Phone</td><td class="py-1">{{ $user->number ?: '—' }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Status</td><td class="py-1">{{ ucfirst($user->status ?: 'active') }}</td></tr>
                <tr><td class="py-1 pr-2 align-top text-slate-500">Joined</td><td class="py-1">{{ optional($user->created_at)->format('d M Y') }}</td></tr>
            </table>
            <a href="{{ route('admin.users.show', $user->id) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50">
                <i class="fas fa-id-card"></i> View profile
            </a>
        @else
            <span class="text-sm text-slate-400">Customer account not found (user id {{ $order->user_id }}).</span>
        @endif
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-shipping-fast mr-1 text-slate-400"></i> Shipping details</h3>
        <table class="w-full text-sm">
            <tr><td class="w-28 py-1 pr-2 align-top text-slate-500">Recipient</td><td class="py-1">{{ $order->ship_name ?: '—' }}</td></tr>
            <tr><td class="py-1 pr-2 align-top text-slate-500">Phone</td><td class="py-1">{{ $order->ship_number ?: '—' }}</td></tr>
            <tr><td class="py-1 pr-2 align-top text-slate-500">Address</td><td class="py-1">
                {{ $order->ship_address1 ?: '' }}{{ $order->ship_address2 ? ', ' . $order->ship_address2 : '' }}<br>
                {{ trim(($order->ship_city ?: '') . ', ' . ($order->ship_state ?: '') . ' ' . ($order->ship_postalcode ?: ''), ', ') }}<br>
                {{ $order->ship_country ?: '' }}
            </td></tr>
            @if (! $order->ship_address1 && $order->address)
                <tr><td class="py-1 pr-2 align-top text-slate-500">Alt address</td><td class="py-1">{{ $order->address }}</td></tr>
            @endif
            <tr><td class="py-1 pr-2 align-top text-slate-500">From &rarr; To</td><td class="py-1">{{ $order->shipfrom ?: '—' }} &rarr; {{ $order->shipto ?: '—' }}</td></tr>
            <tr><td class="py-1 pr-2 align-top text-slate-500">Est. weight</td><td class="py-1">{{ $order->approximate_weight ?: '—' }}</td></tr>
        </table>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-info-circle mr-1 text-slate-400"></i> Order flags</h3>
        <table class="w-full text-sm">
            <tr><td class="w-32 py-1 pr-2 align-top text-slate-500">Purchase assist.</td><td class="py-1">{{ $order->product_purchase ? 'Yes' : 'No' }}</td></tr>
            <tr><td class="py-1 pr-2 align-top text-slate-500">Customs</td><td class="py-1">{{ $order->product_customs ? 'Yes' : 'No' }}</td></tr>
            <tr><td class="py-1 pr-2 align-top text-slate-500">Extra check</td><td class="py-1">{{ $order->product_check ? 'Yes' : 'No' }}</td></tr>
            <tr><td class="py-1 pr-2 align-top text-slate-500">Prohibited check</td><td class="py-1">{{ $order->product_prohibited ? 'Yes' : 'No' }}</td></tr>
            <tr><td class="py-1 pr-2 align-top text-slate-500">Disinfection</td><td class="py-1">{{ $order->product_disinfection ? 'Yes' : 'No' }}</td></tr>
            <tr><td class="py-1 pr-2 align-top text-slate-500">Consolidation</td><td class="py-1">{{ $order->product_consolidation ? 'Yes' : 'No' }}</td></tr>
            @if ($order->edit_offer && $order->edit_offer !== '0')
                <tr><td class="py-1 pr-2 align-top text-slate-500">Client edit request</td><td class="py-1"><span class="inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-700">{{ $order->edit_offer }}</span></td></tr>
            @endif
            @if ($order->product_description)
                <tr><td class="py-1 pr-2 align-top text-slate-500">Description</td><td class="py-1">{{ $order->product_description }}</td></tr>
            @endif
        </table>
    </x-admin.card>
</div>

{{-- ============ Purchased items ============ --}}
<x-admin.card class="mb-4">
    <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-shopping-cart mr-1 text-slate-400"></i> Purchased items ({{ count($products) }})</h3>
    @if (count($products))
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="w-16 py-2 pr-4">Image</th>
                        <th class="py-2 pr-4">Product</th>
                        <th class="py-2 pr-4">Qty</th>
                        <th class="py-2 pr-4">Weight</th>
                        <th class="py-2 pr-4">Price</th>
                        <th class="py-2 pr-4">Total</th>
                        <th class="py-2 pr-4">Tracking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                        <tr>
                            <td class="py-2 pr-4">
                                @if ($product->image)
                                    <a href="{{ asset('uploads/productsimages/' . $product->image) }}" target="_blank" rel="noopener">
                                        <img class="dp-lazy h-10 w-10 rounded object-cover" data-src="{{ asset('uploads/productsimages/' . $product->image) }}" alt="product image">
                                    </a>
                                @else
                                    <span class="text-slate-400">&mdash;</span>
                                @endif
                                @if ($product->receipt)
                                    <a class="mt-1 block text-xs text-slate-500 hover:text-brand" href="{{ asset('uploads/productsimages/' . $product->receipt) }}" target="_blank" rel="noopener"><i class="fas fa-file-download"></i> receipt</a>
                                @endif
                            </td>
                            <td class="py-2 pr-4">
                                {{ $product->productname }}
                                @if ($product->producturl)
                                    <a class="block text-xs text-slate-400 hover:text-brand" href="{{ $product->producturl }}" target="_blank" rel="noopener nofollow"><i class="fas fa-external-link-alt"></i> product page</a>
                                @endif
                            </td>
                            <td class="py-2 pr-4">{{ $product->productquantity }}</td>
                            <td class="py-2 pr-4">{{ $product->productweight ?: '—' }}</td>
                            <td class="py-2 pr-4">${{ number_format((float) $product->productprice, 2) }}</td>
                            <td class="py-2 pr-4">${{ number_format((float) $product->product_total, 2) }}</td>
                            <td class="py-2 pr-4">
                                @if ($product->trackingid)
                                    {{ $product->trackingid }}
                                    @if ($product->trackinglink)
                                        <a class="block text-xs text-slate-500 hover:text-brand" href="{{ $product->trackinglink }}" target="_blank" rel="noopener">track <i class="fas fa-external-link-alt"></i></a>
                                    @endif
                                @else
                                    <span class="text-slate-400">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-slate-400">No purchased items on this order.</p>
    @endif
</x-admin.card>

{{-- ============ Offer ============ --}}
@if ($offer)
    <x-admin.card class="mb-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-file-invoice-dollar mr-1 text-slate-400"></i> Offer #{{ $offer->id }}</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <table class="w-full text-sm">
                    <tr><td class="w-36 py-1 pr-2 align-top text-slate-500">Products total</td><td class="py-1">${{ number_format((float) $offer->product_total, 2) }}</td></tr>
                    <tr><td class="py-1 pr-2 align-top text-slate-500">Services total</td><td class="py-1">${{ number_format((float) $offer->total - (float) $offer->product_total, 2) }}</td></tr>
                    <tr><td class="py-1 pr-2 align-top text-slate-500">Grand total</td><td class="py-1"><strong class="text-slate-900">${{ number_format((float) $offer->total, 2) }}</strong></td></tr>
                    <tr><td class="py-1 pr-2 align-top text-slate-500">Offer status</td><td class="py-1">{{ $offer->offer_status == 1 ? 'Accepted' : ($offer->offer_status == 2 ? 'Rejected' : 'Pending') }}</td></tr>
                    @if ($offer->rejections_note)
                        <tr><td class="py-1 pr-2 align-top text-slate-500">Rejection note</td><td class="py-1">{{ $offer->rejections_note }}</td></tr>
                    @endif
                    @if ($offer->description)
                        <tr><td class="py-1 pr-2 align-top text-slate-500">Description</td><td class="py-1">{{ $offer->description }}</td></tr>
                    @endif
                </table>
            </div>
            <div>
                <span class="mb-1.5 block text-xs text-slate-500">Offered services:</span>
                @forelse ($offerServices as $service)
                    <span class="mb-1 mr-1 inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-700">{{ $service->servicename }}: ${{ number_format((float) $service->servicevalue, 2) }}</span>
                @empty
                    <span class="text-xs text-slate-400">None</span>
                @endforelse

                @if (count($offerProducts))
                    <form action="{{ route('admin.orders.tracking', $order->id) }}" method="post">
                        @csrf
                        <div class="mt-2 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                                        <th class="py-2 pr-3">Offer product</th>
                                        <th class="py-2 pr-3">Qty</th>
                                        <th class="py-2 pr-3">Price</th>
                                        <th class="py-2 pr-3">Spread</th>
                                        <th class="py-2 pr-3">Tracking ID</th>
                                        <th class="py-2 pr-3">Tracking link</th>
                                        <th class="py-2 pr-3">Image</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($offerProducts as $offerProduct)
                                        <tr>
                                            <td class="py-2 pr-3">{{ $offerProduct->productname }}</td>
                                            <td class="py-2 pr-3">{{ $offerProduct->productquantity }}</td>
                                            <td class="py-2 pr-3">${{ number_format((float) $offerProduct->productprice, 2) }}</td>
                                            <td class="py-2 pr-3">${{ number_format((float) $offerProduct->productspread, 2) }}</td>
                                            <td class="py-2 pr-3">
                                                <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $offerProduct->id }}">
                                                <input type="text" class="w-32 rounded-md border border-slate-300 px-2 py-1 text-sm" name="products[{{ $loop->index }}][trackingid]"
                                                       value="{{ $offerProduct->trackingid }}" maxlength="500" placeholder="Tracking ID">
                                            </td>
                                            <td class="py-2 pr-3">
                                                <input type="text" class="w-36 rounded-md border border-slate-300 px-2 py-1 text-sm" name="products[{{ $loop->index }}][trackinglink]"
                                                       value="{{ $offerProduct->trackinglink }}" maxlength="500" placeholder="https://">
                                            </td>
                                            <td class="py-2 pr-3">
                                                @if ($offerProduct->image)
                                                    <a href="{{ asset('uploads/productsimages/' . $offerProduct->image) }}" target="_blank" rel="noopener">
                                                        <img class="dp-lazy h-9 w-9 rounded object-cover" data-src="{{ asset('uploads/productsimages/' . $offerProduct->image) }}" alt="offer image">
                                                    </a>
                                                @else<span class="text-slate-400">&mdash;</span>@endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="mt-2 inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">
                            <i class="fas fa-save"></i> Save tracking
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-admin.card>
@endif

{{-- ============ Make an Offer (legacy /order/{id} parity) ============ --}}
@if ($offerForm)
    <x-admin.card class="mb-4" id="dp-offercard">
        <h3 class="mb-3 text-sm font-semibold text-slate-800">
            <i class="fas fa-handshake mr-1 text-slate-400"></i> {{ $offerForm['revise'] ? 'Revise Offer' : 'Make an Offer' }}
            @if ($offerForm['revise'])
                <span class="ml-2 text-xs font-normal text-slate-500">— submitting replaces the current pending offer</span>
            @endif
        </h3>

        <form id="dp-offer-form" data-url="{{ route('admin.orders.offer', $order->id) }}">
            @csrf

            {{-- Offered services (multi-select, editable charges) --}}
            <h4 class="text-sm font-semibold text-slate-700"><i class="fas fa-list-check mr-1"></i> Offered Services</h4>
            <p class="mb-2 text-xs text-slate-500">Tick the services included in this offer — charges can be adjusted per offer.</p>
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                @foreach ($offerForm['rows'] as $i => $row)
                    @php($wasSelected = array_key_exists($row['name'], $offerForm['selected']))
                    @php($checked = $row['locked'] || ($offerForm['revise'] ? $wasSelected : false))
                    @php($value = $wasSelected ? $offerForm['selected'][$row['name']] : $row['value'])
                    <div class="flex items-center gap-2">
                        <input type="checkbox" class="dp-offer-service-check"
                               data-idx="{{ $i }}"
                               {{ $checked ? 'checked' : '' }}
                               {{ $row['locked'] ? 'disabled data-locked="1"' : '' }}>
                        <input type="text" class="min-w-0 flex-1 rounded-md border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm text-slate-600" value="{{ $row['name'] }}{{ $row['locked'] ? ' (required)' : '' }}" readonly>
                        <input type="number" class="dp-offer-service-value w-24 rounded-md border border-slate-300 px-2 py-1.5 text-right text-sm"
                               data-idx="{{ $i }}" value="{{ $value }}" min="0" step="0.01" {{ $row['locked'] ? 'readonly' : '' }}>
                        <span class="text-sm text-slate-500">$</span>
                    </div>
                @endforeach
            </div>

            {{-- Additional services (add-more rows) --}}
            <h4 class="mt-4 text-sm font-semibold text-slate-700"><i class="fas fa-plus-circle mr-1"></i> Additional Services</h4>
            <p class="mb-2 text-xs text-slate-500">Any custom one-off services for this offer.</p>
            <table class="mb-2 w-full text-left text-sm" id="dp-addtable">
                <thead>
                    <tr class="text-xs uppercase text-slate-500">
                        <th class="w-1/2 py-1.5">Service Name</th>
                        <th class="w-1/3 py-1.5">Service Price</th>
                        <th class="py-1.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="dp-add-row">
                        <td class="py-1 pr-2"><input type="text" class="dp-add-name w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm" placeholder="Service Name" maxlength="100"></td>
                        <td class="py-1 pr-2"><input type="number" class="dp-add-value w-full rounded-md border border-slate-300 px-2 py-1.5 text-right text-sm" placeholder="0.00" min="0" step="0.01"></td>
                        <td class="py-1 text-right">
                            <button type="button" class="dp-add-remove rounded-md border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50" title="Remove"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @foreach ($offerForm['extra'] as $extra)
                        <tr class="dp-add-row">
                            <td class="py-1 pr-2"><input type="text" class="dp-add-name w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm" value="{{ $extra['name'] }}" maxlength="100"></td>
                            <td class="py-1 pr-2"><input type="number" class="dp-add-value w-full rounded-md border border-slate-300 px-2 py-1.5 text-right text-sm" value="{{ $extra['value'] }}" min="0" step="0.01"></td>
                            <td class="py-1 text-right">
                                <button type="button" class="dp-add-remove rounded-md border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50" title="Remove"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" class="inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700" id="dp-add-more"><i class="fas fa-plus"></i> Add More</button>

            {{-- Product pricing (purchase-assist orders only, legacy parity) --}}
            @if ($order->product_purchase && count($products))
                <h4 class="mt-6 text-sm font-semibold text-slate-700"><i class="fas fa-shopping-basket mr-1"></i> Product Pricing</h4>
                <p class="mb-2 text-xs text-slate-500">Line total = price &times; quantity + spread. These prices are offered to the client.</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                                <th class="py-2 pr-4">Product</th>
                                <th class="py-2 pr-4">Qty</th>
                                <th class="py-2 pr-4">Price / unit</th>
                                <th class="py-2 pr-4">Spread</th>
                                <th class="py-2 pr-4">Line total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($products as $product)
                                @php($opRow = $offerProductByProduct[$product->id] ?? null)
                                <tr class="dp-offer-product-row" data-id="{{ $product->id }}" data-qty="{{ $product->productquantity }}">
                                    <td class="py-2 pr-4">
                                        {{ $product->productname }}
                                        @if ($product->producturl)
                                            <a class="block text-xs text-slate-400 hover:text-brand" href="{{ $product->producturl }}" target="_blank" rel="noopener nofollow"><i class="fas fa-external-link-alt"></i> product page</a>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4">{{ $product->productquantity }}</td>
                                    <td class="py-2 pr-4">
                                        <input type="number" class="dp-offer-price w-28 rounded-md border border-slate-300 px-2 py-1 text-right text-sm"
                                               value="{{ $opRow ? $opRow->productprice : $product->productprice }}" min="0" step="0.01">
                                    </td>
                                    <td class="py-2 pr-4">
                                        <input type="number" class="dp-offer-spread w-28 rounded-md border border-slate-300 px-2 py-1 text-right text-sm"
                                               value="{{ $opRow ? $opRow->productspread : 0 }}" min="0" step="0.01">
                                    </td>
                                    <td class="py-2 pr-4"><strong class="dp-offer-linetotal">0.00</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Shipping address (ship-for-me orders, legacy parity) --}}
            @if (! $order->product_purchase)
                <h4 class="mt-6 text-sm font-semibold text-slate-700"><i class="fas fa-map-marker-alt mr-1"></i> Shipping Address</h4>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                    <div class="md:col-span-5">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Country</label>
                        <select class="dp-offer-country w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm" id="dp-offer-country">
                            <option value="">Select Country</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country }}" {{ $country === $order->shipto ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-7">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Shipping Address</label>
                        <input type="text" class="dp-offer-address w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm" id="dp-offer-address"
                               list="dp-offer-addresses" maxlength="1000"
                               value="{{ $offer ? $offer->shipingaddress : '' }}"
                               placeholder="Full shipping address for the offer">
                        <datalist id="dp-offer-addresses">
                            @foreach ($shippingAddresses as $addr)
                                <option data-country="{{ $addr->country }}" value="{{ $addr->name }} {{ $addr->address1 }} {{ $addr->address2 }} {{ $addr->city }} {{ $addr->state }} {{ $addr->postalcode }} {{ $addr->country }} {{ $addr->number }}">{{ $addr->name }} &middot; {{ $addr->country }}</option>
                            @endforeach
                        </datalist>
                    </div>
                </div>
            @endif

            {{-- Offer description + totals --}}
            <div class="mt-4">
                <label class="mb-1 block text-sm font-medium text-slate-700" for="dp-offer-desc"><i class="fas fa-comment-dots mr-1"></i> Offer Description <span class="text-red-500">*</span></label>
                <textarea class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light" id="dp-offer-desc" rows="3" maxlength="5000"
                          placeholder="Describe the offer, conditions or notes for the client...">{{ $offer ? $offer->description : '' }}</textarea>
            </div>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-4 border-t border-slate-100 pt-4">
                <table class="text-sm">
                    <tr><td class="w-44 py-1 pr-2 text-slate-500">Products total</td><td class="py-1">$<span id="dp-t-products">0.00</span></td></tr>
                    <tr><td class="py-1 pr-2 text-slate-500">Services total</td><td class="py-1">$<span id="dp-t-services">0.00</span></td></tr>
                    <tr class="text-base"><td class="py-1 pr-2"><strong>Grand total</strong></td><td class="py-1"><strong>$<span id="dp-t-grand">0.00</span></strong></td></tr>
                </table>
                <div class="text-right">
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        <i class="fas fa-paper-plane"></i> {{ $offerForm['revise'] ? 'Update Offer' : 'Make an Offer' }}
                    </button>
                    <div class="mt-1 text-xs text-slate-400">Total is recalculated server-side on submit.</div>
                </div>
            </div>
        </form>
    </x-admin.card>
@endif

{{-- ============ Payment method (negotiation) ============ --}}
<x-admin.card class="mb-4" id="dp-paycard">
    <h3 class="mb-3 text-sm font-semibold text-slate-800">
        <i class="fas fa-credit-card mr-1 text-slate-400"></i> Payment method
        <span class="ml-2 text-xs font-normal text-slate-500">service context: {{ $serviceContext }}</span>
    </h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <table class="w-full text-sm">
                <tr>
                    <td class="w-32 py-1 pr-2 align-top text-slate-500">Order override</td>
                    <td class="py-1">
                        @if ($order->forced_payment_method_code)
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">{{ $order->forced_payment_method_code }}</span>
                            <span class="text-xs text-slate-500">— checkout offers ONLY this method</span>
                        @else
                            <span class="text-slate-400">none — follows service rules ({{ $serviceContext }})</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="py-1 pr-2 align-top text-slate-500">Open payment</td>
                    <td class="py-1">
                        @if ($openPayment)
                            {{ $openPayment->reference }} &middot;
                            <span class="inline-flex items-center rounded-full bg-cyan-100 px-2.5 py-0.5 text-xs font-medium text-cyan-700">{{ $openPayment->payment_method_code }}</span>
                            <span class="text-xs text-slate-500">{{ $openPayment->status }}</span>
                            @if (in_array($openPayment->status, ['awaiting_payment', 'awaiting_verification']))
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    @if ($openPayment->proof_path)
                                        <a href="{{ route('admin.payments.proof', $openPayment->id) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50">
                                            <i class="fas fa-file-download"></i> Receipt
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.orders.payment-verify', $order->id) }}" class="inline"
                                          onsubmit="return confirm('Confirm the money has been received for {{ $openPayment->reference }}?')">
                                        @csrf
                                        <input type="hidden" name="approve" value="1">
                                        <input type="hidden" name="note" value="Confirmed from order page">
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-green-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-green-700">
                                            <i class="fas fa-check"></i> Mark payment received
                                        </button>
                                    </form>
                                    @if ($openPayment->status === 'awaiting_verification')
                                        <form method="POST" action="{{ route('admin.orders.payment-verify', $order->id) }}" class="inline"
                                              onsubmit="return confirm('Reject this receipt? The customer will have to start a new payment.')">
                                            @csrf
                                            <input type="hidden" name="approve" value="0">
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md border border-red-200 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Reject receipt</button>
                                        </form>
                                    @endif
                                </div>
                            @elseif ($openPayment->status === 'paid')
                                <span class="ml-1 inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">verified &#10003;</span>
                            @endif
                        @else
                            <span class="text-slate-400">no payment initiated yet</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div>
            <form id="dp-pm-form" data-url="{{ route('admin.orders.payment-method', $order->id) }}">
                @csrf
                <div class="mb-3">
                    <label for="dp-pm-method" class="mb-1 block text-xs font-medium text-slate-600">Force method for this order</label>
                    <select id="dp-pm-method" name="method" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                        <option value="">— clear override (normal routing) —</option>
                        @foreach ($paymentMethods as $pm)
                            <option value="{{ $pm->code }}" {{ $order->forced_payment_method_code === $pm->code ? 'selected' : '' }}>{{ $pm->name }} ({{ $pm->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="dp-pm-note" class="mb-1 block text-xs font-medium text-slate-600">Note (optional, audited)</label>
                    <input type="text" id="dp-pm-note" name="note" class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm" maxlength="255" placeholder="e.g. agreed on bank transfer during negotiation">
                </div>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-amber-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-600">Save payment method</button>
                <span id="dp-pm-msg" class="ml-2 text-xs text-slate-500"></span>
            </form>
        </div>
    </div>
</x-admin.card>

@push('admin_scripts')
<script>
(function () {
    var form = document.getElementById('dp-pm-form');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = document.getElementById('dp-pm-msg');
        msg.textContent = 'Saving…';
        fetch(form.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        }).then(function (r) { return r.json(); }).then(function (j) {
            msg.textContent = j.message || (j.ok ? 'Saved.' : 'Failed.');
            if (j.ok) { setTimeout(function () { window.location.reload(); }, 900); }
        }).catch(function () { msg.textContent = 'Request failed.'; });
    });
})();
</script>
@endpush

{{-- ============ Chat log ============ --}}
<x-admin.card class="mb-4">
    <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-comments mr-1 text-slate-400"></i> Recent messages (latest {{ count($chats) }})</h3>
    @if (count($chats))
        <ul class="divide-y divide-slate-100">
            @foreach ($chats as $chat)
                <li class="flex flex-wrap items-start justify-between gap-2 py-3 first:pt-0 last:pb-0">
                    <div>
                        <strong class="text-slate-800">{{ $chat->sender_name ?: ('User #' . $chat->from) }}</strong>
                        <span class="text-xs text-slate-500">({{ $chat->sender_type ?: 'client' }}) &middot; {{ optional($chat->created_at)->format('d M Y, H:i') }}</span>
                        <div class="text-sm text-slate-700">{{ $chat->body }}</div>
                        @if ($chat->image)
                            <a href="{{ asset('uploads/chatimages/' . $chat->image) }}" target="_blank" rel="noopener">
                                <img class="dp-lazy mt-1 h-16 w-16 rounded object-cover" data-src="{{ asset('uploads/chatimages/' . $chat->image) }}" alt="chat image">
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-slate-400">No messages on this order.</p>
    @endif
</x-admin.card>

{{-- ============ Tracking form ============ --}}
<x-admin.card class="mb-4">
    <h3 class="mb-3 text-sm font-semibold text-slate-800"><i class="fas fa-truck mr-1 text-slate-400"></i> Tracking</h3>
    <form id="trackingForm" data-url="{{ route('admin.orders.tracking', $order->id) }}">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600" for="trackingid">Tracking number</label>
                <input type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" id="trackingid" name="trackingid"
                       value="{{ old('trackingid', $order->trackingid) }}" maxlength="255">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600" for="trackinglink">Tracking link</label>
                <input type="url" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" id="trackinglink" name="trackinglink"
                       value="{{ old('trackinglink', $order->trackinglink) }}" maxlength="255" placeholder="https://">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600" for="companyname">Carrier</label>
                <input type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" id="companyname" name="companyname"
                       value="{{ old('companyname', $order->companyname) }}" maxlength="255">
            </div>
        </div>
        <button type="submit" class="mt-3 inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-2 text-sm font-medium text-white hover:bg-brand-dark">
            <i class="fas fa-save"></i> Save tracking
        </button>
    </form>
</x-admin.card>

{{-- ═══ SHIPPER SYSTEM (2026-09-10) — append-only card: create a shipping request ═══ --}}
@if(!$order->has_shipper_assignment)
<x-admin.card class="mb-4 border-amber-300">
    <h3 class="mb-2 text-sm font-semibold text-amber-700"><i class="fas fa-shipping-fast mr-1"></i> Create Shipping Request for Shippers</h3>
    <p class="mb-3 text-sm text-slate-600">
        Generate a masked brief (customer identity hidden) and publish it to verified
        shippers in <strong>{{ $order->shipfrom }}</strong>.
    </p>
    <div class="flex flex-wrap items-center gap-2">
        <form action="{{ route('admin.shipping-requests.generate') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-amber-500 px-3 py-2 text-sm font-medium text-white hover:bg-amber-600">
                <i class="fas fa-magic"></i> Generate Shipping Request (1 Click)
            </button>
        </form>
        <a href="{{ route('admin.shipping-requests.create', ['order_id' => $order->id]) }}" class="inline-flex items-center gap-1.5 rounded-md border border-amber-300 px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">Open Create Form</a>
    </div>
</x-admin.card>
@else
<x-admin.card class="mb-4 border-green-300">
    <h3 class="mb-2 text-sm font-semibold text-green-700"><i class="fas fa-check mr-1"></i> Shipper Assigned</h3>
    <a href="{{ route('admin.shipper-assignments.index', ['order_id' => $order->id]) }}" class="inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
        <i class="fas fa-eye"></i> View Assignment
    </a>
</x-admin.card>
@endif

@endsection

@push('admin_scripts')
<script>
(function () {
    var CSRF = document.querySelector('meta[name=csrf-token]').content;

    function toast(ok, msg) {
        if (!DP.toast) { return; }
        ok ? DP.toast.success(msg) : DP.toast.error(msg);
    }

    // --- status transitions (state machine enforced server-side)
    document.querySelectorAll('.dp-transition').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var status = btn.dataset.status;
            var run = function () {
                fetch('{{ route('admin.orders.status', $order->id) }}', {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ status: status })
                })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok && j.ok, j: j }; }); })
                .then(function (res) {
                    toast(res.ok, res.j.message || 'Status updated.');
                    if (res.ok) { setTimeout(function () { window.location.reload(); }, 700); }
                })
                .catch(function () { toast(false, 'Network error — please retry.'); });
            };
            if (window.Swal) {
                Swal.fire({ title: 'Change order status?', text: btn.textContent.trim(), icon: 'question', showCancelButton: true, confirmButtonText: 'Confirm' })
                    .then(function (r) { if (r.isConfirmed) run(); });
            } else { run(); }
        });
    });

    // --- tracking form
    var form = document.getElementById('trackingForm');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var payload = {
            trackingid: document.getElementById('trackingid').value.trim(),
            trackinglink: document.getElementById('trackinglink').value.trim(),
            companyname: document.getElementById('companyname').value.trim()
        };
        fetch(form.dataset.url, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok && j.ok, j: j }; }); })
        .then(function (res) { toast(res.ok, res.j.message || 'Tracking updated.'); })
        .catch(function () { toast(false, 'Network error — please retry.'); });
    });

    // --- destructive archive/restore buttons
    DP.bindConfirms && DP.bindConfirms();

    DP.lazyImages && DP.lazyImages();
})();
</script>
@endpush

@push('admin_scripts')
{{-- Make-an-Offer form: live totals (display only — server recalculates). --}}
<script>
(function () {
    var card = document.getElementById('dp-offercard');
    if (!card) return;

    var form    = document.getElementById('dp-offer-form');
    var CSRF    = document.querySelector('meta[name=csrf-token]').content;
    var names   = [];
    var locked  = [];
    var defaults = [];

    card.querySelectorAll('.dp-offer-service-check').forEach(function (cb) {
        var i = cb.dataset.idx;
        var valueInput = card.querySelector('.dp-offer-service-value[data-idx="' + i + '"]');
        var nameInput = cb.parentElement.querySelector('input[type=text]');
        names[i]   = nameInput.value.replace(/\s*\(required\)\s*$/, '').trim();
        locked[i]  = cb.dataset.locked === '1';
        defaults[i] = valueInput;
    });

    function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }
    function fmt(n) { return n.toFixed(2); }

    function recalc() {
        var products = 0;
        card.querySelectorAll('.dp-offer-product-row').forEach(function (row) {
            var line = num(row.querySelector('.dp-offer-price').value) * num(row.dataset.qty)
                     + num(row.querySelector('.dp-offer-spread').value);
            row.querySelector('.dp-offer-linetotal').textContent = fmt(line);
            products += line;
        });

        var services = 0;
        card.querySelectorAll('.dp-offer-service-check').forEach(function (cb) {
            if (cb.checked || cb.dataset.locked === '1') {
                services += num(defaults[cb.dataset.idx].value);
            }
        });
        card.querySelectorAll('#dp-addtable tbody tr').forEach(function (row) {
            var name = row.querySelector('.dp-add-name').value.trim();
            if (name !== '') { services += num(row.querySelector('.dp-add-value').value); }
        });

        document.getElementById('dp-t-products').textContent = fmt(products);
        document.getElementById('dp-t-services').textContent = fmt(services);
        document.getElementById('dp-t-grand').textContent   = fmt(products + services);
    }

    card.addEventListener('input', recalc);
    card.addEventListener('change', recalc);

    // --- additional services: add / remove rows
    document.getElementById('dp-add-more').addEventListener('click', function () {
        var tbody = document.querySelector('#dp-addtable tbody');
        var tr = tbody.querySelector('tr.dp-add-row').cloneNode(true);
        tr.querySelector('.dp-add-name').value = '';
        tr.querySelector('.dp-add-value').value = '';
        tbody.appendChild(tr);
    });

    card.addEventListener('click', function (e) {
        var btn = e.target.closest('.dp-add-remove');
        if (!btn) return;
        var rows = card.querySelectorAll('#dp-addtable tbody tr.dp-add-row');
        if (rows.length > 1) { btn.closest('tr').remove(); recalc(); }
    });

    // --- submit (server is authoritative for the total)
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var services = [];
        card.querySelectorAll('.dp-offer-service-check').forEach(function (cb) {
            var i = cb.dataset.idx;
            services.push({
                name: names[i],
                value: num(defaults[i].value),
                checked: cb.checked || cb.dataset.locked === '1' ? 1 : 0
            });
        });

        var additional = [];
        card.querySelectorAll('#dp-addtable tbody tr').forEach(function (row) {
            var name = row.querySelector('.dp-add-name').value.trim();
            if (name !== '') {
                additional.push({ name: name, value: num(row.querySelector('.dp-add-value').value) });
            }
        });

        var products = [];
        card.querySelectorAll('.dp-offer-product-row').forEach(function (row) {
            products.push({
                id: row.dataset.id,
                price: num(row.querySelector('.dp-offer-price').value),
                spread: num(row.querySelector('.dp-offer-spread').value)
            });
        });

        var desc = document.getElementById('dp-offer-desc').value.trim();
        if (desc === '') {
            DP.toast && DP.toast.error('Offer description is required.');
            return;
        }

        var btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        fetch(form.dataset.url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                description: desc,
                shipingaddress: (document.getElementById('dp-offer-address') || {}).value || '',
                country: (document.getElementById('dp-offer-country') || {}).value || '',
                services: services,
                additional: additional,
                products: products
            })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok && j.ok, j: j }; }); })
        .then(function (res) {
            DP.toast && (res.ok ? DP.toast.success(res.j.message || 'Offer saved.') : DP.toast.error(res.j.message || 'Offer failed.'));
            if (res.ok) { setTimeout(function () { window.location.reload(); }, 1200); }
            else { btn.disabled = false; }
        })
        .catch(function () {
            DP.toast && DP.toast.error('Network error — please retry.');
            btn.disabled = false;
        });
    });

    // --- filter address datalist by selected country (legacy countryaddress parity)
    var countrySel = document.getElementById('dp-offer-country');
    var addressList = document.getElementById('dp-offer-addresses');
    function filterAddresses() {
        if (!countrySel || !addressList) return;
        var c = countrySel.value;
        addressList.querySelectorAll('option').forEach(function (opt) {
            opt.hidden = c !== '' && opt.dataset.country !== c;
        });
    }
    if (countrySel) { countrySel.addEventListener('change', filterAddresses); filterAddresses(); }

    recalc();
})();
</script>
@endpush
