@extends('admin.tools.pdf.layout')
@section('doc_title', 'Financial Report')

@section('doc_body')
<p class="muted">Accepted-offer revenue by month — range {{ $from }} → {{ $to }}</p>

<table>
    <thead><tr><th>Month</th><th>Orders</th><th style="text-align:right">Revenue</th></tr></thead>
    <tbody>
        @forelse ($months as $m)
            <tr>
                <td>{{ $m->ym }}</td>
                <td>{{ (int) $m->orders_count }}</td>
                <td style="text-align:right">${{ number_format((float) $m->revenue / 100, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="muted">No accepted offers in this range.</td></tr>
        @endforelse
    </tbody>
</table>

@php $grand = $months->sum(fn ($m) => (float) $m->revenue); @endphp
<table class="totals" style="width:280px;margin-left:auto">
    <tr class="grand"><td>Total</td><td style="text-align:right">${{ number_format($grand / 100, 2) }}</td></tr>
</table>
@endsection
