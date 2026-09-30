@extends('home2.layouts.app')
@section('title', 'Write a Review')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <h2>Review your orders</h2>
        <p class="muted">Orders that reached {{ $eligibleLabel }} are eligible for one review each.</p>

        @if ($orders->isEmpty())
            <div class="h2-form"><p class="muted mb-0">No eligible orders yet — once an order is delivered you can review it here.</p></div>
        @else
            <div class="h2-table-wrap" style="margin-top:1rem">
                <table class="h2-table">
                    <thead><tr><th>Order</th><th>Date</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($orders as $o)
                            <tr>
                                <td data-label="Order"><strong>#{{ $o->order_id }}</strong></td>
                                <td data-label="Date">{{ optional($o->created_at)->format('M d, Y') }}</td>
                                <td data-label="Status">{{ $o->order_status ?: '—' }}</td>
                                <td data-label="Action">
                                    @if (!empty($o->review_done))
                                        <span class="muted small">Reviewed ✓</span>
                                    @else
                                        <a class="h2-btn h2-btn-outline" style="padding:.3rem 1rem" href="{{ route('home2.reviews.create', ['order' => $o->id]) }}">Write review</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('home2.partials.pagination', ['paginator' => $orders])
            <p style="margin-top:1.5rem"><a href="{{ route('home2.reviews.mine') }}" class="h2-btn h2-btn-outline">My published reviews</a></p>
        @endif
    </div>
</section>
@endsection
