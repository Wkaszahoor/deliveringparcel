@extends('home2.layouts.app')
@section('title', 'My Returns')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <h2>My return requests</h2>
        @if ($returns->isEmpty())
            <p class="muted">You have no return requests.</p>
        @else
            <div class="h2-table-wrap" style="margin-top:1rem">
                <table class="h2-table">
                    <thead><tr><th>#</th><th>Reason</th><th>Status</th><th>Requested</th></tr></thead>
                    <tbody>
                        @foreach ($returns as $r)
                            @php $m = $statusMap[$r->status] ?? ['label' => $r->status, 'color' => '#6c757d']; @endphp
                            <tr>
                                <td data-label="#">#{{ $r->id }}</td>
                                <td data-label="Reason">{{ $r->reason }}</td>
                                <td data-label="Status"><span class="h2-badge" style="color:#fff; background:{{ $m['color'] }}">{{ $m['label'] }}</span></td>
                                <td data-label="Requested">{{ optional($r->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('home2.partials.pagination', ['paginator' => $returns])
        @endif
    </div>
</section>
@endsection
