@extends('shipper.layouts.app')
@section('title', 'Open Requests')

@section('content')
<h2 style="margin:8px 0">Open Shipping Requests</h2>
<p style="color:var(--h2-muted)">Masked briefs in your service countries — no customer identity is ever shown.</p>
<div class="h2-table-wrap">
    <table class="h2-table">
        <thead><tr><th>Reference</th><th>Type</th><th>Country</th><th>Value range</th><th>Products</th><th>Expires</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
            <tr>
                <td data-label="Reference"><b>{{ $r->reference }}</b></td>
                <td data-label="Type">{{ $r->service_type === 'buy_for_me' ? 'Buy for Me' : 'Ship for Me' }}</td>
                <td data-label="Country">{{ $r->country_required }}</td>
                <td data-label="Value">${{ number_format($r->value_range_min, 0) }} – ${{ number_format($r->value_range_max, 0) }}</td>
                <td data-label="Products">{{ count($r->product_details ?? []) }}</td>
                <td data-label="Expires">{{ $r->expires_at?->diffForHumans() }}</td>
                <td data-label=""><a class="h2-btn h2-btn-primary" style="padding:.35rem 1rem;font-size:.85rem" href="{{ route('shipper.requests.show', $r->id) }}">View &amp; Quote</a></td>
            </tr>
        @empty<tr><td colspan="7">No open requests in your countries right now — check back soon.</td></tr>@endforelse
        </tbody>
    </table>
</div>
{{ $requests->links('home2.partials.pagination') }}
@endsection
