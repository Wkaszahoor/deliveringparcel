@php
    /*
    | Order status badge — single source of truth: config/admin_orders.php
    | Usage: @include('admin.ordersm.partials.status-badge', ['status' => $order->order_status])
    |
    | $meta['badge'] is a Bootstrap 4 background class (bg-primary, bg-teal, ...) — the new
    | Tailwind layout loads no Bootstrap CSS, so it is mapped to Tailwind pill classes here,
    | mirroring the $badgeClasses map in admin/ordersm/index.blade.php and the JS
    | badgeClasses() helper in that page's inline script.
    */
    $meta = \App\Http\Controllers\Admin\OrdersMgmt\OrderStatusService::meta($status ?? null);
    $badgeClasses = [
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
    ][$meta['badge']] ?? 'bg-slate-100 text-slate-700';
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses }}" data-status="{{ (string) ($status ?? '') }}">{{ $meta['label'] }}</span>
