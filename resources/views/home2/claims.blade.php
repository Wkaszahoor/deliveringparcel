@extends('home2.layouts.app')
@section('title', 'My Claims')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <h2>My claims</h2>
        @if ($claims->isEmpty())
            <p class="muted">You have no claims.</p>
        @else
            <div class="h2-table-wrap" style="margin-top:1rem">
                <table class="h2-table">
                    <thead><tr><th>#</th><th>Type</th><th>Amount</th><th>Status</th><th>Filed</th></tr></thead>
                    <tbody>
                        @foreach ($claims as $c)
                            @php $m = $statusMap[$c->status] ?? ['label' => $c->status, 'color' => '#6c757d']; @endphp
                            <tr>
                                <td data-label="#">#{{ $c->id }}</td>
                                <td data-label="Type">{{ config('admin_quotes.claim_types.' . $c->type, $c->type) }}</td>
                                <td data-label="Amount">${{ number_format((float) $c->amount_claimed, 2) }}</td>
                                <td data-label="Status"><span class="h2-badge" style="color:#fff; background:{{ $m['color'] }}">{{ $m['label'] }}</span></td>
                                <td data-label="Filed">{{ optional($c->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('home2.partials.pagination', ['paginator' => $claims])
        @endif
    </div>
</section>
@endsection
