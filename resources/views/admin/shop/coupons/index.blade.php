@extends('admin.layouts.app')
@section('title', 'Coupons')
@section('page_title', 'Coupons')
@section('page_subtitle', 'Discount codes with usage limits')

@section('content')
<div class="dp-toolbar">
    <input id="f-q" class="form-control form-control-sm" placeholder="Search code…" style="max-width:220px">
    <a href="{{ route('admin.shop.coupons.create') }}" class="btn btn-primary btn-sm ml-auto"><i class="fas fa-plus"></i> New Coupon</a>
</div>
@include('admin.partials.lazy-table', [
    'id' => 'sco',
    'url' => route('admin.shop.coupons.data'),
    'filterIds' => ['f-q'],
    'cols' => [
        ['text', 'Code', 'code'],
        ['text', 'Discount', 'value'],
        ['text', 'Usage', 'usage'],
        ['badge2', 'Active', 'active', 'badge-success', 'On', 'Off'],
        ['text', 'Ends', 'ends'],
    ],
    'acts' => [
        ['pen', 'Edit', 'edit'],
        ['trash', 'Delete coupon', 'delete', 'DELETE', 1, 'danger'],
    ],
])
@endsection
