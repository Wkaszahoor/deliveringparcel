@extends('home2.layouts.app')
@section('title', 'My Reviews')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
            <h2 style="margin:0">My reviews</h2>
            <a href="{{ route('home2.reviews.index') }}" class="h2-btn h2-btn-outline" style="padding:.35rem 1rem">Review an order</a>
        </div>
        @if ($reviews->isEmpty())
            <p class="muted" style="margin-top:1rem">You haven't written any reviews yet.</p>
        @else
            <div style="margin-top:1rem">
                @foreach ($reviews as $r)
                    <div class="h2-card" style="margin-bottom:.75rem">
                        <div class="h2-card-body" style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap">
                            <div>
                                <div class="text-warning">{{ str_repeat('★', (int) $r->rating) . str_repeat('☆', 5 - (int) $r->rating) }}</div>
                                @if ($r->title)<strong>{{ $r->title }}</strong>@endif
                                <p class="muted mb-0" style="font-size:.9rem">{{ $r->body }}</p>
                            </div>
                            <div style="text-align:right">
                                <span class="h2-badge" style="background:{{ ['approved'=>'#137333','pending'=>'#8a6d3b','rejected'=>'#b3261e','hidden'=>'#5f6b7a','spam'=>'#b3261e'][$r->status] ?? '#5f6b7a' }};color:#fff">
                                    {{ ucfirst($r->status) }}
                                </span><br>
                                <small class="muted">{{ optional($r->created_at)->format('M d, Y') }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @include('home2.partials.pagination', ['paginator' => $reviews])
        @endif
    </div>
</section>
@endsection
