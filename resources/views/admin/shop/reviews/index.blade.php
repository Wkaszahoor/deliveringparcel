@extends('admin.layouts.app')
@section('title', 'Reviews')
@section('page_title', 'Product Reviews')
@section('page_subtitle', 'Moderation queue with rating filters')

@section('content')
<div class="dp-kpi-grid mb-3">
    <div class="dp-kpi"><span class="dp-kpi-icon bg-info"><i class="fas fa-star"></i></span><div class="dp-kpi-label">Avg rating</div><div class="dp-kpi-value">{{ number_format($stats['avg'], 2) }}</div></div>
    <div class="dp-kpi"><span class="dp-kpi-icon bg-warning"><i class="fas fa-hourglass-half"></i></span><div class="dp-kpi-label">Pending</div><div class="dp-kpi-value">{{ $stats['pending'] }}</div></div>
    @foreach ($stats['dist'] as $stars => $count)
        <div class="dp-kpi"><span class="dp-kpi-icon bg-secondary"><i class="fas fa-star"></i></span><div class="dp-kpi-label">{{ $stars }} star</div><div class="dp-kpi-value">{{ $count }}</div></div>
    @endforeach
</div>

<div class="dp-toolbar">
    <input id="f-q" class="form-control form-control-sm" placeholder="Search review/product…" style="max-width:240px">
    <select id="f-rating" class="custom-select custom-select-sm" style="max-width:130px">
        <option value="">Rating: all</option><option value="5">5 ★</option><option value="4">4 ★</option><option value="3">3 ★</option><option value="2">2 ★</option><option value="1">1 ★</option>
    </select>
    <select id="f-approved" class="custom-select custom-select-sm" style="max-width:150px">
        <option value="">State: all</option><option value="1">Approved</option><option value="0">Pending</option>
    </select>
</div>
@include('admin.partials.lazy-table', [
    'id' => 'sr',
    'url' => route('admin.shop.reviews.data'),
    'filterIds' => ['f-q', 'f-rating', 'f-approved'],
    'cols' => [
        ['text', 'Product', 'product'],
        ['text', 'User', 'user'],
        ['badge', 'Rating', 'rating', 'bg-info'],
        ['text', 'Title', 'title'],
        ['text', 'Review', 'body'],
        ['badge2', 'Approved', 'approved', 'badge-success', 'Yes', 'No'],
        ['text', 'Date', 'at'],
    ],
    'acts' => [
        ['check', 'Approve', 'approve', 'PUT', 0, 'success'],
        ['times', 'Unapprove', 'reject', 'PUT', 0, 'warning'],
        ['trash', 'Delete review', 'delete', 'DELETE', 1, 'danger'],
    ],
])
@endsection
