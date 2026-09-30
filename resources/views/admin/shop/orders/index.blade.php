@extends('admin.layouts.app')
@section('title', 'Shop Orders')
@section('page_title', 'Shop Orders')
@section('page_subtitle', 'Purchase orders with status management')

@section('content')
<div class="dp-toolbar">
    <input id="f-q" class="form-control form-control-sm" placeholder="Search code/user…" style="max-width:240px">
    <select id="f-status" class="custom-select custom-select-sm" style="max-width:160px">
        <option value="">Status: all</option>
        @foreach ($statusMap as $key => $m)<option value="{{ $key }}">{{ $m['label'] }}</option>@endforeach
    </select>
    <a href="{{ route('admin.shop.analytics') }}" class="btn btn-outline-primary btn-sm ml-auto"><i class="fas fa-chart-bar"></i> Analytics</a>
</div>
@include('admin.partials.lazy-table', [
    'id' => 'so',
    'url' => route('admin.shop.orders.data'),
    'filterIds' => ['f-q', 'f-status'],
    'cols' => [
        ['text', 'Code', 'code'],
        ['sub', 'Customer', 'user', 'email'],
        ['money', 'Total', 'total'],
        ['text', 'Items', 'items'],
        ['badge', 'Status', 'status', 'color'],
        ['text', 'Date', 'at'],
    ],
    'acts' => [
        ['eye', 'View', 'show'],
    ],
])
@endsection
