@extends('admin.tools.pdf.layout')
@section('doc_title', 'Invoice — Offer #' . $offer->id)

@section('doc_body')
<table style="width:100%">
    <tr>
        <td style="width:50%">
            <strong>Bill to</strong><br>
            {{ $offer->user_name ?: 'Customer' }}<br>
            {{ $offer->user_email ?: '' }}<br>
            Ref order: #{{ $offer->ref }}
        </td>
        <td style="text-align:right">
            <strong>Invoice #</strong> OF-{{ str_pad((string) $offer->id, 5, '0', STR_PAD_LEFT) }}<br>
            <strong>Date</strong> {{ optional($offer->created_at)->format('M d, Y') }}<br>
            <strong>Status</strong> {{ $offer->offer_status == 1 ? 'Accepted' : 'Status ' . $offer->offer_status }}
        </td>
    </tr>
</table>

<table>
    <thead><tr><th>Description</th><th style="text-align:right">Amount</th></tr></thead>
    <tbody>
        @foreach ($products as $p)
            <tr><td>{{ $p->productname ?? $p->name ?? 'Product line' }}</td><td style="text-align:right">${{ number_format((float) ($p->price ?? $p->amount ?? 0), 2) }}</td></tr>
        @endforeach
        @foreach ($services as $s)
            <tr><td>{{ $s->servicename ?? $s->name ?? 'Service line' }}</td><td style="text-align:right">${{ number_format((float) ($s->price ?? $s->amount ?? 0), 2) }}</td></tr>
        @endforeach
    </tbody>
</table>

<table class="totals" style="width:280px;margin-left:auto">
    <tr><td class="muted">Product total</td><td style="text-align:right">${{ number_format((float) $offer->product_total, 2) }}</td></tr>
    <tr class="grand"><td>Total</td><td style="text-align:right">${{ number_format((float) $offer->total / 100, 2) }}</td></tr>
</table>
@endsection
