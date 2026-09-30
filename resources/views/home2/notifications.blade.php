@extends('home2.layouts.app')
@section('title', 'Notifications')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap">
            <h2 style="margin:0">Notifications</h2>
            @if (auth()->user()->unreadNotifications()->count())
                <form method="POST" action="{{ route('home2.notifications.read-all') }}">
                    @csrf
                    <button class="h2-btn h2-btn-outline" type="submit" style="padding:.35rem 1rem">Mark all read</button>
                </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <p class="muted" style="margin-top:1rem">No notifications.</p>
        @else
            <div style="margin-top:1rem">
                @foreach ($notifications as $n)
                    <div class="h2-card" style="margin-bottom:.75rem">
                        <div class="h2-card-body" style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; {{ $n->read_at ? 'opacity:.7' : '' }}">
                            <div>
                                <strong>{{ class_basename($n->type) }}</strong><br>
                                <span class="muted">{{ $n->data['message'] ?? $n->data['title'] ?? json_encode($n->data) }}</span>
                            </div>
                            <small class="muted">{{ optional($n->created_at)->format('M d, Y H:i') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
            @include('home2.partials.pagination', ['paginator' => $notifications])
        @endif
    </div>
</section>
@endsection
