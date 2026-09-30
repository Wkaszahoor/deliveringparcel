@extends('shipper.layouts.app')
@section('title', 'My Assignments')

@section('content')
<h2 style="margin:8px 0">My Assignments</h2>
<div class="h2-table-wrap">
    <table class="h2-table">
        <thead><tr><th>Order</th><th>Status</th><th>Your fee</th><th>Started</th><th></th></tr></thead>
        <tbody>
        @forelse($assignments as $a)
            <tr>
                <td data-label="Order"><b>#{{ $a->order_id }}</b></td>
                <td data-label="Status">{{ str_replace('_', ' ', $a->status) }}
                    @if($a->purchase_deadline && $a->isPurchaseOverdue())<span class="h2-badge" style="background:#fdecea;color:#b3261e">OVERDUE</span>@endif</td>
                <td data-label="Fee">${{ number_format($a->shipper_fee, 2) }}</td>
                <td data-label="Started">{{ $a->created_at->diffForHumans() }}</td>
                <td data-label=""><a class="h2-btn h2-btn-primary" style="padding:.35rem 1rem;font-size:.85rem" href="{{ route('shipper.assignments.show', $a->id) }}">Work</a></td>
            </tr>
        @empty<tr><td colspan="5">No assignments yet.</td></tr>@endforelse
        </tbody>
    </table>
</div>
{{ $assignments->links('home2.partials.pagination') }}
@endsection
