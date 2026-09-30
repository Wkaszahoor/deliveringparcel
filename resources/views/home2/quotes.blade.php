@extends('home2.layouts.app')
@section('title', 'My Quotes')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <h2>My quote requests</h2>
        @if ($quotes->isEmpty())
            <p class="muted">You have no quote requests. <a href="{{ url('freequote') }}">Request one here</a>.</p>
        @else
            <div class="h2-table-wrap" style="margin-top:1rem">
                <table class="h2-table">
                    <thead><tr><th>#</th><th>Route</th><th>Weight</th><th>Cargo</th><th>Requested</th></tr></thead>
                    <tbody>
                        @foreach ($quotes as $q)
                            <tr>
                                <td data-label="#">#{{ $q->id }}</td>
                                <td data-label="Route">{{ $q->country }} → {{ $q->destination }}</td>
                                <td data-label="Weight">{{ $q->weight ?: '—' }}</td>
                                <td data-label="Cargo">{{ $q->cargotype ?: '—' }}</td>
                                <td data-label="Requested">{{ optional($q->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('home2.partials.pagination', ['paginator' => $quotes])
        @endif
    </div>
</section>
@endsection
