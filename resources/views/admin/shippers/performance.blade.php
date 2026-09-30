@extends('admin.layouts.app')
@section('title', 'Shipper Performance')
@section('page_title', 'Shipper Performance')
@section('page_subtitle', 'Leaderboard, low-rating alerts, overdue purchases')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header"><h3 class="card-title">Top Shippers</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead><tr><th>Shipper</th><th>Rating</th><th>Done</th></tr></thead>
                    <tbody>
                    @forelse($top as $s)
                        <tr><td><a href="{{ route('admin.shippers.show', $s->id) }}">{{ $s->user->shipper_username ?? 'SHP' }}</a></td>
                            <td>⭐ {{ $s->rating }}</td><td>{{ $s->total_completed }}</td></tr>
                    @empty<tr><td colspan="3" class="text-muted text-center">No rated shippers yet.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-danger">
            <div class="card-header"><h3 class="card-title">Low Rating Alerts (&lt;3.5)</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    @forelse($lowRating as $s)
                        <tr><td><a href="{{ route('admin.shippers.show', $s->id) }}">{{ $s->user->shipper_username ?? 'SHP' }}</a></td>
                            <td class="text-danger">⭐ {{ $s->rating }}</td><td>{{ $s->total_ratings }} ratings</td></tr>
                    @empty<tr><td colspan="3" class="text-muted text-center">None — good news.</td></tr>@endforelse
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header"><h3 class="card-title">Overdue Purchases (Buy-for-Me)</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    @forelse($overdue as $a)
                        <tr><td>Order #{{ $a->order_id }}</td>
                            <td>{{ $a->shipper->user->shipper_username ?? 'SHP' }}</td>
                            <td><a href="{{ route('admin.shipper-assignments.show', $a->id) }}" class="btn btn-xs btn-danger">Open</a></td></tr>
                    @empty<tr><td colspan="3" class="text-muted text-center">None overdue.</td></tr>@endforelse
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
