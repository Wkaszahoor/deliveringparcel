@extends('home2.layouts.app')
@section('title', 'Write a Review')

@section('content')
<section class="h2-section alt">
    <div class="h2-container">
        <h2>Review order #{{ $order->order_id }}</h2>
        <p class="muted">Placed {{ optional($order->created_at)->format('M d, Y') }} · reviews are moderated before publishing.</p>
        <form class="h2-form" method="POST" action="{{ route('home2.reviews.store') }}">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <label for="rv-type">Review type</label>
            <select id="rv-type" name="review_type" class="form-control">
                @foreach ($types as $key => $label)
                    <option value="{{ $key }}" {{ $key === 'order' ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <label for="rv-rating">Rating <span class="text-danger">*</span></label>
            <select id="rv-rating" name="rating" class="form-control" required>
                <option value="5">★★★★★ — Excellent</option>
                <option value="4">★★★★ — Good</option>
                <option value="3">★★★ — Okay</option>
                <option value="2">★★ — Poor</option>
                <option value="1">★ — Terrible</option>
            </select>

            <label for="rv-title">Title</label>
            <input id="rv-title" type="text" name="title" value="{{ old('title') }}" class="form-control" maxlength="120" placeholder="Sum it up in a few words">

            <label for="rv-body">Your review <span class="text-danger">*</span></label>
            <textarea id="rv-body" name="body" rows="5" class="form-control" required minlength="10" maxlength="2000" placeholder="What went well? What could be better?">{{ old('body') }}</textarea>

            <button class="h2-btn h2-btn-primary" type="submit" style="margin-top:1.25rem;width:100%">Submit for moderation</button>
        </form>
    </div>
</section>
@endsection
