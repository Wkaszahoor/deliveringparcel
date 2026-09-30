@extends('admin.tools.pdf.layout')
@section('doc_title', 'Order Summary — #' . $order->order_id)

@section('doc_body')
<table style="width:100%">
    <tr>
        <td style="width:50%">
            <strong>Customer</strong><br>
            {{ $order->user_name ?: '—' }}<br>
            {{ $order->user_email ?: '' }}
        </td>
        <td style="text-align:right">
            <strong>Order</strong> #{{ $order->order_id }}<br>
            <strong>Date</strong> {{ optional($order->created_at)->format('M d, Y') }}<br>
            <strong>Status</strong> {{ $order->order_status ?: 'Processing' }}<br>
            @if ($order->trackingid)<strong>Tracking</strong> {{ $order->trackingid }} ({{ $order->companyname }})@endif
        </td>
    </tr>
</table>

<table>
    <thead><tr><th>Item</th><th>Qty</th><th style="text-align:right">Amount</th></tr></thead>
    <tbody>
        @forelse ($items as $item)
            <tr>
                <td>{{ $item->productname ?? $item->name ?? 'Item' }}</td>
                <td>{{ $item->qty ?? $item->quantity ?? 1 }}</td>
                <td style="text-align:right">${{ number_format((float) ($item->price ?? $item->amount ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">No item details stored for this order.</td></tr>
        @endforelse
    </tbody>
</table>

<table class="totals" style="width:280px;margin-left:auto">
    <tr class="grand"><td>Total</td><td style="text-align:right">${{ number_format((float) $order->total, 2) }}</td></tr>
</table>
@endsection
