@extends('shipper.layouts.app')
@section('title', 'My Quotes')

@section('content')
<h2 style="margin:8px 0">My Quotes</h2>
<div class="h2-table-wrap">
    <table class="h2-table">
        <thead><tr><th>Request</th><th>Amount</th><th>Days</th><th>Status</th><th>Admin response</th><th>When</th></tr></thead>
        <tbody>
        @forelse($quotes as $q)
            <tr>
                <td data-label="Request">{{ $q->request?->reference ?? '—' }}</td>
                <td data-label="Amount"><b>${{ number_format($q->quoted_amount, 2) }}</b></td>
                <td data-label="Days">{{ $q->estimated_days }}</td>
                <td data-label="Status"><span class="h2-badge">{{ $q->status }}</span></td>
                <td data-label="Response">{{ $q->admin_response ?? '—' }}</td>
                <td data-label="When">{{ $q->created_at->diffForHumans() }}</td>
            </tr>
        @empty<tr><td colspan="6">No quotes submitted yet.</td></tr>@endforelse
        </tbody>
    </table>
</div>
{{ $quotes->links('home2.partials.pagination') }}
@endsection
