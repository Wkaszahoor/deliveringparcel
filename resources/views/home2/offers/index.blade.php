@extends('home2.layouts.app')
@section('title', 'My offers')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <h2>Offers on your requests</h2>
        @if ($orders->isEmpty())
            <p class="muted" style="margin-top:1rem">No requests yet — <a href="{{ route('home2.wizard.create') }}">start one</a>.</p>
        @else
            @foreach ($orders as $order)
                <div class="h2-card" style="margin-top:1rem">
                    <div class="h2-card-body">
                        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
                            <strong>Request #{{ $order->order_id }}</strong>
                            <span class="h2-badge">{{ $order->order_status ?: 'pending' }}</span>
                        </div>
                        <p class="muted small" style="margin:.4rem 0">{{ $order->shipfrom }} → {{ $order->shipto }} · {{ optional($order->created_at)->format('M d, Y') }}</p>

                        @forelse ($order->offers as $offer)
                            <div style="border-top:1px solid var(--h2-border,#e5e9f0);padding-top:.75rem;margin-top:.5rem">
                                <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
                                    <div>
                                        <strong>Offer #{{ $offer->id }}</strong> — <strong>${{ number_format((float) $offer->total, 2) }}</strong>
                                        @if ((int) $offer->offer_status === 1)<span class="h2-badge" style="background:#137333;color:#fff">accepted</span>
                                        @elseif ((int) $offer->offer_status === 2)<span class="h2-badge" style="background:#b3261e;color:#fff">rejected</span>
                                        @else<span class="h2-badge" style="background:#0b5fff;color:#fff">awaiting your reply</span>@endif
                                    </div>
                                    @if (!in_array((int) $offer->offer_status, [1, 2]))
                                        <div style="display:flex;gap:.5rem">
                                            <form method="POST" action="{{ route('home2.offers.accept', $offer->id) }}">
                                                @csrf
                                                <button class="h2-btn h2-btn-primary" style="padding:.35rem 1rem">Accept & pay</button>
                                            </form>
                                            <form method="POST" action="{{ route('home2.offers.reject', $offer->id) }}" onsubmit="var n=prompt('Reason (optional)'); if(n!==null)this.rejections_note.value=n; return true;">
                                                @csrf
                                                <input type="hidden" name="rejections_note" value="">
                                                <button class="h2-btn h2-btn-outline" style="padding:.35rem 1rem">Reject</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                @if ($offer->description)<p class="muted small" style="margin:.4rem 0 0">{{ $offer->description }}</p>@endif
                                @if ($offer->rejections_note)<p class="muted small" style="margin:.2rem 0 0">Your note: {{ $offer->rejections_note }}</p>@endif
                            </div>
                        @empty
                            <p class="muted small" style="margin:.5rem 0 0">Awaiting offer from our team…</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
            @include('home2.partials.pagination', ['paginator' => $orders])
        @endif
    </div>
</section>
@endsection
